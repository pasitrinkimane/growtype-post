export function updateUrlWithFilterParams(filterParams, container) {
    let queryParams = [];
    let containerId = container.attr('id');

    Object.entries(filterParams).forEach(([key, value]) => {
        let encodedKey = encodeURIComponent(key);
        let encodedValue = encodeURIComponent(value);
        queryParams.push(`${encodedKey}=${encodedValue}`);
    });

    let queryString = queryParams.join('&');

    if (containerId) {
        queryString = `gpcid=${encodeURIComponent(containerId)}&${queryString}`;
    }

    let newUrl = `${window.location.pathname}?${queryString}`;
    window.history.replaceState({}, '', newUrl);
}
