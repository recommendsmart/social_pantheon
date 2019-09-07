(function (Drupal) {

    Drupal.AjaxCommands.prototype.dfinanceFinancialFieldAjaxUpdateCurrencySymbol = function (ajax, response, status) {
        // If a Financial Field depends on a Currency Entity Reference Field, it will specify the Entity
        // Reference Field name using the data- parameter "data-dfinance-currency-field" so we use that
        // to find all Financial Fields which need to be updated.
        var financial_fields = document.querySelectorAll("[data-dfinance-currency-field='" + response.currency_field + "']");
        financial_fields.forEach(function(financial_field) {
            financial_field.parentNode.childNodes.forEach(function(element) {
                if (element instanceof HTMLElement && element.classList.contains('field-prefix')) {
                    element.innerHTML = response.currency_sign;
                }
            });
        })
    }

}) (Drupal);
