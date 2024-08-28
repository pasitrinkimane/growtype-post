import {formatLoadedPostsKey} from "./formatLoadedPostsKey";
import {growtypePostAjaxLoadMoreContent} from "../events/growtypePostAjaxLoadMoreContent";
import {postCta} from "./postCta";

export function loadMorePosts(elements, args = {}) {
    let btn = elements['btn'] || null;
    let btnText = null;

    if (btn) {
        btnText = btn.text();
        btn.addClass('is-loading');
        btn.text('');
        btn.append('<span class="spinner-border"></span>');
    }

    if (typeof args['existing_posts_ids'] === 'undefined') {
        args['existing_posts_ids'] = [];

        elements['posts_container'].find('.growtype-post-single').each(function () {
            args['existing_posts_ids'].push($(this).attr('data-id'));
        });

        args['existing_posts_ids'] = JSON.stringify(args['existing_posts_ids']);
    }

    jQuery.ajax({
        url: growtype_post.ajax_url,
        type: 'post',
        data: {
            action: 'growtype_post_load_more_posts',
            args: args
        },
        success: function (response) {
            if (response.data.render) {
                elements['posts_container'].append(response.data.render);
            }

            if (!response.data.render || response.data.posts_amount !== args['amount_to_load']) {
                if (elements['btn']) {
                    elements['btn'].closest('.gp-actions-wrapper').hide();
                }

                let loadedPostsKey = formatLoadedPostsKey(elements['filters_container']);

                if (window.growtype_post.loaded_posts === undefined) {
                    window.growtype_post.loaded_posts = {};
                }

                window.growtype_post.loaded_posts[loadedPostsKey] = true;
            }

            if (btn) {
                btn.removeClass('is-loading');
                btn.find('.spinner-border').remove();
                btn.text(btnText);
                btn.closest('.gp-actions-wrapper').show();
            }

            if (response.data.render === "") {
                btn.closest('.gp-actions-wrapper').hide();
            }

            window.growtype_post_load_more_posts_btn_clicked = false;

            document.dispatchEvent(growtypePostAjaxLoadMoreContent({
                response: response,
                args: args
            }));
        },
        error: function () {
            window.growtype_post_load_more_posts_btn_clicked = false;
        }
    });
}
