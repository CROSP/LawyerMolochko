# Molochko Theme

Standalone theme that **replicates the Powerlegal design without Elementor**. Uses **ACF Pro**, vanilla CSS, and jQuery for the front. Same look: header, footer, hero, fancy boxes, typography, colors.

## Requirements

- **ACF Pro** (for Front Page fields: hero shortcode, fancy boxes repeater)
- **Revolution Slider** (optional; if you keep `[rev_slider alias="slider-1"]` on the front page)
- **jQuery** (included with WordPress)

## Activation

1. **Appearance → Themes** → activate **Molochko**.
2. **Settings → Reading** → set **A static page** as front page and choose your home page.
3. **Appearance → Menus** → assign a menu to **Primary Menu**.
4. **Appearance → Customize → Molochko Colors** → set Primary (`#ad9779`), Secondary (`#1a243f`) if needed.
5. **Appearance → Customize → Site Identity** → set **Logo** (or place `logo.png` in `themes/molochko/assets/images/`).

## Front Page (ACF)

On the **Front Page** (the page set as home) you’ll see:

- **Hero Slider Shortcode**  
  e.g. `[rev_slider alias="slider-1"]`. Leave blank to use that default.

- **Fancy Boxes** (repeater)  
  - **Icon**: CSS classes, e.g. `flaticon flaticon-calling`, `flaticon flaticon-award`, `flaticon flaticon-medal`
  - **Title**, **Description**, **Button text**, **Link**, **Image** (optional)

If ACF is not filled, the theme falls back to 3 default cards (Безкоштовна консультація, 10 років досвіду, Нагороди та сертифікати).

## What’s included (no Elementor)

- **Header**: logo + primary menu; mobile hamburger + side panel.
- **Footer**: copyright.
- **Front page**: hero (shortcode) + 3 fancy boxes (layout 4, same HTML/CSS as Powerlegal).
- **Inner pages**: page title block, content, sidebar.
- **Blog**: archive loop, single post, sidebar, pagination.
- **404**, **search** (base templates).
- **Icons**: Flaticon + Material Design Iconic (fonts in `assets/fonts/`).
- **Grid**: Bootstrap 5 grid only (`grid.css`).

## Customization

- **Colors**: Appearance → Customize → Molochko Colors.
- **Logo**: Customize → Site Identity, or `themes/molochko/assets/images/logo.png`.
- **Menus**: Appearance → Menus → Primary Menu.
- **Language switcher (Polylang)**: Add it from the menu screen — open **Primary Menu**, in the **Language switcher** meta box add it to the menu. It will show in both desktop nav and mobile panel. Choose “Displays as a dropdown” and “Displays flags” in the menu item options if you want dropdown + flags.

## Files

- `front-page.php` – hero + fancy boxes
- `header.php` / `footer.php` – global layout
- `template-parts/header.php`, `template-parts/footer.php` – header/footer markup
- `inc/acf-front-page.php` – ACF field group for the front page
- `assets/css/theme.css` – main styles (no Elementor)
- `assets/js/theme.js` – mobile menu, side panel, scroll-to-top
- `assets/css/grid.css` – Bootstrap grid
- `assets/fonts/material/`, `assets/fonts/flaticon/` – icon fonts
