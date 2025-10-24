export function getUrlFilterParams(wrapperId) {
    const urlSearchParams = new URLSearchParams(window.location.search);
    let filterParams = {};

    if (!window.growtype_post['wrappers'][wrapperId]) {
        return {};
    }

    let prefix = window.growtype_post['wrappers'][wrapperId]['filter_url_params_prefix'];

    const urlParams = {};
    for (let [key, value] of urlSearchParams.entries()) {
        if (key.startsWith(prefix)) {
            key = key.replace(prefix, '');
            urlParams[key] = value.split(",");
        }
    }

    if (Object.entries(urlParams).length > 0) {
        let wrapper = $('.growtype-post-container-wrapper[id="' + wrapperId + '"]');

        Object.entries(urlParams).forEach(([key, value]) => {
            if (wrapper.length > 0) {
                let termsFilterBtn = $(wrapper).find('.growtype-post-terms-filter-btn[data-cat-' + key + ']');
                let customFilterBtn = $(wrapper).find('.growtype-post-custom-filters-single[data-name="' + key + '"]');

                if (termsFilterBtn.length > 0 || customFilterBtn.length > 0) {
                    filterParams[key] = value;
                }
            } else {
                filterParams[key] = value;
            }
        });
    }

    return filterParams;
}
