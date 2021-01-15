if (typeof (ss) === 'undefined' || typeof (ss.i18n) === 'undefined') {
    if (typeof (console) !== 'undefined') {
        console.error('Class ss.i18n not defined');
    }
} else {
    ss.i18n.addDictionary('nl', {
        "SilverCart.ProductWizard.ERROR.PickOptionHeading": "Er heeft zich een fout voorgedaan",
        "SilverCart.ProductWizard.ERROR.PickOptionContent": "Selecteer ten minste een optie voordat u verdergaat.",
        "SilverCart.ProductWizard.ERROR.PickOptions": "Selecteer een optie voor elke aanbieding."
    });
}