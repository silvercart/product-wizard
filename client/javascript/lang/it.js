if (typeof (ss) == 'undefined' || typeof (ss.i18n) == 'undefined') {
    if (typeof (console) != 'undefined')
        console.error('Class ss.i18n not defined');
} else {
    ss.i18n.addDictionary('it', {
        "SilverCart.ProductWizard.ERROR.PickOneProductOption": "Seleziona almeno un prodotto.",
        "SilverCart.ProductWizard.ERROR.PickOneProductOptionFrom": "Seleziona almeno un prodotto:",
        "SilverCart.ProductWizard.ERROR.PickOptionHeading": "E' occorso un errore di sistema.",
        "SilverCart.ProductWizard.ERROR.PickOptionContent": "Per continuare scegli una di queste opzioni.",
        "SilverCart.ProductWizard.ERROR.PickOptions": "Scegli un'opzione per ogni offerta.",
        "SilverCart.ProductWizard.ERROR.NoProductsYet": "Non hai ancora selezionato nessun articolo."
    });
}