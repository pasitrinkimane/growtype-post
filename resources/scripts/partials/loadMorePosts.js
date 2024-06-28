import {formatLoadedPostsKey} from "./formatLoadedPostsKey";

export function loadMorePosts(params) {
    let btn = params['btn'] || null;
    let btnText = null;

    if (btn) {
        btnText = btn.text();
        btn.addClass('is-loading');
        btn.text('');
        btn.append('<span class="spinner-border"></span>');
    }

    if (typeof params['existing_posts_ids'] === 'undefined') {
        params['existing_posts_ids'] = [];
        params['posts_container'].find('.growtype-post-single-inner').each(function () {
            params['existing_posts_ids'].push($(this).attr('data-id'));
        });
    }

    jQuery.ajax({
        url: growtype_post.ajax_url,
        type: 'post',
        data: {
            action: 'growtype_post_load_more_posts',
            existing_posts_ids: JSON.stringify(params['existing_posts_ids']),
            amount_to_load: params['amount_to_load'],
            amount_to_show: params['amount_to_show'],
            selected_terms_navigation_values: Object.assign({}, params['selected_terms_navigation_values']),
            preview_style: params['posts_container'].closest('.growtype-post-container-wrapper').attr('data-preview-style'),
            content_url_cache: params['posts_container'].closest('.growtype-post-container-wrapper').attr('data-content-url-cache'),
            content_source: params['posts_container'].closest('.growtype-post-container-wrapper').attr('data-content-source'),
        },
        success: function (response) {
            if (response.data.render) {
                params['posts_container'].append(response.data.render);
            }

            if (!response.data.render || response.data.posts_amount !== params['amount_to_load']) {
                params['btn'].closest('.gp-actions-wrapper').hide();

                let loadedPostsKey = formatLoadedPostsKey(params['filters_container']);

                if (window.growtype_post.loaded_posts === undefined) {
                    window.growtype_post.loaded_posts = {};
                }

                window.growtype_post.loaded_posts[loadedPostsKey] = true;
            }

            if (btn) {
                btn.removeClass('is-loading');
                btn.find('.spinner-border').remove();
                btn.text(btnText);
            }

            window.growtype_post_load_more_posts_btn_clicked = false;
        },
        error: function () {
            window.growtype_post_load_more_posts_btn_clicked = false;
        }
    });
}
