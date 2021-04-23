if (typeof (ss) === 'undefined' || typeof (ss.i18n) === 'undefined') {
    if (typeof (console) !== 'undefined') {
        console.error('Class ss.i18n not defined');
    }
} else {
    ss.i18n.addDictionary('fr', {
        "SilverCart.ProductWizard.ERROR.PickOptionHeading": "Une erreur s'est produite",
        "SilverCart.ProductWizard.ERROR.PickOptionContent": "Sélectionnez au moins une option avant de continuer.",
        "SilverCart.ProductWizard.ERROR.PickOptions": "Sélectionnez une option pour chaque offre.",
        "SilverCart.ProductWizard.ERROR.NoProductsYet": "Vous n'avez encore sélectionné aucun élément."
    });
}