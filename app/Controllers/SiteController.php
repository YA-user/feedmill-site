<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Repository;

final class SiteController extends Controller
{
    public function home(): void
    {
        $this->view('site/home', [
            'title' => 'Главная',
            'page' => Repository::page('home'),
            'news' => Repository::latestNews(3),
            'products' => Repository::products(null, true),
            'categories' => Repository::categories(),
        ]);
    }

    public function newsIndex(): void
    {
        $this->view('site/news/index', ['title' => 'Новости компании', 'news' => Repository::news()]);
    }

    public function newsShow(string $idOrSlug): void
    {
        $item = Repository::findNews($idOrSlug);
        if (!$item) {
            $this->notFound('Новость не найдена');
            return;
        }
        $this->view('site/news/show', ['title' => $item['title'], 'item' => $item]);
    }

    public function page(string $slug): void
    {
        $page = Repository::page($slug);
        if (!$page) {
            $this->notFound('Страница не найдена');
            return;
        }
        $this->view('site/page', ['title' => $page['title'], 'page' => $page]);
    }

    public function products(?int $categoryId = null): void
    {
        $this->view('site/products/index', [
            'title' => 'Продукция',
            'categories' => Repository::categories(),
            'products' => Repository::products($categoryId, true),
            'currentCategoryId' => $categoryId,
        ]);
    }

    public function productShow(string $idOrSlug): void
    {
        $product = Repository::findProduct($idOrSlug);
        if (!$product) {
            $this->notFound('Продукция не найдена');
            return;
        }
        $this->view('site/products/show', [
            'title' => $product['title'],
            'product' => $product,
            'recommendations' => Repository::recommendationsForProduct((int)$product['id']),
        ]);
    }

    public function partners(): void
    {
        $this->view('site/partners', ['title' => 'Наши партнеры', 'partners' => Repository::partners()]);
    }

    public function contacts(): void
    {
        $this->view('site/contacts', ['title' => 'Контакты и схема проезда', 'contacts' => Repository::contacts()]);
    }

    public function useful(?int $categoryId = null): void
    {
        $this->view('site/useful/index', [
            'title' => 'Полезная информация',
            'categories' => Repository::usefulCategories(),
            'articles' => Repository::usefulArticles($categoryId),
            'currentCategoryId' => $categoryId,
        ]);
    }

    public function usefulShow(string $idOrSlug): void
    {
        $article = Repository::findUsefulArticle($idOrSlug);
        if (!$article) {
            $this->notFound('Материал не найден');
            return;
        }
        $this->view('site/useful/show', ['title' => $article['title'], 'article' => $article]);
    }

    public function orderForm(?int $productId = null): void
    {
        $selectedProduct = null;
        if ($productId !== null) {
            $selectedProduct = Repository::findProduct((string)$productId);
        }

        $this->view('site/order/form', [
            'title' => 'Заявка на комбикорм',
            'products' => Repository::products(null, true),
            'selectedProduct' => $selectedProduct,
            'errors' => [],
            'old' => [],
        ]);
    }

