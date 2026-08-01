# Hero Slider with SplideJS

![WordPress](https://img.shields.io/badge/WordPress-%E2%89%A55.2-blue) ![PHP](https://img.shields.io/badge/PHP-%E2%89%A57.2-777BB4) ![License](https://img.shields.io/badge/License-GPL--2.0-green) ![Version](https://img.shields.io/badge/Stable-1.4.0-brightgreen)

A lightweight WordPress plugin that adds a fully responsive, customizable hero slider using [SplideJS](https://splidejs.com/). Add and manage slides directly from your WordPress admin — no coding required.

---

## ✨ Features

- 🖼️ Add an unlimited number of slides from the WordPress Admin (add/remove on the fly)
- 📱 Fully responsive — works on desktop, tablet, and mobile
- ⚡ Powered by SplideJS for smooth, modern transitions
- ▶️ Optional autoplay with a configurable interval
- 🎨 Independent color, font size, and font family controls for the heading, paragraph, and button — site-wide or per shortcode
- 🌗 Dark/Light mode toggle for the admin settings page itself
- 🔘 Each slide supports:
  - Image (via URL or the WordPress Media Library uploader)
  - Alt text for accessibility
  - Heading
  - Paragraph / description
  - Button with custom text and link
- 🧩 Easy shortcode integration — just drop `[hero_slider]` anywhere
- 🎨 Customizable via CSS — no coding required for basic use

---

## 📦 Installation

1. Upload the plugin folder to `/wp-content/plugins/hero-slider-with-splidejs`
2. Activate the plugin via **WordPress Admin → Plugins**
3. Go to **Settings → Hero Slider Settings** to add your slides
4. Use the `[hero_slider]` shortcode on any page or post to display the slider

---

## 🛠️ Usage

### Adding Slides

1. Go to **Settings → Hero Slider Settings**
2. Fill in the slide details:
   - **Image URL** — direct link to your image, or click **Choose Image** to pick one from the Media Library
   - **Image Alt Text** — accessible description of the image
   - **Heading** — main title for the slide
   - **Paragraph** — supporting description text
   - **Button Text** — label for the CTA button
   - **Button Link** — URL the button points to
3. Click **+ Add Slide** for a new blank slide, **Duplicate Slide** to copy an existing one, or **Remove Slide** to delete one
4. Optionally enable **Autoplay** and set the interval under "Slider Behavior"
5. Save changes — the slider updates immediately

### Shortcode Guide

Place this shortcode on any page or post to display the slider:

```
[hero_slider]
```

By default it uses the site-wide **Appearance Defaults** (see below), but you can override any of them just for that one instance using attributes:

```
[hero_slider heading="no" overlay="no" heading_color="#ffcc00" heading_font_size="40" button_bg_color="#111111"]
```

| Attribute              | Values                  | Description                                            |
|------------------------|--------------------------|----------------------------------------------------------|
| `heading`              | `yes` / `no`             | Show or hide the slide heading                          |
| `paragraph`            | `yes` / `no`             | Show or hide the paragraph / description                |
| `overlay`              | `yes` / `no`             | Show or hide the dark gradient overlay behind the text   |
| `heading_color`        | hex color, e.g. `#fff`   | Heading text color                                       |
| `heading_font_size`    | number (px)              | Heading font size                                        |
| `heading_font_family`  | one of the font choices  | Heading font (see Appearance Defaults for the list)      |
| `paragraph_color`      | hex color                | Paragraph text color                                     |
| `paragraph_font_size`  | number (px)              | Paragraph font size                                      |
| `paragraph_font_family`| one of the font choices  | Paragraph font                                           |
| `button_color`         | hex color                | Button text color                                        |
| `button_bg_color`      | hex color                | Button background (replaces the default gradient)        |
| `button_font_size`     | number (px)              | Button font size                                         |
| `button_font_family`   | one of the font choices  | Button font                                              |

All attributes default to whatever is set in Appearance Defaults, and only need to be passed when overriding for that one instance.

### Appearance Defaults & Admin UI

Under **Settings → Hero Slider**, the **Appearance Defaults** card lets you independently configure the **heading**, **paragraph**, and **button** — each with its own text color, font size, and font family (plus a background color for the button) — along with toggles to hide the heading, paragraph, or dark overlay site-wide.

The settings page also has a **Dark Mode** switch in the top bar for the admin UI itself (a personal preference, saved in your browser), and slide cards can be collapsed/expanded by clicking their header — handy once you have more than a few slides.

---

## 📸 Screenshots

> _Add screenshots or a demo GIF here (e.g. the frontend slider and the admin settings page) before publishing/sharing this repo._

---

## 🎨 Customization

You can customize the slider's appearance by:

- Editing `assets/hero-slide.css` inside the plugin folder
- Adding custom CSS via **Appearance → Customize → Additional CSS** in WordPress

---

## ❓ Frequently Asked Questions

**How do I add a new slide?**
Go to **Settings → Hero Slider Settings**, fill in the slide details, and save.

**Can I have multiple sliders on one page?**
Currently the plugin supports one slider instance per page via the `[hero_slider]` shortcode.

**Can I customize the slider design?**
Yes! Use the Appearance settings (or shortcode attributes) to toggle the heading, paragraph, and overlay, or set a text color/font size — no CSS required. For deeper styling, edit `assets/hero-slide.css` or add custom CSS through your theme's customizer.

---

## ⚙️ Compatibility

| Requirement | Version |
|---|---|
| WordPress | ≥ 5.2 (tested up to 5.8) |
| PHP | ≥ 7.2 |

---

## 🤝 Contributing

Contributions are welcome! Please open an issue first to discuss what you'd like to change, then submit a pull request.

---

## 🧑‍💻 Development

Install dev dependencies and run the WordPress Coding Standards linter:

```
composer install
composer run lint
```

---

## 📄 License

This plugin is licensed under the [GPL-2.0 License](LICENSE).
