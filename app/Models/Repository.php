<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Repository
{
    private static function pdo(): PDO
    {
        return Database::pdo();
    }

    private static function localize(?array $row, array $fields): ?array
    {
        return $row ? \localize_row($row, $fields) : null;
    }

    private static function localizeMany(array $rows, array $fields): array
    {
        return array_map(fn (array $row): array => \localize_row($row, $fields), $rows);
    }

    public static function page(string $slug): ?array
    {
        $stmt = self::pdo()->prepare('SELECT * FROM pages WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $page = $stmt->fetch();
        return self::localize($page ?: null, ['title', 'body']);
    }

    public static function latestNews(int $limit = 5): array
    {
        $stmt = self::pdo()->prepare('SELECT * FROM news WHERE is_active = 1 ORDER BY published_at DESC, id DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return self::localizeMany($stmt->fetchAll(), ['title', 'announce', 'body']);
    }

    public static function news(): array
    {
        return self::localizeMany(
            self::pdo()->query('SELECT * FROM news WHERE is_active = 1 ORDER BY published_at DESC, id DESC')->fetchAll(),
            ['title', 'announce', 'body']
        );
    }

    public static function findNews(string $idOrSlug): ?array
    {
        $field = ctype_digit($idOrSlug) ? 'id' : 'slug';
        $stmt = self::pdo()->prepare("SELECT * FROM news WHERE $field = :value AND is_active = 1 LIMIT 1");
        $stmt->execute(['value' => $idOrSlug]);
        $row = $stmt->fetch();
        return self::localize($row ?: null, ['title', 'announce', 'body']);
    }

    public static function categories(): array
    {
        return self::localizeMany(
            self::pdo()->query('SELECT * FROM product_categories ORDER BY sort_order, name')->fetchAll(),
            ['name', 'description']
        );
    }

    public static function products(?int $categoryId = null, bool $activeOnly = true): array
    {
        $sql = 'SELECT p.*, c.name AS category_name, c.name_en AS category_name_en FROM products p LEFT JOIN product_categories c ON c.id = p.category_id WHERE 1=1';
        $params = [];
        if ($categoryId !== null) {
            $sql .= ' AND p.category_id = :category_id';
            $params['category_id'] = $categoryId;
        }
        if ($activeOnly) {
            $sql .= ' AND p.is_active = 1';
        }
        $sql .= ' ORDER BY c.sort_order, p.title';
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return self::localizeMany($stmt->fetchAll(), ['title', 'description', 'recommendation', 'unit', 'category_name']);
    }

    public static function findProduct(string $idOrSlug): ?array
    {
        $field = ctype_digit($idOrSlug) ? 'p.id' : 'p.slug';
        $stmt = self::pdo()->prepare("SELECT p.*, c.name AS category_name, c.name_en AS category_name_en FROM products p LEFT JOIN product_categories c ON c.id = p.category_id WHERE $field = :value AND p.is_active = 1 LIMIT 1");
        $stmt->execute(['value' => $idOrSlug]);
        $row = $stmt->fetch();
        return self::localize($row ?: null, ['title', 'description', 'recommendation', 'unit', 'category_name']);
    }

    public static function recommendationsForProduct(int $productId): array
    {
        $stmt = self::pdo()->prepare('SELECT * FROM recommendations WHERE product_id = :id ORDER BY id DESC');
        $stmt->execute(['id' => $productId]);
        return self::localizeMany($stmt->fetchAll(), ['title', 'body']);
    }

    public static function partners(): array
    {
        return self::localizeMany(
            self::pdo()->query('SELECT * FROM partners ORDER BY name')->fetchAll(),
            ['name', 'description']
        );
    }

    public static function contacts(): array
    {
        return self::localizeMany(
            self::pdo()->query('SELECT * FROM contacts ORDER BY id')->fetchAll(),
            ['title', 'address', 'work_time']
        );
    }

    public static function usefulCategories(): array
    {
        return self::localizeMany(
            self::pdo()->query('SELECT * FROM useful_categories ORDER BY name')->fetchAll(),
            ['name', 'description']
        );
    }

    public static function usefulArticles(?int $categoryId = null): array
    {
        $sql = 'SELECT a.*, c.name AS category_name, c.name_en AS category_name_en FROM useful_articles a LEFT JOIN useful_categories c ON c.id = a.category_id WHERE a.is_active = 1';
        $params = [];
        if ($categoryId !== null) {
            $sql .= ' AND a.category_id = :category_id';
            $params['category_id'] = $categoryId;
        }
        $sql .= ' ORDER BY a.published_at DESC, a.id DESC';
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return self::localizeMany($stmt->fetchAll(), ['title', 'announce', 'body', 'category_name']);
    }

    public static function findUsefulArticle(string $idOrSlug): ?array
    {
        $field = ctype_digit($idOrSlug) ? 'a.id' : 'a.slug';
        $stmt = self::pdo()->prepare("SELECT a.*, c.name AS category_name, c.name_en AS category_name_en FROM useful_articles a LEFT JOIN useful_categories c ON c.id = a.category_id WHERE $field = :value AND a.is_active = 1 LIMIT 1");
        $stmt->execute(['value' => $idOrSlug]);
        $row = $stmt->fetch();
        return self::localize($row ?: null, ['title', 'announce', 'body', 'category_name']);
    }

    public static function media(?string $type = null, ?int $limit = null): array
    {
        $sql = 'SELECT * FROM media WHERE is_active = 1';
        $params = [];
        if ($type !== null) {
            $sql .= ' AND type = :type';
            $params['type'] = $type;
        }
        $sql .= ' ORDER BY sort_order ASC, id DESC';
        if ($limit !== null) {
            $sql .= ' LIMIT :limit';
        }
        $stmt = self::pdo()->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function createOrder(array $data): int
    {
        $pdo = self::pdo();
        $pdo->beginTransaction();
        try {
            $items = $data['items'];
            $first = $items[0];
            $summary = count($items) === 1
                ? $first['product_title']
                : 'Составная заявка: ' . count($items) . ' позиций';
            $totalQuantity = array_reduce($items, fn (float $sum, array $item): float => $sum + (float)$item['quantity'], 0.0);
            $total = array_reduce($items, fn (float $sum, array $item): float => $sum + (float)$item['total'], 0.0);

            $stmt = $pdo->prepare('INSERT INTO orders (product_id, product_title, price, quantity, total, full_name, phone, email, comment, status, created_at) VALUES (:product_id, :product_title, :price, :quantity, :total, :full_name, :phone, :email, :comment, :status, :created_at)');
            $stmt->execute([
                'product_id' => count($items) === 1 ? $first['product_id'] : null,
                'product_title' => $summary,
                'price' => count($items) === 1 ? $first['price'] : 0,
                'quantity' => $totalQuantity,
                'total' => $total,
                'full_name' => $data['full_name'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'comment' => $data['comment'],
                'status' => 'new',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $orderId = (int)$pdo->lastInsertId();

            $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_title, price, quantity, unit, total) VALUES (:order_id, :product_id, :product_title, :price, :quantity, :unit, :total)');
            foreach ($items as $item) {
                $itemStmt->execute([
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'product_title' => $item['product_title'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'] ?: 'кг',
                    'total' => $item['total'],
                ]);
            }

            $pdo->commit();
            return $orderId;
        } catch (\Throwable $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }

    public static function ordersWithItems(): array
    {
        $orders = self::pdo()->query('SELECT * FROM orders ORDER BY created_at DESC, id DESC')->fetchAll();
        if (!$orders) {
            return [];
        }
        $ids = array_map(fn (array $order): int => (int)$order['id'], $orders);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = self::pdo()->prepare('SELECT * FROM order_items WHERE order_id IN (' . $placeholders . ') ORDER BY id ASC');
        $stmt->execute($ids);
        $itemsByOrder = [];
        foreach ($stmt->fetchAll() as $item) {
            $itemsByOrder[(int)$item['order_id']][] = $item;
        }
        foreach ($orders as &$order) {
            $order['items'] = $itemsByOrder[(int)$order['id']] ?? [];
        }
        unset($order);
        return $orders;
    }

    public static function counts(): array
    {
        $tables = ['news', 'products', 'orders', 'partners', 'useful_articles', 'media'];
        $result = [];
        foreach ($tables as $table) {
            $result[$table] = (int)self::pdo()->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        }
        return $result;
    }
}
