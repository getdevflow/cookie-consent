(function () {
    'use strict';

    function initCookieConsent(config) {
        if (!window.cookieconsent || !config) {
            return;
        }

        window.cookieconsent.initialise(config);
    }

    fetch('/cookie-consent/config', {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
            'Accept': 'application/json'
        }
    })
        .then(function (response) {
            if (!response.ok) {
                return null;
            }

            return response.json();
        })
        .then(function (config) {
            initCookieConsent(config);
        })
        .catch(function () {
            // Fail silently so the frontend never breaks.
        });
})();
