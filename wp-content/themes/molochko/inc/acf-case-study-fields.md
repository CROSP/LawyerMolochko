# ACF fields for Case Studies section

## 1. Front page: Case Studies section (heading + button)

**Location:** Post = Front Page (or Options page if you prefer).

Create a field group with **Location** rule:  
`Post Type` is equal to `page` AND `Page` is equal to `[your front page]`  
(or use a separate ACF Options sub-page "Case Studies Section").

| Label            | Name                     | Type   | Notes                    |
|-----------------|--------------------------|--------|--------------------------|
| Subtitle        | `case_studies_subtitle`  | Text   | e.g. "Досвід роботи"     |
| Title           | `case_studies_title`     | Text   | e.g. "Останні кейси"     |
| Description     | `case_studies_description` | Textarea | Short paragraph          |
| Button text     | `case_studies_button_text` | Text | e.g. "Всі кейси"         |
| Button URL      | `case_studies_button_url`  | URL   | Archive or custom URL    |

Defaults are in the theme if these fields are empty.

---

## 2. Post type: Case Study (optional)

**Location:** Post Type is equal to `molochko-case-study`.

The section uses only core data: **Title**, **Featured image**, **Excerpt** (optional), and taxonomy **Категорії кейсів** (`case_study_category`). No ACF is required.

If you want extra fields on each case study, add:

| Label       | Name           | Type     | Notes                    |
|------------|----------------|----------|--------------------------|
| Case date  | `case_date`    | Date Picker | Optional              |
| Outcome    | `outcome`      | Text     | e.g. "Винагорода 100 000 грн" |

These are optional; the template does not display them by default (you can add them to the single case study template later).
