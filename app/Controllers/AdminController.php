<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Repository;
use PDO;

final class AdminController extends Controller
{
    public function login(): void
    {
        if (Auth::check()) {
            \redirect('/admin');
        }

        $errors = [];
        if (\is_post()) {
            if (!\verify_csrf()) {
                $errors[] = 'Проверка формы не пройдена.';
            } else {
                $email = trim((string)($_POST['email'] ?? ''));
                $password = (string)($_POST['password'] ?? '');
                if (Auth::attempt($email, $password)) {
                    \flash('success', 'Добро пожаловать в панель администратора.');
                    \redirect('/admin');
                }
                $errors[] = 'Неверный email или пароль.';
            }
        }

        $this->view('admin/login', ['title' => 'Вход в админ-панель', 'errors' => $errors], 'plain');
    }

    public function logout(): void
    {
        Auth::logout();
        \flash('success', 'Вы вышли из панели администратора.');
        \redirect('/admin/login');
    }

    public function dashboard(): void
    {
        Auth::require();
        $this->view('admin/dashboard', ['title' => 'Панель администратора', 'counts' => Repository::counts()], 'admin');
    }

    public function list(string $sectionKey): void
    {
        Auth::require();
        $section = $this->section($sectionKey);
        $this->requireSectionAccess($sectionKey);
        $pdo = Database::pdo();
        $sql = 'SELECT * FROM ' . $section['table'];
        if (!empty($section['where'])) {
            $sql .= ' WHERE ' . $section['where'];
        }
        $sql .= ' ORDER BY ' . $section['order'];
        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll();
        $this->view('admin/crud/list', compact('sectionKey', 'section', 'rows'), 'admin');
    }

    public function create(string $sectionKey): void
    {
        Auth::require();
        $section = $this->section($sectionKey);
        $this->requireSectionAccess($sectionKey);
        $row = [];
        $errors = [];

        if (\is_post()) {
            if (!\verify_csrf()) {
                $errors[] = 'Проверка формы не пройдена.';
            } else {
                [$data, $errors] = $this->collectData($section, $row);
                if (!$errors) {
                    $this->insert($section['table'], $data);
                    \flash('success', 'Запись добавлена.');
                    \redirect('/admin/' . $sectionKey);
                }
            }
        }

        $this->view('admin/crud/form', compact('sectionKey', 'section', 'row', 'errors'), 'admin');
    }

    public function edit(string $sectionKey, int $id): void
    {
        Auth::require();
        $section = $this->section($sectionKey);
        $this->requireSectionAccess($sectionKey);
        $row = $this->findRow($section['table'], $id);
        if (!$row) {
            $this->notFoundAdmin('Запись не найдена');
            return;
        }
        $errors = [];

        if (\is_post()) {
            if (!\verify_csrf()) {
                $errors[] = 'Проверка формы не пройдена.';
            } else {
                [$data, $errors] = $this->collectData($section, $row);
                if (!$errors) {
                    $this->update($section['table'], $id, $data);
                    \flash('success', 'Изменения сохранены.');
                    \redirect('/admin/' . $sectionKey);
                }
                $row = array_merge($row, $data);
            }
        }

        $this->view('admin/crud/form', compact('sectionKey', 'section', 'row', 'errors'), 'admin');
    }

    public function delete(string $sectionKey, int $id): void
    {
        Auth::require();
        $this->requireSectionAccess($sectionKey);
        if (!\verify_csrf()) {
            \flash('error', 'Проверка формы не пройдена.');
            \redirect('/admin/' . $sectionKey);
        }
        $section = $this->section($sectionKey);
        $deleteSql = 'DELETE FROM ' . $section['table'] . ' WHERE id = :id';
        if (!empty($section['where'])) {
            $deleteSql .= ' AND ' . $section['where'];
        }
        $stmt = Database::pdo()->prepare($deleteSql);
        try {
            $stmt->execute(['id' => $id]);
            \flash('success', 'Запись удалена.');
        } catch (\Throwable $exception) {
            \flash('error', 'Не удалось удалить запись: ' . $exception->getMessage());
        }
        \redirect('/admin/' . $sectionKey);
    }

