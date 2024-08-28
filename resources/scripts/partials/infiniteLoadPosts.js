export function infiniteLoadPosts() {
    function isElementInView($element) {
        if ($element.length === 0) {
            return false;
        }

        var elementTop = $element.offset().top;
        var elementBottom = elementTop + $element.outerHeight();
        var viewportTop = $(window).scrollTop();
        var viewportBottom = viewportTop + $(window).height() + 400;

        return elementBottom > viewportTop && elementTop < viewportBottom;
    }

    let infiniteLoadingWasLoaded = false;

    function initInfiniteLoad() {
        if (!infiniteLoadingWasLoaded && $('.growtype-post-container-wrapper').length > 0 && $('.gp-actions-wrapper .btn-loadmore').length > 0) {

            let params = $('.growtype-post-container-wrapper').attr('data-args');
            params = params ? JSON.parse(params) : '';

            if (params['infinite_load_posts']) {
                infiniteLoadingWasLoaded = true;

                window.growtype_post_load_more_initiated = false;
                window.addEventListener('scroll', function () {
                    let btnLoadmore = $('.gp-actions-wrapper .btn-loadmore');

                    if (isElementInView(btnLoadmore)) {
                        if (!window.growtype_post_load_more_initiated) {
                            window.growtype_post_load_more_initiated = true;
                            btnLoadmore.trigger('click');
                        }
                    } else {
                        window.growtype_post_load_more_initiated = false;
                    }
                });
            }
        }
    }

    if ($('.growtype-post-container-wrapper').length > 0) {
        initInfiniteLoad();
    } else {
        document.addEventListener('growtypePostAjaxLoadContent', function () {
            initInfiniteLoad();
        })
    }
}
