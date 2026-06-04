(function ($) {
    'use strict';

    $(function () {
        var form = document.getElementById('cc-options-form');

        if (!form) {
            return;
        }

        var selected = window.CookieConsentAdmin && window.CookieConsentAdmin.selectedTheme
            ? window.CookieConsentAdmin.selectedTheme
            : '';

        loadThemeSelector(selected);
        update();

        form.addEventListener('change', update, false);
        form.addEventListener('input', debounce(update, 100), false);

        function update() {
            setCode(getOptions(getInputs(form)));
            updateForm(form);
        }

        function setCode(config) {
            var optionsField = $('#icc_popup_options');

            if (!optionsField.length) {
                return;
            }

            if (typeof config === 'string') {
                optionsField.val(normalizeObjectLiteral(config));
                return;
            }

            optionsField.val(JSON.stringify(config));
        }

        function normalizeObjectLiteral(value) {
            value = $.trim(value || '');

            if (value === '') {
                return '{}';
            }

            if (value.charAt(0) === '{') {
                return value;
            }

            return '{' + value + '}';
        }

        function getOptions(input) {
            if (input.custom) {
                return input.custom;
            }

            var content = {};
            var options = {};
            var t = input.text;
            var themes = getThemes();
            var selectedTheme = themes[input.theme] ? input.theme : 'theme1';

            if (t.message) {
                content.message = escapeHtml(t.message);
            }

            if (t.dismiss) {
                content.dismiss = escapeHtml(t.dismiss);
            }

            if (t.allow) {
                content.allow = escapeHtml(t.allow);
            }

            if (t.deny) {
                content.deny = escapeHtml(t.deny);
            }

            if (t.link) {
                content.link = escapeHtml(t.link);
            }

            options.palette = $.extend(true, {}, themes[selectedTheme]);

            if (input.layout === 'wire') {
                options.palette.button.border = options.palette.button.background;
                options.palette.button.background = 'transparent';
                options.palette.button.text = options.palette.button.border;
            }

            if (input.policy === 'policylink') {
                if (input.href) {
                    content.href = input.href;
                } else {
                    options.showLink = false;
                }
            }

            if (input.layout !== 'block' && input.layout !== 'wire') {
                options.theme = input.layout;
            }

            if (input.position !== 'bottom') {
                if (input.position === 'top-push') {
                    options.position = 'top';
                    options.static = true;
                } else {
                    options.position = input.position;
                }
            }

            if (input.compliance !== 'info') {
                options.type = input.compliance;
            }

            if (Object.keys(content).length > 0) {
                options.content = content;
            }

            return options;
        }

        function getInputs(elem) {
            return {
                text: {
                    allow: getValue(elem, '[name="allow-text"]'),
                    link: getValue(elem, '[name="link-text"]'),
                    message: getValue(elem, '[name="message-text"]'),
                    deny: getValue(elem, '[name="deny-text"]'),
                    dismiss: getValue(elem, '[name="dismiss-text"]')
                },
                href: getValue(elem, '[name="link-href"]'),
                policy: getCheckedValue(elem, '[name="policy"]', 'aboutcookies'),
                position: getCheckedValue(elem, '[name="choose-position"]', 'bottom'),
                layout: getCheckedValue(elem, '[name="choose-layout"]', 'block'),
                theme: getCheckedValue(elem, '[name="theme-selector"]', 'theme1'),
                compliance: getCheckedValue(elem, '[name="choose-cookie-compliance"]', 'info'),
                custom: getValue(elem, '[name="custom-attributes"]')
            };
        }

        function getValue(elem, selector) {
            var input = elem.querySelector(selector);
            return input ? input.value : '';
        }

        function getCheckedValue(elem, selector, fallback) {
            var input = elem.querySelector(selector + ':checked');
            return input ? input.value : fallback;
        }

        function escapeHtml(html) {
            var text = document.createTextNode(html);
            var div = document.createElement('div');
            div.appendChild(text);
            return div.innerHTML;
        }

        function updateForm(form) {
            var policyLinkContainer = document.getElementById('text-policylink-container');
            var acceptContainer = document.getElementById('text-accept-container');
            var denyContainer = document.getElementById('text-deny-container');
            var policy = getCheckedValue(form, '[name="policy"]', 'aboutcookies');
            var compliance = getCheckedValue(form, '[name="choose-cookie-compliance"]', 'info');
            var href = getValue(form, '[name="link-href"]');

            if (policyLinkContainer) {
                policyLinkContainer.style.display = (policy === 'policylink' && href === '') ? 'none' : 'inline';
            }

            if (acceptContainer) {
                acceptContainer.style.display = compliance === 'opt-in' ? 'inline' : 'none';
            }

            if (denyContainer) {
                denyContainer.style.display = compliance === 'opt-out' ? 'inline' : 'none';
            }
        }

        function loadThemeSelector(selected) {
            var themes = getThemes();
            var container = $('#choose-colours');

            if (!container.length) {
                return;
            }

            if (!themes[selected]) {
                selected = 'theme1';
            }

            container.empty();

            $.each(themes, function (key, theme) {
                var checked = selected === key ? ' checked' : '';
                var html = '';

                html += '<input type="radio" name="theme-selector" id="' + key + '-colour" class="input-hidden" value="' + key + '"' + checked + ' />';
                html += '<label for="' + key + '-colour"><div class="theme-preview-container" style="background:' + theme.popup.background + ';">';
                html += '<div class="theme-preview-button" style="background:' + theme.button.background + ';"></div></div></label>';

                container.append(html);
            });
        }

        function getThemes() {
            return {
                theme1: {popup: {background: '#000'}, button: {background: '#f1d600'}},
                theme2: {popup: {background: '#eaf7f7', text: '#5c7291'}, button: {background: '#56cbdb', text: '#ffffff'}},
                theme3: {popup: {background: '#252e39'}, button: {background: '#14a7d0'}},
                theme4: {popup: {background: '#000', text: '#0f0'}, button: {background: '#0f0'}},
                theme5: {popup: {background: '#3937a3'}, button: {background: '#e62576'}},
                theme6: {popup: {background: '#64386b', text: '#ffcdfd'}, button: {background: '#f8a8ff', text: '#3f0045'}},
                theme7: {popup: {background: '#237afc'}, button: {background: '#fff', text: '#237afc'}},
                theme8: {popup: {background: '#aa0000', text: '#ffdddd'}, button: {background: '#ff0000'}},
                theme9: {popup: {background: '#383b75'}, button: {background: '#f1d600'}},
                theme10: {popup: {background: '#1d8a8a'}, button: {background: '#62ffaa'}},
                theme11: {popup: {background: '#edeff5', text: '#838391'}, button: {background: '#4b81e8'}},
                theme12: {popup: {background: '#343c66', text: '#cfcfe8'}, button: {background: '#f71559'}},
                theme13: {popup: {background: '#216942', text: '#b2d192'}, button: {background: '#afed71'}},
                theme14: {popup: {background: '#3c404d', text: '#d6d6d6'}, button: {background: '#8bed4f'}},
                theme15: {popup: {background: '#eb6c44', text: '#ffffff'}, button: {background: '#f5d948'}},
                theme16: {popup: {background: '#efefef', text: '#404040'}, button: {background: '#8ec760', text: '#ffffff'}},
                theme17: {popup: {background: '#f7f7f7', text: '#333333'}, button: {background: '#ec7064', text: '#ffffff'}}
            };
        }

        function debounce(callback, delay) {
            var timer = null;

            return function () {
                var args = arguments;
                var context = this;

                window.clearTimeout(timer);
                timer = window.setTimeout(function () {
                    callback.apply(context, args);
                }, delay);
            };
        }
    });
})(jQuery);
