export function growtypePostAjaxLoadMoreContent(params) {
    return new CustomEvent("growtypePostAjaxLoadMoreContent", {
        detail: params
    });
}
