$ = jQuery;

$('.btn-like').click(function () {
    let btn = $(this);
    let link = btn.find('a');

    if ($(this).attr('data-type') === undefined && (link.attr('data-type') === undefined || (link.attr('href') !== undefined && link.attr('href').length > 0))) {
        return;
    }

    event.preventDefault();

    $(this).toggleClass('is-active');

    let dataType = link !== undefined ? link.attr('data-type') : $(this).attr('data-type');
    let postId = $(this).attr('data-id') !== undefined ? $(this).attr('data-id') : growtypePost.postId;

    jQuery.ajax({
        url: growtypePost.ajaxUrl,
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

$('.btn-share').click(function () {
    let link = $(this).find('a');

    if ($(this).attr('data-type') === undefined && (link.attr('data-type') === undefined || (link.attr('href') !== undefined && link.attr('href').length > 0))) {
        return;
    }

    event.preventDefault();

    $(this).toggleClass('is-active');

    let dataType = link !== undefined ? link.attr('data-type') : $(this).attr('data-type');
    let postId = $(this).attr('data-id') !== undefined ? $(this).attr('data-id') : growtypePost.postId;

    jQuery.ajax({
        url: growtypePost.ajaxUrl,
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
