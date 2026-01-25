# Діагностика та усунення проблем MCP

## ⚠️ Проблема: MCP не підключається

Якщо MCP інструменти не працюють, виконайте діагностику:

## 🔍 Крок 1: Перевірка конфігурації

### Перевірте файл `~/.cursor/mcp.json`

Файл повинен містити:

```json
{
  "mcpServers": {
    "Elementor MCP": {
      "command": "npx",
      "args": ["-y", "elementor-mcp"],
      "env": {
        "WP_URL": "https://lawyermolochko.ddev.site:8443",
        "WP_APP_USER": "admin",
        "WP_APP_PASSWORD": "VQyDBhUyvbZYgGgo7KBQH69i"
      }
    }
  }
}
```

**Що перевірити:**
- ✅ Файл існує та має правильний JSON формат
- ✅ `WP_URL` вказує на правильний сайт
- ✅ `WP_APP_USER` - правильний username (зазвичай "admin")
- ✅ `WP_APP_PASSWORD` - правильний application password

## 🔍 Крок 2: Перезапуск Cursor

**Важливо:** Після зміни `mcp.json` потрібно **повністю закрити і відкрити Cursor**:

1. Вихід з Cursor (Cmd+Q на Mac, Alt+F4 на Windows/Linux)
2. Дочекатися повного закриття (перевірити в Activity Monitor/Task Manager)
3. Відкрити Cursor знову
4. Почекати 10-30 секунд для ініціалізації MCP серверів

## 🔍 Крок 3: Перевірка Node.js

MCP сервер потребує Node.js:

```bash
node --version
# Повинно показати версію (наприклад, v18.x.x або v20.x.x)
```

