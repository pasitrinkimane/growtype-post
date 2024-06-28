export function getPostsLimit(minimumVisiblePostsAmount, filterParams) {
    let postsLimit = minimumVisiblePostsAmount;

    if (postsLimit === -1) {
        postsLimit = 99999;
    } else {
        let termsFilterAmountKey = '';
        Object.entries(filterParams).map(function (element, index) {
            let key = element[0].toString();
            let value = element[1].toString();
            termsFilterAmountKey += key + '_' + value + '_';
        });

        if (window.growtype_post.terms_filter[termsFilterAmountKey] !== undefined) {
            postsLimit = parseInt(window.growtype_post.terms_filter[termsFilterAmountKey]['visible']);
        }
    }

    return postsLimit;
}
