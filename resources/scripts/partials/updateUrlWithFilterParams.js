export function updateUrlWithFilterParams(filterParams, container) {
    let queryParams = [];
    let containerId = container.attr('id');

    Object.entries(filterParams).forEach(([key, value]) => {
        let encodedKey = encodeURIComponent(key);
        let encodedValue = encodeURIComponent(value);

        if ($('.growtype-post-terms-filter[data-type="' + key + '"][data-init-cat="' + value + '"]').length > 0) {
            return;
        }

        queryParams.push(`${encodedKey}=${encodedValue}`);
    });

    let newUrl = `${window.location.pathname}`

    if (queryParams.length > 0) {
        let queryString = queryParams.join('&');

        if (containerId) {
            queryString = `gpcid=${encodeURIComponent(containerId)}&${queryString}`;
        }

        newUrl = `${window.location.pathname}?${queryString}`;
    }

    window.history.replaceState({}, '', newUrl);
}
