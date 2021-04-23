if (typeof (ss) == 'undefined' || typeof (ss.i18n) == 'undefined') {
    if (typeof (console) != 'undefined')
        console.error('Class ss.i18n not defined');
} else {
    ss.i18n.addDictionary('nl_BE', {
        "SilverCart.ProductWizard.ERROR.PickOptionHeading": "Er is een fout opgetreden",
        "SilverCart.ProductWizard.ERROR.PickOptionContent": "Gelieve ten minste een optie te kiezen om verder te gaan.",
        "SilverCart.ProductWizard.ERROR.PickOptions": "Gelieve één optie te kiezen voor elke aanbieding."
    });
}