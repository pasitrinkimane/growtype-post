import {initChosenParams} from "./initChosenParams";
import {loadMorePosts} from "./loadMorePosts";
import {updateUrlWithFilterParams} from "./updateUrlWithFilterParams";
import {filterPosts} from "./filterPosts";

export function initFiltering(wrapper) {
    if ($(wrapper).find('.growtype-post-custom-filters').length > 0 && $(wrapper).find('select.growtype-post-custom-filters').next('.chosen-container').length === 0) {
        $(wrapper).find('select.growtype-post-custom-filter').each(function (index, element) {
            $(element).chosen(initChosenParams($(element)));
        })
    }

    if ($(wrapper).find('.growtype-post-custom-filters').length > 0) {
        $(wrapper).find('.growtype-post-custom-filters .growtype-post-custom-filters-single input').on('input', function (element) {
            let wrapper = $(this).closest('.growtype-post-container-wrapper');

            setTimeout(function () {
                applyPostsFiltering(wrapper);
            }, 500)
        });

        $(wrapper).find('.growtype-post-custom-filters .growtype-post-custom-filters-single select').on('change', function (element) {
            let wrapper = $(this).closest('.growtype-post-container-wrapper');

            setTimeout(function () {
                applyPostsFiltering(wrapper);
            }, 500)
        });

        function applyPostsFiltering(wrapper) {
            let values = {};
            wrapper.find('.growtype-post-custom-filters-single').map(function (index, element) {
                let value = '';
                if ($(element).find('select').length > 0) {
                    value = $(element).find('select').val();
                } else {
                    value = $(element).find('input').val();
                }

                values[$(element).attr('data-name')] = {
                    value: value,
                    ajax: $(element).attr('data-ajax') === 'true'
                };
            });

            let valueExists = false;
            Object.entries(values).map(function (element, index) {
                let name = element[0];
                if (wrapper.find('.growtype-post-container .growtype-post-single').attr('data-cat-' + name)) {
                    valueExists = true;
                }
            });

            if (valueExists) {
                console.log('Growtype post - Custom filtering is applied');

                wrapper.find('.gp-actions-wrapper').hide();

                let visiblePosts = $(wrapper).find('.growtype-post-single');

                visiblePosts.show();

                filterPosts(visiblePosts, values, false);
            }

            let ajaxValues = {};
            Object.entries(values).map(function (element, index) {
                let ajax = element[1]['ajax'];
                if (ajax) {
                    ajaxValues[element[0]] = element[1]['value'];
                }
            });

            if (Object.entries(ajaxValues).length > 0) {
                wrapper.find('.gp-actions-wrapper').hide();
                wrapper.find('.growtype-post-container').html('');

                let args = wrapper.attr('data-args');
                args = args ? JSON.parse(args) : {};

                Object.entries(ajaxValues).map(function (element, index) {
                    args[element[0]] = element[1];
                });

                let elements = {};
                elements['btn'] = wrapper.find('.btn-loadmore');
                elements['posts_container'] = wrapper.find('.growtype-post-container');

                loadMorePosts(elements, args);
            }

            let filterValues = {};
            Object.entries(values).map(function (element, index) {
                filterValues[element[0]] = element[1]['value'];
            });

            updateUrlWithFilterParams(filterValues, wrapper);
        }
    }
}
