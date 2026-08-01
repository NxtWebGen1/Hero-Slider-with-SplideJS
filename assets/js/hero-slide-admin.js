jQuery(document).ready(function ($) {
    var $container = $('#hero-slider-slides-container');
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

    // ----- Slide repeater: renumber, add, duplicate, remove -----

    // Renumber slide titles and reindex every field's name/id/for attributes
    // so they stay sequential (0..n-1) after slides are added or removed.
    function renumberSlides() {
        $container.find('.hero-slide-section').each(function (index) {
            var $section = $(this);

            $section.find('.hero-slide-number').text('Slide ' + (index + 1));

            $section.find('input, textarea, button').each(function () {
                var $field = $(this);

                var name = $field.attr('name');
                if (name) {
                    $field.attr('name', name.replace(/\[\d+\]/, '[' + index + ']'));
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

    // Add a new blank slide, cloned from the hidden <template>
    $('#hero-slider-add-slide').on('click', function (e) {
        e.preventDefault();

        var template = document.getElementById('hero-slider-slide-template');
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
        renumberSlides();
    });

    // Duplicate a slide, copying its current (possibly unsaved) field values
    $(document).on('click', '.hero-slider-duplicate-slide', function (e) {
        e.preventDefault();

        var $original = $(this).closest('.hero-slide-section');
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

        renumberSlides();
    });

    // Remove a slide (always keep at least one)
    $(document).on('click', '.hero-slider-remove-slide', function (e) {
        e.preventDefault();

        if ($container.find('.hero-slide-section').length <= 1) {
            return;
        }

        $(this).closest('.hero-slide-section').next('hr.hero-slider-divider').addBack().remove();
        renumberSlides();
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
});
