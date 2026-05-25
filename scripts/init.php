<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;

$storage = __DIR__ . '/../storage';
$uploads = __DIR__ . '/../public/uploads';
foreach ([$storage, $uploads] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
}

$pdo = Database::pdo();
$schema = file_get_contents(__DIR__ . '/../database/schema.sql');
$pdo->exec($schema);

function has_rows(PDO $pdo, string $table): bool {
    return (int)$pdo->query('SELECT COUNT(*) FROM ' . $table)->fetchColumn() > 0;
}

function column_exists(PDO $pdo, string $table, string $column): bool {
    $stmt = $pdo->query('PRAGMA table_info(' . $table . ')');
    foreach ($stmt->fetchAll() as $info) {
        if (($info['name'] ?? '') === $column) {
            return true;
        }
    }
    return false;
}

function add_column_if_missing(PDO $pdo, string $table, string $column, string $definition): void {
    if (!column_exists($pdo, $table, $column)) {
        $pdo->exec('ALTER TABLE ' . $table . ' ADD COLUMN ' . $column . ' ' . $definition);
    }
}

// Миграции для пользователей, которые уже запускали старую версию проекта.
add_column_if_missing($pdo, 'news', 'image', 'TEXT');
add_column_if_missing($pdo, 'news', 'video_url', 'TEXT');
add_column_if_missing($pdo, 'products', 'video_url', 'TEXT');

$stockImages = [
    'news' => stock_image('news'),
    'cattle' => stock_image('cattle'),
    'poultry' => stock_image('poultry'),
    'pigs' => stock_image('pigs'),
    'premix' => stock_image('premix'),
];

if (!has_rows($pdo, 'users')) {
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role, active, created_at) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute(['Главный администратор', 'admin@example.com', password_hash('admin123', PASSWORD_DEFAULT), 'admin', 1, date('Y-m-d H:i:s')]);
    $stmt->execute(['Контент-менеджер', 'manager@example.com', password_hash('manager123', PASSWORD_DEFAULT), 'content_manager', 1, date('Y-m-d H:i:s')]);
}

if (!has_rows($pdo, 'pages')) {
    $pages = [
        ['home', 'АгроКорм — комбикормовый завод', '<p><strong>АгроКорм</strong> производит гранулированные комбикорма, зерносмеси, белково-витаминно-минеральные добавки и премиксы для сельскохозяйственных животных и птицы.</p><p>Мы объединяем лабораторный контроль, собственные рецептуры и удобную форму заявки, чтобы хозяйства быстро получали корм под свою задачу.</p>'],
        ['about', 'О компании', '<p>Комбикормовый завод «АгроКорм» — производственное предприятие, ориентированное на фермерские хозяйства, птицефабрики, молочные комплексы и свиноводческие предприятия.</p><p>Основные направления: разработка рецептур, производство комбикормов, консультации по кормлению, доставка партий.</p>'],
        ['history', 'История компании', '<p>Предприятие начало работу как региональная линия гранулирования кормов. Затем были открыты лаборатория входного контроля сырья и участок фасовки.</p><p>Сегодня завод выпускает линейку кормов для КРС, птицы и свиноводства, а также премиксы для корректировки рационов.</p>'],
        ['certification', 'Сертификация и научная поддержка', '<p>Каждая партия сопровождается паспортом качества. В производстве применяются входной контроль сырья, контроль влажности, проверка гранулометрии и питательности.</p><p>Рецептуры разрабатываются с учетом отраслевых норм и рекомендаций специалистов по кормлению.</p>'],
        ['production', 'Производство', '<p>Производственная линия включает очистку и измельчение сырья, дозирование компонентов, смешивание, гранулирование, охлаждение и фасовку.</p><p>Система складского учета помогает отслеживать партии сырья и готовой продукции.</p>'],
    ];
    $stmt = $pdo->prepare('INSERT INTO pages (slug, title, body) VALUES (?, ?, ?)');
    foreach ($pages as $page) { $stmt->execute($page); }
}

