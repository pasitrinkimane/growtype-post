import { growtypePostAjaxLoadContent } from "./../events/growtypePostAjaxLoadContent";
import { termsFilter } from "./termsFilter";
import { loadMoreBtnTrigger } from "./loadMoreBtnTrigger";
import { updateFiltersWithUrlParams } from "./updateFiltersWithUrlParams";
import { getUrlFilterParams } from "./getUrlFilterParams";
import { initFiltering } from "./initFiltering";
import { setWrapperDefaultParams } from "./setWrapperDefaultParams";
import { infiniteLoadPosts } from "./infiniteLoadPosts";
import { adjustPostsGrid } from "./adjustPostsGrid";

/**
 * Ajax load posts
 */
export function loadContent() {
    $('.growtype-post-ajax-load-content').each(function (index, element) {
        let component = $(this);
        let args = component.attr('data-args');
        args = args ? JSON.parse(args) : '';

        let wrapperId = args['parent_id'];
        setWrapperDefaultParams(wrapperId)

        let urlFilterParams = getUrlFilterParams(wrapperId);

        args['selected_terms_navigation_values'] = urlFilterParams;

        if (urlFilterParams['orderby']) {
            args['orderby'] = urlFilterParams['orderby'][0];
        }

        $('a[data-growtype-post-load-more="' + args['parent_id'] + '"]').hide();

        if ($(window).width() <= 570 && args['posts_per_page_mobile']) {
            args['posts_per_page'] = args['posts_per_page_mobile'];
        }

        if (args) {
            jQuery.ajax({
                url: growtype_post.ajax_url,
                type: 'post',
                data: {
                    action: 'growtype_post_ajax_load_content',
                    args: args,
                    nonce: growtype_post.nonce
                },
                success: function (response) {
                    if (response.data.render) {
                        let wrapper = $(response.data.render);

                        component.replaceWith(wrapper);

                        wrapper.attr('data-initial-content-loading-type', 'ajax');

                        termsFilter(wrapper);

                        loadMoreBtnTrigger(wrapper.find('.btn-loadmore'));

                        $('a[data-growtype-post-load-more="' + args['parent_id'] + '"]').show();

                        updateFiltersWithUrlParams(wrapper);

                        if (parseInt(response.data.posts_amount) !== parseInt(args['posts_per_page'])) {
                            wrapper.find('.gp-actions-wrapper').hide();
                        }

                        infiniteLoadPosts(wrapper);

                        adjustPostsGrid(wrapper);

                        document.dispatchEvent(growtypePostAjaxLoadContent({
                            response: response,
                            wrapper: wrapper
                        }));
                    }
                }
            });
        }
    });

    document.addEventListener('growtypePostAjaxLoadContent', function (event) {
        initFiltering(event['detail']['wrapper']);
    });
}
