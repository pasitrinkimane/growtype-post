export function growtypePostTermsFilterContent(params) {
    return new CustomEvent("growtypePostTermsFilterContent", {
        detail: params
    });
}
