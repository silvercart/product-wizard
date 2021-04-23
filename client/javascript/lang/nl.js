if (typeof (ss) === 'undefined' || typeof (ss.i18n) === 'undefined') {
    if (typeof (console) !== 'undefined') {
        console.error('Class ss.i18n not defined');
    }
} else {
    ss.i18n.addDictionary('nl', {
        "SilverCart.ProductWizard.ERROR.PickOptionHeading": "Er is een fout opgetreden",
        "SilverCart.ProductWizard.ERROR.PickOptionContent": "Kies minstens één optie om verder te gaan.",
        "SilverCart.ProductWizard.ERROR.PickOptions": "Selecteer één optie per aanbod.",
        "SilverCart.ProductWizard.ERROR.NoProductsYet": "U heeft nog geen artikelen geselecteerd."
    });
}