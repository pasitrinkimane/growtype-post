import {growtypePostGetTermsFilterSelectedValues} from "./getTermsFilterSelectedValues";
import {growtypePostLoadPosts} from "./loadPosts";
import {getPostsLimit} from "./getPostsLimit";
import {formatLoadedPostsKey} from "./formatLoadedPostsKey";
import {loadMorePosts} from "./loadMorePosts";
import {getUrlFilterParams} from "./getUrlFilterParams";
import {initChosenParams} from "./initChosenParams";
import {growtypePostTermsFilterContent} from "../events/growtypePostTermsFilterContent";
import {updateUrlWithFilterParams} from "./updateUrlWithFilterParams";

/**
 * Terms filter
 */
export function termsFilter(wrapper) {
    let wrapperId = $(wrapper).attr('id');
    let paramKey = wrapperId + '-f-visible';
    let urlFilterParams = getUrlFilterParams(wrapperId);

    $(wrapper).find('.growtype-post-filters-visibility-trigger').click(function () {
        let url = new URL(window.location);
        let filtersWrapper = $(this).closest('.growtype-post-filters-wrapper');

        if (filtersWrapper.attr('data-filters-visible') === '1') {
            filtersWrapper.attr('data-filters-visible', '0');

            url.searchParams.delete(paramKey);
        } else {
            filtersWrapper.attr('data-filters-visible', '1');

            url.searchParams.set(paramKey, '1');
        }

        window.history.replaceState(null, '', url);
    });

    checkFilterVisibility();

    function checkFilterVisibility() {
        const urlSearchParams = new URLSearchParams(window.location.search);

        if (urlSearchParams.get(paramKey) === '1') {
            $(wrapper).find('.growtype-post-filters-visibility-trigger')?.click();
        }
    }

    $(document).ready(function () {
        if ($(wrapper).find('.growtype-post-terms-filter').length > 0 && $(wrapper).find('select.growtype-post-terms-filter').next('.chosen-container').length === 0) {
            $(wrapper).find('select.growtype-post-terms-filter').each(function (index, element) {
                $(element).chosen(initChosenParams($(element)));
            })
        }
    })

    $(wrapper).find('.growtype-post-terms-filter').on('change', function (event) {
        let postsWrapper = $(event.target).closest('.growtype-post-container-wrapper');
        let filtersContainer = $(event.target).closest('.growtype-post-terms-filters');
        let minimumVisiblePostsAmount = postsWrapper.find('.growtype-post-container').attr('data-visible-posts');
        minimumVisiblePostsAmount = parseInt(minimumVisiblePostsAmount);

        /**
         * Update buttons
         */
        $(wrapper).find('.growtype-post-terms-filter[data-type="' + $(event.target).attr('data-type') + '"]').find('.growtype-post-terms-filter-btn').removeClass('is-active');

        $(event.target).find('option:selected').each(function (index, element) {
            let cat = $(event.target).attr('data-type');
            let value = $(element).attr('data-cat-' + cat);
            let btn = $(wrapper).find('.growtype-post-terms-filter-btn[data-cat-' + cat + '="' + value + '"]');

            if (btn.length > 0) {
                btn.addClass('is-active');
            }
        });

        /**
         * Update select elements
         */
        updateSelectElements($(event.target).find('option'));

        /**
         * Get current filter params
         */
        let filterParams = growtypePostGetTermsFilterSelectedValues(filtersContainer);

        /**
         * Update URL
         */
        updateUrlWithFilterParams(filterParams, filtersContainer.closest('.growtype-post-container-wrapper'));

        /**
         * Get posts limit
         */
        let postsLimit = getPostsLimit(wrapper, minimumVisiblePostsAmount, filterParams);

        /**
         * Filter posts
         */
        termsFilterPosts(postsWrapper, filterParams, postsLimit, minimumVisiblePostsAmount);
    });

    /**
     * Filter posts
     */
    let termsFilterBtnClicked = false;
    $(wrapper).find('.growtype-post-terms-filter-btn').click(function () {
        if (!termsFilterBtnClicked) {
            termsFilterBtnClicked = true;

            postTermsFilterBtnClick($(this));

            setTimeout(function () {
                termsFilterBtnClicked = false;
            }, 500)
        }
    });

    /**
     *
     */
    $(wrapper).find('.growtype-post-terms-filter').each(function (index, element) {
        let selectedBtn
        if (Object.entries(urlFilterParams).length === 0) {
            selectedBtn = $(element).find('.growtype-post-terms-filter-btn[data-cat-' + $(element).attr('data-type') + '="' + $(element).attr('data-init-cat') + '"]');
        } else {
            Object.entries(urlFilterParams).forEach(([key, value]) => {
                let btn = $(element).find('.growtype-post-terms-filter-btn[data-cat-' + key + '="' + value + '"]');

                if (btn.length > 0) {
                    selectedBtn = btn;
                }
            });
        }

        if ($(this).attr('data-init-cat') !== '' && $(element).is(':visible')) {
            if ($(element).is('select')) {
                $(element).trigger('change');
            } else if ($(element).is('div')) {
                postTermsFilterBtnClick(selectedBtn, false);
            }
        }
    });

    function postTermsFilterBtnClick(btn, preventDoubleClick = true) {
        if (btn.attr('data-disabled') && preventDoubleClick || btn.length === 0) {
            return;
        }

        let triggerType = btn.attr('data-trigger-type');
        let multipleSelect = btn.attr('data-multiple-select');

        if (triggerType === 'click' && btn.hasClass('is-active') && preventDoubleClick) {
            return;
        }

        let postsWrapper = btn.closest('.growtype-post-container-wrapper');
        let filtersContainer = btn.closest('.growtype-post-terms-filters');
        let minimumVisiblePostsAmount = postsWrapper.find('.growtype-post-container').attr('data-visible-posts');
        minimumVisiblePostsAmount = parseInt(minimumVisiblePostsAmount);

        /**
         * Update select elements
         */
        updateBtnElements(btn, triggerType, multipleSelect).then(() => {
            updateSelectElements(btn, !btn.hasClass('is-active')).then(() => {
                /**
                 * Get current filter params
                 */
                let filterParams = growtypePostGetTermsFilterSelectedValues(filtersContainer);

                /**
                 * Update URL
                 */
                updateUrlWithFilterParams(filterParams, filtersContainer.closest('.growtype-post-container-wrapper'));

                /**
                 * Get posts limit
                 */
                let postsLimit = getPostsLimit(wrapper, minimumVisiblePostsAmount, filterParams);

                /**
                 * Filter posts
                 */
                termsFilterPosts(postsWrapper, filterParams, postsLimit, minimumVisiblePostsAmount);
            });
        });
    }

    function updateBtnElements(btn, triggerType, multipleSelect) {
        return new Promise((resolve) => {
            let wrapper = $(btn).closest('.growtype-post-container-wrapper');
            $.each(btn[0].attributes, function (index, attribute) {
                if (attribute.name.startsWith('data-cat')) {
                    let key = attribute.name.replace('data-cat-', '');

                    /**
                     * Update filter state
                     */
                    if (triggerType === 'toggle') {
                        if (!btn.hasClass('is-active')) {
                            if (multipleSelect === 'false') {
                                wrapper.find('.growtype-post-terms-filters-single[data-type="' + key + '"]').find('.growtype-post-terms-filter-btn').removeClass('is-active');
                            }

                            btn.addClass('is-active');
                        } else {
                            btn.removeClass('is-active');
                        }
                    } else {
                        wrapper.find('.growtype-post-terms-filters-single[data-type="' + key + '"]').find('.growtype-post-terms-filter-btn').removeClass('is-active');
                        btn.addClass('is-active');
                    }
                }
            });

            resolve();
        });
    }

    function updateSelectElements(elements, clear = false) {
        return new Promise((resolve) => {
            let values = {};

            $.each(elements, function (index, element) {
                const $option = $(element);

                // Iterate over the attributes of the current option element
                $.each(element.attributes, function (index, attribute) {
                    if (attribute.name.startsWith('data-cat')) {
                        let key = attribute.name.replace('data-cat-', '');
                        let value = '';

                        // Check if the option is selected and not in "clear" mode
                        if ($option.is(':selected') && !clear) {
                            value = $option.val(); // Use .val() to get the option value
                        }

                        // Initialize the key if not already set
                        if (!values[key]) {
                            values[key] = [];
                        }

                        // Add the value to the key only if not empty
                        if (value || clear) {
                            values[key].push(value);
                        }
                    }
                });
            });

            updateAllSelects(values); // Update select elements based on collected values

            resolve();
        });
    }

    function updateAllSelects(values) {
        $.each(values, function (key, valueArray) {
            const selectElement = $('select[data-type="' + key + '"]');

            if (selectElement.length === 0) {
                console.warn(`No select element found for key: ${key}`);
                return; // Skip if no select element matches
            }

            if (selectElement.prop('multiple')) {
                const selectedValues = valueArray.filter(value => value); // Exclude empty values
                selectElement.val(selectedValues.length ? selectedValues : []); // Set selected values

                if (selectElement.next().hasClass('chosen-container')) {
                    selectElement.trigger("chosen:updated");
                }
            } else {
                const optionValue = valueArray[0] || ''; // Default to empty if no value
                selectElement.val(optionValue);

                if (selectElement.next().hasClass('chosen-container')) {
                    selectElement.trigger("chosen:updated");
                }
            }
        });
    }

    function termsFilterPosts(postsWrapper, filterParams, postsLimit, minimumVisiblePostsAmount) {
        let filtersContainer = postsWrapper.find('.growtype-post-terms-filters:visible');
        let loadingType = postsWrapper.find('.growtype-post-container').attr('data-loading-type');

        /**
         * Filter posts
         * @type {number}
         */
        let postElement = postsWrapper.find('.growtype-post-single');

        if (!postElement.parent().hasClass('growtype-post-container')) {
            postElement = postElement.parent();
        }

        postElement.fadeOut().promise().done(function () {
            growtypePostLoadPosts(postsWrapper, filterParams, postsLimit);

            let loadedPostsKey = formatLoadedPostsKey(filtersContainer);

            if (window.growtype_post['wrappers'][wrapperId]['loaded_posts'] && window.growtype_post['wrappers'][wrapperId]['loaded_posts'][loadedPostsKey]) {
                postsWrapper.find('.gp-actions-wrapper').fadeOut();
            } else {
                if (loadingType === 'ajax') {
                    postsWrapper.find('.gp-actions-wrapper').fadeIn();
                }
            }

            if (loadingType === 'ajax') {
                if (postsWrapper.find('.btn-loadmore:visible').length > 0) {
                    let postAmountToShowLimit = minimumVisiblePostsAmount - postsWrapper.find('.growtype-post-single:visible').length;

                    if (postAmountToShowLimit > 0) {
                        let args = postsWrapper.closest('.growtype-post-container-wrapper').attr('data-args');
                        args = args ? JSON.parse(args) : {};

                        args['amount_to_load'] = postAmountToShowLimit;
                        args['amount_to_show'] = postAmountToShowLimit;
                        args['selected_terms_navigation_values'] = Object.assign({}, filterParams);

                        if (postsWrapper.find('select.growtype-post-custom-filter[name="orderby"]').length > 0) {
                            args['orderby'] = postsWrapper.find('select.growtype-post-custom-filter[name="orderby"]').val();
                        }

                        let elements = {};
                        elements['filters_container'] = filtersContainer;
                        elements['btn'] = postsWrapper.find('.btn-loadmore');
                        elements['posts_container'] = postsWrapper.find('.growtype-post-container');

                        loadMorePosts(elements, args);
                    }
                }
            }

            document.dispatchEvent(growtypePostTermsFilterContent({
                wrapper: postsWrapper
            }));
        });
    }
}
