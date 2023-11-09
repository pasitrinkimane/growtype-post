$ = jQuery;

window.growtype_post.terms_filter = [];

/**
 * Like post
 */
$('.growtype-post-btn-like').click(function () {
    let btn = $(this);
    let link = btn.find('a');

    if ($(this).attr('data-type') === undefined && (link.attr('data-type') === undefined || (link.attr('href') !== undefined && link.attr('href').length > 0))) {
        return;
    }

    event.preventDefault();

    $(this).toggleClass('is-active');

    let dataType = link !== undefined ? link.attr('data-type') : $(this).attr('data-type');
    let postId = $(this).attr('data-id') !== undefined ? $(this).attr('data-id') : growtype_post.post_id;

    jQuery.ajax({
        url: growtype_post.ajax_url,
        type: 'post',
        data: {
            action: 'like_post',
            post_id: postId,
            data_type: dataType
        },
        success: function (response) {
            btn.find('.e-amount').remove();

            if (response['likes'] > 0) {
                btn.prepend('<span class="e-amount">' + response['likes'] + '</span>');
            }
        }
    });
})

/**
 * Share post
 */
$('.growtype-post-btn-share').click(function () {
    let link = $(this).find('a');

    if ($(this).attr('data-type') === undefined && (link.attr('data-type') === undefined || (link.attr('href') !== undefined && link.attr('href').length > 0))) {
        return;
    }

    event.preventDefault();

    $(this).toggleClass('is-active');

    let dataType = link !== undefined ? link.attr('data-type') : $(this).attr('data-type');
    let postId = $(this).attr('data-id') !== undefined ? $(this).attr('data-id') : growtype_post.post_id;

    jQuery.ajax({
        url: growtype_post.ajax_url,
        type: 'post',
        data: {
            action: 'share_post',
            post_id: postId,
            data_type: dataType
        },
        success: function (response) {
            let newUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + response.share_url;
            window.open(newUrl, "_blank");

            if (link) {
                link.attr('href', newUrl).attr('target', '_blank')
            }
        }
    });
})

/**
 * Load more posts
 */
$('a[data-growtype-post-load-more]').click(function () {
    let btn = $(this);
    let id = btn.attr('data-growtype-post-load-more')

    let postsContainer = $('#' + id + '.growtype-post-container');

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
            growtypePostLoadPosts(postsContainer, filterParams, visiblePostsAmount);
        }
    }
})

/**
 * Filter posts
 */
$('.growtype-post-terms-filter-btn').click(function () {
    if (!$(this).hasClass('is-active')) {
        let postsContainer = $(this).closest('.wp-block-growtype-post');
        let filtersContainer = $(this).closest('.growtype-post-terms-filters');
        let filterContainer = $(this).closest('.growtype-post-terms-filter');
        let minimumVisiblePostsAmount = postsContainer.find('.growtype-post-container').attr('data-visible-posts');
        minimumVisiblePostsAmount = parseInt(minimumVisiblePostsAmount);

        /**
         * Update filter state
         */
        filterContainer.find('.growtype-post-terms-filter-btn').removeClass('is-active');

        $(this).addClass('is-active');

        /**
         * Get current filter params
         */
        let filterParams = growtypePostGetTermsFilterSelectedValues(filtersContainer);

        /**
         * Get posts limit
         */
        let postsLimit = minimumVisiblePostsAmount;

        let termsFilterAmountKey = '';
        Object.entries(filterParams).map(function (element, index) {
            let key = element[0].toString();
            let value = element[1].toString();
            termsFilterAmountKey += key + '_' + value + '_';
        });

        if (window.growtype_post.terms_filter[termsFilterAmountKey] !== undefined) {
            postsLimit = parseInt(window.growtype_post.terms_filter[termsFilterAmountKey]['visible']);
        }

        /**
         * Filter posts
         * @type {number}
         */
        postsContainer.find('.growtype-post-single').fadeOut().promise().done(function () {
            growtypePostLoadPosts(postsContainer, filterParams, postsLimit);
        });
    }
});

/**
 * @param postsContainer
 * @param filterParams
 * @param postsLimit
 */
function growtypePostLoadPosts(postsContainer, filterParams, postsLimit) {
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

    let id = $(this).closest('.wp-block-growtype-post').find('.growtype-post-container').attr('id');
    if (validPosts === availablePosts) {
        $('a[data-growtype-post-load-more="' + id + '"]').closest('.wp-block-button').hide();
    } else {
        $('a[data-growtype-post-load-more="' + id + '"]').closest('.wp-block-button').fadeIn();
    }
}

/**
 * @param termsFilter
 * @returns {*[]}
 */
function growtypePostGetTermsFilterSelectedValues(termsFilter) {
    let filterParams = [];
    termsFilter.find('.growtype-post-terms-filter-btn').each(function (index, btn) {
        if ($(btn).hasClass('is-active')) {
            $(this).each(function (index) {
                $.each(this.attributes, function (index, attr) {
                    if (attr.name.indexOf('data-cat') === 0) {
                        let element = attr.name.replace("data-cat-", "");

                        let value = $(btn).attr('data-cat-' + element);
                        if (filterParams[element] === undefined) {
                            filterParams[element] = [value];
                        } else {
                            filterParams[element].push(value);
                        }
                    }
                });
            });
        }
    });

    return filterParams;
}