if (!has_rows($pdo, 'news')) {
    $news = [
        ['Запущена новая линия гранулирования', 'zapuschena-novaya-liniya-granulirovaniya', 'Завод увеличил производительность и улучшил стабильность гранулы.', '<p>На предприятии запущена новая линия гранулирования. Она позволяет выпускать партии с более стабильной структурой и снижать время изготовления заказов.</p>', $stockImages['premix'], '', '2026-05-01'],
        ['Расширена линейка кормов для птицы', 'rasshirena-lineyka-kormov-dlya-ptitsy', 'В каталог добавлены стартовые и финишные рационы.', '<p>В линейку продукции добавлены корма для стартового, ростового и финишного периодов. Рецептуры сбалансированы по энергии, протеину и аминокислотам.</p>', $stockImages['poultry'], '', '2026-04-18'],
        ['Лаборатория обновила методики контроля сырья', 'laboratoriya-obnovila-metodiki-kontrolya-syrya', 'Внедрен расширенный входной контроль зерна и белковых компонентов.', '<p>Лаборатория завода обновила регламенты входного контроля. Это помогает поддерживать стабильное качество комбикормов.</p>', $stockImages['news'], '', '2026-03-27'],
    ];
    $stmt = $pdo->prepare('INSERT INTO news (title, slug, announce, body, image, video_url, published_at, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)');
    foreach ($news as $item) { $stmt->execute($item); }
}

if (!has_rows($pdo, 'product_categories')) {
    $categories = [
        ['Комбикорма для КРС', 'Рационы для телят, молодняка и молочного стада.', 10],
        ['Комбикорма для птицы', 'Старт, рост и финиш для бройлеров и несушек.', 20],
        ['Комбикорма для свиноводства', 'Корма для поросят, откорма и супоросных свиноматок.', 30],
        ['Премиксы и БВМД', 'Витаминно-минеральные добавки для корректировки рационов.', 40],
    ];
    $stmt = $pdo->prepare('INSERT INTO product_categories (name, description, sort_order) VALUES (?, ?, ?)');
    foreach ($categories as $category) { $stmt->execute($category); }
}

if (!has_rows($pdo, 'products')) {
    $products = [
        [1, 'КРС-Молоко 18%', 'krs-moloko-18', '<p>Полнорационный комбикорм для высокопродуктивного молочного стада. Поддерживает стабильную энергию рациона и качество молока.</p><ul><li>сырой протеин — 18%</li><li>оптимальное соотношение энергии и клетчатки</li><li>гранула 6 мм</li></ul>', '<p>Рекомендуется вводить постепенно в течение 5–7 дней. Норма зависит от продуктивности и основного рациона.</p>', 34.50, 'кг', $stockImages['cattle'], ''],
        [1, 'Теленок Старт', 'telenok-start', '<p>Стартовый корм для раннего развития рубца и набора живой массы у телят.</p><ul><li>высокая вкусовая привлекательность</li><li>поддержка иммунитета</li><li>удобная мелкая гранула</li></ul>', '<p>Использовать с 10–14 дня жизни при свободном доступе к воде.</p>', 42.00, 'кг', $stockImages['cattle'], ''],
        [2, 'Бройлер Рост', 'broyler-rost', '<p>Комбикорм для периода активного роста бройлера. Содержит сбалансированный аминокислотный профиль.</p>', '<p>Применяется после стартового периода до перехода на финишный рацион.</p>', 38.90, 'кг', $stockImages['poultry'], ''],
        [2, 'Несушка Пик', 'nesushka-pik', '<p>Рацион для кур-несушек в период максимальной яйценоскости. Поддерживает качество скорлупы и стабильность кладки.</p>', '<p>Следить за доступом к воде и минеральной подкормке.</p>', 36.40, 'кг', $stockImages['poultry'], ''],
        [3, 'Поросята Престарт', 'porosyata-prestart', '<p>Корм для раннего приучения поросят к сухому кормлению. Отличается высокой переваримостью компонентов.</p>', '<p>Вводить малыми порциями, обновляя корм несколько раз в день.</p>', 55.00, 'кг', $stockImages['pigs'], ''],
        [4, 'Премикс Универсал 1%', 'premiks-universal-1', '<p>Витаминно-минеральная смесь для обогащения рационов сельскохозяйственных животных.</p>', '<p>Смешивать с зерновой частью рациона согласно норме ввода.</p>', 110.00, 'кг', $stockImages['premix'], ''],
    ];
    $stmt = $pdo->prepare('INSERT INTO products (category_id, title, slug, description, recommendation, price, unit, image, video_url, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)');
    foreach ($products as $product) { $stmt->execute($product); }
}

