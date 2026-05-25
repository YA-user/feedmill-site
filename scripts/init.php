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
foreach ([
    'pages' => ['title_en' => 'TEXT', 'body_en' => 'TEXT'],
    'news' => ['title_en' => 'TEXT', 'announce_en' => 'TEXT', 'body_en' => 'TEXT'],
    'product_categories' => ['name_en' => 'TEXT', 'description_en' => 'TEXT'],
    'products' => ['title_en' => 'TEXT', 'description_en' => 'TEXT', 'recommendation_en' => 'TEXT', 'unit_en' => 'TEXT'],
    'recommendations' => ['title_en' => 'TEXT', 'body_en' => 'TEXT'],
    'partners' => ['name_en' => 'TEXT', 'description_en' => 'TEXT'],
    'contacts' => ['title_en' => 'TEXT', 'address_en' => 'TEXT', 'work_time_en' => 'TEXT'],
    'useful_categories' => ['name_en' => 'TEXT', 'description_en' => 'TEXT'],
    'useful_articles' => ['title_en' => 'TEXT', 'announce_en' => 'TEXT', 'body_en' => 'TEXT'],
] as $table => $columns) {
    foreach ($columns as $column => $definition) {
        add_column_if_missing($pdo, $table, $column, $definition);
    }
}
$pdo->exec('CREATE TABLE IF NOT EXISTS user_permissions (
    user_id INTEGER NOT NULL,
    section_key TEXT NOT NULL,
    PRIMARY KEY (user_id, section_key),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)');

function update_translation(PDO $pdo, string $table, string $keyColumn, string|int $keyValue, array $fields): void {
    $sets = [];
    $params = ['key_value' => $keyValue];
    foreach ($fields as $column => $value) {
        $sets[] = $column . ' = CASE WHEN ' . $column . ' IS NULL OR ' . $column . " = '' THEN :" . $column . ' ELSE ' . $column . ' END';
        $params[$column] = $value;
    }
    $stmt = $pdo->prepare('UPDATE ' . $table . ' SET ' . implode(', ', $sets) . ' WHERE ' . $keyColumn . ' = :key_value');
    $stmt->execute($params);
}

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

