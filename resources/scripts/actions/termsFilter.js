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

        const currentVisible = $(wrapper).find('.growtype-post-filters-wrapper').attr('data-filters-visible') === '1';
        if (urlSearchParams.get(paramKey) === '1' && !currentVisible) {
            $(wrapper).find('.growtype-post-filters-visibility-trigger')?.click();
        } else if (urlSearchParams.get(paramKey) === '0' && currentVisible) {
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
        let visiblePosts = postsWrapper.find('.growtype-post-container').attr('data-visible-posts');
        visiblePosts = parseInt(visiblePosts);

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
        let postsLimit = getPostsLimit(wrapper, visiblePosts, filterParams);

        /**
         * Filter posts
         */
        termsFilterPosts(postsWrapper, filterParams, postsLimit, visiblePosts);
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
     * Build a map of URL params that are NOT represented in the UI.
     * For example, if URL has s_tags=pack_anime,tattoo and only tattoo has a button,
     * this will return { tags: ['pack_anime'] }.
     */
    function getHiddenUrlFilterParams() {
        let hidden = {};
        Object.entries(urlFilterParams).forEach(([key, valueArray]) => {
            if (!Array.isArray(valueArray)) valueArray = [valueArray];

            let hiddenValues = valueArray.filter(val => {
                // If 'all' is passed, it's never "hidden" - it means reset.
                if (val === 'all') return false;

                let btn = $(wrapper).find('.growtype-post-terms-filter-btn[data-cat-' + key + '="' + val + '"]');
                let opt = $(wrapper).find('select[data-type="' + key + '"] option[data-cat-' + key + '="' + val + '"]');

                // If no button or option exists for this specific value, it's hidden.
                return btn.length === 0 && opt.length === 0;
            });

            if (hiddenValues.length > 0) {
                hidden[key] = hiddenValues;
            }
        });
        return hidden;
    }

    $(wrapper).find('.growtype-post-terms-filter').each(function (index, element) {
        let activeValue = $(element).attr('data-init-cat');
        let type = $(element).attr('data-type');

        // Use URL param if exists
        if (urlFilterParams[type]) {
            activeValue = Array.isArray(urlFilterParams[type]) ? urlFilterParams[type][0] : urlFilterParams[type];
        }

        if ($(element).is('select')) {
            if (activeValue && activeValue !== '') {
                let $option = $(element).find('option[data-cat-' + type + '="' + activeValue + '"]');
                if ($option.length) {
                    $(element).val($option.val());
                    if ($(element).next().hasClass('chosen-container')) {
                        $(element).trigger("chosen:updated");
                    }
                }
            }
        } else if ($(element).is('div')) {
            if (activeValue && activeValue !== '') {
                let btn = $(element).find('.growtype-post-terms-filter-btn[data-cat-' + type + '="' + activeValue + '"]');
                if (btn.length > 0) {
                    $(element).find('.growtype-post-terms-filter-btn').removeClass('is-active');
                    btn.addClass('is-active');
                }
            }
        }
    });

    /**
     * Trigger a single filtering pass after all filters are initialized.
     */
    let initialFilterParams = growtypePostGetTermsFilterSelectedValues($(wrapper).find('.growtype-post-terms-filters'));
    let hiddenParams = getHiddenUrlFilterParams();
    let hasFilters = Object.keys(initialFilterParams).length > 0 || Object.keys(hiddenParams).length > 0;

    if (hasFilters) {
        Object.entries(hiddenParams).forEach(([key, values]) => {
            if (!initialFilterParams[key]) {
                initialFilterParams[key] = values;
            } else {
                initialFilterParams[key] = [...new Set([...initialFilterParams[key], ...values])];
            }
        });

        let postsWrapper = $(wrapper);
        let visiblePosts = parseInt(postsWrapper.find('.growtype-post-container').attr('data-visible-posts')) || 20;

        // Perform a single client-side filter pass immediately.
        // We call growtypePostLoadPosts directly instead of termsFilterPosts 
        // to avoid redundant fade-out animations and extra AJAX "load more" calls during initialization.
        growtypePostLoadPosts(postsWrapper, initialFilterParams, visiblePosts);

        // Ensure the grid is adjusted and events are dispatched
        setTimeout(() => {
            document.dispatchEvent(new CustomEvent('growtypePostTermsFilterContent', {detail: {wrapper: postsWrapper}}));
        }, 10);
    }

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
        let visiblePosts = postsWrapper.find('.growtype-post-container').attr('data-visible-posts');
        visiblePosts = parseInt(visiblePosts);

        let visiblePostsMobile = postsWrapper.find('.growtype-post-container').attr('data-visible-posts-mobile');

        let minimumVisiblePosts = visiblePosts;

        if ($(window).width() <= 768) {
            minimumVisiblePosts = visiblePostsMobile;
        }

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
                 * Merge in hidden URL params (e.g. s_tags=pack_anime) that have
                 * no UI control. We append them to UI values.
                 */
                let hiddenParams = getHiddenUrlFilterParams();
                Object.entries(hiddenParams).forEach(([key, values]) => {
                    if (!filterParams[key]) {
                        filterParams[key] = values;
                    } else {
                        // Merge and de-duplicate
                        filterParams[key] = [...new Set([...filterParams[key], ...values])];
                    }
                });

                /**
                 * Update URL
                 */
                updateUrlWithFilterParams(filterParams, filtersContainer.closest('.growtype-post-container-wrapper'));

                /**
                 * Get posts limit
                 */
                let postsLimit = getPostsLimit(wrapper, minimumVisiblePosts, filterParams);

                /**
                 * Filter posts
                 */
                termsFilterPosts(postsWrapper, filterParams, postsLimit, minimumVisiblePosts);
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

    function termsFilterPosts(postsWrapper, filterParams, postsLimit, visiblePosts) {
        let filtersContainer = postsWrapper.find('.growtype-post-terms-filters:visible');
        let loadingType = postsWrapper.find('.growtype-post-container').attr('data-load-more-posts-loading-type');

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
                let loadedPostsKey = formatLoadedPostsKey(filtersContainer);
                let isFullyLoaded = window.growtype_post['wrappers'][wrapperId]['loaded_posts'] && window.growtype_post['wrappers'][wrapperId]['loaded_posts'][loadedPostsKey];

                if (!isFullyLoaded && postsWrapper.find('.btn-loadmore:visible').length > 0) {
                    let postAmountToShowLimit = visiblePosts - postsWrapper.find('.growtype-post-single:visible').length;

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
