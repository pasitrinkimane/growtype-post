import {growtypePostGetTermsFilterSelectedValues} from "./getTermsFilterSelectedValues";
import {growtypePostLoadPosts} from "./loadPosts";
import {formatLoadedPostsKey} from "./formatLoadedPostsKey";
import {loadMorePosts} from "./loadMorePosts";

export function loadMoreBtnTrigger(element) {
    window.growtype_post_load_more_posts_btn_clicked = false;

    element.click(function (e) {
        e.preventDefault();

        if (window.growtype_post_load_more_posts_btn_clicked) {
            return;
        }

        window.growtype_post_load_more_posts_btn_clicked = true;

        let btn = $(this);
        let id = btn.attr('data-growtype-post-load-more');
        let postsContainer = $('#' + id);

        if (postsContainer.length === 0) {
            postsContainer = btn.closest('.growtype-post-container')
        }

        if (postsContainer.length === 0) {
            postsContainer = btn.closest('.growtype-post-container-wrapper').find('.growtype-post-container')
        }

        if (postsContainer) {
            let loadingType = postsContainer.attr('data-loading-type');
            let initiallyVisiblePosts = btn.attr('data-growtype-post-load-more-amount') === undefined ? postsContainer.attr('data-visible-posts') : btn.attr('data-growtype-post-load-more-amount');
            initiallyVisiblePosts = parseInt(initiallyVisiblePosts);
            let postsAmountToLoad = initiallyVisiblePosts;
            let postAmountToShowLimit = initiallyVisiblePosts + postsAmountToLoad;
            let filtersContainer = postsContainer.closest('.wp-block-growtype-post').find('.growtype-post-terms-filters');
            let filterParams = growtypePostGetTermsFilterSelectedValues(filtersContainer);

            if (loadingType === 'limited') {

                /**
                 * Save previous filter params
                 * @type {*[]}
                 */
                let termsFilterAmountKey = '';
                Object.entries(filterParams).map(function (element, index) {
                    let key = element[0].toString();
                    let value = element[1].toString();
                    termsFilterAmountKey += key + '_' + value + '_';
                });

                if (window.growtype_post.terms_filter[termsFilterAmountKey] !== undefined) {
                    postAmountToShowLimit = parseInt(window.growtype_post.terms_filter[termsFilterAmountKey]['visible']) + postsAmountToLoad;
                }

                window.growtype_post.terms_filter[termsFilterAmountKey] = {
                    visible: postAmountToShowLimit
                };

                /**
                 * Filter posts
                 * @type {number}
                 */
                growtypePostLoadPosts(postsContainer.closest('.wp-block-growtype-post'), filterParams, postAmountToShowLimit);

                window.growtype_post_load_more_posts_btn_clicked = false;
            } else if (loadingType === 'ajax') {
                loadMorePosts({
                    amount_to_load: postsAmountToLoad,
                    amount_to_show: postAmountToShowLimit,
                    selected_terms_navigation_values: filterParams,
                    filters_container: filtersContainer,
                    btn: btn,
                    posts_container: postsContainer
                })
            }
        }
    })

    /**
     * Hide load more button if all posts are visible
     */
    if ($('.gp-actions-wrapper').length > 0) {
        let visiblePosts = $('.gp-actions-wrapper').closest('.growtype-post-container-wrapper').find('.growtype-post-container').attr('data-visible-posts');
        let existingPosts = $('.gp-actions-wrapper').closest('.growtype-post-container-wrapper').find('.growtype-post-container .growtype-post-single').length;
        let loadingType = $('.gp-actions-wrapper').closest('.growtype-post-container-wrapper').find('.growtype-post-container').attr('data-loading-type');

        if (existingPosts <= visiblePosts && loadingType !== 'ajax') {
            $('.gp-actions-wrapper').hide();
        }
    }
}
