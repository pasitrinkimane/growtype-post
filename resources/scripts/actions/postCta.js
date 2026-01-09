/**
 * Post CTA
 */
export function postCta(wrapper) {
    initActionBtns();

    document.addEventListener('growtypePostAjaxLoadContent', function (event) {
        initActionBtns();
    })

    document.addEventListener('growtypePostAjaxLoadMoreContent', function (params) {
        let existingIds = JSON.parse(params['detail']['args']['existing_posts_ids']);

        $('.growtype-post-single').each(function (index, element) {
            if (!existingIds.includes($(element).attr('data-id'))) {
                likeActionInit($(element).find('.growtype-post-btn-like'));
                sharePostAction($(element).find('.growtype-post-btn-share'))
            }
        })
    })

    function initActionBtns() {
        /**
         * Like post
         */
        likeActionInit($(wrapper).find('.growtype-post-btn-like'));
        sharePostAction($(wrapper).find('.growtype-post-btn-share'))
    }

    function likeActionInit(element) {
        element.each(function (index, element) {
            if ($(element).attr('data-loaded') === 'true') {
                return;
            }

            $(element).attr('data-loaded', true);

            likeAction($(element))
        })
    }

    function likeAction(element) {
        element.click(function () {
            let btn = $(this);
            let link = btn.find('a');

            if (btn.attr('data-type') === undefined && (link.attr('data-type') === undefined || (link.attr('href') !== undefined && link.attr('href').length > 0))) {
                return;
            }

            event.preventDefault();

            if (btn.hasClass('is-active') && btn.closest('.growtype-post-liked-posts-container-wrapper').length > 0) {
                btn.closest('.growtype-post-single').fadeOut();
            }

            $(this).toggleClass('is-active');

            let dataType = link !== undefined ? link.attr('data-type') : btn.attr('data-type');
            let postId = btn.attr('data-id') !== undefined ? btn.attr('data-id') : growtype_post.post_id;

            jQuery.ajax({
                url: growtype_post.ajax_url,
                type: 'post',
                data: {
                    action: 'growtype_post_like_post',
                    nonce: growtype_post.nonce,
                    post_id: postId,
                    data_type: dataType
                },
                success: function (response) {
                    btn.find('.e-amount').remove();

                    if (response.data['likes'] > 0) {
                        btn.prepend('<span class="e-amount">' + response.data['likes'] + '</span>');
                    }
                }
            });
        })
    }

    /**
     * Share post
     */
    function sharePostAction(element) {
        /**
         * Share post
         */
        element.click(function () {
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
                    nonce: growtype_post.nonce,
                    post_id: postId,
                    data_type: dataType
                },
                success: function (response) {
                    let newUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + response.data.share_url;
                    setTimeout(() => {
                        window.open(newUrl, "_blank");
                    })

                    if (link) {
                        link.attr('href', newUrl).attr('target', '_blank')
                    }
                }
            });
        })
    }
}
