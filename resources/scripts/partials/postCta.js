export function postCta() {
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

                if (response.data['likes'] > 0) {
                    btn.prepend('<span class="e-amount">' + response.data['likes'] + '</span>');
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
