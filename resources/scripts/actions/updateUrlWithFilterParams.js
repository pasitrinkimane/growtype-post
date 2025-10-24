let queryParamsMap = new Map();

document.addEventListener('DOMContentLoaded', (event) => {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.forEach((value, key) => {
        queryParamsMap.set(key, value);
    });
});

export function updateUrlWithFilterParams(filterParams, wrapper) {
    let wrapperId = wrapper.attr('id');
    let prefix = window.growtype_post['wrappers'][wrapperId]['filter_url_params_prefix'];

    let includedInUrl = wrapper.find('.growtype-post-terms-filters').attr('data-selections-included-in-url');
    includedInUrl = includedInUrl ? true : false;

    if (!includedInUrl) {
        return;
    }

    const urlSearchParams = new URLSearchParams(window.location.search);
    const queryParamsMap = new Map();

    urlSearchParams.forEach((value, key) => {
        if (!key.startsWith(prefix)) {
            queryParamsMap.set(key, value);
        }
    });

    Object.entries(filterParams).forEach(([key, value]) => {
        let encodedKey = prefix + encodeURIComponent(key);
        let encodedValue = encodeURIComponent(value);

        let filterExists = $('.growtype-post-terms-filter[data-type="' + key + '"][data-init-cat="' + value + '"]').length > 0;

        if (!filterExists) {
            queryParamsMap.set(encodedKey, encodedValue);
        }
    });

    if (wrapperId) {
        queryParamsMap.set('gpwid', encodeURIComponent(wrapperId));
    }

    let queryParams = [];
    queryParamsMap.forEach((value, key) => {
        queryParams.push(`${key}=${value}`);
    });

    if (queryParams.length > 0) {
        let newUrl = `${window.location.pathname}`;
        let queryString = queryParams.join('&');
        newUrl = `${window.location.pathname}?${queryString}` + window.location.hash;

        setTimeout(function () {
            window.history.replaceState({}, '', newUrl);
        }, 500);
    }
}
