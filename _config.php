<?php

use SilverCart\ProductAttributes\Extensions\Pages\GlobalProductAttributesControllerExtension;
use SilverCart\ProductWizard\Model\Pages\ProductWizardStepPageController;
use SilverStripe\View\Requirements;

Requirements::javascript('silverstripe/admin:client/dist/js/i18n.js');
Requirements::add_i18n_javascript('silvercart/product-wizard:client/javascript/lang');

if (class_exists(GlobalProductAttributesControllerExtension::class)) {
    ProductWizardStepPageController::add_extension(GlobalProductAttributesControllerExtension::class);
}