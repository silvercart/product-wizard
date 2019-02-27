var $          = $          ? $          : jQuery;
var silvercart = silvercart ? silvercart : [];

silvercart.SteppedProductWizard = (function () {
    var property = {},
        selector = {
            productWizardStep: "#product-wizard-step",
            productWizardOptions: "#product-wizard-step-options",
            choosableOption: "#product-wizard-step-options .choosable-option",
            infoOnHover: "#product-wizard-step-options .info-on-hover",
            infoBox: "#product-wizard-step .info-box",
            infoBoxHeading: "#product-wizard-step .info-box .info-box-heading",
            infoBoxContent: "#product-wizard-step .info-box .info-box-content",
            stepForm: "form[name='ProductWizardStepForm']",
            stepPanel: "#product-wizard-step .panel",
            selectProductButton: "#product-wizard-step .select-product"
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
                            $(this).closest('label').hide();
                        }
                    });
                }
            },
            selectProduct: function() {
                var productID   = $(this).data('product-id'),
                    optionID    = $(this).data('option-id'),
                    selectField = $('input[name="StepOptions[' + optionID + '][' + productID + '][Select]"]');
                if (selectField.val() === '0') {
                    selectField.val('1');
                    selectField.closest('.product-box').addClass('picked');
                } else {
                    selectField.val('0');
                    selectField.closest('.product-box').removeClass('picked');
                }
            },
            showNotSelectedRadioButtons: function() {
                $('input[type="radio"]', $(this)).each(function() {
                    if ($(this).is(':checked') === false) {
                        $(this).closest('label').show();
                    }
                });
            },
            showOptionInformation: function() {
                $(selector.infoBoxHeading).removeClass('text-danger');
                $(selector.infoBoxContent).removeClass('text-danger');
                $(selector.infoBoxHeading).html($(this).data('info-heading'));
                $(selector.infoBoxContent).html($(this).data('info-content'));
                return false;
            }
        },
        public = {
            init: function()
            {
                if ($(selector.productWizardOptions).length > 0) {
                    $(selector.choosableOption).on('click', private.chooseOption);
                    $(selector.infoOnHover).on('mouseenter', private.showOptionInformation);
                    $(selector.stepForm).on('submit', private.doStepOptionValidation);
                }
                private.initRadioButtons();
                $(selector.stepPanel).on('mouseover', private.showNotSelectedRadioButtons);
                $(selector.stepPanel).on('mouseout', private.hideNotSelectedRadioButtons);
                $(selector.selectProductButton).on('click', private.selectProduct);
            }
        };
    return public;
});

var silvercartProductWizard = silvercart.SteppedProductWizard();
$(function()
{
    silvercartProductWizard.init();
});