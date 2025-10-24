import {loadMoreBtnTrigger} from "./actions/loadMoreBtnTrigger";
import {customFilterPosts} from "./actions/customFilterPosts";
import {updateFiltersWithUrlParams} from "./actions/updateFiltersWithUrlParams";
import {termsFilter} from "./actions/termsFilter";
import {loadContent} from "./actions/loadContent";
import {postCta} from "./actions/postCta";
import {infiniteLoadPosts} from "./actions/infiniteLoadPosts";
import {setWrapperDefaultParams} from "./actions/setWrapperDefaultParams";

jQuery(document).ready(function () {
    loadContent();
});

jQuery('.growtype-post-container-wrapper').each(function (index, wrapper) {
    let wrapperId = jQuery(wrapper).attr('id');
    let visiblePosts = jQuery(wrapper).find('.growtype-post-container').attr('data-visible-posts');
    let visiblePostsMobile = jQuery(wrapper).find('.growtype-post-container').attr('data-visible-posts-mobile');

    setWrapperDefaultParams(wrapperId);
    loadMoreBtnTrigger(jQuery(wrapper).find('a[data-growtype-post-load-more]'));
    loadMoreBtnTrigger(jQuery(wrapper).find('.gp-actions-wrapper .btn-loadmore'));
    customFilterPosts(wrapper);
    updateFiltersWithUrlParams(wrapper);
    termsFilter(wrapper);
    postCta(wrapper);
    infiniteLoadPosts(wrapper);

    if ($(window).width() <= 768 && visiblePosts !== visiblePostsMobile) {
        jQuery(wrapper)
            .find('.growtype-post-container .growtype-post-single')
            .each(function (index) {
                if (index >= visiblePostsMobile) {
                    jQuery(this).css('display', 'none');
                }
            });
    }
});
