export function filterPosts(posts, filterParams, showValid = true) {
    posts.each(function (index, element) {
        let row = $(element);
        let validValues = [];
        Object.entries(filterParams).forEach(function ([name, element]) {
            let value = element['value'].toLowerCase();
            let content = row.attr('data-cat-' + name);

            if (content) {
                content = content.toLowerCase();
                content = content.includes(',') ? content.split(',') : [content];
            } else {
                content = [];
            }

            if ((name === 'search' && value.length === 0) || value === 'all') {
                validValues[name] = true;
                return;
            }

            if (name === 'search' && content.some(item => item.includes(value))) {
                validValues[name] = true;
                return;
            }

            if (content.includes(value)) {
                validValues[name] = true;
            } else {
                validValues[name] = false;
            }
        });

        let isValid = true;
        Object.entries(validValues).map(function (element, index) {
            if (!element[1]) {
                isValid = false;
            }
        });

        if (showValid) {
            if (isValid) {
                row.show();
            }
        } else {
            if (!isValid) {
                row.hide();
            }
        }
    });
}
