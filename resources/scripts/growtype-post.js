import {growtypePostLoadMore} from "./partials/loadMore";
import {growtypePostGetTermsFilterSelectedValues} from "./partials/getTermsFilterSelectedValues";
import {growtypePostLoadPosts} from "./partials/loadPosts";
import {growtypePostAjaxLoadContent} from "./partials/events/growtypePostAjaxLoadContent";

$ = jQuery;

window.growtype_post.terms_filter = [];

/**
 * Load more posts
 */
growtypePostLoadMore($('a[data-growtype-post-load-more]'));
growtypePostLoadMore($('.gp-actions-wrapper .btn-loadmore'));

/**
 * Filter posts
 */
function termsTrigger() {
    $('.growtype-post-terms-filter').on('change', function (event) {
        let postsContainer = $(event.target).closest('.wp-block-growtype-post');
        let filtersContainer = $(event.target).closest('.growtype-post-terms-filters');
        let minimumVisiblePostsAmount = postsContainer.find('.growtype-post-container').attr('data-visible-posts');
        minimumVisiblePostsAmount = parseInt(minimumVisiblePostsAmount);

        /**
         * Get current filter params
         */
        let filterParams = growtypePostGetTermsFilterSelectedValues(filtersContainer);

        /**
         * Get posts limit
         */
        let postsLimit = growtypeGetPostsLimit(minimumVisiblePostsAmount, filterParams);

        /**
         * Filter posts
         * @type {number}
         */
        postsContainer.find('.growtype-post-single').fadeOut().promise().done(function () {
            growtypePostLoadPosts(postsContainer, filterParams, postsLimit);
        });
    });

    /**
     * Filter posts
     */
    $('.growtype-post-terms-filter-btn').click(function () {
        if ($(this).attr('data-disabled')) {
            return;
        }

        let triggerType = $(this).attr('data-trigger-type');
        let multipleSelect = $(this).attr('data-multiple-select');

        if (triggerType === 'click' && $(this).hasClass('is-active')) {
            return;
        }

        let postsContainer = $(this).closest('.wp-block-growtype-post');
        let filtersContainer = $(this).closest('.growtype-post-terms-filters');
        let filterContainer = $(this).closest('.growtype-post-terms-filter');
        let minimumVisiblePostsAmount = postsContainer.find('.growtype-post-container').attr('data-visible-posts');
        minimumVisiblePostsAmount = parseInt(minimumVisiblePostsAmount);

        /**
         * Update filter state
         */
        if (triggerType === 'toggle') {
            if (!$(this).hasClass('is-active')) {
                if (multipleSelect === 'false') {
                    filterContainer.find('.growtype-post-terms-filter-btn').removeClass('is-active');
                }

                $(this).addClass('is-active');
            } else {
                $(this).removeClass('is-active');
            }
        } else {
            filterContainer.find('.growtype-post-terms-filter-btn').removeClass('is-active');
            $(this).addClass('is-active');
        }

        /**
         * Get current filter params
         */
        let filterParams = growtypePostGetTermsFilterSelectedValues(filtersContainer);

        /**
         * Get posts limit
         */
        let postsLimit = growtypeGetPostsLimit(minimumVisiblePostsAmount, filterParams);

        /**
         * Filter posts
         * @type {number}
         */
        postsContainer.find('.growtype-post-single').fadeOut().promise().done(function () {
            growtypePostLoadPosts(postsContainer, filterParams, postsLimit);
        });
    });

    /**
     *
     */
    $('.growtype-post-terms-filter').each(function (index, element) {
        if ($(this).attr('data-init-cat') !== '' && $(element).is(':visible')) {
            if ($(element).is('select')) {
                $(element).trigger('change');
            } else if ($(element).is('div')) {
                $('.growtype-post-terms-filter-btn[data-cat-' + $(element).attr('data-type') + '="' + $(element).attr('data-init-cat') + '"]').trigger('click');
            }
        }
    })
}

termsTrigger();

/**
 *
 */
function growtypeGetPostsLimit(minimumVisiblePostsAmount, filterParams) {
    let postsLimit = minimumVisiblePostsAmount;
    let termsFilterAmountKey = '';
    Object.entries(filterParams).map(function (element, index) {
        let key = element[0].toString();
        let value = element[1].toString();
        termsFilterAmountKey += key + '_' + value + '_';
    });

    if (window.growtype_post.terms_filter[termsFilterAmountKey] !== undefined) {
        postsLimit = parseInt(window.growtype_post.terms_filter[termsFilterAmountKey]['visible']);
    }

    return postsLimit;
}

function postCta() {
    /**
     * Like post
     */
    $('.growtype-post-btn-like').click(function () {
        let btn = $(this);
        let link = btn.find('a');

        if ($(this).attr('data-type') === undefined && (link.attr('data-type') === undefined || (link.attr('href') !== undefined && link.attr('href').length > 0))) {
            return;
        }

        event.preventDefault();

        $(this).toggleClass('is-active');

        let dataType = link !== undefined ? link.attr('data-type') : $(this).attr('data-type');
        let postId = $(this).attr('data-id') !== undefined ? $(this).attr('data-id') : growtype_post.post_id;

        jQuery.ajax({
            url: growtype_post.ajax_url,
            type: 'post',
            data: {
                action: 'like_post',
                post_id: postId,
                data_type: dataType
            },
            success: function (response) {
                btn.find('.e-amount').remove();

                if (response.data['likes'] > 0) {
                    btn.prepend('<span class="e-amount">' + response.data['likes'] + '</span>');
                }
            }
        });
    })

    /**
     * Share post
     */
    $('.growtype-post-btn-share').click(function () {
        let link = $(this).find('a');

        if ($(this).attr('data-type') === undefined && (link.attr('data-type') === undefined || (link.attr('href') !== undefined && link.attr('href').length > 0))) {
            return;
        }

        event.preventDefault();

        $(this).toggleClass('is-active');

        let dataType = link !== undefined ? link.attr('data-type') : $(this).attr('data-type');
        let postId = $(this).attr('data-id') !== undefined ? $(this).attr('data-id') : growtype_post.post_id;

        jQuery.ajax({
            url: growtype_post.ajax_url,
            type: 'post',
            data: {
                action: 'share_post',
                post_id: postId,
                data_type: dataType
            },
            success: function (response) {
                let newUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + response.data.share_url;
                setTimeout(() => {
                    window.open(newUrl, "_blank");
                })

                if (link) {
                    link.attr('href', newUrl).attr('target', '_blank')
                }
            }
        });
    })
}

postCta();

/**
 * Ajax load posts
 */

$(document).ready(function () {
    $('.growtype-post-ajax-load-content').each(function (index, element) {
        let component = $(this);
        let container = component.closest('.wp-block-growtype-post');
        let args = component.attr('data-args');
        args = args ? JSON.parse(args) : '';

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

                        termsTrigger();

                        postCta();

                        growtypePostLoadMore(content.find('.btn-loadmore'));

                        $('a[data-growtype-post-load-more="' + args['parent_id'] + '"]').show();

                        document.dispatchEvent(growtypePostAjaxLoadContent(response));
                    }
                }
            });
        }
    });
})
