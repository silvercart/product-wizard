if (typeof (ss) == 'undefined' || typeof (ss.i18n) == 'undefined') {
    if (typeof (console) != 'undefined')
        console.error('Class ss.i18n not defined');
} else {
    ss.i18n.addDictionary('de', {
        "SilverCart.ProductWizard.ERROR.PickOptionHeading": "Es ist ein Fehler aufgetreten",
        "SilverCart.ProductWizard.ERROR.PickOptionContent": "Bitte wählen Sie mindestens eine Option, um fortzufahren."
    });
}