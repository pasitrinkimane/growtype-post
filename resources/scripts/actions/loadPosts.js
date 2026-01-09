import {adjustPostsGrid} from "./adjustPostsGrid";

const growtypePostLoadPostsEvent = new Event('growtypePostLoadPosts');

export function growtypePostLoadPosts(wrapper, filterParams, postsLimit) {
    let loadingType = wrapper.find('.growtype-post-container').attr('data-load-more-posts-loading-type')
    let validPosts = 0;
    let availablePosts = 0;
    wrapper.find('.growtype-post-single').each(function (index, post) {
        let postIsVisible = true;

        Object.entries(filterParams).map(function (element, index) {
            let key = element[0].toString();
            let values = element[1];

            Object.entries(values).map(function (value) {
                let selectedValue = value[1];

                if (selectedValue === 'all' || selectedValue === undefined || selectedValue === '') {
                    return;
                }

                var attr = $(post).attr('data-cat-' + key);

                if (typeof attr === 'undefined' || attr === false) {
                    postIsVisible = false;
                    return;
                }

                let exists = false;

                let postCatValues = $(post).attr('data-cat-' + key).split(',');

                postCatValues.forEach(function (item) {
                    if (item.trim() === selectedValue) {
                        exists = true;
                    }
                });

                if (!exists) {
                    postIsVisible = false;
                }
            });
        });

        if (postIsVisible) {
            availablePosts++;
        }

        let shouldBeVisible = validPosts < postsLimit;

        if (shouldBeVisible) {
            if (postIsVisible) {
                validPosts++;

                $(post).fadeIn();
            }
        }
    });

    let id = wrapper.find('.growtype-post-container').attr('id');

    let loadMoreBtn = $('a[data-growtype-post-load-more="' + id + '"]');

    /**
     * Check if id is present
     */
    if (loadMoreBtn.length === 0) {
        loadMoreBtn = wrapper.find('.wp-block-button')
    }

    if (loadMoreBtn.length === 0) {
        loadMoreBtn = wrapper.find('.gp-actions-wrapper .btn-loadmore')
    }

    if (loadingType !== 'ajax') {
        if (validPosts === availablePosts) {
            loadMoreBtn.closest('.wp-block-button, .gp-actions-wrapper').hide();
        } else {
            loadMoreBtn.closest('.wp-block-button, .gp-actions-wrapper').fadeIn();
        }
    }

    adjustPostsGrid(wrapper);

    document.dispatchEvent(growtypePostLoadPostsEvent);
}
