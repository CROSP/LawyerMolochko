# Відновлення зламаного сайту (no text, лише Safe Mode)

## Крок 1: Запустити скрипт відновлення

У терміналі в корені проєкту:

```bash
ddev exec "php /var/www/html/fix-broken-elementor.php"
```

Скрипт робить:
- очищення кешу Elementor (CSS/дані);
- вимкнення mu-plugin `elementor-section-c7f0487-align-fix.php` (перейменування в .bak);
- вимкнення Elementor Safe Mode.

## Крок 2: Перевірка сайту

1. Відкрийте сайт у **режимі інкогніто** або очистіть cookies для сайту.
2. Зайдіть на головну (без `?elementor-mode=safe`).

Якщо фронтенд відновився — на цьому можна зупинитися.

---

## Якщо все ще не працює: відновлення з бекапу БД

Ймовірні причини: зміни в `_elementor_data` (скрипти `replace-company-name.php`, `translate-about-powerlegal.php`, `update-footer-columns.php`) або пошкоджені дані Elementor.

### Відкотити всю БД з бекапу

```bash
ddev import-db --file=backup_before_company_name_replace_20260125_152842.sql
```

Або з іншого свіжого бекапу, якщо є.

### Після імпорту

1. Elementor → **Tools** → **Regenerate CSS & Data** → **Regenerate Files**.
2. Очистіть кеш браузера / відкрийте сайт в інкогніто.

---

## Повернення align‑фіксу (після відновлення)

Якщо потрібен знову `align-items: flex-start` для секції з фенсі‑боксами:

```bash
mv wp-content/mu-plugins/elementor-section-c7f0487-align-fix.php.bak wp-content/mu-plugins/elementor-section-c7f0487-align-fix.php
```

---

## Що саме могло зламати фронтенд

- **mu-plugin `elementor-section-c7f0487-align-fix.php`** — додає лише `align-items: flex-start` для однієї секції; рідко, але могло конфліктувати.
- **Зміни в `_elementor_data`** через `replace-company-name.php`, `translate-about-powerlegal.php` чи `update-footer-columns.php` — неправильний str_replace або збереження могло пошкодити JSON і призвести до порожнього контенту.
- **Пошкоджений або застарілий кеш Elementor** — очищення кешу та Regenerate Files часто це вирішують.
