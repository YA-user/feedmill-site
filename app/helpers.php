<?php
declare(strict_types=1);

function config(?string $key = null, mixed $default = null): mixed
{
    static $config = null;
    if ($config === null) {
        $config = require __DIR__ . '/../config/config.php';
    }
    if ($key === null) {
        return $config;
    }
    return $config[$key] ?? $default;
}

function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string
{
    $path = '/' . ltrim($path, '/');
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $base = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
    if ($base === '/' || $base === '.' || $base === '/public') {
        $base = '';
    }
    return $base . $path;
}

function asset(string $path): string
{
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:')) {
        return $path;
    }
    return url(ltrim($path, '/'));
}

function stock_image(string $key): string
{
    $images = [
        'hero' => 'https://images.pexels.com/photos/12656497/pexels-photo-12656497.jpeg?auto=compress&cs=tinysrgb&w=1800',
        'news' => 'https://images.pexels.com/photos/21958120/pexels-photo-21958120.jpeg?auto=compress&cs=tinysrgb&w=1200',
        'cattle' => 'https://images.pexels.com/photos/8487943/pexels-photo-8487943.jpeg?auto=compress&cs=tinysrgb&w=1200',
        'poultry' => 'https://images.pexels.com/photos/15109798/pexels-photo-15109798.jpeg?auto=compress&cs=tinysrgb&w=1200',
        'pigs' => 'https://images.pexels.com/photos/12235996/pexels-photo-12235996.jpeg?auto=compress&cs=tinysrgb&w=1200',
        'premix' => 'https://images.pexels.com/photos/21958120/pexels-photo-21958120.jpeg?auto=compress&cs=tinysrgb&w=1200',
        'grain' => 'https://images.pexels.com/photos/12656497/pexels-photo-12656497.jpeg?auto=compress&cs=tinysrgb&w=1200',
        'field' => 'https://images.pexels.com/photos/2165688/pexels-photo-2165688.jpeg?auto=compress&cs=tinysrgb&w=1200',
        'harvest' => 'https://images.pexels.com/photos/96715/pexels-photo-96715.jpeg?auto=compress&cs=tinysrgb&w=1200',
        'farm' => 'https://images.pexels.com/photos/2255459/pexels-photo-2255459.jpeg?auto=compress&cs=tinysrgb&w=1200',
        'storage' => 'https://images.pexels.com/photos/265216/pexels-photo-265216.jpeg?auto=compress&cs=tinysrgb&w=1200',
        'office' => 'https://images.pexels.com/photos/3184465/pexels-photo-3184465.jpeg?auto=compress&cs=tinysrgb&w=1200',
        'truck' => 'https://images.pexels.com/photos/6169660/pexels-photo-6169660.jpeg?auto=compress&cs=tinysrgb&w=1200',
    ];
    return $images[$key] ?? $images['grain'];
}

function page_image(?string $slug): string
{
    return match ((string)$slug) {
        'about' => stock_image('farm'),
        'history' => stock_image('field'),
        'certification' => stock_image('grain'),
        'production' => stock_image('storage'),
        default => stock_image('hero'),
    };
}

function stock_gallery(): array
{
    return [
        ['title' => 'Поля зерновых', 'text' => 'Сырье для рационов начинается с контроля зерна и стабильных поставок.', 'image' => stock_image('field')],
        ['title' => 'Зерно и компоненты', 'text' => 'Партии сырья проверяются перед измельчением и смешиванием.', 'image' => stock_image('harvest')],
        ['title' => 'Фермерские хозяйства', 'text' => 'Корма подбираются под задачи хозяйства, возраст и продуктивность животных.', 'image' => stock_image('farm')],
        ['title' => 'Готовая продукция', 'text' => 'Фасовка и отгрузка партий под заявку клиента.', 'image' => stock_image('storage')],
    ];
}

function default_image(string $type = 'default'): string
{
    return match ($type) {
        'news' => stock_image('news'),
        'poultry' => stock_image('poultry'),
        'pigs' => stock_image('pigs'),
        'premix' => stock_image('premix'),
        default => stock_image('cattle'),
    };
}

function media_image(?string $path, string $type = 'default'): string
{
    $path = trim((string)$path);
    return $path !== '' ? $path : default_image($type);
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    if (!isset($_SESSION['flash'][$key])) {
        return null;
    }
    $value = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);
    return $value;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): bool
{
    $token = $_POST['_csrf'] ?? '';
    return is_string($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function selected(mixed $current, mixed $expected): string
{
    return (string)$current === (string)$expected ? ' selected' : '';
}

function checked(mixed $value): string
{
    return !empty($value) ? ' checked' : '';
}

function format_money(mixed $value): string
{
    return number_format((float)$value, 2, ',', ' ') . ' ₽';
}

function excerpt(string $html, int $length = 170): string
{
    $text = trim(preg_replace('/\s+/u', ' ', strip_tags($html)) ?? '');
    if (function_exists('mb_strlen')) {
        if (mb_strlen($text, 'UTF-8') <= $length) {
            return $text;
        }
        return mb_substr($text, 0, $length - 1, 'UTF-8') . '…';
    }
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length - 1) . '…';
}

function slugify(string $text): string
{
    $map = [
        'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'zh','з'=>'z','и'=>'i','й'=>'y',
        'к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f',
        'х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'sch','ъ'=>'','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya',
        'А'=>'a','Б'=>'b','В'=>'v','Г'=>'g','Д'=>'d','Е'=>'e','Ё'=>'e','Ж'=>'zh','З'=>'z','И'=>'i','Й'=>'y',
        'К'=>'k','Л'=>'l','М'=>'m','Н'=>'n','О'=>'o','П'=>'p','Р'=>'r','С'=>'s','Т'=>'t','У'=>'u','Ф'=>'f',
        'Х'=>'h','Ц'=>'c','Ч'=>'ch','Ш'=>'sh','Щ'=>'sch','Ъ'=>'','Ы'=>'y','Ь'=>'','Э'=>'e','Ю'=>'yu','Я'=>'ya',
    ];
    $text = strtr($text, $map);
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/i', '-', $text) ?: '';
    $text = trim($text, '-');
    return $text !== '' ? $text : 'item-' . time();
}


function video_embed_url(string $url): string
{
    $url = trim($url);
    if ($url === '') {
        return '';
    }
    if (preg_match('~youtube\.com/watch\?v=([A-Za-z0-9_-]+)~', $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }
    if (preg_match('~youtu\.be/([A-Za-z0-9_-]+)~', $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }
    if (preg_match('~rutube\.ru/video/([A-Za-z0-9]+)/?~', $url, $m)) {
        return 'https://rutube.ru/play/embed/' . $m[1];
    }
    return $url;
}

function admin_section_url(string $section): string
{
    return url('/admin/' . $section);
}
