import {getUrlFilterParams} from "./getUrlFilterParams";

/**
 * Update filter with url params
 */
export function updateFiltersWithUrlParams(wrapper) {
    let wrapperId = $(wrapper).attr('id');
    let urlFilterParams = getUrlFilterParams(wrapperId);

    Object.entries(urlFilterParams).map(function (values) {
        let termsSelect = $(wrapper).find('select.growtype-post-terms-filter[data-type="' + values[0] + '"]');

        if (termsSelect.length > 0) {
            let selectedValues = [];

            values[1].forEach(function (value) {
                let selectedVal = termsSelect.find('option[data-cat-' + values[0] + '="' + value + '"]').val();
                if (selectedVal) {
                    selectedValues.push(selectedVal);
                }
            });

            termsSelect.val(selectedValues);
        }

        let customFiltersSelect = $(wrapper).find('select.growtype-post-custom-filter[name="' + values[0] + '"]');

        if (customFiltersSelect.length > 0) {
            let selectedValues = [];

            values[1].forEach(function (value) {
                let selectedVal = customFiltersSelect.find('option[value="' + value + '"]').val();
                if (selectedVal) {
                    selectedValues.push(selectedVal); // Add selected value if found
                }
            });

            customFiltersSelect.val(selectedValues);
        }
    });

    if (Object.entries(urlFilterParams).length > 0) {
        $(wrapper).find('.growtype-post-terms-filter-btn').removeClass('is-active');

        Object.entries(urlFilterParams).forEach(([key, valueArray]) => {
            valueArray.forEach(value => {
                let btn = $(wrapper).find('.growtype-post-terms-filter-btn[data-cat-' + key + '="' + value + '"]');

                if (btn.length > 0) {
                    btn.addClass('is-active');
                }
            });
        });
    }
}
