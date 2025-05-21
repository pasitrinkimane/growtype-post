import {formatLoadedPostsKey} from "./formatLoadedPostsKey";
import {growtypePostAjaxLoadMoreContent} from "../events/growtypePostAjaxLoadMoreContent";

let currentRequest;

export function loadMorePosts(elements, args = {}) {
    return new Promise((resolve, reject) => {
        let wrapper = elements['posts_container'].closest('.growtype-post-container-wrapper');
        let wrapperId = $(wrapper).attr('id');

        if (typeof elements['filters_container'] === 'undefined') {
            elements['filters_container'] = wrapper.find('.growtype-post-terms-filters');
        }

        if (typeof elements['btn'] === 'undefined') {
            elements['btn'] = wrapper.find('.btn-loadmore');
        }

        if (typeof args['existing_posts_ids'] === 'undefined') {
            args['existing_posts_ids'] = [];

            elements['posts_container'].find('.growtype-post-single').each(function () {
                args['existing_posts_ids'].push($(this).attr('data-id'));
            });

            args['existing_posts_ids'] = JSON.stringify(args['existing_posts_ids']);
        }

        if (typeof args['orderby'] === 'undefined') {
            if (wrapper.find('select.growtype-post-custom-filter[name="orderby"]').length > 0) {
                args['orderby'] = elements['posts_container'].closest('.growtype-post-container-wrapper').find('select.growtype-post-custom-filter[name="orderby"]').val();
            }
        }

        if (typeof args['amount_to_load'] === 'undefined') {
            args['amount_to_load'] = args['posts_per_page'];
        }

        let btn = elements['btn'] || null;

        if (btn) {
            btn.addClass('is-loading');
            btn.append('<span class="spinner-border"></span>');
        }

        if (currentRequest) {
            currentRequest.abort();
        }

        currentRequest = jQuery.ajax({
            url: growtype_post.ajax_url,
            type: 'post',
            data: {
                action: 'growtype_post_load_more_posts',
                args: args
            },
            success: function (response) {
                let wrapper = '';
                if (response.data.render) {
                    if (args['shuffle'] !== undefined && args['shuffle'] === true) {
                        elements['posts_container'].html('');
                    }

                    let content = elements['posts_container'].append(response.data.render);
                    wrapper = content.closest('.growtype-post-container-wrapper')
                }

                if (btn) {
                    btn.removeClass('is-loading');
                    btn.find('.spinner-border').remove();
                    btn.closest('.gp-actions-wrapper').show();
                }

                if (!response.data.render || response.data.render === "" || response.data.posts_amount !== args['amount_to_load']) {
                    if (btn) {
                        btn.closest('.gp-actions-wrapper').hide();
                    }

                    let loadedPostsKey = formatLoadedPostsKey(elements['filters_container']);

                    if (window.growtype_post['wrappers'][wrapperId]['loaded_posts'] === undefined) {
                        window.growtype_post['wrappers'][wrapperId]['loaded_posts'] = {};
                    }

                    window.growtype_post['wrappers'][wrapperId][loadedPostsKey] = true;
                }

                window.growtype_post['wrappers'][wrapperId]['load_more_posts_btn_clicked'] = false;

                document.dispatchEvent(growtypePostAjaxLoadMoreContent({
                    response: response,
                    wrapper: wrapper,
                    args: args
                }));

                resolve(response);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                window.growtype_post['wrappers'][wrapperId]['load_more_posts_btn_clicked'] = false;
                reject(errorThrown);
            }
        });
    });
}
