/**
 * @param postsContainer
 * @param filterParams
 * @param postsLimit
 */

const growtypePostLoadPostsEvent = new Event('growtypePostLoadPosts');

export function growtypePostLoadPosts(postsContainer, filterParams, postsLimit) {
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

            if ($(post).attr('data-cat-' + key) !== value) {
                postIsVisible = false;
            }
        });

        if (postIsVisible) {
            availablePosts++;
        }

        if (validPosts < postsLimit) {
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

    if (validPosts === availablePosts) {
        loadMoreBtn.closest('.wp-block-button').hide();
    } else {
        loadMoreBtn.closest('.wp-block-button').fadeIn();
    }

    document.dispatchEvent(growtypePostLoadPostsEvent);
}
