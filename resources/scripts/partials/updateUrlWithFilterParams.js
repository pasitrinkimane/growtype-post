let queryParamsMap = new Map();

document.addEventListener('DOMContentLoaded', (event) => {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.forEach((value, key) => {
        queryParamsMap.set(key, value);
    });
});

export function updateUrlWithFilterParams(filterParams, container) {
    let containerId = container.attr('id');
    let prefix = 'gps_';

    let includedInUrl = container.find('.growtype-post-terms-filters').attr('data-selections-included-in-url');
    includedInUrl = includedInUrl ? true : false;

    if (!includedInUrl) {
        return;
    }

    Object.entries(filterParams).forEach(([key, value]) => {
        let encodedKey = prefix + encodeURIComponent(key);
        let encodedValue = encodeURIComponent(value);

        // Check if the filter is already initialized in the container
        let filterExists = $('.growtype-post-terms-filter[data-type="' + key + '"][data-init-cat="' + value + '"]').length > 0;

        if (filterExists) {
            return;
        }

        // Update the map with the new value for the key
        queryParamsMap.set(encodedKey, encodedValue);
    });

    // Ensure `gpwid` is only added once
    if (containerId) {
        queryParamsMap.set('gpwid', encodeURIComponent(containerId));
    }

    // Construct the query parameters from the map
    let queryParams = [];
    queryParamsMap.forEach((value, key) => {
        if (filterParams[key.replace(prefix, '')] !== undefined) {
            queryParams.push(`${key}=${value}`);
        }
    });

    let newUrl = `${window.location.pathname}`;

    if (queryParams.length > 0) {
        let queryString = queryParams.join('&');
        newUrl = `${window.location.pathname}?${queryString}`;
    }

    window.history.replaceState({}, '', newUrl);
}
