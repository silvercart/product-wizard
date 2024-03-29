if (typeof (ss) == 'undefined' || typeof (ss.i18n) == 'undefined') {
    if (typeof (console) != 'undefined')
        console.error('Class ss.i18n not defined');
} else {
    ss.i18n.addDictionary('en', {
        "SilverCart.ProductWizard.ERROR.PickOneProductOption": "Please choose at least one product.",
        "SilverCart.ProductWizard.ERROR.PickOneProductOptionFrom": "Please choose at least one product from:",
        "SilverCart.ProductWizard.ERROR.PickOptionHeading": "An error has occurred",
        "SilverCart.ProductWizard.ERROR.PickOptionContent": "Please select at least one option to continue.",
        "SilverCart.ProductWizard.ERROR.PickOptions": "Please select one option for each offer.",
        "SilverCart.ProductWizard.ERROR.NoProductsYet": "You have not selected any items yet.",
    });
}