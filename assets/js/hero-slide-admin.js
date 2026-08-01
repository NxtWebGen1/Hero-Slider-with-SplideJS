jQuery(document).ready(function ($) {
    var $wrap = $('.hero-slider-wrap');
    var THEME_STORAGE_KEY = 'heroSliderAdminTheme';

    // ----- Dark / Light mode toggle (admin UI preference only, stored client-side) -----

    function getStoredTheme() {
        try {
            return window.localStorage.getItem(THEME_STORAGE_KEY);
        } catch (err) {
            return null;
        }
    }

    function storeTheme(theme) {
        try {
            window.localStorage.setItem(THEME_STORAGE_KEY, theme);
        } catch (err) {
            // localStorage unavailable (private mode, disabled storage, etc) — theme just won't persist.
        }
    }

    function applyTheme(theme) {
        $wrap.toggleClass('hs-dark', theme === 'dark');
        $('#hero-slider-theme-switch').prop('checked', theme === 'dark');
    }

    applyTheme(getStoredTheme() === 'dark' ? 'dark' : 'light');

    $(document).on('change', '#hero-slider-theme-switch', function () {
        var theme = this.checked ? 'dark' : 'light';
        storeTheme(theme);
        applyTheme(theme);
    });

    // ----- Collapsible slide cards -----

    function toggleSlideCollapse($section) {
        $section.toggleClass('collapsed');
    }

    $(document).on('click', '.hero-slide-header', function () {
        toggleSlideCollapse($(this).closest('.hero-slide-section'));
    });

    $(document).on('keydown', '.hero-slide-header', function (e) {
        if (e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar') {
            e.preventDefault();
            toggleSlideCollapse($(this).closest('.hero-slide-section'));
        }
    });

    // Live-update the header preview as the heading field is typed into.
    $(document).on('input', 'input[name$="[heading]"]', function () {
        var $title = $(this).closest('.hero-slide-section').find('.hero-slide-title');
        var $preview = $title.find('.hero-slide-title-preview');
        var value = $(this).val();

        if (!value) {
            $preview.remove();
            return;
        }

        if (!$preview.length) {
            $preview = $('<span class="hero-slide-title-preview"></span>').appendTo($title);
        }
        $preview.text('— ' + value);
    });

    // ----- Slide repeater: renumber, add, duplicate, remove (scoped to one slider panel at a time) -----

    // Renumber slide titles and reindex every field's name/id/for attributes within a single
    // slider's slide container, so indexes stay sequential (0..n-1) regardless of what happens
    // in other sliders' panels. The [slides][N] segment (not just any bracketed number) is what
    // gets replaced, since a slider's own id can itself be a numeric string (e.g. "123").
    function renumberSlides($container) {
        $container.find('.hero-slide-section').each(function (index) {
            var $section = $(this);

            $section.find('.hero-slide-number').text('Slide ' + (index + 1));

            $section.find('input, textarea, button').each(function () {
                var $field = $(this);

                var name = $field.attr('name');
                if (name) {
                    $field.attr('name', name.replace(/\[slides\]\[\d+\]/, '[slides][' + index + ']'));
                }

                var id = $field.attr('id');
                if (id) {
                    $field.attr('id', id.replace(/_\d+$/, '_' + index));
                }
            });

            $section.find('label').each(function () {
                var $label = $(this);
                var forAttr = $label.attr('for');
                if (forAttr) {
                    $label.attr('for', forAttr.replace(/_\d+$/, '_' + index));
                }
            });
        });
    }

    // Add a new blank slide, cloned from the panel's own hidden <template>
    $(document).on('click', '.hero-slider-add-slide', function (e) {
        e.preventDefault();

        var $panel = $(this).closest('.hero-slider-panel');
        var $container = $panel.find('.hero-slider-slides-container');
        var template = $panel.find('template.hero-slider-slide-template')[0];
        var newIndex = $container.find('.hero-slide-section').length;
        var fragment = template.content.cloneNode(true);

        $(fragment)
            .find('[name], [id], [for]')
            .addBack('[name], [id], [for]')
            .each(function () {
                var $el = $(this);
                if ($el.attr('name')) {
                    $el.attr('name', $el.attr('name').replace('__INDEX__', newIndex));
                }
                if ($el.attr('id')) {
                    $el.attr('id', $el.attr('id').replace('__INDEX__', newIndex));
                }
                if ($el.attr('for')) {
                    $el.attr('for', $el.attr('for').replace('__INDEX__', newIndex));
                }
            });

        $container.append(fragment);
        renumberSlides($container);
    });

    // Duplicate a slide, copying its current (possibly unsaved) field values
    $(document).on('click', '.hero-slider-duplicate-slide', function (e) {
        e.preventDefault();

        var $original = $(this).closest('.hero-slide-section');
        var $container = $(this).closest('.hero-slider-slides-container');
        var $originalHr = $original.next('hr.hero-slider-divider');

        var $sectionClone = $original.clone();
        $sectionClone.removeClass('collapsed');
        var $hrClone = $originalHr.length ? $originalHr.clone() : $('<hr class="hero-slider-divider">');

        // clone() copies the "value"/"checked" attributes as originally rendered,
        // not any values the user has typed since — so copy live field state manually.
        var originalFields = $original.find('input, textarea');
        var clonedFields = $sectionClone.find('input, textarea');
        originalFields.each(function (i) {
            var $orig = $(this);
            var $copy = clonedFields.eq(i);
            if ($orig.is(':checkbox, :radio')) {
                $copy.prop('checked', $orig.prop('checked'));
            } else {
                $copy.val($orig.val());
            }
        });

        $sectionClone.insertAfter($originalHr.length ? $originalHr : $original);
        $hrClone.insertAfter($sectionClone);

        renumberSlides($container);
    });

    // Remove a slide (always keep at least one per slider)
    $(document).on('click', '.hero-slider-remove-slide', function (e) {
        e.preventDefault();

        var $container = $(this).closest('.hero-slider-slides-container');

        if ($container.find('.hero-slide-section').length <= 1) {
            return;
        }

        $(this).closest('.hero-slide-section').next('hr.hero-slider-divider').addBack().remove();
        renumberSlides($container);
    });

    // WordPress Media Library uploader for the image field
    $(document).on('click', '.hero-slider-upload-btn', function (e) {
        e.preventDefault();

        var button = $(this);
        var input = button.siblings('.hero-slider-image-url');

        var frame = wp.media({
            title: 'Select Slide Image',
            button: { text: 'Use this image' },
            multiple: false
        });

        frame.on('select', function () {
            var attachment = frame.state().get('selection').first().toJSON();
            input.val(attachment.url);
        });

        frame.open();
    });

    // ----- Multi-slider management: switch between, create, and delete sliders -----

    var $panelsContainer = $('#hero-slider-panels');
    var $sliderSelect = $('#hero-slider-active-select');

    // .attr() (not .data()) is used deliberately throughout this section: jQuery's .data()
    // auto-converts numeric-looking attribute values (e.g. a slider id of "123") into an
    // actual JS number, which would silently break the strict string comparisons below.
    function showActiveSlider() {
        var activeId = $sliderSelect.val();
        $panelsContainer.find('.hero-slider-panel').each(function () {
            $(this).toggle($(this).attr('data-slider-id') === activeId);
        });
    }

    showActiveSlider();
    $sliderSelect.on('change', showActiveSlider);

    function existingSliderIds() {
        return $panelsContainer.find('.hero-slider-panel').map(function () {
            return $(this).attr('data-slider-id');
        }).get();
    }

    $('#hero-slider-new-slider').on('click', function (e) {
        e.preventDefault();

        var raw = window.prompt('Enter a short ID for the new slider (letters, numbers, - and _ only), e.g. "homepage":', '');
        if (raw === null) {
            return;
        }

        var sliderId = raw.trim().toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9_-]/g, '');
        if (!sliderId) {
            window.alert('Please enter a valid slider ID (letters, numbers, - and _ only).');
            return;
        }

        if (existingSliderIds().indexOf(sliderId) !== -1) {
            window.alert('A slider with that ID already exists. Choose a different ID.');
            return;
        }

        var template = document.getElementById('hero-slider-panel-template');
        var fragment = template.content.cloneNode(true);

        // A <template>'s content lives in its own inert DocumentFragment, which normal
        // DOM traversal (including jQuery's .find()) cannot see into — so the nested
        // per-slide <template class="hero-slider-slide-template"> inside this panel
        // needs its placeholder replaced separately, or later "+ Add Slide" clicks on
        // this new slider would clone a slide still carrying the literal "__SLIDER_ID__".
        function replaceSliderIdPlaceholder(root) {
            $(root)
                .find('[name], [id], [for], [data-slider-id]')
                .addBack('[name], [id], [for], [data-slider-id]')
                .each(function () {
                    var $el = $(this);
                    ['name', 'id', 'for', 'data-slider-id'].forEach(function (attr) {
                        if ($el.attr(attr)) {
                            $el.attr(attr, $el.attr(attr).replace('__SLIDER_ID__', sliderId));
                        }
                    });
                });
        }

        replaceSliderIdPlaceholder(fragment);
        $(fragment).find('template.hero-slider-slide-template').each(function () {
            replaceSliderIdPlaceholder(this.content);
        });

        $panelsContainer.append(fragment);
        $sliderSelect.append($('<option></option>').val(sliderId).text(sliderId));
        $sliderSelect.val(sliderId);
        showActiveSlider();
    });

    $('#hero-slider-delete-slider').on('click', function (e) {
        e.preventDefault();

        var ids = existingSliderIds();
        if (ids.length <= 1) {
            window.alert('You must have at least one slider.');
            return;
        }

        var activeId = $sliderSelect.val();
        if (!window.confirm('Delete slider "' + activeId + '" and all of its slides? This cannot be undone once you save.')) {
            return;
        }

        $panelsContainer.find('.hero-slider-panel').filter(function () {
            return $(this).attr('data-slider-id') === activeId;
        }).remove();

        $sliderSelect.find('option[value="' + activeId + '"]').remove();
        $sliderSelect.trigger('change');
    });

    // ----- Shortcode Guide: copy-to-clipboard -----

    $(document).on('click', '.hero-slider-copy-shortcode', function () {
        var $button = $(this);
        var text = $button.data('shortcode');

        if (!navigator.clipboard || !navigator.clipboard.writeText) {
            return;
        }

        navigator.clipboard.writeText(text).then(function () {
            var original = $button.text();
            $button.text('Copied!');
            setTimeout(function () {
                $button.text(original);
            }, 1500);
        });
    });
});