    public function orders(): void
    {
        Auth::require();
        $this->requireSectionAccess('orders');
        $pdo = Database::pdo();
        if (\is_post() && \verify_csrf()) {
            $id = (int)($_POST['id'] ?? 0);
            $status = (string)($_POST['status'] ?? 'new');
            if (in_array($status, ['new', 'processing', 'done', 'cancelled'], true)) {
                $stmt = $pdo->prepare('UPDATE orders SET status = :status WHERE id = :id');
                $stmt->execute(['status' => $status, 'id' => $id]);
                \flash('success', 'Статус заявки обновлен.');
            }
            \redirect('/admin/orders');
        }
        $orders = Repository::ordersWithItems();
        $this->view('admin/orders', ['title' => 'Заявки', 'orders' => $orders], 'admin');
    }

    public function users(): void
    {
        Auth::require();
        if (!Auth::hasRole('admin')) {
            \flash('error', 'Управление пользователями доступно только главному администратору.');
            \redirect('/admin');
        }

        $pdo = Database::pdo();
        $errors = [];
        $editId = (int)($_GET['edit'] ?? 0);
        $editing = $editId > 0 ? $this->findRow('users', $editId) : null;

        if (\is_post()) {
            if (!\verify_csrf()) {
                $errors[] = 'Проверка формы не пройдена.';
            } else {
                $id = (int)($_POST['id'] ?? 0);
                $name = trim((string)($_POST['name'] ?? ''));
                $email = strtolower(trim((string)($_POST['email'] ?? '')));
                $role = (string)($_POST['role'] ?? 'content_manager');
                $active = isset($_POST['active']) ? 1 : 0;
                $password = (string)($_POST['password'] ?? '');
                $permissions = array_values(array_intersect(
                    array_map('strval', (array)($_POST['permissions'] ?? [])),
                    array_keys($this->permissionSections())
                ));

                if ($name === '') {
                    $errors[] = 'Введите имя пользователя.';
                }
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = 'Введите корректный email.';
                }
                if (!in_array($role, ['admin', 'content_manager'], true)) {
                    $errors[] = 'Выберите роль.';
                }
                if ($id === 0 && strlen($password) < 6) {
                    $errors[] = 'Для нового пользователя пароль должен быть не короче 6 символов.';
                }

                if (!$errors) {
                    if ($id > 0) {
                        $fields = 'name = :name, email = :email, role = :role, active = :active';
                        $params = compact('name', 'email', 'role', 'active') + ['id' => $id];
                        if ($password !== '') {
                            $fields .= ', password_hash = :password_hash';
                            $params['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
                        }
                        $stmt = $pdo->prepare('UPDATE users SET ' . $fields . ' WHERE id = :id');
                        $stmt->execute($params);
                        $this->syncUserPermissions($id, $role === 'content_manager' ? $permissions : []);
                        \flash('success', 'Пользователь обновлен.');
                    } else {
                        $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role, active, created_at) VALUES (:name, :email, :password_hash, :role, :active, :created_at)');
                        $stmt->execute([
                            'name' => $name,
                            'email' => $email,
                            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                            'role' => $role,
                            'active' => $active,
                            'created_at' => date('Y-m-d H:i:s'),
                        ]);
                        $this->syncUserPermissions((int)$pdo->lastInsertId(), $role === 'content_manager' ? $permissions : []);
                        \flash('success', 'Пользователь создан.');
                    }
                    \redirect('/admin/users');
                }
            }
        }

        if (isset($_POST['delete_user']) && \verify_csrf()) {
            $deleteId = (int)$_POST['delete_user'];
            if ($deleteId !== (int)(Auth::user()['id'] ?? 0)) {
                $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
                $stmt->execute(['id' => $deleteId]);
                \flash('success', 'Пользователь удален.');
            }
            \redirect('/admin/users');
        }

