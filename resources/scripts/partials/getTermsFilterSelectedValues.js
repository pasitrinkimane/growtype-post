export function growtypePostGetTermsFilterSelectedValues(filtersContainer) {
    if (!filtersContainer) return;

    const filterParams = {};
    const chosenExists = filtersContainer.find('.chosen-container').length > 0;

    const filterWrapper = filtersContainer.closest('.growtype-post-filters-wrapper');
    const filterElements = filterWrapper.find('.growtype-post-terms-filter-btn, select option');

    // Process filter elements
    filterElements.each(function () {
        const btn = $(this);
        const isActive = btn.hasClass('is-active');
        const isSelected = btn.is(':selected');
        const isChosen = btn.closest('select').next('.chosen-container').length > 0;

        // Check condition based on chosenExists
        if ((isActive || (isSelected && isChosen)) || (!chosenExists && (isActive || isSelected))) {
            $.each(this.attributes, (index, attr) => {
                if (attr.name.startsWith('data-cat')) {
                    const element = attr.name.replace('data-cat-', '');
                    const value = btn.attr(attr.name);

                    if (value !== 'none') {
                        if (!filterParams[element]) {
                            filterParams[element] = [value];
                        } else if (!filterParams[element].includes(value)) {
                            filterParams[element].push(value);
                        }
                    }
                }
            });
        }
    });

    // Handle multiselect filters with no selected values
    filtersContainer.find('select[multiple]').each(function () {
        const select = $(this);
        const elementName = select.attr('name') || select.data('filter-param');
        const selectedOptions = select.find(':selected');

        if (selectedOptions.length === 0 && elementName) {
            delete filterParams[elementName];
        }
    });

    return filterParams;
}
