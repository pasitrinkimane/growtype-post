export function growtypePostAjaxLoadContent(params) {
    return new CustomEvent("growtypePostAjaxLoadContent", {
        detail: params
    });
}
