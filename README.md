# Restaurante da Dona Maria

Baseline v1.0.0 — multi-item order modal, admin CRUD, image upload with 800px variants, cache-busted JS (v6), and simple email + WhatsApp checkout.

**Live repo:** https://github.com/Fredcm83/RestaurantedaDonaMaria  
**Latest release:** https://github.com/Fredcm83/RestaurantedaDonaMaria/releases/tag/v1.0.0

---

## Features
- **Menu Manager (Admin):** create, edit, delete items; upload images; tags; featured/available flags.
- **Responsive images (800px):** backend auto-generates a single 800px variant to keep pages lean.
- **Order flow:** add multiple items, quantities, customer details; email summary; open WhatsApp for confirmation.
- **Cache-busted assets:** JS served with `?v=6-<mtime>` to avoid stale caches.
- **No framework:** PHP + MySQL, minimal JS and CSS. Fast and host-friendly.

---

## Tech stack
- PHP 7.4+ (works on typical shared hosting)
- MySQL/MariaDB
- Vanilla JS, no build tools
- Plain CSS

---

## Install

1. **Upload files** to your hosting (this repo’s `site/` is the web root for the project).
2. **Config DB:** edit `db/config.php`:
   ```php
   define('DB_HOST',     'localhost');
   define('DB_NAME',     'your_db');
   define('DB_USER',     'your_user');
   define('DB_PASS',     'your_pass');
   define('DB_CHARSET',  'utf8mb4');

   // Email + WhatsApp
   define('ORDER_TO_EMAIL', 'you@example.com');
   define('WHATSAPP_E164',  '+12015550123'); // E.164 format
