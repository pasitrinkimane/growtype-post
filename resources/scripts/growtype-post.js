import {loadMoreBtnTrigger} from "./partials/loadMoreBtnTrigger";
import {filterPosts} from "./partials/filterPosts";
import {updateFilterWithUrlParams} from "./partials/updateFilterWithUrlParams";
import {termsFilter} from "./partials/termsFilter";
import {ajaxLoadPosts} from "./partials/ajaxLoadPosts";
import {postCta} from "./partials/postCta";

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
filterPosts();

/**
 * Update filter with url params
 */
updateFilterWithUrlParams();

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
