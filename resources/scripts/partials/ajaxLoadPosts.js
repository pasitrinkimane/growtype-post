import {growtypePostAjaxLoadContent} from "./../events/growtypePostAjaxLoadContent";
import {postCta} from "./postCta";
import {termsFilter} from "./termsFilter";
import {loadMoreBtnTrigger} from "./loadMoreBtnTrigger";
import {updateFilterWithUrlParams} from "./updateFilterWithUrlParams";
import {getUrlFilterParams} from "./getUrlFilterParams";

export function ajaxLoadPosts() {
    $(document).ready(function () {
        $('.growtype-post-ajax-load-content').each(function (index, element) {
            let component = $(this);
            let container = component.closest('.wp-block-growtype-post');
            let urlFilterParams = getUrlFilterParams();
            let args = component.attr('data-args');
            args = args ? JSON.parse(args) : '';

            args['selected_terms_navigation_values'] = urlFilterParams;

            $('a[data-growtype-post-load-more="' + args['parent_id'] + '"]').hide();

            if (args) {
                jQuery.ajax({
                    url: growtype_post.ajax_url,
                    type: 'post',
                    data: {
                        action: 'growtype_post_ajax_load_content',
                        args: args
                    },
                    success: function (response) {
                        if (response.data.render) {
                            let content = $(response.data.render);

                            container.html(content);

                            termsFilter();

                            postCta();

                            loadMoreBtnTrigger(content.find('.btn-loadmore'));

                            $('a[data-growtype-post-load-more="' + args['parent_id'] + '"]').show();

                            /**
                             * Update filter with url params
                             */
                            updateFilterWithUrlParams();

                            if (parseInt(response.data.posts_amount) !== parseInt(args['posts_per_page'])) {
                                content.find('.gp-actions-wrapper').hide();
                            }

                            document.dispatchEvent(growtypePostAjaxLoadContent(response));
                        }
                    }
                });
            }
        });
    })
}
