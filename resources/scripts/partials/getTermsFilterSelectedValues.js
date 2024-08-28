import {updateUrlWithFilterParams} from "./updateUrlWithFilterParams";

/**
 * @param termsFilter
 * @returns {*[]}
 */
export function growtypePostGetTermsFilterSelectedValues(termsFilter) {

    if (termsFilter === undefined) {
        return;
    }

    let filterParams = [];
    let chosenExists = termsFilter.find('.chosen-container:visible').length > 0;

    let filterSelector = '.growtype-post-terms-filter-btn:visible, select option';

    if (!chosenExists) {
        filterSelector = '.growtype-post-terms-filter-btn:visible, select:visible option';
    }

    termsFilter.find(filterSelector).each(function (index, btn) {
        let condition = $(btn).hasClass('is-active') || ($(btn).is(':selected') && $(btn).closest('select').next('.chosen-container:visible').length > 0);

        if (!chosenExists) {
            condition = $(btn).hasClass('is-active') || $(btn).is(':selected');
        }

        if (condition) {
            $(this).each(function (index) {
                $.each(this.attributes, function (index, attr) {
                    if (attr.name.indexOf('data-cat') === 0) {
                        let element = attr.name.replace("data-cat-", "");
                        let value = $(btn).attr('data-cat-' + element);

                        if (value === 'none') {
                            return;
                        }

                        if (filterParams[element] === undefined) {
                            filterParams[element] = [value];
                        } else {
                            filterParams[element].push(value);
                        }
                    }
                });
            });
        }
    });

    updateUrlWithFilterParams(filterParams, termsFilter.closest('.growtype-post-container-wrapper'));

    return filterParams;
}
