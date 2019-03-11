var $          = $          ? $          : jQuery;
var silvercart = silvercart ? silvercart : [];

silvercart.SteppedProductWizard = (function () {
    var property = {
            maxOptionSetHeight: 650,
            maxOffsetTop: 0,
            minOffsetTop: 600,
            minWindowWidth: 992,
            optionSetSelector: false,
            optionSetBoxSelector: false
        },
        selector = {
            productWizardStep: "#product-wizard-step",
            productWizardOptions: "#product-wizard-step-options",
            buttonBack: "#ProductWizardStepBackButton",
            buttonSubmit: "form[name='ProductWizardStepForm'] button[type='submit']",
            choosableOption: "#product-wizard-step-options .choosable-option",
            containerContent: "#ProductWizardStepContent",
            containerMain: "#ProductWizardStepMain",
            containerSidebar: "#ProductWizardStepSidebar",
            infoOnHover: "#product-wizard-step-options .info-on-hover",
            infoBox: "#product-wizard-step .info-box",
            infoBoxHeading: "#product-wizard-step .info-box .info-box-heading",
            infoBoxContent: "#product-wizard-step .info-box .info-box-content",
            stepForm: "form[name='ProductWizardStepForm']",
            stepOptionSetNavigation: "#ProductWizardStepOptionSetNavigation",
            stepOptionSets: ".ProductWizardStepOptionSet",
            stepOptionSetBox: ".ProductWizardStepOptionSetBox",
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
                    $('input[type="text"]', selectField.closest('.product-box')).attr('required', 'required');
                } else {
                    selectField.val('0');
                    selectField.closest('.product-box').removeClass('picked');
                    $('input[type="text"]', selectField.closest('.product-box')).removeAttr('required');
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
            },
            showOriginalOptionInformation: function() {
                if ($(selector.infoBoxHeading).data('original').length > 0) {
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
            showOptionSet: function() {
                if (!private.validateFields()) {
                    return;
                }
                var optionSet = $($(this).data('target'));
                if (optionSet.length > 0) {
                    $(selector.stepOptionSets).addClass('d-none');
                    optionSet.removeClass('d-none');
                    $('li.active', selector.stepOptionSetNavigation).removeClass('active');
                    $('a[data-target="' + $(this).data('target') + '"]', selector.stepOptionSetNavigation).closest('li').addClass('active');
                    $('a.showOptionSet').addClass('d-none');
                    $('a.backOptionSet').addClass('d-none');
                    $(selector.buttonSubmit).addClass('d-none');
                    $(selector.buttonBack).addClass('d-none');
                    if ($('a[data-source="' + $(this).data('target') + '"].showOptionSet').length > 0) {
                        $('a[data-source="' + $(this).data('target') + '"].showOptionSet').removeClass('d-none');
                    } else {
                        $(selector.buttonSubmit).removeClass('d-none');
                    }
                    if ($('a[data-source="' + $(this).data('target') + '"].backOptionSet').length > 0) {
                        $('a[data-source="' + $(this).data('target') + '"].backOptionSet').removeClass('d-none');
                    } else {
                        $(selector.buttonBack).removeClass('d-none');
                    }
                    property.optionSetSelector = $(this).data('target');
                    private.rearrangePanels();
                }
            },
            initResponsiveOptionSets: function () {
                if ($(window).width() < property.minWindowWidth) {
                    return;
                }
                var optionSetHeight = $(window).height();
                if (optionSetHeight < property.minOffsetTop) {
                    property.maxOffsetTop = property.minOffsetTop;
                } else if (optionSetHeight > property.maxOptionSetHeight) {
                    property.maxOffsetTop = property.maxOptionSetHeight;
                } else {
                    property.maxOffsetTop = optionSetHeight;
                }
                var buttonOffsetTop = $(selector.buttonSubmit).offset().top,
                    optionIndex     = 0;
                if (buttonOffsetTop > property.maxOffsetTop) {
                    $(selector.containerSidebar).removeClass('d-none');
                    $('li:first-child', selector.stepOptionSetNavigation).addClass('active');
                    $(selector.containerContent).addClass($(selector.containerContent).data('add-css-class'));
                    $(selector.buttonSubmit).addClass('d-none');
                    var button = '', backButton = '', previousOptionSet = false;
                    $(selector.stepOptionSets).each(function() {
                        if (optionIndex === 0) {
                            previousOptionSet = $(this);
                            optionIndex++;
                            return;
                        }
                        backButton = '<a href="javascript:;" class="btn btn-default pull-left backOptionSet d-none" data-target="#' + previousOptionSet.attr('id') + '" data-source="#' + $(this).attr('id') + '">' + $(selector.buttonBack).html() + '</a>';
                        if (optionIndex > 1) {
                            button     = '<a href="javascript:;" class="btn btn-primary pull-right showOptionSet d-none" data-target="#' + $(this).attr('id') + '" data-source="#' + previousOptionSet.attr('id') + '">' + $(selector.buttonSubmit).html() + '</a>';
                        } else {
                            button     = '<a href="javascript:;" class="btn btn-primary pull-right showOptionSet" data-target="#' + $(this).attr('id') + '" data-source="#' + previousOptionSet.attr('id') + '">' + $(selector.buttonSubmit).html() + '</a>';
                        }
                        $(selector.buttonSubmit).closest('div').append(button);
                        $(selector.buttonSubmit).closest('div').prepend(backButton);
                        $(this).addClass('d-none');
                        previousOptionSet = $(this);
                        optionIndex++;
                    });
                    $(selector.stepOptionSets).addClass('col-md-12');
                    $(selector.stepOptionSets).removeClass('col-md-4');
                    $(selector.stepOptionSetBox).addClass('col-md-4');
                    
                    property.optionSetSelector = $('li.active a', selector.stepOptionSetNavigation).data('target');
                    private.rearrangePanels();
                }
            },
            rearrangePanels: function(optionSetBox) {
                if (typeof optionSetBox === 'undefined') {
                    optionSetBox = $('.ProductWizardStepOptionSetBox', property.optionSetSelector);
                }
                var optionSetOffsetTop = optionSetBox.offset().top,
                    optionSetHeight    = optionSetBox.height(),
                    optionSetBottom    = optionSetOffsetTop + optionSetHeight;
                if (optionSetBottom > property.maxOffsetTop) {
                    var columnIndex = $('>div', property.optionSetSelector).length,
                        boxID       = $(property.optionSetSelector).attr('id') + 'Box' + columnIndex;
                        property.optionSetBoxSelector = '#' + boxID;
                    if ($('.panel', optionSetBox).length > 1) {
                        $('.panel', optionSetBox).each(function() {
                            if ($(this).hasClass('panel-products')) {
                                private.rearrangeProductPanel($(this), boxID);
                            } else {
                                private.rearrangeDefaultPanel($(this), boxID);
                            }
                        });
                        if ($(property.optionSetBoxSelector).length > 0) {
                            private.rearrangePanels($(property.optionSetBoxSelector));
                        }
                    } else if ($('.panel.panel-products', optionSetBox).length === 1) {
                        private.rearrangeProductPanel($('.panel.panel-products', optionSetBox));
                    }
                }
            },
            movePanel: function(panel) {
                if ($(property.optionSetBoxSelector).length === 0) {
                    var boxID = property.optionSetBoxSelector.replace('#', '');
                    $(property.optionSetSelector).append('<div class="col-md-4 mt-50" id="' + boxID + '"></div>');
                }
                $(property.optionSetBoxSelector).append(panel);
            },
            rearrangeDefaultPanel: function(panel) {
                var panelOffsetTop = panel.offset().top,
                    panelHeight    = panel.height(),
                    panelBottom    = panelOffsetTop + panelHeight;
                if (panelBottom > property.maxOffsetTop) {
                    private.movePanel(panel, property.optionSetBoxSelector);
                }
            },
            rearrangeProductPanel: function(panel) {
                var currentIndex = 0,
                    newPanel     = false;
                $('.panel-body .row >div', panel).each(function() {
                    currentIndex++;
                    var productOffsetTop = $(this).offset().top,
                        productHeight    = $(this).height(),
                        productBottom    = productOffsetTop + productHeight;
                    if (productBottom > property.maxOffsetTop) {
                        if (newPanel === false) {
                            newPanel = panel.clone();
                            $('.panel-body .row', newPanel).html('');
                        }
                        private.movePanel(newPanel);
                        $('.panel-body .row', newPanel).append($(this));
                    }
                });
                if ($('.panel-body .row >div', panel).length === 0) {
                    panel.remove();
                }
                if (newPanel !== false) {
                    var columnIndex = $('>div', property.optionSetSelector).length,
                        boxID       = $(property.optionSetSelector).attr('id') + 'Box' + columnIndex;
                        property.optionSetBoxSelector = '#' + boxID;
                    private.rearrangeProductPanel(newPanel);
                }
            }
        },
        public = {
            init: function()
            {
                if ($(selector.productWizardOptions).length > 0) {
                    $(selector.choosableOption).on('click', private.chooseOption);
                    $(selector.infoOnHover).on('mouseover', private.showOptionInformation);
                    $(selector.infoOnHover).on('mouseout', private.showOriginalOptionInformation);
                    $(selector.stepForm).on('submit', private.doStepOptionValidation);
                }
                private.initRadioButtons();
                private.initResponsiveOptionSets();
                $(selector.stepPanel).on('mouseover', private.showNotSelectedRadioButtons);
                $(selector.stepPanel).on('mouseout', private.hideNotSelectedRadioButtons);
                $(selector.selectProductButton).on('click', private.selectProduct);
                $(window).on('resize', private.initResponsiveOptionSets);
                $('a', selector.stepOptionSetNavigation).on('click', private.showOptionSet);
                $('a.showOptionSet', selector.stepForm).on('click', private.showOptionSet);
                $('a.backOptionSet', selector.stepForm).on('click', private.showOptionSet);
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