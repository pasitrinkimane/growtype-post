/**
 *
 */
export function filterPosts() {
    if ($('.growtype-post-custom-filters').length > 0) {
        $('.growtype-post-custom-filters .growtype-post-custom-filters-single input').on('input', function (element) {
            let form = $(this).closest('.growtype-post-container-wrapper');

            setTimeout(function () {
                findValue(form);
            }, 500)
        });

        $('.growtype-post-custom-filters .growtype-post-custom-filters-single select').on('change', function (element) {
            let form = $(this).closest('.growtype-post-container-wrapper');

            setTimeout(function () {
                findValue(form);
            }, 500)
        });

        function findValue(form) {
            let values = {};
            form.find('.growtype-post-custom-filters-single').map(function (index, element) {
                let value = '';
                if ($(element).find('select').length > 0) {
                    value = $(element).find('select').val();
                } else {
                    value = $(element).find('input').val();
                }

                values[$(element).attr('data-name')] = value;
            });

            form.find('.growtype-post-container .growtype-post-single').show();

            form.find('.growtype-post-container .growtype-post-single').each(function (index, element) {
                let row = $(element);

                let isVisible = true;
                Object.entries(values).map(function (element, index) {
                    let name = element[0];
                    if (row.attr('data-cat-' + name)) {
                        let value = element[1].toLowerCase();
                        let content = row.attr('data-cat-' + name).toLowerCase();

                        if (value === 'all') {
                            return;
                        }

                        if (!content.includes(value)) {
                            isVisible = false;
                        }
                    }
                })

                if (!isVisible) {
                    row.hide();
                }
            });
        }
    }
}
