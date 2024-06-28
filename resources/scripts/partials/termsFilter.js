import {growtypePostGetTermsFilterSelectedValues} from "./getTermsFilterSelectedValues";
import {growtypePostLoadPosts} from "./loadPosts";
import {getPostsLimit} from "./getPostsLimit";
import {formatLoadedPostsKey} from "./formatLoadedPostsKey";
import {loadMorePosts} from "./loadMorePosts";

export function termsFilter() {
    $('.growtype-post-terms-filter').on('change', function (event) {
        let postsWrapper = $(event.target).closest('.growtype-post-container-wrapper');
        let filtersContainer = $(event.target).closest('.growtype-post-terms-filters');
        let minimumVisiblePostsAmount = postsWrapper.find('.growtype-post-container').attr('data-visible-posts');
        minimumVisiblePostsAmount = parseInt(minimumVisiblePostsAmount);

        /**
         * Get current filter params
         */
        let filterParams = growtypePostGetTermsFilterSelectedValues(filtersContainer);

        /**
         * Get posts limit
         */
        let postsLimit = getPostsLimit(minimumVisiblePostsAmount, filterParams);

        /**
         * Filter posts
         */
        filterPosts(postsWrapper, filterParams, postsLimit, minimumVisiblePostsAmount);
    });

    /**
     * Filter posts
     */
    $('.growtype-post-terms-filter-btn').click(function () {
        if ($(this).attr('data-disabled')) {
            return;
        }

        let triggerType = $(this).attr('data-trigger-type');
        let multipleSelect = $(this).attr('data-multiple-select');

        if (triggerType === 'click' && $(this).hasClass('is-active')) {
            return;
        }

        let postsWrapper = $(this).closest('.growtype-post-container-wrapper');
        let filtersContainer = $(this).closest('.growtype-post-terms-filters');
        let filterContainer = $(this).closest('.growtype-post-terms-filter');
        let minimumVisiblePostsAmount = postsWrapper.find('.growtype-post-container').attr('data-visible-posts');
        minimumVisiblePostsAmount = parseInt(minimumVisiblePostsAmount);

        /**
         * Update filter state
         */
        if (triggerType === 'toggle') {
            if (!$(this).hasClass('is-active')) {
                if (multipleSelect === 'false') {
                    filterContainer.find('.growtype-post-terms-filter-btn').removeClass('is-active');
                }

                $(this).addClass('is-active');
            } else {
                $(this).removeClass('is-active');
            }
        } else {
            filterContainer.find('.growtype-post-terms-filter-btn').removeClass('is-active');
            $(this).addClass('is-active');
        }

        /**
         * Get current filter params
         */
        let filterParams = growtypePostGetTermsFilterSelectedValues(filtersContainer);

        /**
         * Get posts limit
         */
        let postsLimit = getPostsLimit(minimumVisiblePostsAmount, filterParams);

        /**
         * Filter posts
         */
        filterPosts(postsWrapper, filterParams, postsLimit, minimumVisiblePostsAmount);
    });

    function filterPosts(postsWrapper, filterParams, postsLimit, minimumVisiblePostsAmount) {

        let filtersContainer = postsWrapper.find('.growtype-post-terms-filters');
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

            if (window.growtype_post.loaded_posts && window.growtype_post.loaded_posts[loadedPostsKey]) {
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
                        loadMorePosts({
                            amount_to_load: postAmountToShowLimit,
                            amount_to_show: postAmountToShowLimit,
                            selected_terms_navigation_values: filterParams,
                            filters_container: filtersContainer,
                            posts_container: postsWrapper.find('.growtype-post-container'),
                            btn: postsWrapper.find('.btn-loadmore')
                        })

                        // postsWrapper.find('.btn-loadmore').click();
                    }
                }
            }
        });
    }

    /**
     *
     */
    $('.growtype-post-terms-filter').each(function (index, element) {
        if ($(this).attr('data-init-cat') !== '' && $(element).is(':visible')) {
            if ($(element).is('select')) {
                $(element).trigger('change');
            } else if ($(element).is('div')) {
                $('.growtype-post-terms-filter-btn[data-cat-' + $(element).attr('data-type') + '="' + $(element).attr('data-init-cat') + '"]').trigger('click');
            }
        }
    });
}
