import {growtypePostGetTermsFilterSelectedValues} from "./getTermsFilterSelectedValues";
import {growtypePostLoadPosts} from "./loadPosts";

export function growtypePostLoadMore(element) {
    let loadMoreBtnClicked = false;
    element.click(function (e) {
        e.preventDefault();

        if (loadMoreBtnClicked) {
            return;
        }

        loadMoreBtnClicked = true;

        let btn = $(this);
        let id = btn.attr('data-growtype-post-load-more');
        let postsContainer = $('#' + id + '.growtype-post-container');

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

            let existingPostsIds = [];
            postsContainer.find('.growtype-post-single-inner').each(function () {
                existingPostsIds.push($(this).attr('data-id'));
            });

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

                loadMoreBtnClicked = false;
            } else if (loadingType === 'ajax') {
                let btnText = btn.text();

                btn.addClass('is-loading');
                btn.text('');
                btn.append('<span class="spinner-border"></span>');

                jQuery.ajax({
                    url: growtype_post.ajax_url,
                    type: 'post',
                    data: {
                        action: 'growtype_post_load_more_posts',
                        existing_posts_ids: JSON.stringify(existingPostsIds),
                        amount_to_load: postsAmountToLoad,
                        amount_to_show: postAmountToShowLimit,
                        filter_params: JSON.stringify(Object.assign({}, filterParams)),
                        preview_style: postsContainer.closest('.growtype-post-container-wrapper').attr('data-preview-style'),
                    },
                    success: function (response) {
                        if (response.data.render) {
                            postsContainer.append(response.data.render);
                            btn.removeClass('is-loading');
                            btn.find('.spinner-border').remove();
                            btn.text(btnText);
                        }

                        loadMoreBtnClicked = false;
                    },
                    error: function () {
                        loadMoreBtnClicked = false;
                    }
                });
            }
        }
    })
}
