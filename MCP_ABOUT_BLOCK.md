# Update front page blocks via MCP (normal way)

When **WordPress MCP is connected**, use these tool calls.

---

## Practice Areas section heading

**Tool:** `acf_update_fields_batch`  
**Arguments:** `post_id` = front page ID (from `acf_get_front_page_id`), `fields_json`:

```json
{"practice_areas_subtitle":"Наша експертиза","practice_areas_title":"Напрямки юридичної практики","practice_areas_description":"Надаємо кваліфіковану юридичну допомогу у кримінальних справах, спорах через ДТП, військових питаннях, сімейних та трудових спорах. Маємо значний досвід у цих напрямках та гарантуємо індивідуальний підхід до кожної справи."}
```

## Practice Areas grid items (repeater)

The front page has an ACF repeater **`practice_areas`**. Each row: `title`, `description`, `icon` (e.g. `flaticon flaticon-businessman`), `icon_image` (optional), `link` (optional, object `url`, `title`, `target`).

- To **read**: use `acf_get_field` with `post_id` = front page ID, `field` = `practice_areas`.
- To **update**: use `acf_update_fields_batch` with `post_id` and `fields_json` containing `{"practice_areas": [ {"title":"...", "description":"...", "icon":"flaticon flaticon-...", "link":{"url":"#", "title":"Детальніше", "target":"_self"}}, ... ]}`.

Default 20 items (titles, descriptions, icons) are in the theme at `wp-content/themes/molochko/inc/practice-areas-data.php`. If the repeater is empty, the theme fills it once from that file. After that, edit via WP Admin (Front Page) or MCP.

---

## About block

## 1. Get front page ID

**Tool:** `acf_get_front_page_id`  
**Arguments:** `{}`

Returns: `{ "front_page_id": <int> }`. Use that as `post_id` in step 2. If `0`, set a static front page in Settings → Reading first.

## 2. Update About fields (batch)

**Tool:** `acf_update_fields_batch`  
**Arguments:**

```json
{
  "post_id": "<front_page_id from step 1>",
  "fields_json": "{\"about_subtitle\":\"ПРО бюро\",\"about_title\":\"Пристрасть до справедливості. Досвід для перемоги.\",\"about_description\":\"<p>В Адвокатському бюро ми створили команду юристів, які поєднують глибокі знання, практичний досвід та щире прагнення допомогти клієнтам у складних юридичних ситуаціях.</p><p>Наша культура базується на довірі, прозорості та відповідальності. Щодня ми працюємо над тим, щоб клієнт відчував підтримку на кожному етапі — від першої консультації до завершення справи.</p>\",\"about_cta_line\":\"Телефонуйте нам 24/7. Почнімо боротися разом.\",\"about_contact_line\":\"<p><a href=\\\"tel:380506060079\\\">+38(050)-606-00-79</a> або <a href=\\\".../book-appointment\\\">Записатися на консультацію</a></p>\",\"about_name\":\"Молочко Тарас Вікторович\",\"about_role\":\"голова\"}"
}
```

Replace `<front_page_id from step 1>` with the number from step 1. Adjust `about_contact_line` phone/href if needed.

## Without MCP (one-time script)

From project root (or inside DDEV: `ddev exec php update-about-acf.php`):

```bash
php update-about-acf.php
```

Uses the same logic as MCP (get front page ID, update ACF fields). Requires static front page and ACF active. Run where WordPress DB is available (e.g. server or DDEV).
