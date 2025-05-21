import {initFiltering} from "./initFiltering";

/**
 * Filter posts
 */
export function customFilterPosts(wrapper) {
    let wrapperId = $(wrapper).attr('id');

    initFiltering(wrapper);
    getVisiblePosts(wrapper);

    function getVisiblePosts(wrapper) {
        window.growtype_post['wrappers'][wrapperId]['last_visible_posts'] = $(wrapper).find('.growtype-post-container .growtype-post-single:visible');
    }

    ['growtypePostTermsFilterContent', 'growtypePostAjaxLoadMoreContent', 'growtypePostAjaxLoadContent'].forEach(evt =>
        document.addEventListener(evt, function (element) {
            getVisiblePosts(element['detail']['wrapper'])
        }, false)
    );
}
