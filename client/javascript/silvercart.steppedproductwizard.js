var $          = $          ? $          : jQuery;
var silvercart = silvercart ? silvercart : [];

silvercart.ProductWizard = [];
silvercart.ProductWizard.Base = (function () {
    var property = {},
        selector = {},
        private = {},
        public = {
            getBaseControllerURL: function() {
                var parts = document.location.href.split('/');
                parts.pop();
                parts.pop();
                return parts.join('/') + '/';
            }
        };
    return public;
});
silvercart.ProductWizard.CartSummary = (function () {
    var property = {
            postRequestCount: 0,
        },
        selector = {
            container: "#ProductWizardCartSummary",
            amounts: "#ProductWizardCartSummaryAmounts",
            positions: "#ProductWizardCartSummaryPositions",
            stepBase: "#ProductWizardCartSummaryStep-",
            wizardOptionProduct: '.wizard-option-product',
        },
        private = {
            getBaseControllerURL: function() {
                return silvercart.ProductWizard.Base().getBaseControllerURL();
            },
            renderServices: function(services) {
                $.each(services, function(serviceID, serviceQuantity) {
                    var option = $(selector.wizardOptionProduct + '[data-product-id="' + serviceID + '"]');
                    if (option.length > 0) {
                        var optionID = option.data('option-id'),
                            btnQty   = $('#pick-quantity-' + optionID + ' a[data-quantity="' + serviceQuantity + '"]'),
                            inputQty = $('.pick-more-quantity-field[data-option-id="' + optionID + '"][data-product-id="' + serviceID + '"]');
                        if (btnQty.length > 0) {
                            btnQty.addClass('skip-ajax');
                            btnQty.trigger('click');
                            btnQty.removeClass('skip-ajax');
                        } else if (inputQty.length > 0) {
                            $('.pick-more-quantity[data-option-id="' + optionID + '"]').trigger('click');
                            inputQty.val(serviceQuantity);
                        }
                    }
                });
            },
            renderServiceProducts: function(serviceProducts) {
                $.each(serviceProducts, function(serviceProductID, serviceProductQuantity) {
                    var option     = $(selector.wizardOptionProduct + '[data-product-id="' + serviceProductID + '"]'),
                        serviceIDs = option.data('service-ids');
                    if (option.length > 0) {
                        var optionID = option.data('option-id'),
                            btnQty   = $('#pick-quantity-' + optionID + ' a[data-quantity="' + serviceProductQuantity + '"]'),
                            inputQty = $('.pick-more-quantity-field[data-option-id="' + optionID + '"]');
                        if (btnQty.length > 0) {
                            btnQty.addClass('skip-ajax');
                            btnQty.trigger('click');
                            btnQty.removeClass('skip-ajax');
                        } else if (inputQty.length > 0) {
                            $('.pick-more-quantity[data-option-id="' + optionID + '"]').trigger('click');
                            inputQty.val(serviceProductQuantity);
                        }
                        if (typeof serviceIDs === 'string'
                         && serviceIDs.split(',').length > 1
                        ) {
                            public.postOptionData(optionID, serviceProductID, serviceProductQuantity)
                        }
                    }
                });
            },
            renderSteps: function(steps) {
                $.each(steps, function(stepID, stepPositions) {
                    var stepSummary = $(selector.stepBase + stepID);
                    if (stepSummary.length === 0) {
                        return;
                    }
                    stepSummary.html('');
                    var table = document.createElement('table');
                    $(table).addClass('w-100');
                    if (stepPositions.length === 0) {
                        var row = document.createElement('tr'),
                            col = document.createElement('td');
                        $(col).html(ss.i18n._t('SilverCart.ProductWizard.ERROR.NoProductsYet', 'You have not selected any items yet.'));
                        $(row).append(col);
                        $(table).append(row);
                    } else {
                        $.each(stepPositions, function(optionID, positions) {
                            private.renderPositions(positions, table);
                        });
                    }
                    stepSummary.append($(table));
                });
            },
            renderPositions: function(positions, table) {
                $.each(positions, function(key, position) {
                    var row  = document.createElement('tr'),
                        col1 = document.createElement('td'),
                        col2 = document.createElement('td');
                    $(col1).addClass('text-muted align-top line-height-1 pb-5px pr-5px word-break-word');
                    $(col2).addClass('text-muted align-top line-height-1 pb-5px text-right text-nowrap');
                    $(col1).html(position.productQuantity + 'x ' + position.productTitle);
                    if (typeof position.priceTotalConsequential === 'object') {
                        $(col2).html(position.priceTotal.Nice + '<br/>' + position.BillingPeriodNice + '<br/>' + position.priceTotalConsequential.Nice + '<br/>' + position.BillingPeriodConsequentialNice);
                    } else {
                        $(col2).html(position.priceTotal.Nice + '<br/>' + position.BillingPeriodNice);
                    }
                    $(row).append(col1).append(col2);
                    $(table).append(row);
                });
            },
            renderPriceTotalAmounts: function(amounts) {
                var trClasses = $('tr', selector.amounts).attr('class');
                $(selector.amounts).html('');
                $.each(amounts, function(key, value) {
                    var row  = document.createElement('tr'),
                        col1 = document.createElement('td'),
                        col2 = document.createElement('td');
                        $(row).addClass(trClasses);
                        $(col2).addClass('text-right');
                        $(col1).html(key);
                        $(col2).html(value.Nice);
                        $(row).append(col1).append(col2);
                    $(selector.amounts).append($(row));
                });
            },
            togglePositionTable: function() {
                var stepID = $(this).data('step-id');
                $('.fa', this).toggleClass('fa-angle-right fa-angle-down');
                $(selector.stepBase + stepID).slideToggle();
            }
        },
        public = {
            initWith: function(json) {
                var data = $.parseJSON(json);
                private.renderSteps(data.Steps);
                private.renderServices(data.Services);
                private.renderServiceProducts(data.ServiceProducts);
                private.renderPriceTotalAmounts(data.Amounts);
            },
            init: function()
            {
                if ($(selector.container).length === 0) {
                    return;
                }
                $(document).on('click', selector.positions + ' a', private.togglePositionTable);
                $(selector.container).addClass('loading');
                $.get(
                        private.getBaseControllerURL() + 'getCartSummaryData',
                        function(data) {
                            public.initWith(data);
                            $(selector.container).removeClass('loading');
                            
                            var stepLink = $(selector.positions + ' a.current'),
                                stepID   = stepLink.data('step-id');
                            $('.fa', stepLink).toggleClass('fa-angle-right fa-angle-down');
                            $(selector.stepBase + stepID).slideToggle();
                        }
                );
            },
            postOptionData: function(optionID, productID, quantity, callback) {
                if (quantity < 0) {
                    quantity = 0;
                }
                $(selector.container).addClass('loading');
                property.postRequestCount++;
                $.post(
                        private.getBaseControllerURL() + 'postOptionData',
                        {
                            'OptionID':   optionID,
                            'ProductID':  productID,
                            'Quantity': quantity
                        },
                        function(data) {
                            property.postRequestCount--;
                            if (property.postRequestCount === 0) {
                                public.initWith(data);
                                $(selector.container).removeClass('loading');
                                if ($('.alert-submit-button-error-message').length > 0) {
                                    silvercart.ProductWizard.OptionsWithProgress().validateFields();
                                }
                            }
                            var callable = eval(callback);
                            if (typeof callable === 'function') {
                                callable(optionID, productID, quantity, data);
                            }
                        }
                );
            },
            postPlainOptionData: function(radioOptionName, allowMultiple) {
                if (allowMultiple) {
                    var optionID           = $('input[name="' + radioOptionName + '"]').data('option-id'),
                        postData           = {};
                        postData[optionID] = {};
                    $('input[type="checkbox"][data-option-id="' + optionID + '"]').each(function() {
                        if ($(this).is(':checked')) {
                            postData[optionID][$(this).val()] = $(this).val();
                        }
                    });
                } else {
                    var radioOptionValue   = $('input[name="' + radioOptionName + '"]:checked').val(),
                        optionID           = $('input[name="' + radioOptionName + '"]:checked').data('option-id'),
                        postData           = {};
                        postData[optionID] = radioOptionValue;
                }
                $(selector.container).addClass('loading');
                $.post(
                        private.getBaseControllerURL() + 'postPlainOptionData',
                        {
                            'StepOptions': postData
                        },
                        function(data) {
                            public.initWith(data);
                            $(selector.container).removeClass('loading');
                            if ($('.alert-submit-button-error-message').length > 0) {
                                silvercart.ProductWizard.OptionsWithProgress().validateFields();
                            }
                        }
                );
            },
            deleteOptionData: function(optionID, productID) {
                $(selector.container).addClass('loading');
                $.post(
                        private.getBaseControllerURL() + 'deleteOptionData',
                        {
                            'OptionID':   optionID,
                            'ProductID':  productID
                        },
                        function(data) {
                            public.initWith(data);
                            $(selector.container).removeClass('loading');
                        }
                );
            }
        };
    return public;
});
silvercart.ProductWizard.OptionsWithProgress = (function () {
    var property = {
            optionSetSelector: false,
            cartSummary: false,
            spinnerTimeout: false,
            behavior: {
                enableAll:  'enable-all',
                disableAll: 'disable-all',
            },
            skipPickRadioOption: false,
            reenableDataStorage: [],
        },
        selector = {
            container: "#ProductWizardStepOptionsWithProgress",
            inputUpdateQuantityOnChange: '.update-quantity-on-change',
            option: ".wizard-option",
            optionPicker: ".wizard-option-picker",
            optionPickerBtnChoose: ".wizard-option-picker.btn-choose",
            optionProduct: '.wizard-option-product',
            optionProductRequired: '.wizard-required-product-option',
            pickQuantity: ".pick-quantity",
            pickMoreQuantity: ".pick-more-quantity",
            pickMoreQuantityField: ".pick-more-quantity-field",
            productQuantityPicker: ".product-quantity-picker",
            radioOption: "#product-wizard-step-options input[type='radio']",
            radioMultipleOption: "#product-wizard-step-options input[type='checkbox']",
            radioOptionPicker: ".radio-option-picker",
            stepForm: "form[name='ProductWizardStepForm']",
            selectProductButton: "#product-wizard-step .select-product",
            selectProductOptionButton: "#product-wizard-step .select-product-option",
            submitButton: "form[name='ProductWizardStepForm'] button[type='submit']",
            variantPicker: ".wizard-option .variant-picker"
        },
        private = {
            getBaseControllerURL: function() {
                return silvercart.ProductWizard.Base().getBaseControllerURL();
            },
            validateFields: function() {
                $('.alert-submit-button-error-message').remove();
                var valid = true, pickedProducts = 0;
                $('input', property.optionSetSelector).each(function() {
                    if (typeof $(this).attr('required') !== 'undefined') {
                        if ($(this).attr('type') === 'radio') {
                            var selectedField = $('input[name="' + $(this).attr('name') + '"]:checked');
                            if (selectedField.length === 0) {
                                valid = false;
                                $(this).closest(selector.option).addClass('validation-error');
                            }
                        } else if ($(this).val() === '') {
                            valid = false;
                            $(this).tooltip({title: ss.i18n._t('Form.FIELD_MAY_NOT_BE_EMPTY', 'This field may not be empty.')});
                            $(this).tooltip('show');
                        }
                    } else if ($(this).closest('.wizard-option').hasClass('wizard-option-product')
                            && $(this).closest('.wizard-option').hasClass('picked')
                    ) {
                        pickedProducts++;
                    }
                });
                if (!valid) {
                    var error = ss.i18n._t('SilverCart.ProductWizard.ERROR.PickOptions', 'Please choose an option for every offer.');
                    $(selector.submitButton).before('<div class="alert alert-danger alert-submit-button-error-message"><span class="fa fa-exclamation-circle"></span> ' + error + '</div>');
                }
                if (valid) {
                    var enableAllOption = $(selector.radioOptionPicker + '[data-behavior="' + property.behavior.enableAll + '"]');
                    if (enableAllOption.length > 0
                     && enableAllOption.hasClass('checked')
                     && pickedProducts === 0
                    ) {
                        var error = ss.i18n._t('SilverCart.ProductWizard.ERROR.PickOneProductOption', 'Please choose at least one product.');
                        $(selector.submitButton).before('<div class="alert alert-danger alert-submit-button-error-message"><span class="fa fa-exclamation-circle"></span> ' + error + '</div>');
                        valid = false;
                    }
                }
                if (valid) {
                    var requiredProductOptions = $(selector.optionProductRequired);
                    requiredProductOptions.each(function() {
                        if ($('.wizard-option.picked', $(this)).length === 0) {
                            var error = ss.i18n._t('SilverCart.ProductWizard.ERROR.PickOneProductOption', 'Please choose at least one product.');
                            if ($(this).data('title').length > 0) {
                                error = ss.i18n._t('SilverCart.ProductWizard.ERROR.PickOneProductOptionFrom', 'Please choose at least one product from:') + ' "' + $(this).data('title') + '"';
                            }
                            $(selector.submitButton).before('<div class="alert alert-danger alert-submit-button-error-message"><span class="fa fa-exclamation-circle"></span> ' + error + '</div>');
                            valid = false;
                        }
                    });
                }
                return valid;
            },
            resetValidationTooltip: function(input) {
                if (typeof input === 'undefined') {
                    input = $(this);
                }
                if (input.val() !== '') {
                    input.tooltip('hide');
                }
            },
            resetValidationTooltipByInput: function() {
                private.resetValidationTooltip($(this));
            },
            resetValidationTooltipBySpinner: function() {
                private.resetValidationTooltip($(this).closest('input'));
            },
            pickOptionByModal: function() {
                var productID     = $(this).data('product-id'),
                    optionID      = $(this).data('option-id'),
                    option        = $('#wizard-option-' + optionID),
                    quantityField = $('input[name="StepOptions[' + option.data('option-id') + '][' + option.data('product-id') + '][Quantity]"]');
                if (option.hasClass('not-picked')) {
                    private.pickOption(option);
                    private.switchBtnChooseLabel(option);
                }
            },
            selectProductOptionButtonClick: function() {
                var productID     = $(this).data('product-id'),
                    optionID      = $(this).data('option-id'),
                    option        = $('#wizard-option-' + optionID + '-' + productID),
                    quantityField = $('input[name="StepOptions[' + option.data('option-id') + '][' + option.data('product-id') + '][Quantity]"]'),
                    selectField   = $('input[name="StepOptions[' + option.data('option-id') + '][' + option.data('product-id') + '][Select]"]');
                if (parseInt(selectField.val()) === 0) {
                    selectField.val(1);
                    quantityField.val(1);
                    option
                            .addClass('picked')
                            .removeClass('not-picked');
                    $('.btn.select-product-option', option)
                            .addClass('btn-primary')
                            .removeClass('btn-outline-primary');
                    private.switchBtnLabel($('.btn.select-product-option', option));
                    property.cartSummary.postOptionData(optionID, productID, 1, option.data('post-callback'));
                } else {
                    selectField.val(0);
                    quantityField.val(0);
                    option
                            .addClass('not-picked')
                            .removeClass('picked');
                    $('.btn.select-product-option', option)
                            .addClass('btn-outline-primary')
                            .removeClass('btn-primary');
                    private.switchBtnLabel($('.btn.select-product-option', option));
                    property.cartSummary.postOptionData(optionID, productID, 0);
                }
                if (parseInt(option.data('allow-multiple-products')) === 1) {
                    return;
                }
                $('.wizard-option[data-option-id="' + optionID + '"]').each(function() {
                    var subQuantityField = $('input[name="StepOptions[' + $(this).data('option-id') + '][' + $(this).data('product-id') + '][Quantity]"]'),
                        subSelectField   = $('input[name="StepOptions[' + $(this).data('option-id') + '][' + $(this).data('product-id') + '][Select]"]');
                    if ($(this).attr('id') === option.attr('id')
                     || parseInt(subQuantityField.val()) === 0
                    ) {
                        return;
                    }
                    subSelectField.val(0);
                    subQuantityField.val(0);
                    if ($(this).hasClass('picked')) {
                        private.switchBtnLabel($('.btn.select-product-option', $(this)));
                        $(this)
                                .removeClass('picked')
                                .addClass('not-picked');
                    }
                    $('.btn.select-product-option', $(this))
                            .addClass('btn-outline-primary')
                            .removeClass('btn-primary');
                    property.cartSummary.postOptionData($(this).data('option-id'), $(this).data('product-id'), 0);
                });
            },
            pickOptionByPicker: function() {
                var option = $(this).closest(selector.option),
                    quantityField = $('input[name="StepOptions[' + option.data('option-id') + '][' + option.data('product-id') + '][Quantity]"]');
                if (option.hasClass('readonly')) {
                    if (option.hasClass('picked')) {
                        quantityField.val('0');
                    } else {
                        quantityField.val('1');
                    }
                }
                private.pickOption(option);
                private.switchBtnChooseLabel(option);
            },
            pickOption: function(option, skipAjax) {
                if (!option.hasClass('pickable')) {
                    return;
                }
                skipAjax = skipAjax === true ? skipAjax : false;
                option.removeClass('validation-error');
                var productID     = option.data('product-id'),
                    optionID      = option.data('option-id'),
                    selectField   = $('input[name="StepOptions[' + optionID + '][' + productID + '][Select]"]'),
                    quantityField = $('input[name="StepOptions[' + optionID + '][' + productID + '][Quantity]"]');
                if (selectField.val() === '0') {
                    selectField.val('1');
                    option.addClass('picked');
                    option.removeClass('not-picked');
                    quantityField.attr('required', 'required');
                    if (quantityField.val() === '0') {
                        if ($('.dropdown-item.pick-quantity[data-quantity="1"]', option).length > 0) {
                            $('.dropdown-item.pick-quantity[data-quantity="1"]', option).trigger('click');
                        } else {
                            quantityField.val('1');
                            if (skipAjax === false) {
                                property.cartSummary.postOptionData(optionID, productID, quantityField.val());
                            }
                        }
                    } else if (skipAjax === false) {
                        property.cartSummary.postOptionData(optionID, productID, quantityField.val());
                    }
                    var enableAllOption = $(selector.radioOptionPicker + '[data-behavior="' + property.behavior.enableAll + '"]');
                    if (enableAllOption.length > 0) {
                        enableAllOption.trigger('click');
                    }
                } else {
                    selectField.val('0');
                    option.addClass('not-picked');
                    option.removeClass('picked');
                    quantityField.removeAttr('required');
                    if (quantityField.val() !== '0') {
                        $('.dropdown-item.pick-quantity[data-quantity="0"]', option).trigger('click');
                    } else if (skipAjax === false) {
                        property.cartSummary.deleteOptionData(optionID, productID);
                    }
                    if ($(selector.optionProduct + '.picked').length === 0) {
                        var disableAllOption = $(selector.radioOptionPicker + '[data-behavior="' + property.behavior.disableAll + '"]');
                        if (disableAllOption.length > 0) {
                            disableAllOption.trigger('click');
                        }
                    }
                }
            },
            pickQuantityOnChange: function() {
                var quantity  = $(this).val(),
                    option    = $(this).closest(selector.option),
                    productID = option.data('product-id'),
                    optionID  = option.data('option-id');
                if (option.hasClass('not-picked')
                 && parseInt(quantity) > 0
                ) {
                    private.pickOption(option, $(this).hasClass('skip-ajax'));
                } else if (!option.hasClass('not-picked')
                        && parseInt(quantity) <= 0
                ) {
                    private.pickOption(option, $(this).hasClass('skip-ajax'));
                }
                if ($(this).hasClass('skip-ajax')) {
                    return;
                }
                property.cartSummary.postOptionData(optionID, productID, quantity);
            },
            pickQuantity: function() {
                var quantity      = $(this).data('quantity'),
                    option        = $(this).closest(selector.option),
                    picker        = $(this).closest(selector.productQuantityPicker),
                    productID     = picker.length === 0 ? option.data('product-id') : picker.data('product-id'),
                    optionID      = option.data('option-id'),
                    quantityField = $('input[name="StepOptions[' + optionID + '][' + productID + '][Quantity]"]');
                if (picker.length > 0) {
                    var optionValue = picker.data('option-value');
                    quantityField = $('input[name="StepOptions[Quantity][' + optionID + '][' + optionValue + ']"]');
                }
                quantityField.val(quantity);
                $('#product-quantity-dropdown-' + optionID).html($(this).html());
                if (option.hasClass('not-picked')
                 && parseInt(quantity) > 0
                ) {
                    private.pickOption(option, $(this).hasClass('skip-ajax'));
                } else if (!option.hasClass('not-picked')
                        && parseInt(quantity) <= 0
                ) {
                    private.pickOption(option, $(this).hasClass('skip-ajax'));
                }
                if ($(this).hasClass('skip-ajax')) {
                    return;
                }
                property.cartSummary.postOptionData(optionID, productID, quantity);
            },
            pickMoreQuantity: function() {
                var optionID    = $(this).data('option-id'),
                    optionValue = $(this).data('option-value');
                if (typeof optionValue === 'undefined') {
                    $('#pick-quantity-' + optionID).hide();
                    $('#pick-more-quantity-' + optionID).removeClass('d-none').show();
                } else {
                    $('#pick-quantity-' + optionID + '-' + optionValue).hide();
                    $('#pick-more-quantity-' + optionID + '-' + optionValue).removeClass('d-none').show();
                }
            },
            pickMoreQuantityFieldChanged: function() {
                clearTimeout(property.spinnerTimeout);
                var quantity  = $(this).val(),
                    optionID  = $(this).data('option-id'),
                    productID = $(this).data('product-id');
                property.spinnerTimeout = setTimeout(function() {
                    property.cartSummary.postOptionData(optionID, productID, quantity);
                },300);
            },
            pickRadioOptionByPicker: function(e) {
                var target = $(e.target);
                if (target.data('toggle') === 'modal'
                 || target.parent('a').data('toggle') === 'modal'
                ) {
                    return;
                }
                var optionID       = $(this).data('option-id'),
                    optionValue    = $(this).data('value'),
                    visibleOptions = $(selector.radioOptionPicker + '[data-option-id="' + optionID + '"]:visible', selector.stepForm),
                    pickedOption   = $(selector.radioOptionPicker + '[data-option-id="' + optionID + '"][data-value="' + optionValue + '"]', selector.stepForm);
                if (!pickedOption.is(':visible')) {
                    visibleOptions.last().css('cssText', 'display: none !important;');
                    pickedOption.css('cssText', 'display: block !important;');
                }
                if (pickedOption.closest(selector.option).hasClass('allow-multiple-choices')) {
                    if ($(selector.radioOptionPicker + '[data-option-id="' + optionID + '"][data-value="' + optionValue + '"]').hasClass('checked')) {
                        $(selector.radioOptionPicker + '[data-option-id="' + optionID + '"][data-value="' + optionValue + '"]').removeClass('checked');
                    } else {
                        $(selector.radioOptionPicker + '[data-option-id="' + optionID + '"][data-value="' + optionValue + '"]').addClass('checked');
                    }
                } else {
                    $(selector.radioOptionPicker + '[data-option-id="' + optionID + '"]').removeClass('checked');
                    $(selector.radioOptionPicker + '[data-option-id="' + optionID + '"][data-value="' + optionValue + '"]').addClass('checked');
                    pickedOption.closest(selector.option).removeClass('validation-error').removeClass('not-picked').addClass('picked');
                    var quantityPicker = $(selector.productQuantityPicker + '[data-option-value-id="' + optionID + '-' + optionValue + '"]');
                    $(selector.productQuantityPicker, pickedOption.closest(selector.option)).addClass('d-none');
                    if (quantityPicker.length > 0) {
                        quantityPicker.removeClass('d-none');
                    }
                }
            },
            pickRadioOption: function()
            {
                if (property.skipPickRadioOption) {
                    property.skipPickRadioOption = false;
                    return;
                }
                var optionBehavior = $(this).data('behavior');
                if (optionBehavior === property.behavior.enableAll) {
                    property.reenableDataStorage.forEach(function (data) {
                        var optionProduct  = $(selector.optionProduct + '.not-picked[data-option-id="' + data.optionID + '"][data-product-id="' + data.productID + '"]'),
                            selectField    = $('input[name="StepOptions[' + data.optionID + '][' + data.productID + '][Select]"]'),
                            quantityField  = $('input[name="StepOptions[' + data.optionID + '][' + data.productID + '][Quantity]"]'),
                            quantityPicker = $('.dropdown-item.pick-quantity[data-quantity="' + data.quantity + '"]', optionProduct);
                        if (quantityField.length > 0) {
                            quantityField.val(data.quantity);
                        }
                        //property.skipPickRadioOption = true;
                        if (quantityPicker.length > 0) {
                            $(quantityPicker).trigger('click');
                        } else {
                            $(selector.optionPicker + '.card-header', optionProduct).trigger('click');
                        }
                    });
                    property.reenableDataStorage = [];
                } else if (optionBehavior === property.behavior.disableAll) {
                    $(selector.optionProduct + '.picked').each(function() {
                        var picker = $(selector.optionPicker + '.card-header', $(this));
                        if (picker.length === 0) {
                            return;
                        }
                        
                        var productID     = $(this).data('product-id'),
                            optionID      = $(this).data('option-id'),
                            selectField   = $('input[name="StepOptions[' + optionID + '][' + productID + '][Select]"]'),
                            quantityField = $('input[name="StepOptions[' + optionID + '][' + productID + '][Quantity]"]');
                        var data = {};
                        data['productID'] = productID;
                        data['optionID']  = optionID;
                        data['select']    = selectField.val();
                        data['quantity']  = quantityField.val();
                        property.reenableDataStorage.push(data);
                        picker.trigger('click');
                    });
                }
                property.cartSummary.postPlainOptionData($(this).attr('name'), $(this).closest(selector.option).hasClass('allow-multiple-choices'));
            },
            pickVariant: function() {
                var productID     = $(this).data('product-id'),
                    variantID     = $(this).data('variant-id'),
                    optionID      = $(this).data('option-id'),
                    option        = $('#wizard-option-' + optionID);
                option.addClass('loading');
                $.post(
                        private.getBaseControllerURL() + 'pickVariant',
                        {
                            'OptionID':   optionID,
                            'ProductID':  productID,
                            'VariantID':  variantID
                        },
                        function(data) {
                            //option.removeClass('loading');
                            option.replaceWith(data);
                            silvercart.theme.initFancybox();
                        }
                );
            },
            switchBtnChooseLabel: function(option) {
                var btnChoose = $(selector.optionPickerBtnChoose, option);
                private.switchBtnLabel(btnChoose);
                var modal = $('#modal-product-' + option.data('product-id'));
                var btnChooseModal = $('a.select-product', modal);
                private.switchBtnLabel(btnChooseModal);
            },
            switchBtnLabel: function(btn) {
                if (typeof btn.data('alternate-label') !== 'undefined') {
                    var alternateLabel = btn.data('alternate-label');
                    if (typeof btn.data('alternate-icon') !== 'undefined'
                     && btn.data('alternate-icon') !== ''
                    ) {
                        alternateLabel = '<span class="fa fa-' + btn.data('alternate-icon') + '"></span> ' + alternateLabel;
                        btn.data('alternate-icon', '');
                    }
                    btn.data('alternate-label', btn.html());
                    btn.html(alternateLabel);
                }
            },
            equalizeWizardOptionHeadings: function() {
                $('.wizard-option .card-header').each(function() {
                    if ($(this).outerHeight() < 55) {
                        $(this).css('height', '55px');
                    } else if ($(this).outerHeight() > 55) {
                        $(this).css('line-height', '1em');
                    }
                });
            }
        },
        public = {
            init: function()
            {
                if ($(selector.container).length === 0) {
                    return;
                }
                private.equalizeWizardOptionHeadings();
                property.cartSummary = silvercart.ProductWizard.CartSummary();
                property.cartSummary.init();
                $(document).on('click', selector.selectProductButton, private.pickOptionByModal);
                $(document).on('click', selector.selectProductOptionButton, private.selectProductOptionButtonClick);
                $(document).on('keyup', selector.stepForm + ' input', private.resetValidationTooltipByInput);
                $(document).on('click', selector.optionPicker, private.pickOptionByPicker);
                $(document).on('click', selector.pickQuantity, private.pickQuantity);
                $(document).on('click', selector.pickMoreQuantity, private.pickMoreQuantity);
                $(document).on('change keyup', selector.pickMoreQuantityField, private.pickMoreQuantityFieldChanged);
                $(document).on('change', selector.radioOption, private.pickRadioOption);
                $(document).on('change', selector.radioMultipleOption, private.pickRadioOption);
                $(document).on('change', selector.inputUpdateQuantityOnChange, private.pickQuantityOnChange);
                $(document).on('click', selector.radioOptionPicker, private.pickRadioOptionByPicker);
                $(document).on('click', selector.variantPicker, private.pickVariant);
                $(document).on('click', selector.submitButton, private.validateFields);
            },
            validateFields: function() {
                private.validateFields();
            }
        };
    return public;
});
silvercart.ProductWizard.OptionsWithInfo = (function () {
    var property = {},
        selector = {
            container: "#ProductWizardStepOptionsWithInfo",
            choosableOption: "#product-wizard-step-options .choosable-option",
            infoBox: "#product-wizard-step .info-box",
            infoBoxHeading: "#product-wizard-step .info-box .info-box-heading",
            infoBoxContent: "#product-wizard-step .info-box .info-box-content",
            infoOnHover: "#product-wizard-step-options .info-on-hover",
            productWizardOptions: "#product-wizard-step-options",
            showOriginalOptionInformation: "#product-wizard-step-show-original-option-information",
            stepForm: "form[name='ProductWizardStepForm']"
        },
        private = {
            chooseOption: function() {
                $(this).toggleClass('picked');
                if ($(this).hasClass('picked')) {
                    $('input[type="hidden"]', this).val('1');
                } else {
                    $('input[type="hidden"]', this).val('0');
                }
                return false;
            },
            doStepOptionValidation: function() {
                var isValid = true;
                $(selector.productWizardOptions + ' input').each(function() {
                    if ($(this).val() === '1') {
                        isValid = true;
                    }
                });
                if (!isValid) {
                    $(selector.infoBoxHeading).html('<span class="fa fa-exclamation-circle"></span> ' + ss.i18n._t('SilverCart.ProductWizard.ERROR.PickOptionHeading', 'An error occured'));
                    $(selector.infoBoxContent).html(ss.i18n._t('SilverCart.ProductWizard.ERROR.PickOptionContent', 'Please pick at least one option to continue.'));
                    $(selector.infoBoxHeading).addClass('text-danger');
                    $(selector.infoBoxContent).addClass('text-danger');
                }
                return isValid;
            },
            showOptionInformation: function() {
                if ($(selector.showOriginalOptionInformation).length === 0) {
                    var buttonCloseID = selector.showOriginalOptionInformation.replace('#', ''),
                        buttonClose = '<a href="javascript:;" class="text-lg p-absolute t-15 r-20 d-none" id="' + buttonCloseID + '"><span class="fa fa-times-circle"></span></a>';
                    $(selector.infoBox).append(buttonClose);
                }
                $(selector.infoBoxHeading).removeClass('text-danger');
                $(selector.infoBoxContent).removeClass('text-danger');
                $(selector.infoBoxHeading).html($(this).data('info-heading'));
                $(selector.infoBoxContent).html($(this).data('info-content'));
                return false;
            },
            showOriginalOptionInformation: function() {
                if ($(selector.infoBoxHeading).data('original').length > 0) {
                    $(selector.showOriginalOptionInformation).remove();
                    $(selector.infoBoxHeading).html($(selector.infoBoxHeading).data('original'));
                    $(selector.infoBoxContent).html($(selector.infoBoxContent).data('original'));
                }
            },
        },
        public = {
            init: function()
            {
                if ($(selector.container).length === 0) {
                    return;
                }
                $(document).on('click', selector.choosableOption, private.chooseOption);
                $(document).on('mouseover', selector.infoOnHover, private.showOptionInformation);
                $(document).on('mouseout', selector.infoOnHover, private.showOriginalOptionInformation);
                $(document).on('click', selector.showOriginalOptionInformation, private.showOriginalOptionInformation);
                $(document).on('submit', selector.stepForm, private.doStepOptionValidation);
            }
        };
    return public;
});

$(function()
{
    silvercart.ProductWizard.OptionsWithProgress().init();
    silvercart.ProductWizard.OptionsWithInfo().init();
});