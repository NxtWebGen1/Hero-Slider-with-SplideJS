jQuery(document).ready(function ($) {
    var $container = $('#hero-slider-slides-container');

    // Renumber slide titles and reindex every field's name/id/for attributes
    // so they stay sequential (0..n-1) after slides are added or removed.
    function renumberSlides() {
        $container.find('.hero-slide-section').each(function (index) {
            var $section = $(this);

            $section.find('.hero-slide-title').text('Slide ' + (index + 1));

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
