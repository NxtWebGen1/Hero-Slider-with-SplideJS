# Hero Slider with SplideJS

![WordPress](https://img.shields.io/badge/WordPress-%E2%89%A55.2-blue) ![PHP](https://img.shields.io/badge/PHP-%E2%89%A57.2-777BB4) ![License](https://img.shields.io/badge/License-GPL--2.0-green) ![Version](https://img.shields.io/badge/Stable-1.1.0-brightgreen)

A lightweight WordPress plugin that adds a fully responsive, customizable hero slider using [SplideJS](https://splidejs.com/). Add and manage slides directly from your WordPress admin — no coding required.

---

## ✨ Features

- 🖼️ Add multiple slides from the WordPress Admin
- 📱 Fully responsive — works on desktop, tablet, and mobile
- ⚡ Powered by SplideJS for smooth, modern transitions
- 🔘 Each slide supports:
  - Image (via URL)
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
   - **Image URL** — direct link to your image
   - **Heading** — main title for the slide
   - **Paragraph** — supporting description text
   - **Button Text** — label for the CTA button
   - **Button Link** — URL the button points to
3. Save changes — the slider updates immediately

### Shortcode

Place this shortcode on any page or post:

```
[hero_slider]
```

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
Yes! Edit `assets/hero-slide.css` or add custom CSS through your theme's customizer.

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

## 📄 License

This plugin is licensed under the [GPL-2.0 License](LICENSE).
