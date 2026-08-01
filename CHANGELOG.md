# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.4.0] - 2026-08-02

### Added
- Independent typography controls for the heading, paragraph, and button — each with its own text color, font size, and font family (plus a background color for the button) — as both site-wide Appearance Defaults and matching `[hero_slider]` shortcode attributes (`heading_color`, `heading_font_size`, `heading_font_family`, `paragraph_*`, `button_*`)
- `heading`/`paragraph`/`overlay` show/hide toggles, both site-wide and per shortcode
- Full admin settings page redesign: card-based layout, a Dark/Light mode switch for the admin UI (saved in the browser), toggle-switch styled checkboxes, and collapsible slide cards with a live heading preview in the header
- Shortcode Guide section in the README documenting every attribute

### Fixed
- Per-element text color was initially wired to the wrapping `<div>` instead of the `<h2>`/`<p>` elements themselves; since those already have an explicit `color` rule in the stylesheet, an inherited color from a parent would have been silently ignored

---

## [1.3.0] - 2026-08-02

### Added
- WordPress Media Library uploader for slide images ("Choose Image" button)
- Dedicated alt text field per slide, falling back to the heading if left blank
- Unlimited slides via "+ Add Slide" / "Duplicate Slide" / "Remove Slide" (previously hardcoded to a fixed 5)
- Autoplay toggle with configurable interval under "Slider Behavior"
- `uninstall.php` to remove plugin options on deletion
- Admin warning when a slide has content but no image (won't render on the frontend)
- `composer.json` + `phpcs.xml.dist` (WordPress Coding Standards) + `.editorconfig` for contributors

### Fixed
- `[hero_slider]` shortcode was registered as `hero-slider`, so it never rendered
- Frontend JS was never enqueued to load in the footer due to a malformed `wp_enqueue_script()` call
- Script cache-busting version for `hero-slide.js` used the CSS file's modified time instead of its own
- `hero-slide.js` (which calls `new Splide(...)`) was enqueued without depending on `splide-js`, so the Splide library could load after the code that needs it
- Settings were saved without sanitization; now sanitized/escaped per field on save

### Security
- Added direct-access guards (`ABSPATH` check) to all PHP files

---

## [1.1.0] - 2025-04-27

### Added
- Dynamic slide management via the admin settings page
- Support for multiple slides on the frontend

### Fixed
- Button link functionality not working properly

### Improved
- Enhanced mobile and tablet responsiveness
- Bug fixes and small UI tweaks for better user experience

---

## [1.0.0] - 2025-04-27

### Added
- Initial release of Hero Slider plugin
- Single static image slider support
- Each slide includes: heading, description, and button
- Customizable via hardcoded fields in the plugin code
- Basic styling including fade-in effects and responsiveness
