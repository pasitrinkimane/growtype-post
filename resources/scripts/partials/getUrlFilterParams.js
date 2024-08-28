export function getUrlFilterParams() {
    const urlSearchParams = new URLSearchParams(window.location.search);
    let filterParams = {};

    if (urlSearchParams['size'] && urlSearchParams['size'] > 0) {
        const urlParams = {};
        for (let [key, value] of urlSearchParams.entries()) {
            key = key.replace('gps_', '');
            urlParams[key] = value.split(",");
        }

        if ($('.growtype-post-container-wrapper').length === 0) {
            filterParams = urlParams;
        } else {
            if (Object.entries(urlParams).length > 0) {
                Object.entries(urlParams).forEach(([key, value]) => {
                    let termsFilterBtn = $('.growtype-post-terms-filter-btn[data-cat-' + key + ']');
                    let customFilterBtn = $('.growtype-post-custom-filters-single[data-name="' + key + '"]');

                    if (termsFilterBtn.length > 0 || customFilterBtn.length > 0) {
                        filterParams[key] = value;
                    }
                });
            }
        }
    }

    return filterParams;
}
