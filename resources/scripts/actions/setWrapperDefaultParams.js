export function setWrapperDefaultParams(wrapperId) {
    window.growtype_post['wrappers'][wrapperId] = {};
    window.growtype_post['wrappers'][wrapperId]['terms_filter'] = [];
    window.growtype_post['wrappers'][wrapperId]['filter_url_params_prefix'] = wrapperId + '-s_';
}
