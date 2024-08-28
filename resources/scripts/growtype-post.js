import {loadMoreBtnTrigger} from "./partials/loadMoreBtnTrigger";
import {customFilterPosts} from "./partials/customFilterPosts";
import {updateFiltersWithUrlParams} from "./partials/updateFiltersWithUrlParams";
import {termsFilter} from "./partials/termsFilter";
import {ajaxLoadPosts} from "./partials/ajaxLoadPosts";
import {postCta} from "./partials/postCta";
import {infiniteLoadPosts} from "./partials/infiniteLoadPosts";

$ = jQuery;

window.growtype_post.terms_filter = [];

/**
 * Load more posts
 */
loadMoreBtnTrigger($('a[data-growtype-post-load-more]'));
loadMoreBtnTrigger($('.gp-actions-wrapper .btn-loadmore'));

/**
 * Filter posts
 */
customFilterPosts();

/**
 * Update filter with url params
 */
updateFiltersWithUrlParams();

/**
 * Terms filter
 */
termsFilter();

/**
 * Post CTA
 */
postCta();

/**
 * Ajax load posts
 */
ajaxLoadPosts();

/**
 * Infinite load posts
 */
infiniteLoadPosts();
