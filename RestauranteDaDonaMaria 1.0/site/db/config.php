<?php
// DB (already created)
define('DB_HOST', 'localhost');
define('DB_NAME', 'fsiteons_maria');
define('DB_USER', 'fsiteons_maria');
define('DB_PASS', 'Ib=PVbl4}}V;');
define('DB_CHARSET', 'utf8mb4');

// PUBLIC base path where the site lives
define('BASE_PATH', '/maria/site');

// Orders
define('ORDER_TO_EMAIL', 'fcm030383@gmail.com');
define('WHATSAPP_E164', '+12019237409');

// Legacy fallback admin hash (unused once DB user exists).
define('ADMIN_PASSWORD_HASH', '$2y$10$9Bz3Yp2P5mGv9o3vG8bJueeA0z0W1sQMsYbXcKq3oH9k9zGQb8rGO');

// Auto install is OFF (db exists)
define('AUTO_INSTALL', false);

// Uploads
define('UPLOADS_MAX_MB', 8);
define('UPLOADS_VARIANTS', '400,800,1600');
define('UPLOADS_KEEP_ORIGINAL', false);
define('UPLOADS_GENERATE_WEBP', true);

// Computed paths
define('UPLOADS_URL', BASE_PATH . '/uploads');
define('UPLOADS_ROOT', __DIR__ . '/../uploads');
