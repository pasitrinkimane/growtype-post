/**
 * @param postsContainer
 * @param filterParams
 * @param postsLimit
 */

const growtypePostLoadPostsEvent = new Event('growtypePostLoadPosts');

export function growtypePostLoadPosts(postsContainer, filterParams, postsLimit) {
    let loadingType = postsContainer.find('.growtype-post-container').attr('data-loading-type')
    let validPosts = 0;
    let availablePosts = 0;
    postsContainer.find('.growtype-post-single').each(function (index, post) {

        let postIsVisible = true;

        // if (Object.entries(filterParams).length === 0 || Object.entries(filterParams)[0][1].includes('all')) {
        //     postIsVisible = false;
        // }

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

        let shouldBeVisible = loadingType === 'ajax' ? true : validPosts < postsLimit;

        if (shouldBeVisible) {
            if (postIsVisible) {
                validPosts++;

                $(post).fadeIn();
            }
        }
    });

    let id = postsContainer.find('.growtype-post-container').attr('id');

    let loadMoreBtn = $('a[data-growtype-post-load-more="' + id + '"]');

    /**
     * Check if id is present
     */
    if (loadMoreBtn.length === 0) {
        loadMoreBtn = postsContainer.find('.wp-block-button')
    }

    if (loadMoreBtn.length === 0) {
        loadMoreBtn = postsContainer.find('.gp-actions-wrapper .btn-loadmore')
    }

    if (loadingType !== 'ajax') {
        if (validPosts === availablePosts) {
            loadMoreBtn.closest('.wp-block-button, .gp-actions-wrapper').hide();
        } else {
            loadMoreBtn.closest('.wp-block-button, .gp-actions-wrapper').fadeIn();
        }
    }

    document.dispatchEvent(growtypePostLoadPostsEvent);
}