Якщо Node.js не встановлений:
- **Mac**: `brew install node`
- **Windows**: Завантажити з [nodejs.org](https://nodejs.org/)
- **Linux**: `sudo apt install nodejs npm` (або відповідна команда для вашого дистрибутиву)

## 🔍 Крок 4: Перевірка WordPress REST API

Перевірте, що WordPress REST API доступний:

```bash
# Відкрийте в браузері або використайте curl:
curl https://lawyermolochko.ddev.site:8443/wp-json/wp/v2/

# Або перевірте в браузері:
# https://lawyermolochko.ddev.site:8443/wp-json/wp/v2/
```

**Очікуваний результат:** JSON з інформацією про доступні endpoints.

Якщо помилка SSL/сертифікату, це нормально для локального розробки з самопідписаним сертифікатом.

## 🔍 Крок 5: Перевірка Application Password

Application Password може бути деактивовано або видалено.

### Перевірка через WP-CLI (в DDEV):

```bash
# Увійдіть в DDEV контейнер
ddev ssh

# Перевірте application passwords
wp user application-password list 1

# Якщо немає - створіть новий
wp user application-password create 1 'Elementor MCP'

# Скопіюйте створений пароль (формат: XXXX XXXX XXXX XXXX)
# Оновіть WP_APP_PASSWORD в ~/.cursor/mcp.json
```

### Створення через WordPress Admin:

1. Увійдіть в WordPress: `https://lawyermolochko.ddev.site:8443/wp-admin`
2. Перейдіть: **Users → Your Profile → Application Passwords**
3. Створіть новий: "Elementor MCP"
4. Скопіюйте пароль (виглядає як: `XXXX XXXX XXXX XXXX`)
5. Оновіть `WP_APP_PASSWORD` в `~/.cursor/mcp.json`
6. **Перезапустіть Cursor**

## 🔍 Крок 6: Перевірка налаштувань WordPress

### Перевірка, що REST API увімкнено:

```bash
# В DDEV контейнері
ddev exec wp option get rest_authentication_errors
# Повинно бути пусто або "allow"
```

### Перевірка, що Application Passwords увімкнені:

```bash
ddev exec wp option get enable_application_passwords
# Повинно бути "1" (увімкнено)
```

Якщо вимкнено, увімкніть:
```bash
ddev exec wp option update enable_application_passwords 1
```

## 🔍 Крок 7: Тестування підключення вручну

### Тест через curl:

```bash
# Замініть YOUR_PASSWORD на реальний application password (з пробілами)
curl -X GET "https://lawyermolochko.ddev.site:8443/wp-json/wp/v2/pages/222" \
  -u "admin:YOUR_PASSWORD" \
  -k  # -k ігнорує SSL помилки для локального розробки
```

**Очікуваний результат:** JSON з даними сторінки.

### Тест через Node.js:

Створіть тестовий файл `test-mcp.js`:

```javascript
const https = require('https');
const url = 'https://lawyermolochko.ddev.site:8443/wp-json/wp/v2/pages/222';
const auth = Buffer.from('admin:rJtC9nPsIC54LE0buyUjryzj').toString('base64');

https.get(url, {
  headers: {
    'Authorization': `Basic ${auth}`
  },
  rejectUnauthorized: false  // Для локального розробки з самопідписаним сертифікатом
}, (res) => {
  let data = '';
  res.on('data', (chunk) => { data += chunk; });
  res.on('end', () => {
    console.log('Status:', res.statusCode);
    console.log('Data:', data.substring(0, 200));
  });
}).on('error', (err) => {
  console.error('Error:', err.message);
});
```

Запустіть:
```bash
node test-mcp.js
```

## 🐛 Типові помилки та рішення

### Помилка: "WordPress API request failed"

**Причини:**
1. ❌ Application Password неправильний або видалений
2. ❌ WordPress REST API вимкнено
3. ❌ SSL сертифікат не приймається (для локального розробки це нормально)

**Рішення:**
- Перевірте Application Password (Крок 5)
- Перевірте REST API (Крок 4)
- Для локального розробки помилки SSL можна ігнорувати

### Помилка: "Connection refused" або "ECONNREFUSED"

**Причини:**
1. ❌ DDEV не запущений
2. ❌ Неправильний URL
3. ❌ Порт заблокований

**Рішення:**
```bash
# Перевірте, що DDEV запущений
ddev status

# Якщо ні - запустіть
ddev start

# Перевірте URL
ddev describe
```

### Помилка: "MCP server not found" або інструменти недоступні

**Причини:**
1. ❌ MCP конфігурація неправильна
2. ❌ Cursor не перезапущений після зміни конфігурації
3. ❌ Node.js не встановлений

**Рішення:**
- Перевірте `~/.cursor/mcp.json` (Крок 1)
- Перезапустіть Cursor (Крок 2)
- Перевірте Node.js (Крок 3)

### Помилка: "npx: command not found"

**Причини:**
- ❌ Node.js/npm не встановлені
- ❌ PATH не налаштований

**Рішення:**
- Встановіть Node.js (Крок 3)
- Перевірте `npm --version`
- Перезапустіть термінал/Cursor

## ✅ Чек-лист для діагностики

Відмітьте кожен пункт:

- [ ] `~/.cursor/mcp.json` існує і має правильний формат
- [ ] `WP_URL` правильний і сайт доступний
- [ ] `WP_APP_PASSWORD` правильний і активний
- [ ] Node.js встановлений (`node --version`)
- [ ] WordPress REST API доступний (`/wp-json/wp/v2/`)
- [ ] Application Passwords увімкнені в WordPress
- [ ] Cursor повністю перезапущений після зміни конфігурації
- [ ] DDEV запущений (`ddev status`)

## 📞 Додаткова допомога

Якщо проблема не вирішується:

1. **Перевірте логи Cursor:**
   - Cursor → Help → Toggle Developer Tools → Console
   - Шукайте помилки, пов'язані з MCP

2. **Перевірте логи WordPress:**
   ```bash
   ddev exec wp debug log
   # Або перевірте wp-content/debug.log
   ```

3. **Тестуйте MCP сервер напряму:**
   ```bash
   npx -y elementor-mcp
   # Потрібні змінні оточення: WP_URL, WP_APP_USER, WP_APP_PASSWORD
   ```

## 🔄 Резервний план

Якщо MCP не працює, ви все ще можете використовувати:

1. **Custom PHP скрипти** (тимчасово, доки MCP не налаштований)
2. **WP-CLI через DDEV** (`ddev exec wp ...`)
3. **WordPress Admin панель** (для ручних змін)

Але MCP - це рекомендований спосіб для майбутнього.

---

**Останнє оновлення:** 2026-01-17
