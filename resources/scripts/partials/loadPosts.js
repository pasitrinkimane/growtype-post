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
        Object.entries(filterParams).map(function (element, index) {
            let key = element[0].toString();
            let value = element[1].toString();

            if (value === 'all' || value === undefined || value === '') {
                return;
            }

            var attr = $(post).attr('data-cat-' + key);
            if (typeof attr === 'undefined' || attr === false) {
                return;
            }

            let exists = false;

            $(post).attr('data-cat-' + key).split(',').forEach(function (item) {
                if (item.trim() === value) {
                    exists = true;
                }
            });

            if (!exists) {
                postIsVisible = false;
            }
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