if (!has_rows($pdo, 'recommendations')) {
    $items = [
        [1, 'Переход на новый рацион', '<p>Смена рациона проводится постепенно: 30% нового корма в первые два дня, затем 60%, далее полный переход.</p>'],
        [3, 'Контроль воды', '<p>Для птицы важно обеспечить постоянный доступ к чистой воде: это напрямую влияет на потребление корма.</p>'],
        [6, 'Смешивание премикса', '<p>Премикс предварительно смешивают с небольшой частью наполнителя, затем вводят в общий объем смеси.</p>'],
    ];
    $stmt = $pdo->prepare('INSERT INTO recommendations (product_id, title, body) VALUES (?, ?, ?)');
    foreach ($items as $item) { $stmt->execute($item); }
}

if (!has_rows($pdo, 'media')) {
    $media = [
        ['photo', 'Гранулированные корма', 'Фото карточка для раздела производства и главной страницы.', 'assets/img/feed-poultry.svg', '', 10, 1],
        ['photo', 'Премиксы и добавки', 'Иллюстрация линейки витаминно-минеральных добавок.', 'assets/img/feed-premix.svg', '', 20, 1],
        ['photo', 'Корма для КРС', 'Иллюстрация раздела кормов для крупного рогатого скота.', 'assets/img/feed-cattle.svg', '', 30, 1],
        ['video', 'Видео о подборе рациона', 'Видео можно заменить в админке на ссылку YouTube/Vimeo/Rutube или embed-ссылку.', '', 'https://www.youtube.com/embed/tgbNymZ7vqY', 40, 1],
    ];
    $stmt = $pdo->prepare('INSERT INTO media (type, title, description, file_path, video_url, sort_order, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    foreach ($media as $item) { $stmt->execute([...$item, date('Y-m-d H:i:s')]); }
}

if (!has_rows($pdo, 'partners')) {
    $partners = [
        ['АгроЛогистика Юг', '<p>Транспортный партнер по доставке комбикормов хозяйствам региона.</p>', 'https://example.com', ''],
        ['ВетРацион', '<p>Консультационный партнер по ветеринарному сопровождению и корректировке рационов.</p>', 'https://example.com', ''],
        ['ЗерноПоставка', '<p>Поставщик зернового сырья с регулярным лабораторным контролем качества.</p>', 'https://example.com', ''],
    ];
    $stmt = $pdo->prepare('INSERT INTO partners (name, description, website, logo) VALUES (?, ?, ?, ?)');
    foreach ($partners as $partner) { $stmt->execute($partner); }
}

if (!has_rows($pdo, 'contacts')) {
    $stmt = $pdo->prepare('INSERT INTO contacts (title, address, phone, email, work_time, map_url) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        'Отдел продаж', 'г. Севастополь, ул. Индустриальная, 12', '+7 (800) 250-10-45', 'sales@agrokorm.local', 'Пн–Пт, 09:00–18:00',
        'https://yandex.ru/map-widget/v1/?ll=33.525432%2C44.616650&z=12&pt=33.525432,44.616650,pm2rdm'
    ]);
}

