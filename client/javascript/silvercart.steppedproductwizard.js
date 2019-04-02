var $          = $          ? $          : jQuery;
var silvercart = silvercart ? silvercart : [];

silvercart.ProductWizard = [];
silvercart.ProductWizard.CartSummary = (function () {
    var property = {},
        selector = {
            container: "#ProductWizardCartSummary",
            amounts: "#ProductWizardCartSummaryAmounts",
            positions: "#ProductWizardCartSummaryPositions",
            stepBase: "#ProductWizardCartSummaryStep-"
        },
        private = {
            getBaseControllerURL: function() {
                var parts = document.location.href.split('/');
                parts.pop();
                parts.pop();
                return parts.join('/') + '/';
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
                        $(col).html('Sie haben noch keine Artikel ausgewählt.');
                        $(row).append(col);
                        $(table).append(row);
                    } else {
                        $.each(stepPositions, function(optionID, positions) {
                            $.each(positions, function(key, position) {
                                var row  = document.createElement('tr'),
                                    col1 = document.createElement('td'),
                                    col2 = document.createElement('td');
                                $(col1).addClass('text-muted align-top line-height-1 pb-5px pr-5px');
                                $(col2).addClass('text-muted align-top line-height-1 pb-5px text-right text-nowrap');
                                $(col1).html(position.productQuantity + 'x ' + position.productTitle);
                                $(col2).html(position.priceTotal.Nice + '<br/>' + position.BillingPeriodNice);
                                $(row).append(col1).append(col2);
                                $(table).append(row);
                            });
                        });
                    }
                    stepSummary.append($(table));
                });
            },
            renderPositions: function(positions) {
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
                private.renderPriceTotalAmounts(data.Amounts);
            },
            init: function()
            {
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
            postOptionData: function(optionID, productID, quantity) {
                if (quantity < 1) {
                    quantity = 1;
                }
                $(selector.container).addClass('loading');
                $.post(
                        private.getBaseControllerURL() + 'postOptionData',
                        {
                            'OptionID':   optionID,
                            'ProductID':  productID,
                            'Quantity': quantity
                        },
                        function(data) {
                            public.initWith(data);
                            $(selector.container).removeClass('loading');
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
silvercart.ProductWizard.Main = (function () {
    var property = {
            optionSetSelector: false,
            cartSummary: false
        },
        selector = {
            option: ".wizard-option",
            optionPicker: ".wizard-option-picker",
            pickQuantity: ".pick-quantity",
            pickMoreQuantity: ".pick-more-quantity",
            productWizardStep: "#product-wizard-step",
            productWizardOptions: "#product-wizard-step-options",
            choosableOption: "#product-wizard-step-options .choosable-option",
            infoOnHover: "#product-wizard-step-options .info-on-hover",
            infoBox: "#product-wizard-step .info-box",
            infoBoxHeading: "#product-wizard-step .info-box .info-box-heading",
            infoBoxContent: "#product-wizard-step .info-box .info-box-content",
            stepForm: "form[name='ProductWizardStepForm']",
            stepPanel: "#product-wizard-step .card",
            selectProductButton: "#product-wizard-step .select-product",
            showOriginalOptionInformation: "#product-wizard-step-show-original-option-information"
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
                var isValid = false;
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
            initRadioButtons: function() {
                $(selector.stepPanel).each(private.hideNotSelectedRadioButtons);
            },
            hideNotSelectedRadioButtons: function() {
                if ($('input[type="radio"]:checked', $(this)).length > 0) {
                    $('input[type="radio"]', $(this)).each(function() {
                        if ($(this).is(':checked') === false) {
                            $(this).closest('.form-check').hide();
                        }
                    });
                }
            },
            showNotSelectedRadioButtons: function() {
                $('input[type="radio"]', $(this)).each(function() {
                    if ($(this).is(':checked') === false) {
                        $(this).closest('.form-check').show();
                    }
                });
            },
            showOptionInformation: function() {
                if ($(selector.showOriginalOptionInformation).length === 0) {
                    var buttonCloseID = selector.showOriginalOptionInformation.replace('#', ''),
                        buttonClose = '<a href="javascript:;" class="text-lg p-absolute t-15 r-20" id="' + buttonCloseID + '"><span class="fa fa-times-circle"></span></a>';
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
            validateFields: function() {
                var valid = true;
                $('input', property.optionSetSelector).each(function() {
                    if (typeof $(this).attr('required') !== 'undefined') {
                        if ($(this).val() === '') {
                            valid = false;
                            $(this).bstooltip({title: ss.i18n._t('Form.FIELD_MAY_NOT_BE_EMPTY', 'This field may not be empty.')});
                            $(this).bstooltip('show');
                        }
                    }
                });
                return valid;
            },
            resetValidationTooltip: function(input) {
                if (typeof input === 'undefined') {
                    input = $(this);
                }
                console.log(input.val());
                if (input.val() !== '') {
                    input.bstooltip('destroy');
                }
            },
            resetValidationTooltipByInput: function() {
                private.resetValidationTooltip($(this));
            },
            resetValidationTooltipBySpinner: function() {
                console.log($(this).closest('input'));
                private.resetValidationTooltip($(this).closest('input'));
            },
            pickOptionByModal: function() {
                var productID     = $(this).data('product-id'),
                    optionID      = $(this).data('option-id'),
                    option        = $('#wizard-option-' + optionID);
                if (option.hasClass('not-picked')) {
                    private.pickOption(option);
                }
            },
            pickOptionByPicker: function() {
                var option = $(this).closest(selector.option);
                private.pickOption(option);
            },
            pickOption: function(option) {
                if (!option.hasClass('pickable')) {
                    return;
                }
                var productID     = option.data('product-id'),
                    optionID      = option.data('option-id'),
                    selectField   = $('input[name="StepOptions[' + optionID + '][' + productID + '][Select]"]'),
                    quantityField = $('input[name="StepOptions[' + optionID + '][' + productID + '][Quantity]"]');
                if (selectField.val() === '0') {
                    selectField.val('1');
                    option.addClass('picked');
                    option.removeClass('not-picked');
                    quantityField.attr('required', 'required');
                    property.cartSummary.postOptionData(optionID, productID, quantityField.val());
                } else {
                    selectField.val('0');
                    option.addClass('not-picked');
                    option.removeClass('picked');
                    quantityField.removeAttr('required');
                    property.cartSummary.deleteOptionData(optionID, productID);
                }
            },
            pickQuantity: function() {
                var quantity      = $(this).data('quantity'),
                    option        = $(this).closest(selector.option),
                    productID     = option.data('product-id'),
                    optionID      = option.data('option-id'),
                    quantityField = $('input[name="StepOptions[' + optionID + '][' + productID + '][Quantity]"]');
                quantityField.val(quantity);
                $('#product-quantity-dropdown-' + optionID).html($(this).html());
                if (option.hasClass('not-picked')
                 && parseInt(quantity) > 0
                ) {
                    private.pickOption(option);
                }
                property.cartSummary.postOptionData(optionID, productID, quantity);
            }
        },
        public = {
            init: function()
            {
                property.cartSummary = silvercart.ProductWizard.CartSummary();
                property.cartSummary.init();
                if ($(selector.productWizardOptions).length > 0) {
                    $(selector.choosableOption).on('click', private.chooseOption);
                    $(selector.infoOnHover).on('mouseover', private.showOptionInformation);
                    $(document).on('click', selector.showOriginalOptionInformation, private.showOriginalOptionInformation);
                    $(selector.stepForm).on('submit', private.doStepOptionValidation);
                }
                private.initRadioButtons();
                $(selector.stepPanel).on('mouseover', private.showNotSelectedRadioButtons);
                $(selector.stepPanel).on('mouseout', private.hideNotSelectedRadioButtons);
                $(selector.selectProductButton).on('click', private.pickOptionByModal);
                $('input', selector.stepForm).on('keyup', private.resetValidationTooltipByInput);
                $(selector.optionPicker).on('click', private.pickOptionByPicker);
                $(selector.pickQuantity).on('click', private.pickQuantity);
            }
        };
    return public;
});

var silvercartProductWizard            = silvercart.ProductWizard.Main();
$(function()
{
    silvercartProductWizard.init();
});