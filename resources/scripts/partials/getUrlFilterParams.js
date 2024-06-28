export function getUrlFilterParams() {
    const urlSearchParams = new URLSearchParams(window.location.search);
    let filterParams = {};

    if (urlSearchParams['size'] && urlSearchParams['size'] > 0) {
        const urlParams = {};
        for (let [key, value] of urlSearchParams.entries()) {
            urlParams[key] = value.split(",");
        }

        if ($('.growtype-post-container-wrapper').length === 0) {
            filterParams = urlParams;
        } else {
            if (Object.entries(urlParams).length > 0) {
                Object.entries(urlParams).forEach(([key, value]) => {
                    let filterBtn = $('.growtype-post-terms-filter-btn[data-cat-' + key + ']');

                    if (filterBtn.length > 0) {
                        filterParams[key] = value;
                    }
                });
            }
        }
    }

    return filterParams;
}
