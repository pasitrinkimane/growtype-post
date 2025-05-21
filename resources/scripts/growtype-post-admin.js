$ = jQuery;

function growtypePostAdminShowNotice(data, success = true) {
    if (Array.isArray(data)) {
        data.forEach((msg) => {
            growtypePostAdminRenderNotice(msg)
        });
    } else {
        growtypePostAdminRenderNotice(data)
    }
}

function growtypePostAdminRenderNotice(data, success = true, autoDisappear = false, wrapperType = 'alert') {
    let message = data.message && data.message.length > 0 ? data.message : 'Data was updated.';

    success = data.success ?? success;

    let alertHtml = '';
    const alertWrapperClass = 'growtype-auth-alert-wrapper';
    const alertContentClass = 'growtype-auth-alert';

    let alertWrapper = '<div class="' + alertWrapperClass + '" data-type="' + wrapperType + '"></div>';
    let alertContent = `
    <div class="${alertContentClass} ${!success ? 'alert-danger' : 'alert-success'}">
        <div class="growtype-auth-alert-message">${message}</div>
        <button type="button" class="btn-close"></button>
    </div>
`;

    if ($('.' + alertWrapperClass).length > 0) {
        // If the wrapper already exists, just append the content
        alertHtml = alertContent;
        $('.' + alertWrapperClass).append(alertHtml);
    } else {
        // If the wrapper doesn't exist, create it and append the content inside
        alertHtml = $(alertWrapper).append(alertContent).prop('outerHTML'); // Convert to HTML string
        $('body').append(alertHtml);
    }

    // if (data.redirect_url) {
    //     if (window.confirm('Auth is required. Do you want to proceed?')) {
    //         window.location.href = data.redirect_url;
    //     }
    // }

    if (autoDisappear) {
        setTimeout(() => {
            $('.growtype-auth-alert-wrapper').remove();
        }, 3000);
    }

    growtypePostAdminCloseNotice()
}

function growtypePostAdminCloseNotice() {
    $('.growtype-auth-alert-wrapper .btn-close').click(function () {
        growtypePostAdminCloseNotices();
    });
}

function growtypePostAdminCloseNotices() {
    $('.growtype-auth-alert-wrapper').remove();
}

function growtypePostAdminFormShowLoader(form, withText = true) {
    // Add the loading class
    form.addClass('is-loading');

    // Create the loader container
    const loaderWrapper = $('<div class="growtype-post-loader-wrapper"></div>');
    const loaderSpinner = $('<div class="growtype-post-loader-spinner"></div>');
    const loaderText = $('<div class="growtype-post-loader-text"></div>');

    // Define the list items
    const steps = [
        'Analyzing topic and keywords',
        'Generating article outline',
        'Writing introduction',
        'Creating main body content',
        'Enhancing readability and flow',
        'Generating supporting images',
        'Writing conclusion and summary',
        'Finalizing and formatting the article'
    ];

    loaderWrapper.append(loaderSpinner);

    if (withText) {
        steps.forEach((step, index) => {
            const listItem = $(`<div class="loader-step" data-step="${index}">${step}<span class="dots"></span></div>`);
            loaderText.append(listItem);
        });

        loaderWrapper.append(loaderText);

        loaderText.find(`.loader-step[data-step="0"]`).addClass('is-active');

        // Animate the steps
        let currentStep = 0;

        function animateDots(stepElement) {
            let dots = '';
            const dotsInterval = setInterval(() => {
                dots += '.';
                if (dots.length > 3) dots = '';
                stepElement.find('.dots').text(dots);
            }, 500); // Animate dots every 500ms

            return dotsInterval;
        }

        function showNextStep() {
            if (currentStep < steps.length) {
                const currentStepElement = loaderText.find(`.loader-step[data-step="${currentStep}"]`);
                const dotsInterval = animateDots(currentStepElement);

                const delay = Math.floor(Math.random() * (4000 - 2000 + 1)) + 2000; // Random delay between 2 and 6 seconds
                setTimeout(() => {
                    clearInterval(dotsInterval); // Stop dots animation for the current step
                    currentStepElement.find('.dots').text(''); // Clear dots
                    currentStepElement.addClass('is-completed'); // Mark as completed

                    currentStep++;
                    if (currentStep < steps.length) {
                        loaderText.find(`.loader-step[data-step="${currentStep}"]`).addClass('is-active');
                        showNextStep(); // Call recursively for the next step
                    }
                }, delay);
            }
        }

        showNextStep();
    }

    form.append(loaderWrapper);
}

function growtypePostAdminFormHideLoader(form) {
    // Remove the loader and the loading class
    form.find('.growtype-post-loader-wrapper').remove();
    form.removeClass('is-loading');
}