if (!has_rows($pdo, 'useful_categories')) {
    $categories = [
        ['Кормление КРС', 'Материалы по кормлению крупного рогатого скота.'],
        ['Птицеводство', 'Практические рекомендации для птицефабрик.'],
        ['Качество сырья', 'Контроль зерна, влажности и хранения.'],
    ];
    $stmt = $pdo->prepare('INSERT INTO useful_categories (name, description) VALUES (?, ?)');
    foreach ($categories as $category) { $stmt->execute($category); }
}

if (!has_rows($pdo, 'useful_articles')) {
    $articles = [
        [1, 'Как плавно менять рацион у молочного стада', 'kak-menyat-racion-krs', 'Переходный период снижает стресс и помогает сохранить продуктивность.', '<p>Резкая смена рациона может привести к снижению потребления корма. Оптимально вводить новый комбикорм постепенно, отслеживая аппетит и продуктивность.</p>', '2026-04-12'],
        [2, 'Почему гранула важна для бройлера', 'pochemu-granula-vazhna-dlya-broylera', 'Однородная гранула улучшает поедаемость и снижает потери.', '<p>Размер и прочность гранулы влияют на потребление корма. Для разных периодов выращивания применяется разная фракция.</p>', '2026-03-20'],
        [3, 'Входной контроль зерна на комбикормовом заводе', 'vhodnoy-kontrol-zerna', 'Качество сырья определяет стабильность готового корма.', '<p>Перед производством проверяют влажность, запах, засоренность и другие показатели зерновых компонентов.</p>', '2026-02-17'],
    ];
    $stmt = $pdo->prepare('INSERT INTO useful_articles (category_id, title, slug, announce, body, published_at, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)');
    foreach ($articles as $article) { $stmt->execute($article); }
}

// Бэкфилл: старые одиночные заявки превращаем в состав заказа из одной позиции.
$orderItemCount = (int)$pdo->query('SELECT COUNT(*) FROM order_items')->fetchColumn();
$orderCount = (int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
if ($orderItemCount === 0 && $orderCount > 0) {
    $orders = $pdo->query('SELECT * FROM orders')->fetchAll();
    $stmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_title, price, quantity, unit, total) VALUES (?, ?, ?, ?, ?, ?, ?)');
    foreach ($orders as $order) {
        $stmt->execute([
            $order['id'],
            $order['product_id'] ?? null,
            $order['product_title'] ?? 'Позиция заказа',
            $order['price'] ?? 0,
            $order['quantity'] ?? 0,
            'кг',
            $order['total'] ?? 0,
        ]);
    }
}

if (!is_file(config('mail_log'))) {
    file_put_contents(config('mail_log'), "Журнал писем сайта АгроКорм\n");
}

$imageBackfill = [
    ['news', 'image', 'assets/img/feed-premix.svg', $stockImages['premix']],
    ['news', 'image', 'assets/img/feed-poultry.svg', $stockImages['poultry']],
    ['news', 'image', 'assets/img/feed-cattle.svg', $stockImages['news']],
    ['products', 'image', 'assets/img/feed-cattle.svg', $stockImages['cattle']],
    ['products', 'image', 'assets/img/feed-poultry.svg', $stockImages['poultry']],
    ['products', 'image', 'assets/img/feed-pigs.svg', $stockImages['pigs']],
    ['products', 'image', 'assets/img/feed-premix.svg', $stockImages['premix']],
];
foreach ($imageBackfill as [$table, $column, $from, $to]) {
    $stmt = $pdo->prepare("UPDATE {$table} SET {$column} = ? WHERE {$column} = ?");
    $stmt->execute([$to, $from]);
}

echo "Готово. База данных и демо-данные созданы/проверены.\n";
echo "Админ: admin@example.com / admin123\n";
