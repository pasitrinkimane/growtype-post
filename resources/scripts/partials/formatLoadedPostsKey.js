import {growtypePostGetTermsFilterSelectedValues} from "./getTermsFilterSelectedValues";

export function formatLoadedPostsKey(filtersContainer) {
    let filterParams = growtypePostGetTermsFilterSelectedValues(filtersContainer);

    let loadedPostsKey = '';
    Object.entries(filterParams).map(function (element, index) {
        let key = element[0];
        let value = element[1].toString();
        loadedPostsKey += key + '_' + value + '_';
    })

    return loadedPostsKey;
}
