import {loadMoreBtnTrigger} from "./partials/loadMoreBtnTrigger";
import {customFilterPosts} from "./partials/customFilterPosts";
import {updateFiltersWithUrlParams} from "./partials/updateFiltersWithUrlParams";
import {termsFilter} from "./partials/termsFilter";
import {ajaxLoadPosts} from "./partials/ajaxLoadPosts";
import {postCta} from "./partials/postCta";
import {infiniteLoadPosts} from "./partials/infiniteLoadPosts";
import {setWrapperDefaultParams} from "./partials/setWrapperDefaultParams";

jQuery(document).ready(function () {
    ajaxLoadPosts();
});

jQuery('.growtype-post-container-wrapper').each(function (index, wrapper) {
    let wrapperId = jQuery(wrapper).attr('id');

    setWrapperDefaultParams(wrapperId);
    loadMoreBtnTrigger(jQuery(wrapper).find('a[data-growtype-post-load-more]'));
    loadMoreBtnTrigger(jQuery(wrapper).find('.gp-actions-wrapper .btn-loadmore'));
    customFilterPosts(wrapper);
    updateFiltersWithUrlParams(wrapper);
    termsFilter(wrapper);
    postCta(wrapper);
    infiniteLoadPosts(wrapper);
});
