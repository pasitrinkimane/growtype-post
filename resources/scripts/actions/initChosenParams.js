export function initChosenParams(element) {
    return {
        width: '100%',
        disable_search: element.attr('data-disable-search') === 'true',
        allow_single_deselect: element.attr('data-allow-single-deselect') === 'true',
        // placeholder_text_single: "Select single...",
        // placeholder_text_multiple: "Select multiple...",
        no_results_text: "Oops, nothing found!"
    };
}
