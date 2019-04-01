var $          = $          ? $          : jQuery;
var silvercart = silvercart ? silvercart : [];

silvercart.SteppedProductWizard = (function () {
    var property = {
            optionSetSelector: false
        },
        selector = {
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
            selectProduct: function() {
                var productID     = $(this).data('product-id'),
                    optionID      = $(this).data('option-id'),
                    selectField   = $('input[name="StepOptions[' + optionID + '][' + productID + '][Select]"]'),
                    quantityField = $('input[name="StepOptions[' + optionID + '][' + productID + '][Quantity]"]');
                if (selectField.val() === '0') {
                    selectField.val('1');
                    selectField.closest('.product-box').addClass('picked');
                    quantityField.attr('required', 'required');
                } else {
                    selectField.val('0');
                    selectField.closest('.product-box').removeClass('picked');
                    quantityField.removeAttr('required');
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
            }
        },
        public = {
            init: function()
            {
                if ($(selector.productWizardOptions).length > 0) {
                    $(selector.choosableOption).on('click', private.chooseOption);
                    $(selector.infoOnHover).on('mouseover', private.showOptionInformation);
                    $(document).on('click', selector.showOriginalOptionInformation, private.showOriginalOptionInformation);
                    $(selector.stepForm).on('submit', private.doStepOptionValidation);
                }
                private.initRadioButtons();
                $(selector.stepPanel).on('mouseover', private.showNotSelectedRadioButtons);
                $(selector.stepPanel).on('mouseout', private.hideNotSelectedRadioButtons);
                $(selector.selectProductButton).on('click', private.selectProduct);
                $('input', selector.stepForm).on('keyup', private.resetValidationTooltipByInput);
            }
        };
    return public;
});

var silvercartProductWizard = silvercart.SteppedProductWizard();
$(function()
{
    silvercartProductWizard.init();
});