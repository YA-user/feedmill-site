PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'content_manager',
    active INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS pages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    slug TEXT NOT NULL UNIQUE,
    title TEXT NOT NULL,
    title_en TEXT,
    body TEXT NOT NULL,
    body_en TEXT
);

CREATE TABLE IF NOT EXISTS news (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    slug TEXT NOT NULL UNIQUE,
    announce TEXT NOT NULL,
    title_en TEXT,
    announce_en TEXT,
    body TEXT NOT NULL,
    body_en TEXT,
    image TEXT,
    video_url TEXT,
    published_at TEXT NOT NULL,
    is_active INTEGER NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS product_categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    name_en TEXT,
    description TEXT,
    description_en TEXT,
    sort_order INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    title_en TEXT,
    slug TEXT NOT NULL UNIQUE,
    description TEXT NOT NULL,
    description_en TEXT,
    recommendation TEXT,
    recommendation_en TEXT,
    price REAL NOT NULL DEFAULT 0,
    unit TEXT NOT NULL DEFAULT 'кг',
    unit_en TEXT,
    image TEXT,
    video_url TEXT,
    is_active INTEGER NOT NULL DEFAULT 1,
    FOREIGN KEY (category_id) REFERENCES product_categories(id) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS recommendations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    title_en TEXT,
    body TEXT NOT NULL,
    body_en TEXT,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS partners (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    name_en TEXT,
    description TEXT NOT NULL,
    description_en TEXT,
    website TEXT,
    logo TEXT
);

CREATE TABLE IF NOT EXISTS contacts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    title_en TEXT,
    address TEXT NOT NULL,
    address_en TEXT,
    phone TEXT NOT NULL,
    email TEXT NOT NULL,
    work_time TEXT,
    work_time_en TEXT,
    map_url TEXT
);

CREATE TABLE IF NOT EXISTS useful_categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    name_en TEXT,
    description TEXT,
    description_en TEXT
);

CREATE TABLE IF NOT EXISTS useful_articles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    title_en TEXT,
    slug TEXT NOT NULL UNIQUE,
    announce TEXT NOT NULL,
    announce_en TEXT,
    body TEXT NOT NULL,
    body_en TEXT,
    published_at TEXT NOT NULL,
    is_active INTEGER NOT NULL DEFAULT 1,
    FOREIGN KEY (category_id) REFERENCES useful_categories(id) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS media (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    type TEXT NOT NULL CHECK (type IN ('photo', 'video')),
    title TEXT NOT NULL,
    description TEXT,
    file_path TEXT,
    video_url TEXT,
    sort_order INTEGER NOT NULL DEFAULT 0,
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS user_permissions (
    user_id INTEGER NOT NULL,
    section_key TEXT NOT NULL,
    PRIMARY KEY (user_id, section_key),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id INTEGER,
    product_title TEXT NOT NULL,
    price REAL NOT NULL,
    quantity REAL NOT NULL,
    total REAL NOT NULL,
    full_name TEXT NOT NULL,
    phone TEXT NOT NULL,
    email TEXT,
    comment TEXT,
    status TEXT NOT NULL DEFAULT 'new',
    created_at TEXT NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS order_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER NOT NULL,
    product_id INTEGER,
    product_title TEXT NOT NULL,
    price REAL NOT NULL,
    quantity REAL NOT NULL,
    unit TEXT NOT NULL DEFAULT 'кг',
    total REAL NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);
