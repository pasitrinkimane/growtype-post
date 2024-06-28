import {growtypePostLoadPosts} from "./loadPosts";
import {getPostsLimit} from "./getPostsLimit";
import {getUrlFilterParams} from "./getUrlFilterParams";

export function updateFilterWithUrlParams() {

    if ($('.growtype-post-container-wrapper').length === 0) {
        return;
    }

    let urlFilterParams = getUrlFilterParams();

    if (Object.entries(urlFilterParams).length > 0) {
        let applyFilter = false
        Object.entries(urlFilterParams).forEach(([key, value]) => {
            let filterBtn = $('.growtype-post-terms-filter-btn[data-cat-' + key + '="' + value + '"]');

            if (filterBtn.length > 0) {
                if (!filterBtn.hasClass('is-active')) {
                    applyFilter = true;
                }
            }
        });

        $('.growtype-post-terms-filter-btn').removeClass('is-active');

        Object.entries(urlFilterParams).forEach(([key, value]) => {
            let filterBtn = $('.growtype-post-terms-filter-btn[data-cat-' + key + '="' + value + '"]');

            if (filterBtn.length > 0) {
                filterBtn.addClass('is-active');
            }
        });

        let postsContainer = $('.growtype-post-container-wrapper');

        postsContainer.map(function (index, container) {

            const urlSearchParams = new URLSearchParams(window.location.search);

            for (let [key, value] of urlSearchParams.entries()) {
                if (key === 'gpcid') {
                    let specificContainer = $('#' + value);

                    if (specificContainer.length > 0) {
                        container = specificContainer;
                    }
                }
            }

            if (container.length > 0) {
                let minimumVisiblePostsAmount = container.find('.growtype-post-container').attr('data-visible-posts');
                minimumVisiblePostsAmount = parseInt(minimumVisiblePostsAmount);

                /**
                 * Get posts limit
                 */
                let postsLimit = getPostsLimit(minimumVisiblePostsAmount, urlFilterParams);

                /**
                 * Filter posts
                 * @type {number}
                 */
                if (applyFilter) {
                    container.find('.growtype-post-single').fadeOut().promise().done(function () {
                        growtypePostLoadPosts(container, urlFilterParams, postsLimit);
                    });
                }
            }
        });
    }
}