    public function orderSubmit(): void
    {
        if (!\verify_csrf()) {
            \flash('error', 'Проверка формы не пройдена. Обновите страницу и попробуйте снова.');
            \redirect('/order');
        }

        $old = [
            'items' => [],
            'full_name' => trim((string)($_POST['full_name'] ?? '')),
            'phone' => trim((string)($_POST['phone'] ?? '')),
            'email' => trim((string)($_POST['email'] ?? '')),
            'comment' => trim((string)($_POST['comment'] ?? '')),
        ];

        $errors = [];
        $items = [];
        $productIds = $_POST['product_id'] ?? [];
        $quantities = $_POST['quantity'] ?? [];
        if (!is_array($productIds)) {
            $productIds = [$productIds];
        }
        if (!is_array($quantities)) {
            $quantities = [$quantities];
        }

        $max = max(count($productIds), count($quantities));
        for ($i = 0; $i < $max; $i++) {
            $productId = (int)($productIds[$i] ?? 0);
            $quantityText = str_replace(',', '.', trim((string)($quantities[$i] ?? '')));

            // Полностью пустые добавленные строки пропускаем.
            if ($productId === 0 && $quantityText === '') {
                continue;
            }

            $old['items'][] = ['product_id' => $productId, 'quantity' => $quantityText];
            $rowNumber = count($old['items']);
            $product = $productId > 0 ? Repository::findProduct((string)$productId) : null;
            if (!$product) {
                $errors[] = 'Выберите продукцию в строке №' . $rowNumber . '.';
                continue;
            }

            $quantity = (float)$quantityText;
            if ($quantity <= 0) {
                $errors[] = 'Укажите количество больше 0 в строке №' . $rowNumber . '.';
                continue;
            }

            $price = (float)$product['price'];
            $items[] = [
                'product_id' => (int)$product['id'],
                'product_title' => $product['title'],
                'price' => $price,
                'quantity' => $quantity,
                'unit' => $product['unit'] ?: 'кг',
                'total' => $price * $quantity,
            ];
        }

        if (!$items && !$errors) {
            $errors[] = 'Добавьте хотя бы один вид корма в заявку.';
        }
        if ($old['full_name'] === '') {
            $errors[] = 'Введите ФИО или название организации.';
        }
        if ($old['phone'] === '' || !preg_match('/^[0-9+()\-\s]{6,25}$/u', $old['phone'])) {
            $errors[] = 'Введите корректный контактный телефон.';
        }
        if ($old['email'] !== '' && !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Введите корректный email или оставьте поле пустым.';
        }

        if ($errors) {
            $this->view('site/order/form', [
                'title' => 'Заявка на комбикорм',
                'products' => Repository::products(null, true),
                'selectedProduct' => null,
                'errors' => $errors,
                'old' => $old,
            ]);
            return;
        }

        $total = array_reduce($items, fn (float $sum, array $item): float => $sum + (float)$item['total'], 0.0);
        $orderId = Repository::createOrder([
            'items' => $items,
            'full_name' => $old['full_name'],
            'phone' => $old['phone'],
            'email' => $old['email'],
            'comment' => $old['comment'],
        ]);

        $this->notifyAdmin($orderId, $items, $old, $total);
        \flash('success', 'Заявка №' . $orderId . ' принята. Менеджер свяжется с вами.');
        \redirect('/order/success');
    }

    public function orderSuccess(): void
    {
        $this->view('site/order/success', ['title' => 'Заявка отправлена']);
    }

    public function sitemap(): void
    {
        $this->view('site/sitemap', [
            'title' => 'Карта сайта',
            'categories' => Repository::categories(),
            'articles' => Repository::usefulArticles(),
        ]);
    }

    private function notifyAdmin(int $orderId, array $items, array $old, float $total): void
    {
        $lines = [];
        foreach ($items as $item) {
            $lines[] = '- ' . $item['product_title'] . ': ' . $item['quantity'] . ' ' . $item['unit'] . ' × ' . \format_money($item['price']) . ' = ' . \format_money($item['total']);
        }
        $message = "Новая заявка №{$orderId}\n" .
            "Состав заказа:\n" . implode("\n", $lines) . "\n" .
            "Сумма: " . \format_money($total) . "\n" .
            "Клиент: {$old['full_name']}\n" .
            "Телефон: {$old['phone']}\n" .
            "Email: {$old['email']}\n" .
            "Комментарий: {$old['comment']}\n";

        $logLine = "\n--- " . date('Y-m-d H:i:s') . " ---\n" . $message;
        file_put_contents(\config('mail_log'), $logLine, FILE_APPEND);
        @mail((string)\config('admin_email'), 'Новая заявка с сайта АгроКорм', $message);
    }

}
