import {growtypePostGetTermsFilterSelectedValues} from "./getTermsFilterSelectedValues";
import {growtypePostLoadPosts} from "./loadPosts";
import {formatLoadedPostsKey} from "./formatLoadedPostsKey";
import {loadMorePosts} from "./loadMorePosts";

/**
 * Load more posts
 */
export function loadMoreBtnTrigger(element) {
    let wrapper = element.closest('.growtype-post-container-wrapper');
    let wrapperId = wrapper.attr('id');

    element.click(function (e) {
        e.preventDefault();

        if (window.growtype_post['wrappers'][wrapperId]['load_more_posts_btn_clicked']) {
            return;
        }

        window.growtype_post['wrappers'][wrapperId]['load_more_posts_btn_clicked'] = true;

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

                if (window.growtype_post['wrappers'][wrapperId]['terms_filter'][termsFilterAmountKey] !== undefined) {
                    postAmountToShowLimit = parseInt(window.growtype_post['wrappers'][wrapperId]['terms_filter'][termsFilterAmountKey]['visible']) + postsAmountToLoad;
                }

                window.growtype_post['wrappers'][wrapperId]['terms_filter'][termsFilterAmountKey] = {
                    visible: postAmountToShowLimit
                };

                growtypePostLoadPosts(postsContainer.closest('.wp-block-growtype-post'), filterParams, postAmountToShowLimit);

                window.growtype_post['wrappers'][wrapperId]['load_more_posts_btn_clicked'] = false;
            } else if (loadingType === 'ajax') {
                let args = postsContainer.closest('.growtype-post-container-wrapper').attr('data-args');
                args = args ? JSON.parse(args) : {};

                args['amount_to_load'] = postsAmountToLoad;
                args['amount_to_show'] = postAmountToShowLimit;
                args['selected_terms_navigation_values'] = Object.assign({}, filterParams);

                let orderby = wrapper.find('.growtype-post-filters-wrapper select[name="orderby"]').val();

                if (orderby) {
                    args['orderby'] = orderby;
                }

                let elements = {};
                elements['filters_container'] = filtersContainer;
                elements['btn'] = btn;
                elements['posts_container'] = postsContainer;

                loadMorePosts(elements, args);
            }
        }
    })

    /**
     * Hide load more button if all posts are visible
     */
    if (wrapper.find('.gp-actions-wrapper').length > 0) {
        let visiblePosts = wrapper.find('.gp-actions-wrapper').closest('.growtype-post-container-wrapper').find('.growtype-post-container').attr('data-visible-posts');
        let existingPosts = wrapper.find('.gp-actions-wrapper').closest('.growtype-post-container-wrapper').find('.growtype-post-container .growtype-post-single').length;
        let loadingType = wrapper.find('.gp-actions-wrapper').closest('.growtype-post-container-wrapper').find('.growtype-post-container').attr('data-loading-type');

        if (existingPosts >= visiblePosts) {
            wrapper.find('.gp-actions-wrapper').show();
        }
    }
}