        $users = $pdo->query('SELECT id, name, email, role, active, created_at FROM users ORDER BY id')->fetchAll();
        $permissionSections = $this->permissionSections();
        $editingPermissions = $editing ? $this->loadUserPermissions((int)$editing['id']) : [];
        $this->view('admin/users', compact('users', 'editing', 'errors', 'permissionSections', 'editingPermissions'), 'admin');
    }

    public function sectionExists(string $sectionKey): bool
    {
        return isset($this->sections()[$sectionKey]);
    }

    private function sections(): array
    {
        return [
            'news' => [
                'title' => 'Новости', 'table' => 'news', 'order' => 'published_at DESC, id DESC', 'slugFrom' => 'title',
                'description' => 'Новости компании на сайте: заголовок, краткий текст, полный текст, фото и видео.',
                'columns' => ['title' => 'Заголовок', 'image' => 'Фото', 'video_url' => 'Видео', 'published_at' => 'Дата', 'is_active' => 'Опубликовано'],
                'fields' => [
                    ['name' => 'title', 'label' => 'Заголовок', 'type' => 'text', 'required' => true],
                    ['name' => 'title_en', 'label' => 'Заголовок (EN)', 'type' => 'text'],
                    ['name' => 'slug', 'label' => 'URL-адрес', 'type' => 'text', 'hint' => 'Можно оставить пустым — заполнится автоматически.'],
                    ['name' => 'announce', 'label' => 'Краткий текст для списка новостей', 'type' => 'textarea', 'required' => true],
                    ['name' => 'announce_en', 'label' => 'Краткий текст (EN)', 'type' => 'textarea'],
                    ['name' => 'body', 'label' => 'Полный текст новости', 'type' => 'wysiwyg', 'required' => true],
                    ['name' => 'body_en', 'label' => 'Полный текст новости (EN)', 'type' => 'wysiwyg'],
                    ['name' => 'image', 'label' => 'Фото новости', 'type' => 'file_image', 'hint' => 'Если фото не загрузить, сайт покажет стандартную картинку.'],
                    ['name' => 'video_url', 'label' => 'Видео новости, URL', 'type' => 'url', 'hint' => 'Если ссылку не указать, сайт покажет стандартный видеоблок.', 'placeholder' => 'https://www.youtube.com/watch?v=...'],
                    ['name' => 'published_at', 'label' => 'Дата публикации', 'type' => 'date', 'required' => true],
                    ['name' => 'is_active', 'label' => 'Опубликовано', 'type' => 'checkbox'],
                ],
            ],
            'pages' => [
                'title' => 'Страницы', 'table' => 'pages', 'order' => 'id ASC', 'slugFrom' => 'title',
                'description' => 'Основные текстовые страницы сайта: о компании, история, сертификация, производство.',
                'columns' => ['slug' => 'Ключ', 'title' => 'Название'],
                'fields' => [
                    ['name' => 'title', 'label' => 'Название', 'type' => 'text', 'required' => true],
                    ['name' => 'title_en', 'label' => 'Название (EN)', 'type' => 'text'],
                    ['name' => 'slug', 'label' => 'Ключ страницы', 'type' => 'text', 'required' => true, 'hint' => 'Например: history, certification, production.'],
                    ['name' => 'body', 'label' => 'Содержимое', 'type' => 'wysiwyg', 'required' => true],
                    ['name' => 'body_en', 'label' => 'Содержимое (EN)', 'type' => 'wysiwyg'],
                ],
            ],
            'contacts' => [
                'title' => 'Контакты', 'table' => 'contacts', 'order' => 'id ASC',
                'description' => 'Контактные данные, которые показываются на странице “Контакты и схема проезда”.',
                'columns' => ['title' => 'Название', 'phone' => 'Телефон', 'email' => 'Email'],
                'fields' => [
                    ['name' => 'title', 'label' => 'Название офиса/отдела', 'type' => 'text', 'required' => true],
                    ['name' => 'title_en', 'label' => 'Название офиса/отдела (EN)', 'type' => 'text'],
                    ['name' => 'address', 'label' => 'Адрес', 'type' => 'text', 'required' => true],
                    ['name' => 'address_en', 'label' => 'Адрес (EN)', 'type' => 'text'],
                    ['name' => 'phone', 'label' => 'Телефон', 'type' => 'text', 'required' => true],
                    ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                    ['name' => 'work_time', 'label' => 'Время работы', 'type' => 'text'],
                    ['name' => 'work_time_en', 'label' => 'Время работы (EN)', 'type' => 'text'],
                    ['name' => 'map_url', 'label' => 'Yandex map iframe URL', 'type' => 'text'],
                ],
            ],
            'partners' => [
                'title' => 'Партнеры', 'table' => 'partners', 'order' => 'name ASC',
                'description' => 'Список партнеров компании: название, описание, сайт и логотип.',
                'columns' => ['name' => 'Название', 'website' => 'Сайт'],
                'fields' => [
                    ['name' => 'name', 'label' => 'Название', 'type' => 'text', 'required' => true],
                    ['name' => 'name_en', 'label' => 'Название (EN)', 'type' => 'text'],
                    ['name' => 'description', 'label' => 'Описание', 'type' => 'wysiwyg', 'required' => true],
                    ['name' => 'description_en', 'label' => 'Описание (EN)', 'type' => 'wysiwyg'],
                    ['name' => 'website', 'label' => 'Сайт', 'type' => 'url'],
                    ['name' => 'logo', 'label' => 'Логотип', 'type' => 'file_image'],
                ],
            ],
            'categories' => [
                'title' => 'Категории продукции', 'table' => 'product_categories', 'order' => 'sort_order ASC, name ASC',
                'description' => 'Группы продукции, по которым покупатель фильтрует каталог.',
                'columns' => ['name' => 'Название', 'sort_order' => 'Порядок'],
                'fields' => [
                    ['name' => 'name', 'label' => 'Название', 'type' => 'text', 'required' => true],
                    ['name' => 'name_en', 'label' => 'Название (EN)', 'type' => 'text'],
                    ['name' => 'description', 'label' => 'Описание', 'type' => 'textarea'],
                    ['name' => 'description_en', 'label' => 'Описание (EN)', 'type' => 'textarea'],
                    ['name' => 'sort_order', 'label' => 'Порядок сортировки', 'type' => 'number'],
                ],
            ],
            'products' => [
                'title' => 'Продукция', 'table' => 'products', 'order' => 'title ASC', 'slugFrom' => 'title',
                'description' => 'Карточки комбикорма в каталоге: категория, описание, цена, фото и видео.',
                'columns' => ['title' => 'Название', 'category_id' => 'Категория', 'price' => 'Цена', 'is_active' => 'Активно'],
                'fields' => [
                    ['name' => 'category_id', 'label' => 'Категория', 'type' => 'select', 'options' => $this->options('product_categories'), 'required' => true],
                    ['name' => 'title', 'label' => 'Название', 'type' => 'text', 'required' => true],
                    ['name' => 'title_en', 'label' => 'Название (EN)', 'type' => 'text'],
                    ['name' => 'slug', 'label' => 'URL-адрес', 'type' => 'text'],
                    ['name' => 'description', 'label' => 'Описание', 'type' => 'wysiwyg', 'required' => true],
                    ['name' => 'description_en', 'label' => 'Описание (EN)', 'type' => 'wysiwyg'],
                    ['name' => 'recommendation', 'label' => 'Краткие рекомендации', 'type' => 'wysiwyg'],
                    ['name' => 'recommendation_en', 'label' => 'Краткие рекомендации (EN)', 'type' => 'wysiwyg'],
                    ['name' => 'price', 'label' => 'Цена за единицу', 'type' => 'number', 'required' => true, 'step' => '0.01'],
                    ['name' => 'unit', 'label' => 'Единица измерения', 'type' => 'text'],
                    ['name' => 'unit_en', 'label' => 'Единица измерения (EN)', 'type' => 'text'],
                    ['name' => 'image', 'label' => 'Фото продукции', 'type' => 'file_image', 'hint' => 'Если фото не загрузить, сайт покажет стандартную картинку.'],
                    ['name' => 'video_url', 'label' => 'Видео о продукции, URL', 'type' => 'url', 'hint' => 'Если ссылку не указать, сайт покажет стандартный видеоблок.', 'placeholder' => 'https://www.youtube.com/watch?v=...'],
                    ['name' => 'is_active', 'label' => 'Активно', 'type' => 'checkbox'],
                ],
            ],
            'recommendations' => [
                'title' => 'Рекомендации', 'table' => 'recommendations', 'order' => 'id DESC',
                'description' => 'Рекомендации, которые привязаны к конкретным видам продукции.',
                'columns' => ['product_id' => 'Продукт', 'title' => 'Тема'],
                'fields' => [
                    ['name' => 'product_id', 'label' => 'Продукция', 'type' => 'select', 'options' => $this->options('products'), 'required' => true],
                    ['name' => 'title', 'label' => 'Тема', 'type' => 'text', 'required' => true],
                    ['name' => 'title_en', 'label' => 'Тема (EN)', 'type' => 'text'],
                    ['name' => 'body', 'label' => 'Текст рекомендации', 'type' => 'wysiwyg', 'required' => true],
                    ['name' => 'body_en', 'label' => 'Текст рекомендации (EN)', 'type' => 'wysiwyg'],
                ],
            ],
            'info-categories' => [
                'title' => 'Категории полезной информации', 'table' => 'useful_categories', 'order' => 'name ASC',
                'description' => 'Разделы для статей и справочных материалов.',
                'columns' => ['name' => 'Название'],
                'fields' => [
                    ['name' => 'name', 'label' => 'Название', 'type' => 'text', 'required' => true],
                    ['name' => 'name_en', 'label' => 'Название (EN)', 'type' => 'text'],
                    ['name' => 'description', 'label' => 'Описание', 'type' => 'textarea'],
                    ['name' => 'description_en', 'label' => 'Описание (EN)', 'type' => 'textarea'],
                ],
            ],
            'info-articles' => [
                'title' => 'Полезная информация', 'table' => 'useful_articles', 'order' => 'published_at DESC, id DESC', 'slugFrom' => 'title',
                'description' => 'Полезные статьи для покупателей: анонс, полный текст и дата публикации.',
                'columns' => ['title' => 'Название', 'category_id' => 'Категория', 'published_at' => 'Дата', 'is_active' => 'Активно'],
                'fields' => [
                    ['name' => 'category_id', 'label' => 'Категория', 'type' => 'select', 'options' => $this->options('useful_categories'), 'required' => true],
                    ['name' => 'title', 'label' => 'Название', 'type' => 'text', 'required' => true],
                    ['name' => 'title_en', 'label' => 'Название (EN)', 'type' => 'text'],
                    ['name' => 'slug', 'label' => 'URL-адрес', 'type' => 'text'],
                    ['name' => 'announce', 'label' => 'Анонс', 'type' => 'textarea', 'required' => true],
                    ['name' => 'announce_en', 'label' => 'Анонс (EN)', 'type' => 'textarea'],
                    ['name' => 'body', 'label' => 'Полный текст', 'type' => 'wysiwyg', 'required' => true],
                    ['name' => 'body_en', 'label' => 'Полный текст (EN)', 'type' => 'wysiwyg'],
                    ['name' => 'published_at', 'label' => 'Дата публикации', 'type' => 'date', 'required' => true],
                    ['name' => 'is_active', 'label' => 'Опубликовано', 'type' => 'checkbox'],
                ],
            ],
        ];
    }

    private function section(string $sectionKey): array
    {
        $sections = $this->sections();
        if (!isset($sections[$sectionKey])) {
            throw new \RuntimeException('Неизвестный раздел администрирования: ' . $sectionKey);
        }
        return $sections[$sectionKey];
    }

    private function requireSectionAccess(string $sectionKey): void
    {
        if (!Auth::canAccessSection($sectionKey)) {
            \flash('error', 'У вас нет доступа к этому разделу.');
            \redirect('/admin');
        }
    }

    private function permissionSections(): array
    {
        return [
            'news' => 'Новости',
            'pages' => 'Страницы сайта',
            'contacts' => 'Контакты',
            'partners' => 'Партнеры',
            'categories' => 'Категории продукции',
            'products' => 'Продукция',
            'recommendations' => 'Рекомендации',
            'info-categories' => 'Категории полезной информации',
            'info-articles' => 'Полезная информация',
            'orders' => 'Заявки покупателей',
        ];
    }

    private function loadUserPermissions(int $userId): array
    {
        $stmt = Database::pdo()->prepare('SELECT section_key FROM user_permissions WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        return array_column($stmt->fetchAll(), 'section_key');
    }

    private function syncUserPermissions(int $userId, array $permissions): void
    {
        $pdo = Database::pdo();
        $pdo->prepare('DELETE FROM user_permissions WHERE user_id = :user_id')->execute(['user_id' => $userId]);
        if (!$permissions) {
            return;
        }
        $stmt = $pdo->prepare('INSERT INTO user_permissions (user_id, section_key) VALUES (:user_id, :section_key)');
        foreach (array_unique($permissions) as $sectionKey) {
            $stmt->execute(['user_id' => $userId, 'section_key' => $sectionKey]);
        }
    }

    private function options(string $table): array
    {
        $label = $table === 'products' ? 'title' : 'name';
        $rows = Database::pdo()->query('SELECT id, ' . $label . ' AS label FROM ' . $table . ' ORDER BY label')->fetchAll();
        $options = [];
        foreach ($rows as $row) {
            $options[(string)$row['id']] = $row['label'];
        }
        return $options;
    }

    private function collectData(array $section, array $current): array
    {
        $data = [];
        $errors = [];

        foreach ($section['fields'] as $field) {
            $name = $field['name'];
            $type = $field['type'];
            if ($type === 'checkbox') {
                $data[$name] = isset($_POST[$name]) ? 1 : 0;
                continue;
            }
            if ($type === 'file_image') {
                $hasUpload = !empty($_FILES[$name]['name']) && (($_FILES[$name]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE);
                if (($field['required'] ?? false) && empty($current[$name]) && !$hasUpload) {
                    $errors[] = 'Поле «' . $field['label'] . '» обязательно.';
                }
                $data[$name] = $this->handleUpload($name, $current[$name] ?? null);
                continue;
            }
            $value = trim((string)($_POST[$name] ?? ''));
            if (($field['required'] ?? false) && $value === '') {
                $errors[] = 'Поле «' . $field['label'] . '» обязательно.';
            }
            if ($type === 'email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Поле «' . $field['label'] . '» должно быть email.';
            }
            if ($type === 'url' && $value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
                $errors[] = 'Поле «' . $field['label'] . '» должно быть ссылкой.';
            }
            if ($type === 'number') {
                $value = str_replace(',', '.', $value);
                if ($value === '') {
                    $value = '0';
                }
            }
            $data[$name] = $value;
        }

        if (!empty($section['slugFrom']) && empty($data['slug']) && !empty($data[$section['slugFrom']])) {
            $data['slug'] = \slugify((string)$data[$section['slugFrom']]);
        }
        if (!empty($section['fixed']) && is_array($section['fixed'])) {
            $data = array_merge($data, $section['fixed']);
        }

        return [$data, $errors];
    }

    private function handleUpload(string $field, ?string $current): string
    {
        if (empty($_FILES[$field]['name']) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return (string)($current ?? '');
        }
        if (($_FILES[$field]['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            return (string)($current ?? '');
        }
        $extension = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        if (!in_array($extension, $allowed, true)) {
            return (string)($current ?? '');
        }
        $directory = __DIR__ . '/../../public/uploads';
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }
        $fileName = $field . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
        $target = $directory . '/' . $fileName;
        if (!move_uploaded_file($_FILES[$field]['tmp_name'], $target)) {
            return (string)($current ?? '');
        }
        return 'uploads/' . $fileName;
    }

    private function insert(string $table, array $data): void
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn ($column) => ':' . $column, $columns);
        $stmt = Database::pdo()->prepare('INSERT INTO ' . $table . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')');
        $stmt->execute($data);
    }

    private function update(string $table, int $id, array $data): void
    {
        $sets = [];
        foreach (array_keys($data) as $column) {
            $sets[] = $column . ' = :' . $column;
        }
        $data['id'] = $id;
        $stmt = Database::pdo()->prepare('UPDATE ' . $table . ' SET ' . implode(', ', $sets) . ' WHERE id = :id');
        $stmt->execute($data);
    }

    private function findRow(string $table, int $id): ?array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM ' . $table . ' WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    private function notFoundAdmin(string $message): void
    {
        http_response_code(404);
        $this->view('admin/not-found', ['title' => '404', 'message' => $message], 'admin');
    }
}