$translations = [
    ['pages', 'slug', 'home', ['title_en' => 'AgroKorm feed mill', 'body_en' => '<p><strong>AgroKorm</strong> produces pelleted feed, grain mixes, vitamin-mineral additives and premixes for farm animals and poultry.</p><p>The demo site includes a catalog, order form, news and useful materials for customers.</p>']],
    ['pages', 'slug', 'about', ['title_en' => 'About the company', 'body_en' => '<p>AgroKorm is a training demo feed mill focused on farms, poultry houses, dairy complexes and pig farms.</p><p>The company develops feed formulas, produces compound feed and gives basic feeding recommendations.</p>']],
    ['pages', 'slug', 'history', ['title_en' => 'Company history', 'body_en' => '<p>The plant started as a regional pelleting line and later added raw material quality control and packaging.</p><p>Today the demo enterprise produces feeds for cattle, poultry and pig farming.</p>']],
    ['pages', 'slug', 'certification', ['title_en' => 'Certification and scientific support', 'body_en' => '<p>Each batch is accompanied by quality documentation. The process includes incoming raw material control and checks of moisture, particle size and nutritional value.</p>']],
    ['pages', 'slug', 'production', ['title_en' => 'Production', 'body_en' => '<p>The production line includes cleaning, grinding, dosing, mixing, pelleting, cooling and packaging.</p><p>Batch accounting helps track raw materials and finished products.</p>']],
    ['news', 'slug', 'zapuschena-novaya-liniya-granulirovaniya', ['title_en' => 'New pelleting line launched', 'announce_en' => 'The plant increased productivity and pellet stability.', 'body_en' => '<p>A new pelleting line has been launched at the plant. It helps produce batches with a more stable structure and shorter lead time.</p>']],
    ['news', 'slug', 'rasshirena-lineyka-kormov-dlya-ptitsy', ['title_en' => 'Poultry feed range expanded', 'announce_en' => 'Starter and finisher diets were added to the catalog.', 'body_en' => '<p>The product range now includes starter, grower and finisher poultry feeds balanced by energy, protein and amino acids.</p>']],
    ['news', 'slug', 'laboratoriya-obnovila-metodiki-kontrolya-syrya', ['title_en' => 'Laboratory updated raw material checks', 'announce_en' => 'Extended incoming control for grain and protein components was introduced.', 'body_en' => '<p>The plant laboratory updated incoming control rules to keep feed quality stable.</p>']],
    ['product_categories', 'id', 1, ['name_en' => 'Cattle feed', 'description_en' => 'Diets for calves, young stock and dairy herds.']],
    ['product_categories', 'id', 2, ['name_en' => 'Poultry feed', 'description_en' => 'Starter, grower and finisher feeds for broilers and layers.']],
    ['product_categories', 'id', 3, ['name_en' => 'Pig feed', 'description_en' => 'Feeds for piglets, finishing pigs and sows.']],
    ['product_categories', 'id', 4, ['name_en' => 'Premixes and additives', 'description_en' => 'Vitamin-mineral additives for diet balancing.']],
    ['products', 'slug', 'krs-moloko-18', ['title_en' => 'Cattle Milk 18%', 'description_en' => '<p>Complete feed for productive dairy herds. It supports stable diet energy and milk quality.</p>', 'recommendation_en' => '<p>Introduce gradually over 5-7 days. The rate depends on productivity and the base diet.</p>', 'unit_en' => 'kg']],
    ['products', 'slug', 'telenok-start', ['title_en' => 'Calf Starter', 'description_en' => '<p>Starter feed for early rumen development and weight gain in calves.</p>', 'recommendation_en' => '<p>Use from day 10-14 with free access to water.</p>', 'unit_en' => 'kg']],
    ['products', 'slug', 'broyler-rost', ['title_en' => 'Broiler Grower', 'description_en' => '<p>Feed for the active growth period of broilers with a balanced amino acid profile.</p>', 'recommendation_en' => '<p>Use after starter feed before switching to finisher diet.</p>', 'unit_en' => 'kg']],
    ['products', 'slug', 'nesushka-pik', ['title_en' => 'Layer Peak', 'description_en' => '<p>Diet for laying hens during peak egg production.</p>', 'recommendation_en' => '<p>Keep clean water and mineral supplements available.</p>', 'unit_en' => 'kg']],
    ['products', 'slug', 'porosyata-prestart', ['title_en' => 'Piglet Prestarter', 'description_en' => '<p>Feed for early adaptation of piglets to dry feeding.</p>', 'recommendation_en' => '<p>Introduce in small portions and refresh several times a day.</p>', 'unit_en' => 'kg']],
    ['products', 'slug', 'premiks-universal-1', ['title_en' => 'Universal Premix 1%', 'description_en' => '<p>Vitamin-mineral blend for enriching farm animal diets.</p>', 'recommendation_en' => '<p>Mix with the grain part of the diet according to the inclusion rate.</p>', 'unit_en' => 'kg']],
    ['recommendations', 'id', 1, ['title_en' => 'Switching to a new diet', 'body_en' => '<p>Change the diet gradually: 30% new feed for the first two days, then 60%, then full transition.</p>']],
    ['recommendations', 'id', 2, ['title_en' => 'Water control', 'body_en' => '<p>Poultry needs constant access to clean water because it directly affects feed intake.</p>']],
    ['recommendations', 'id', 3, ['title_en' => 'Premix mixing', 'body_en' => '<p>Premix is first blended with a small filler portion and then added to the total mix.</p>']],
    ['partners', 'id', 1, ['name_en' => 'AgroLogistics South', 'description_en' => '<p>Transport partner for compound feed deliveries to regional farms.</p>']],
    ['partners', 'id', 2, ['name_en' => 'VetRation', 'description_en' => '<p>Consulting partner for veterinary support and diet correction.</p>']],
    ['partners', 'id', 3, ['name_en' => 'GrainSupply', 'description_en' => '<p>Grain supplier with regular laboratory quality control.</p>']],
    ['contacts', 'id', 1, ['title_en' => 'Sales department', 'address_en' => 'Sevastopol, Industrialnaya st., 12', 'work_time_en' => 'Mon-Fri, 09:00-18:00']],
    ['useful_categories', 'id', 1, ['name_en' => 'Cattle feeding', 'description_en' => 'Materials about cattle feeding.']],
    ['useful_categories', 'id', 2, ['name_en' => 'Poultry farming', 'description_en' => 'Practical recommendations for poultry farms.']],
    ['useful_categories', 'id', 3, ['name_en' => 'Raw material quality', 'description_en' => 'Grain control, moisture and storage.']],
    ['useful_articles', 'slug', 'kak-menyat-racion-krs', ['title_en' => 'How to change a dairy herd diet smoothly', 'announce_en' => 'A transition period reduces stress and helps keep productivity.', 'body_en' => '<p>A sudden diet change may reduce feed intake. Introduce new feed gradually and monitor appetite and productivity.</p>']],
    ['useful_articles', 'slug', 'pochemu-granula-vazhna-dlya-broylera', ['title_en' => 'Why pellet quality matters for broilers', 'announce_en' => 'Uniform pellets improve feed intake and reduce losses.', 'body_en' => '<p>Pellet size and strength affect feed intake. Different growing periods use different fractions.</p>']],
    ['useful_articles', 'slug', 'vhodnoy-kontrol-zerna', ['title_en' => 'Incoming grain control at a feed mill', 'announce_en' => 'Raw material quality defines the stability of finished feed.', 'body_en' => '<p>Before production, grain components are checked for moisture, smell, impurity and other indicators.</p>']],
];
foreach ($translations as [$table, $keyColumn, $keyValue, $fields]) {
    update_translation($pdo, $table, $keyColumn, $keyValue, $fields);
}

$managerId = (int)$pdo->query("SELECT id FROM users WHERE email = 'manager@example.com' LIMIT 1")->fetchColumn();
if ($managerId > 0 && (int)$pdo->query('SELECT COUNT(*) FROM user_permissions WHERE user_id = ' . $managerId)->fetchColumn() === 0) {
    $stmt = $pdo->prepare('INSERT INTO user_permissions (user_id, section_key) VALUES (?, ?)');
    foreach (['news', 'pages', 'products', 'info-articles', 'orders'] as $sectionKey) {
        $stmt->execute([$managerId, $sectionKey]);
    }
}

echo "Готово. База данных и демо-данные созданы/проверены.\n";
echo "Админ: admin@example.com / admin123\n";
