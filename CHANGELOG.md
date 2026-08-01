# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.5.1] - 2026-08-02

### Changed
- Removed the attributes reference table from the in-admin Shortcode Guide card (kept in the README instead) — it used WP core's `widefat` table class, which was fighting with the plugin's dark mode and rendering hard to read
- Full admin color palette redesign: a distinct indigo/violet theme instead of generic blue/gray, in both light and dark mode

### Fixed
- Text inputs, textareas, `<select>` elements, and WP's own `.button` elements (Add/Duplicate/Remove Slide, Choose Image, Copy, New/Delete Slider) weren't styled by the dark-mode toggle at all, since WP admin's built-in styling doesn't know about it — they kept their fixed light appearance regardless of the toggle. Now themed consistently in both modes.
- Buttons and `<select>` dropdowns used the same subtle background/border as the card they sat on, making them barely distinguishable from their surroundings in both themes; they now use their own more visible control colors, with a clearer hover state
- `.hero-slide-title-preview` (the live heading preview next to a slide's number) was a flex child with `max-width` + ellipsis truncation but no `min-width: 0` — a classic flexbox gotcha where the browser's default `min-width: auto` overrides `max-width` on a flex item, letting it overflow its row instead of truncating; fixed alongside making the toggle icon and "Slide N" label `flex-shrink: 0` so they're never squeezed
- Buttons and the slider `<select>` had mismatched heights (WP core `.button` padding/line-height vs. this plugin's custom select padding), causing visible misalignment when sitting side by side; both now share a consistent min-height
- Added overflow-wrap/word-break safety to card subtitles and the shortcode snippet display so unusually long text (e.g. a long slider ID) wraps instead of overflowing its card

---

## [1.5.0] - 2026-08-02

### Added
- Multi-slider support: create, switch between, and delete multiple independent sliders (each with its own slides and autoplay/interval), managed from a new "Sliders" card with a slider switcher
- `id` shortcode attribute — `[hero_slider id="homepage"]` — to pick which configured slider to display; omitting it falls back to the first configured slider
- In-admin **Shortcode Guide** card at the bottom of the settings page, listing a ready-to-copy shortcode (with a Copy button) for every saved slider, plus the full attribute reference table
- One-time automatic migration of existing single-slider data into a slider named "default", so upgrading doesn't break existing `[hero_slider]` shortcodes already placed on the site

### Fixed
- The frontend previously hard-coded `#main-carousel`/`#thumbnail-carousel` IDs and a single page-wide autoplay config; two sliders on the same page would have collided (duplicate IDs, wrong autoplay settings). Each `[hero_slider]` instance now renders with a unique ID and reads its own slider's autoplay/interval from data attributes, so multiple independently-configured sliders can coexist on one page
- Caught in review: a slider-repeater regex used during renumbering matched the first bracketed number in a field's `name` attribute, which would have targeted the wrong bracket if a slider was ever named a purely-numeric ID (e.g. `"123"`); narrowed to match `[slides][N]` specifically
- Caught in review: cloning a brand-new slider's markup didn't reach inside its nested per-slide `<template>` (a `<template>`'s content lives in an inert, separately-cloned DocumentFragment), which would have left "+ Add Slide" on a new slider silently writing to a bogus slider key; now processed explicitly
- `array_key_first()` (PHP 7.3+) was used to pick a fallback slider despite the plugin declaring PHP 7.2 support; replaced with a 7.2-compatible equivalent

---

## [1.4.0] - 2026-08-02

### Added
- Independent typography controls for the heading, paragraph, and button — each with its own text color, font size, and font family (plus a background color for the button) — as both site-wide Appearance Defaults and matching `[hero_slider]` shortcode attributes (`heading_color`, `heading_font_size`, `heading_font_family`, `paragraph_*`, `button_*`)
- `heading`/`paragraph`/`overlay` show/hide toggles, both site-wide and per shortcode
- Full admin settings page redesign: card-based layout, a Dark/Light mode switch for the admin UI (saved in the browser), toggle-switch styled checkboxes, and collapsible slide cards with a live heading preview in the header
- Shortcode Guide section in the README documenting every attribute

### Fixed
- Per-element text color was initially wired to the wrapping `<div>` instead of the `<h2>`/`<p>` elements themselves; since those already have an explicit `color` rule in the stylesheet, an inherited color from a parent would have been silently ignored
- Settings page was capped at a 960px max-width, leaving a large empty gap on wider screens; now fills the available admin content area

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
