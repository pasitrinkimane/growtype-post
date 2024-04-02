import {growtypePostGetTermsFilterSelectedValues} from "./getTermsFilterSelectedValues";
import {growtypePostLoadPosts} from "./loadPosts";

export function growtypePostLoadMore() {
    $('a[data-growtype-post-load-more]').click(function () {
        let btn = $(this);
        let id = btn.attr('data-growtype-post-load-more');
        let postsContainer = $('#' + id + '.growtype-post-container');

        if (postsContainer.length === 0) {
            postsContainer = btn.closest('.growtype-post-container')
        }

        if (postsContainer) {
            let loadingType = postsContainer.attr('data-loading-type');
            let initiallyVisiblePosts = btn.attr('data-growtype-post-load-more-amount') === undefined ? postsContainer.attr('data-visible-posts') : btn.attr('data-growtype-post-load-more-amount');
            initiallyVisiblePosts = parseInt(initiallyVisiblePosts);
            let postsAmountToLoad = initiallyVisiblePosts;

            if (loadingType === 'limited') {
                let visiblePostsAmount = initiallyVisiblePosts + postsAmountToLoad;
                let filtersContainer = postsContainer.closest('.wp-block-growtype-post').find('.growtype-post-terms-filters');
                let filterParams = growtypePostGetTermsFilterSelectedValues(filtersContainer);

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
                    visiblePostsAmount = parseInt(window.growtype_post.terms_filter[termsFilterAmountKey]['visible']) + postsAmountToLoad;
                }

                window.growtype_post.terms_filter[termsFilterAmountKey] = {
                    visible: visiblePostsAmount
                };

                /**
                 * Filter posts
                 * @type {number}
                 */
                growtypePostLoadPosts(postsContainer.closest('.wp-block-growtype-post'), filterParams, visiblePostsAmount);
            }
        }
    })
}
