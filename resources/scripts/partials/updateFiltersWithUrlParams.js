import {growtypePostLoadPosts} from "./loadPosts";
import {getPostsLimit} from "./getPostsLimit";
import {getUrlFilterParams} from "./getUrlFilterParams";

export function updateFiltersWithUrlParams(onlyTerms = false) {

    if ($('.growtype-post-container-wrapper').length === 0) {
        return;
    }

    let urlFilterParams = getUrlFilterParams();

    Object.entries(urlFilterParams).map(function (values) {
        let termsSelect = $('select.growtype-post-terms-filter[data-type="' + values[0] + '"]');

        if (termsSelect.length > 0) {
            let selectedValues = [];

            values[1].map(function (value) {
                let selectedVal = termsSelect.find('option[data-cat-' + values[0] + '="' + value + '"]').val();
                selectedValues.push(selectedVal);
            })

            termsSelect.val(selectedValues);

            termsSelect.trigger("chosen:updated");
        }

        let customFiltersSelect = $('select.growtype-post-custom-filter[name="' + values[0] + '"]');

        if (customFiltersSelect.length > 0) {
            let selectedValues = [];

            values[1].map(function (value) {
                let selectedVal = customFiltersSelect.find('option[value="' + value + '"]').val();
                selectedValues.push(selectedVal);
            })

            customFiltersSelect.val(selectedValues);

            customFiltersSelect.trigger("chosen:updated");
        }
    });

    if (Object.entries(urlFilterParams).length > 0) {
        let applyFilter = false
        Object.entries(urlFilterParams).forEach(([key, value]) => {
            let filterBtn = $('.growtype-post-terms-filter-btn[data-cat-' + key + '="' + value + '"]');

            if (filterBtn.length > 0) {
                if (!filterBtn.hasClass('is-active')) {
                    applyFilter = true;
                }
            }
        });

        $('.growtype-post-terms-filter-btn').removeClass('is-active');

        Object.entries(urlFilterParams).forEach(([key, value]) => {
            let filterBtn = $('.growtype-post-terms-filter-btn[data-cat-' + key + '="' + value + '"]');

            if (filterBtn.length > 0) {
                filterBtn.addClass('is-active');
            }
        });

        /**
         * Check if only terms should be updated
         */
        if (onlyTerms) {
            return;
        }

        let postsContainer = $('.growtype-post-container-wrapper');

        postsContainer.map(function (index, container) {
            container = $(container);

            const urlSearchParams = new URLSearchParams(window.location.search);

            for (let [key, value] of urlSearchParams.entries()) {
                if (key === 'gpwid') {
                    let specificContainer = $('#' + value);

                    if (specificContainer.length > 0) {
                        container = specificContainer;
                    }
                }
            }

            if (container.length > 0) {
                let minimumVisiblePostsAmount = container.find('.growtype-post-container').attr('data-visible-posts');
                minimumVisiblePostsAmount = parseInt(minimumVisiblePostsAmount);

                /**
                 * Get posts limit
                 */
                let postsLimit = getPostsLimit(minimumVisiblePostsAmount, urlFilterParams);

                /**
                 * Filter posts
                 * @type {number}
                 */
                if (applyFilter) {
                    container.find('.growtype-post-single').fadeOut().promise().done(function () {
                        growtypePostLoadPosts(container, urlFilterParams, postsLimit);
                    });
                }
            }
        });
    }
}
