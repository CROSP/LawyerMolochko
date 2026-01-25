# 🔄 MCP замість Custom PHP Скриптів

## 📋 Короткий огляд

Ваш сайт тепер використовує **MCP (Model Context Protocol)** для управління WordPress/Elementor сторінками замість кастомних PHP скриптів.

## 🎯 Що змінилося

### ❌ Старий спосіб (Custom PHP скрипти)

```bash
# Запуск PHP скриптів через DDEV
ddev exec php update-elementor-description.php
ddev exec php update-all-elementor-descriptions.php
ddev exec php force-update-elementor-description.php
```

**Проблеми:**
- Потрібен прямий доступ до файлів WordPress
- Потрібно знати структуру Elementor API
- Складно обробляти помилки
- Потребує DDEV для виконання

### ✅ Новий спосіб (MCP)

Просто попросіть AI помічника через природну мову:

```
Отримай сторінку з ID 222
Онови опис елемента 322ae9a на сторінці 222
Створи нову сторінку "Послуги"
```

**Переваги:**
- ✅ Працює з будь-якого місця (не потрібен DDEV)
- ✅ Безпечний доступ через REST API
- ✅ Автоматична обробка помилок
- ✅ Легко інтегрується з AI

## 🛠 Доступні MCP інструменти

1. **get_page** - Отримати сторінку
2. **create_page** - Створити нову сторінку
3. **update_page** - Оновити існуючу сторінку
4. **delete_page** - Видалити сторінку
5. **get_page_id_by_slug** - Знайти ID за slug
6. **download_page_to_file** - Зберегти сторінку у файл
7. **update_page_from_file** - Оновити сторінку з файлу

## 📚 Документація

- **[MCP_USAGE_GUIDE.md](MCP_USAGE_GUIDE.md)** - Повне керівництво з використання MCP
- **[MCP_TROUBLESHOOTING.md](MCP_TROUBLESHOOTING.md)** - Діагностика проблем

## 🚀 Швидкий старт

### 1. Перевірте підключення MCP

Якщо MCP інструменти не працюють:
1. Перезапустіть Cursor повністю
2. Перевірте `~/.cursor/mcp.json` (конфігурація)
3. Перевірте Node.js: `node --version`
4. Дивіться [MCP_TROUBLESHOOTING.md](MCP_TROUBLESHOOTING.md)

### 2. Використовуйте природну мову

Просто попросіть AI помічника:

**Приклади:**
- "Отримай інформацію про сторінку з ID 222"
- "Онови опис на головній сторінці"
- "Знайди всі сторінки з текстом 'старий текст' і онови їх"

### 3. Не використовуйте PHP скрипти

**Замість:**
```bash
ddev exec php update-elementor-description.php
```

**Використовуйте:**
```
Онови опис елемента на сторінці 222
```

## 📂 Файли проєкту

### Активні скрипти (залишаються як backup)

Ці файли залишаються, але **не використовуйте їх регулярно**:

- `update-elementor-description.php` → Використовуйте MCP `update_page`
- `update-all-elementor-descriptions.php` → Використовуйте MCP `update_page` (з циклом)
- `force-update-elementor-description.php` → Використовуйте MCP `update_page`
- `update-via-mcp-api.php` → Не потрібен, використовуйте MCP напряму

### Документація

- `MCP_USAGE_GUIDE.md` - Як використовувати MCP
- `MCP_TROUBLESHOOTING.md` - Діагностика проблем
- `ELEMENTOR_MCP_SETUP_COMPLETE.md` - Інформація про налаштування

## ⚙️ Конфігурація

MCP налаштований в `~/.cursor/mcp.json`:

```json
{
  "mcpServers": {
    "Elementor MCP": {
      "command": "npx",
      "args": ["-y", "elementor-mcp"],
      "env": {
        "WP_URL": "https://lawyermolochko.ddev.site:8443",
        "WP_APP_USER": "admin",
        "WP_APP_PASSWORD": "rJtC9nPsIC54LE0buyUjryzj"
      }
    }
  }
}
```

## 💡 Приклади використання

### Отримати сторінку
```
Отримай сторінку з ID 222 і покажи її структуру
```

### Оновити сторінку
```
Онови сторінку 222, змінивши опис елемента 322ae9a на новий текст
```

### Створити нову сторінку
```
Створи нову сторінку "Контакти" з Elementor контентом
```

### Масове оновлення
```
Знайди всі сторінки з елементами, що містять "старий текст" 
і заміни на "новий текст"
```

### Backup сторінки
```
Збережи сторінку 222 у файл backup-page-222.json
```

## ❓ Часті питання

**Q: Чи можу я все ще використовувати PHP скрипти?**  
A: Так, але MCP рекомендований для нових операцій.

**Q: Чому MCP не працює?**  
A: Дивіться [MCP_TROUBLESHOOTING.md](MCP_TROUBLESHOOTING.md)

**Q: Чи потрібен DDEV для MCP?**  
A: Ні! MCP працює через REST API, не потребує DDEV.

**Q: Що робити, якщо потрібна функція, якої немає в MCP?**  
A: MCP підтримує стандартні операції WordPress. Для спеціальних потреб можна додати custom endpoints.

## 🔗 Корисні посилання

- [Elementor MCP GitHub](https://github.com/aguaitech/Elementor-MCP)
- [WordPress REST API Handbook](https://developer.wordpress.org/rest-api/)
- Ваша конфігурація: `~/.cursor/mcp.json`

---

**Важливо:** Для роботи MCP потрібен **повний перезапуск Cursor** після зміни конфігурації!

**Статус:** ✅ Готово до використання
