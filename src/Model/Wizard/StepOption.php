<?php

namespace SilverCart\ProductWizard\Model\Wizard;

use SilverCart\Dev\Tools;
use SilverCart\Forms\FormFields\TextField;
use SilverCart\Model\Order\ShoppingCart;
use SilverCart\Model\Order\ShoppingCartPosition;
use SilverCart\Model\Product\Product;
use SilverCart\ProductAttributes\Model\Product\ProductAttribute;
use SilverCart\ProductWizard\Extensions\Model\Order\ShoppingCartPositionExtension as ProductWizardShoppingCartPosition;
use SilverCart\ProductWizard\Model\Pages\ProductWizardStepPage;
use SilverCart\ProductWizard\Model\Pages\ProductWizardStepPageController;
use SilverCart\ProductWizard\Model\Wizard\OptionProductRelation;
use SilverStripe\Assets\File;
use SilverStripe\CMS\Model\RedirectorPage;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldFilterHeader;
use SilverStripe\Forms\GroupedDropdownField;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\FieldType\DBInt;
use SilverStripe\ORM\FieldType\DBMoney;
use SilverStripe\ORM\FieldType\DBText;
use SilverStripe\ORM\FieldType\DBVarchar;
use SilverStripe\ORM\SS_List;
use SilverStripe\View\ArrayData;
use SilverStripe\View\SSViewer;

/**
 * A step option a customer can pick on a SilverCart ProductWizardStepPage.
 * 
 * @package SilverCart
 * @subpackage ProductWizard\Model\Wizard
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 15.02.2019
 * @copyright 2019 pixeltricks GmbH
 * @license see license file in modules root directory
 * 
 * @property bool   $IsOptional                 Is Optional
 * @property bool   $IsPreselected              Is Preselected
 * @property string $Title                      Title
 * @property string $Content                    Content
 * @property string $Text                       Text
 * @property string $ExtraClasses               Extra CSS Classes
 * @property string $OptionType                 Option Type
 * @property string $DisplayType                Display Type
 * @property string $DefaultValue               Default Value
 * @property string $Options                    Options
 * @property string $ButtonTitle                Button Title
 * @property string $ButtonTargetLink           Button Target Link
 * @property string $ButtonTargetType           Button Target Type
 * @property string $DisplayConditionType       Display Condition Type
 * @property string $DisplayConditionOperation  Display Condition Operation
 * @property string $ProductRelationData        Product Relation Data
 * @property bool   $ProductViewIsReadonly      Product View Is Readonly
 * @property int    $ProductQuantityDropdownMax Product Quantity Dropdown Max
 * @property string $ProductQuantitySingular    Product Quantity Singular
 * @property string $ProductQuantityPlural      Product Quantity Plural
 * @property string $ProductPriceLabels         Product Price Labels
 * @property string $RedirectionType            Redirection Type
 * @property string $RedirectionExternalURL     Redirection External URL
 * @property bool   $DisableLabelForFree        Disable Label For Free
 * @property bool   $AllowMultipleChoices       Allow Multiple Choices
 * @property int    $Sort                       Sort
 * 
 * @method File          ButtonTargetFile()  Returns the related ButtonTargetFile.
 * @method SiteTree      RedirectionLinkTo() Returns the related RedirectionLinkTo.
 * @method Step          Step()              Returns the related Step.
 * @method StepOptionSet StepOptionSet()     Returns the related StepOptionSet.
 * 
 * @method \SilverStripe\ORM\HasManyList DisplayConditions() Returns the related DisplayConditions.
 */
class StepOption extends DataObject
{
    use \SilverCart\ORM\ExtensibleDataObject;
    use DisplayConditional;
    
    const SESSION_KEY                  = 'SilverCart.ProductWizard.StepOption';
    const SESSION_KEY_PRODUCT_VARIANTS = self::SESSION_KEY . '.PickedVariants';
    
    const OPTION_TYPE_BINARY       = 'BinaryQuestion';
    const OPTION_TYPE_BUTTON       = 'Button';
    const OPTION_TYPE_LABEL        = 'Label';
    const OPTION_TYPE_NUMBER       = 'Number';
    const OPTION_TYPE_PRODUCT_VIEW = 'ProductView';
    const OPTION_TYPE_RADIO        = 'Radio';
    const OPTION_TYPE_REDIRECTION  = 'Redirection';
    const OPTION_TYPE_TEXTAREA     = 'TextArea';
    const OPTION_TYPE_TEXTFIELD    = 'TextField';
    
    /**
     * Delimiter to use for the option separation.
     *
     * @var string
     */
    private static $option_delimiter = "\n";
    /**
     * Maximum char length for a short option.
     *
     * @var int
     */
    private static $short_option_max_length = 6;
    /**
     * Determines whether $this->getCMSFields is called or not.
     *
     * @var bool
     */
    protected $getCMSFieldsIsCalled = false;
    /**
     * Option list as ArrayList.
     *
     * @var ArrayList
     */
    protected $optionList = null;
    /**
     * The (by step / step option set) related ProductWizardStepPage.
     *
     * @var ProductWizardStepPage
     */
    protected $productWizardStepPage = null;
    /**
     * DB table name.
     *
     * @var array
     */
    private static $table_name = 'SilvercartProductWizardStepOption';
    /**
     * DB attributes.
     *
     * @var array
     */
    private static $db = [
        'IsOptional'                 => 'Boolean(1)',
        'IsPreselected'              => 'Boolean(0)',
        'Title'                      => 'Varchar(256)',
        'Content'                    => DBHTMLText::class,
        'Text'                       => DBText::class,
        'ExtraClasses'               => 'Varchar',
        'OptionType'                 => 'Enum("BinaryQuestion,Number,TextField,TextArea,Radio,Label,Button,ProductView,Redirection","BinaryQuestion")',
        'DisplayType'                => 'Enum("tile,list","tile")',
        'DefaultValue'               => 'Varchar(256)',
        'Options'                    => DBText::class,
        'ButtonTitle'                => DBVarchar::class,
        'ButtonTargetLink'           => DBVarchar::class,
        'ButtonTargetType'           => 'Enum(",blank","blank")',
        'DisplayConditionType'       => 'Enum(",Show,Hide","")',
        'DisplayConditionOperation'  => 'Enum(",And,Or","")',
        'ProductRelationData'        => DBText::class,
        'ProductViewIsReadonly'      => 'Boolean(0)',
        'ProductQuantityDropdownMax' => 'Int(5)',
        'ProductQuantitySingular'    => 'Varchar(24)',
        'ProductQuantityPlural'      => 'Varchar(24)',
        'ProductPriceLabels'         => 'Text',
        'RedirectionType'            => 'Enum("Internal,External","Internal")',
        'RedirectionExternalURL'     => 'Varchar(2083)', // 2083 is the maximum length of a URL in Internet Explorer.
        'DisableLabelForFree'        => 'Boolean(0)',
        'AllowMultipleChoices'       => 'Boolean(0)',
        'Sort'                       => DBInt::class,
    ];
    /**
     * Has one relations.
     *
     * @var array
     */
    private static $has_one = [
        'ButtonTargetFile'  => File::class,
        'RedirectionLinkTo' => SiteTree::class,
        'Step'              => Step::class,
        'StepOptionSet'     => StepOptionSet::class,
    ];
    /**
     * Has many relations.
     *
     * @var array
     */
    private static $has_many = [
        'DisplayConditions' => DisplayCondition::class,
    ];
    /**
     * Has one relations.
     *
     * @var array
     */
    private static $many_many = [
        'Products' => Product::class,
    ];
    /**
     * Casted attributes.
     *
     * @var array
     */
    private static $casting = [];
    /**
     * Default sort field and direction.
     *
     * @var string
     */
    private static $default_sort = 'Sort ASC';
    /**
     * Products relation.
     * 
     * @var \SilverStripe\ORM\ManyManyList|\SilverStripe\ORM\UnsavedRelationList|NULL
     */
    protected $products = null;
    
    /**
     * Stores the picked variant data in session.
     * 
     * @param int $optionID  Step option ID
     * @param int $productID Related product ID
     * @param int $variantID Product variant ID
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 23.05.2019
     */
    public static function pickVariantBy(int $optionID, int $productID, int $variantID) : void
    {
        Tools::Session()->set(self::SESSION_KEY_PRODUCT_VARIANTS . ".{$optionID}.{$productID}", $variantID);
        Tools::saveSession();
    }

    /**
     * Returns the field labels.
     * 
     * @param bool $includerelations Include relations?
     * 
     * @return array
     */
    public function fieldLabels($includerelations = true) : array
    {
        return $this->defaultFieldLabels($includerelations, [
            'Advanced'                           => _t(self::class . '.Advanced', 'Advanced'),
            'Continue'                           => _t(self::class . '.Continue', 'Continue'),
            'OptionProductRelation'              => _t(self::class . '.OptionProductRelation', 'Related Products'),
            'OptionTypeRedirectionHeader'        => _t(self::class . '.OptionTypeRedirectionHeader', "This option will redirect customers to another page"),
            'ProductMinQuantity'                 => _t(self::class . '.ProductMinQuantity', 'Minimum quantity'),
            'ProductMinQuantityDesc'             => _t(self::class . '.ProductMinQuantityDesc', 'Minimum quantity to add to cart.'),
            'ProductDynQuantityStepOptionID'     => _t(self::class . '.ProductDynQuantityStepOptionID', 'Step Option'),
            'ProductDynQuantityStepOptionIDDesc' => _t(self::class . '.ProductDynQuantityStepOptionIDDesc', 'The user input value of this step option will be used as cart quantity.'),
            'RedirectTo'                         => _t(RedirectorPage::class . '.REDIRECTTO', "Redirect to"),
            'RedirectToPage'                     => _t(RedirectorPage::class . '.REDIRECTTOPAGE', "A page on your website"),
            'RedirectToExternal'                 => _t(RedirectorPage::class . '.REDIRECTTOEXTERNAL', "Another website"),
            'RedirectionLinkToID'                => _t(RedirectorPage::class . '.YOURPAGE', "Page on your website"),
            'RedirectionExternalURL'             => _t(RedirectorPage::class . '.OTHERURL', "Other website URL"),
        ]);
    }
    
    /**
     * Returns the CMS fields.
     * 
     * @return FieldList
     */
    public function getCMSFields() : FieldList
    {
        $this->getCMSFieldsIsCalled = true;
        $this->beforeUpdateCMSFields(function(FieldList $fields) {
            $fields->removeByName('StepID');
            $fields->removeByName('StepOptionSetID');
            $fields->removeByName('ProductRelationData');
            $fields->removeByName('ProductPriceLabels');
            $fields->removeByName('Sort');
            $fields->dataFieldByName('Text')->setDescription($this->fieldLabel('TextDesc'));
            $fields->dataFieldByName('ExtraClasses')->setDescription($this->owner->fieldLabel('ExtraClassesDesc'));
            if ($this->exists()) {
                $displayConditionsGrid = $fields->dataFieldByName('DisplayConditions');
                /* @var $displayConditionsGrid \SilverStripe\Forms\GridField\GridField */
                $displayConditionsGrid->getConfig()->removeComponentsByType(GridFieldAddExistingAutocompleter::class);
                $displayConditionsGrid->getConfig()->removeComponentsByType(GridFieldFilterHeader::class);
            } else {
                $fields->removeByName('IsOptional');
                $fields->removeByName('Text');
            }
            $optionTypes = [];
            foreach ($this->dbObject('OptionType')->enumValues() as $enumValue) {
                $optionTypes[$enumValue] = _t(self::class . ".OptionType{$enumValue}", $enumValue);
            }
            $fields->dataFieldByName('OptionType')
                    ->setSource($optionTypes);
            if ($this->OptionType === self::OPTION_TYPE_BINARY) {
                if (empty($this->Text)) {
                    $fields->removeByName('Text');
                }
            }
            if ($this->OptionType !== self::OPTION_TYPE_BUTTON
             && $this->OptionType !== self::OPTION_TYPE_RADIO
             && $this->OptionType !== self::OPTION_TYPE_LABEL
             && !$this->IsProductView()
            ) {
                $fields->removeByName('DisplayType');
            } else {
                $displayTypes = [];
                foreach ($this->dbObject('DisplayType')->enumValues() as $enumValue) {
                    $displayTypes[$enumValue] = _t(self::class . ".DisplayType-{$enumValue}", $enumValue);
                }
                $fields->dataFieldByName('DisplayType')
                        ->setSource($displayTypes);
            }
            if ($this->OptionType !== self::OPTION_TYPE_NUMBER
             && $this->OptionType !== self::OPTION_TYPE_RADIO
             && $this->OptionType !== self::OPTION_TYPE_TEXTAREA
             && $this->OptionType !== self::OPTION_TYPE_TEXTFIELD
            ) {
                $fields->removeByName('DefaultValue');
            } else {
                $fields->dataFieldByName('DefaultValue')->setDescription($this->fieldLabel('DefaultValueDesc'));
            }
            if (!$this->IsProductView()) {
                $fields->removeByName('IsPreselected');
                $fields->removeByName('Products');
                $fields->removeByName('ProductViewIsReadonly');
                $fields->removeByName('ProductQuantityDropdownMax');
                $fields->removeByName('ProductQuantityPlural');
                $fields->removeByName('ProductQuantitySingular');
            } else {
                $fields->dataFieldByName('ProductQuantityDropdownMax')->setDescription($this->fieldLabel('ProductQuantityDropdownMaxDesc'));
                $fields->dataFieldByName('ProductQuantityPlural')->setDescription($this->fieldLabel('ProductQuantityPluralDesc'));
                $fields->dataFieldByName('ProductQuantitySingular')->setDescription($this->fieldLabel('ProductQuantitySingularDesc'));
                foreach ($this->Products() as $product) {
                    $fields->addFieldToTab('Root.Main', TextField::create(
                            "ProductPriceLabel[{$product->ID}]",
                            _t(self::class . '.ProductPriceLabelFor', 'Price label for {product}', ['product' => $product->Title]),
                            $this->getProductPriceLabel($product->ID)));
                }
            }
            if ($this->OptionType !== self::OPTION_TYPE_BINARY
             && $this->OptionType !== self::OPTION_TYPE_BUTTON
             && $this->OptionType !== self::OPTION_TYPE_LABEL
             && $this->OptionType !== self::OPTION_TYPE_NUMBER
             && $this->OptionType !== self::OPTION_TYPE_TEXTAREA
             && $this->OptionType !== self::OPTION_TYPE_TEXTFIELD
             && !$this->IsProductView()
            ) {
                $fields->removeByName('Content');
            } else {
                $fields->dataFieldByName('Content')->setRows(3);
            }
            if ($this->OptionType === self::OPTION_TYPE_BUTTON) {
                $fields->dataFieldByName('ButtonTargetFile')->setDescription($this->fieldLabel('ButtonTargetFileDesc'));
                $fields->dataFieldByName('ButtonTargetLink')->setDescription($this->fieldLabel('ButtonTargetLinkDesc'));
                $targetTypes = [];
                foreach ($this->dbObject('ButtonTargetType')->enumValues() as $enumValue) {
                    $i18nKey = empty($enumValue) ? 'Empty' : ucfirst($enumValue);
                    $targetTypes[$enumValue] = _t(self::class . ".ButtonTargetType{$i18nKey}", $i18nKey);
                }
                $fields->dataFieldByName('ButtonTargetType')
                        ->setSource($targetTypes);
            } else {
                $fields->removeByName('ButtonTitle');
                $fields->removeByName('ButtonTargetFile');
                $fields->removeByName('ButtonTargetLink');
                $fields->removeByName('ButtonTargetType');
            }
            $this->addFieldsForOptionTypeNumber($fields);
            $this->addFieldsForOptionTypeRadio($fields);
            $this->addFieldsForOptionTypeRedirection($fields);
            $this->addDisplayConditionalCMSFields($fields);
        });
        return parent::getCMSFields();
    }
    
    /**
     * Adds the CMS fields for the option type 'Radio'.
     * 
     * @param FieldList $fields CMS fields
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 26.02.2019
     */
    public function addFieldsForOptionTypeRadio(FieldList $fields) : void
    {
        if ($this->OptionType !== self::OPTION_TYPE_RADIO) {
            $fields->removeByName('Options');
            $fields->removeByName('DisableLabelForFree');
            if ($this->OptionType !== self::OPTION_TYPE_PRODUCT_VIEW) {
                $fields->removeByName('AllowMultipleChoices');
            }
        } else {
            $fields->dataFieldByName('Options')->setDescription($this->fieldLabel('OptionsDesc'));
            $optionList = $this->getOptionList();
            if ($optionList->exists()) {
                $fields->findOrMakeTab('Root.Advanced', $this->fieldLabel('Advanced'));
                $productsSource                  = Product::get()->map()->toArray();
                $products                        = $this->getProductRelation()->getProductsMap();
                $behaviorSource                  = $this->getProductRelation()->getBehaviorsMap();
                $behaviors                       = $this->getProductRelation()->getBehaviors();
                $minQuantityValue                = $this->getProductRelation()->getMinimumQuantity();
                $dynQuantityOptionValue          = $this->getProductRelation()->getDynamicQuantityOption()->ID;
                $descriptions                    = $this->getProductRelation()->getDescriptions();
                $longDescriptions                = $this->getProductRelation()->getLongDescriptions();
                $useCustomQuantities             = $this->getProductRelation()->getUseCustomQuantities();
                $useCustomQuantityDropdownMaxima = $this->getProductRelation()->getUseCustomQuantityDropdownMaxima();
                $useCustomQuantityPlurals        = $this->getProductRelation()->getUseCustomQuantityPlurals();
                $useCustomQuantitySingulars      = $this->getProductRelation()->getUseCustomQuantitySingulars();
                $useCustomQuantityTexts          = $this->getProductRelation()->getUseCustomQuantityTexts();
                $fields->addFieldToTab('Root.Advanced', TextField::create('OptionProductRelation[MinimumQuantity]', $this->fieldLabel('ProductMinQuantity'), $minQuantityValue)->setDescription($this->fieldLabel('ProductMinQuantityDesc')));
                $fields->addFieldToTab('Root.Advanced', GroupedDropdownField::create('OptionProductRelation[DynamicQuantityOption]', $this->fieldLabel('ProductDynQuantityStepOptionID'), $this->getGroupedContextOptions(), $dynQuantityOptionValue)->setEmptyString('')->setDescription($this->fieldLabel('ProductDynQuantityStepOptionIDDesc')));
                foreach ($optionList as $option) {
                    $productsValue = '';
                    $title         = _t(self::class . '.OptionProductRelationTitle', 'Product for option {option}', [
                        'option' => (int) $option->Value + 1,
                    ]);
                    $description = _t(self::class . '.OptionProductRelationDesc', 'Add this product to cart when selecting "{option}".', [
                        'option' => $option->Title,
                    ]);
                    if (array_key_exists($option->Value, $products)) {
                        $productsValue = $products[$option->Value];
                    }
                    $descValue       = '';
                    $descTitle       = _t(self::class . '.OptionDescriptionTitle', 'Description for option {option}', [
                        'option' => (int) $option->Value + 1,
                    ]);
                    $descDescription = _t(self::class . '.OptionDescriptionDesc', 'Will be displayed as a description for "{option}".', [
                        'option' => $option->Title,
                    ]);
                    if (array_key_exists($option->Value, $descriptions)) {
                        $descValue = $descriptions[$option->Value];
                    }
                    $longDescValue       = '';
                    $longDescTitle       = _t(self::class . '.OptionLongDescriptionTitle', 'Long description for option {option}', [
                        'option' => (int) $option->Value + 1,
                    ]);
                    $longDescDescription = _t(self::class . '.OptionLongDescriptionDesc', 'Will be displayed as a long description in a modal for "{option}".', [
                        'option' => $option->Title,
                    ]);
                    if (array_key_exists($option->Value, $longDescriptions)) {
                        $longDescValue = $longDescriptions[$option->Value];
                    }
                    $useCustomQuantityValue = false;
                    $useCustomQuantityTitle = _t(self::class . '.OptionUseCustomQuantityTitle', 'Use custom quantity for option {option}', [
                        'option' => (int) $option->Value + 1,
                    ]);
                    if (array_key_exists($option->Value, $useCustomQuantities)) {
                        $useCustomQuantityValue = $useCustomQuantities[$option->Value];
                    }
                    $useCustomQuantityDropdownMaximumValue = '';
                    $useCustomQuantityDropdownMaximumTitle = _t(self::class . '.OptionUseCustomQuantityDropdownMaximumTitle', 'Dropdown maximum quantity for option {option}', [
                        'option' => (int) $option->Value + 1,
                    ]);
                    if (array_key_exists($option->Value, $useCustomQuantityDropdownMaxima)) {
                        $useCustomQuantityDropdownMaximumValue = $useCustomQuantityDropdownMaxima[$option->Value];
                    }
                    $useCustomQuantityPluralValue = '';
                    $useCustomQuantityPluralTitle = _t(self::class . '.OptionUseCustomQuantityPluralTitle', 'Plural quantity unit for option {option}', [
                        'option' => (int) $option->Value + 1,
                    ]);
                    if (array_key_exists($option->Value, $useCustomQuantityPlurals)) {
                        $useCustomQuantityPluralValue = $useCustomQuantityPlurals[$option->Value];
                    }
                    $useCustomQuantitySingularValue = '';
                    $useCustomQuantitySingularTitle = _t(self::class . '.OptionUseCustomQuantitySingularTitle', 'Singular quantity unit for option {option}', [
                        'option' => (int) $option->Value + 1,
                    ]);
                    if (array_key_exists($option->Value, $useCustomQuantitySingulars)) {
                        $useCustomQuantitySingularValue = $useCustomQuantitySingulars[$option->Value];
                    }
                    $useCustomQuantityTextValue = '';
                    $useCustomQuantityTextTitle = _t(self::class . '.OptionUseCustomQuantityTextTitle', 'Info text quantity picker for option {option}', [
                        'option' => (int) $option->Value + 1,
                    ]);
                    if (array_key_exists($option->Value, $useCustomQuantityTexts)) {
                        $useCustomQuantityTextValue = $useCustomQuantityTexts[$option->Value];
                    }
                    $behaviorValue  = '';
                    $behaviorTitle  = _t(self::class . '.OptionBehaviorTitle', 'Behavior for option {option}', [
                        'option' => (int) $option->Value + 1,
                    ]);
                    if (array_key_exists($option->Value, $behaviors)) {
                        $behaviorValue = $behaviors[$option->Value];
                    }
                    $fields->addFieldToTab('Root.Advanced', TextField::create("OptionProductRelation[Descriptions][{$option->Value}]", $descTitle, $descValue)->setDescription($descDescription));
                    $fields->addFieldToTab('Root.Advanced', HTMLEditorField::create("OptionProductRelation[LongDescriptions][{$option->Value}]", $longDescTitle, $longDescValue)->setDescription($longDescDescription)->setRows(3));
                    $fields->addFieldToTab('Root.Advanced', DropdownField::create("OptionProductRelation[Products][{$option->Value}]", $title, $productsSource, $productsValue)->setDescription($description)->setEmptyString(''));
                    $fields->addFieldToTab('Root.Advanced', CheckboxField::create("OptionProductRelation[UseCustomQuantities][{$option->Value}]", $useCustomQuantityTitle, $useCustomQuantityValue));
                    $fields->addFieldToTab('Root.Advanced', TextField::create("OptionProductRelation[UseCustomQuantityDropdownMaxima][{$option->Value}]", $useCustomQuantityDropdownMaximumTitle, $useCustomQuantityDropdownMaximumValue));
                    $fields->addFieldToTab('Root.Advanced', TextField::create("OptionProductRelation[UseCustomQuantitySingulars][{$option->Value}]", $useCustomQuantitySingularTitle, $useCustomQuantitySingularValue));
                    $fields->addFieldToTab('Root.Advanced', TextField::create("OptionProductRelation[UseCustomQuantityPlurals][{$option->Value}]", $useCustomQuantityPluralTitle, $useCustomQuantityPluralValue));
                    $fields->addFieldToTab('Root.Advanced', TextField::create("OptionProductRelation[UseCustomQuantityTexts][{$option->Value}]", $useCustomQuantityTextTitle, $useCustomQuantityTextValue));
                    $fields->addFieldToTab('Root.Advanced', DropdownField::create("OptionProductRelation[Behaviors][{$option->Value}]", $behaviorTitle, $behaviorSource, $behaviorValue));
                }
            }
        }
    }
    
    /**
     * Adds the CMS fields for the option type 'Number'.
     * 
     * @param FieldList $fields CMS fields
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 26.02.2019
     */
    public function addFieldsForOptionTypeNumber(FieldList $fields) : void
    {
        if ($this->OptionType === self::OPTION_TYPE_NUMBER) {
            $fields->findOrMakeTab('Root.OptionProductRelation', $this->fieldLabel('OptionProductRelation'));
            $productsSource = Product::get()->map()->toArray();
            $products       = $this->getProductRelation()->getProductsMap();
            $productsValue  = '';
            if (array_key_exists(0, $products)) {
                $productsValue = $products[0];
            }
            $title       = _t(self::class . '.NumberProductRelationTitle', 'Product for this option');
            $description = _t(self::class . '.NumberProductRelationDesc', 'Add this product to cart. The user input of this option will be used as quantity.');
            $fields->addFieldToTab('Root.OptionProductRelation', DropdownField::create("OptionProductRelation[Products][0]", $title, $productsSource, $productsValue)->setDescription($description)->setEmptyString(''));
        }
    }
    
    /**
     * Adds the CMS fields for the option type 'Redirection'.
     * 
     * @param FieldList $fields CMS fields
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 16.04.2019
     */
    public function addFieldsForOptionTypeRedirection(FieldList $fields) : void
    {
        if ($this->OptionType === self::OPTION_TYPE_REDIRECTION) {
            $fields->removeByName('IsOptional');
            $fields->removeByName('Text');
            $fields->insertBefore('RedirectionType', HeaderField::create('RedirectorDescHeader', $this->fieldLabel('OptionTypeRedirectionHeader')));
            $fields->addFieldsToTab(
                'Root.Main',
                [
                    HeaderField::create('RedirectorDescHeader', $this->fieldLabel('OptionTypeRedirectionHeader')),
                    OptionsetField::create('RedirectionType', $this->fieldLabel('RedirectTo'), [
                            "Internal" => $this->fieldLabel('RedirectToPage'),
                            "External" => $this->fieldLabel('RedirectToExternal'),
                        ], 'Internal'),
                    TreeDropdownField::create('RedirectionLinkToID', $this->fieldLabel('RedirectionLinkToID'), SiteTree::class),
                    TextField::create('RedirectionExternalURL', $this->fieldLabel('RedirectionExternalURL'))
                ]
            );
        } else {
            $fields->removeByName('RedirectionType');
            $fields->removeByName('RedirectionLinkToID');
            $fields->removeByName('RedirectionExternalURL');
        }
    }
    
    /**
     * On before write.
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 25.02.2019
     */
    protected function onBeforeWrite()
    {
        parent::onBeforeWrite();
        $this->onBeforeWriteDisplayCondition();
        if (array_key_exists('OptionProductRelation', $_POST)) {
            $relation = OptionProductRelation::createByArray($_POST['OptionProductRelation'], $this);
            $this->ProductRelationData = $relation->serialize();
        }
        if (array_key_exists('ProductPriceLabel', $_POST)) {
            $this->ProductPriceLabels = serialize($_POST['ProductPriceLabel']);
        }
    }
    
    /**
     * Returns the summary fields.
     * 
     * @return array
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 18.02.2019
     */
    public function summaryFields() : array
    {
        $summaryFields = [
            'Sort'                    => '#',
            'Title'                   => $this->fieldLabel('Title'),
            'OptionTypeNice'          => $this->fieldLabel('OptionType'),
            'DisplayConditionSummary' => $this->fieldLabel('DisplayConditions'),
        ];
        $this->extend('updateSummaryFields', $summaryFields);
        return $summaryFields;
    }
    
    /**
     * Returns the related products.
     * 
     * @return \SilverStripe\ORM\ManyManyList|\SilverStripe\ORM\UnsavedRelationList
     */
    public function Products() : SS_List
    {
        if ($this->products === null) {
            if (Controller::has_curr()
             && Controller::curr() instanceof ProductWizardStepPageController
            ) {
                if (class_exists(ProductAttribute::class)) {
                    $this->products = ProductAttribute::filterProductsGlobally($this->getManyManyComponents('Products'));
                } else {
                    $this->products = $this->getManyManyComponents('Products');
                }
                $this->extend('updateProducts', $this->products);
            } else {
                $this->products = $this->getManyManyComponents('Products');
            }
        }
        return $this->products;
    }
    
    /**
     * Returns the rendered object.
     * 
     * @param string $templateAddition Optional template name addition
     * 
     * @return DBHTMLText
     */
    public function forTemplate(string $templateAddition = '') : DBHTMLText
    {
        $addition  = empty($templateAddition) ? "_{$this->OptionType}" : "_{$templateAddition}";
        $templates = SSViewer::get_templates_by_class(static::class, $addition, __CLASS__);
        return $this->renderWith($templates);
    }

    /**
     * Return the link that we should redirect to.
     * Only return a value if there is a legal redirection destination.
     * 
     * @return string|null
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 16.04.2019
     */
    public function redirectionLink() : ?string
    {
        if ($this->RedirectionType == 'External') {
            return $this->RedirectionExternalURL ?: null;
        }
        $linkTo = $this->RedirectionLinkToID ? SiteTree::get()->byID($this->RedirectionLinkToID) : null;
        if (empty($linkTo)) {
            return null;
        }
        if ($linkTo instanceof RedirectorPage) {
            return $linkTo->regularLink();
        }
        return $linkTo->Link();
    }
    
    /**
     * Returns the target link for a button option.
     * 
     * @return string
     */
    public function getButtonTarget() : string
    {
        $target = $this->ButtonTargetLink;
        if ($this->ButtonTargetFile()->exists()) {
            $target = $this->ButtonTargetFile()->Link();
        }
        return (string) $target;
    }
    
    /**
     * Returns the link target type for a button option.
     * 
     * @return string
     */
    public function getButtonTargetTypeAttr() : DBHTMLText
    {
        $targetType = DBHTMLText::create();
        if (!empty($this->ButtonTargetType)) {
            $targetType->setValue(" target=\"{$this->ButtonTargetType}\" ");
        }
        return $targetType;
    }
    
    /**
     * Returns the content with the short code parser option if the current context
     * is not $this->getCMSFields().
     * 
     * @return DBHTMLText
     */
    public function getContent() : DBHTMLText
    {
        $content = DBHTMLText::create()->setValue($this->getField('Content'));
        if (!$this->getCMSFieldsIsCalled) {
            $content->setOptions(['shortcodes' => true]);
        }
        return $content;
    }
    
    /**
     * Returns the option type as an i18n readable string.
     * 
     * @return string
     */
    public function getOptionTypeNice()
    {
        $default = empty($this->OptionType) ? '---' : $this->OptionType;
        return _t(self::class . ".OptionType{$this->OptionType}", $default);
    }
    
    /**
     * Returns the product price label for the given $productID.
     * 
     * @param int $productID Product ID
     * 
     * @return string
     */
    public function getProductPriceLabel(int $productID) : string
    {
        $label  = '';
        $labels = unserialize($this->ProductPriceLabels);
        if (is_array($labels)
         && array_key_exists($productID, $labels)) {
            $label = $labels[$productID];
        }
        return $label;
    }
    
    /**
     * Returns whether this (radio) option has a custom quantity for the given
     * option index.
     * 
     * @param int $optionIndex Option Index
     * 
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 18.06.2019
     */
    public function hasCustomQuantity(int $optionIndex) : bool
    {
        $hasCustomQuantity   = false;
        $useCustomQuantities = $this->getProductRelation()->getUseCustomQuantities();
        if (array_key_exists($optionIndex, $useCustomQuantities)) {
            $hasCustomQuantity = (bool) $useCustomQuantities[$optionIndex];
        }
        return $hasCustomQuantity;
    }
    
    /**
     * Returns the maximum dropdown value for a radio option.
     * 
     * @param int $optionIndex Radio option index
     * 
     * @return int
     */
    public function getRadioQuantityDropdownMax(int $optionIndex) : int
    {
        $max    = 5;
        $maxima = $this->getProductRelation()->getUseCustomQuantityDropdownMaxima();
        if (array_key_exists($optionIndex, $maxima)) {
            $max = (int) $maxima[$optionIndex];
        }
        return $max;
    }
    
    /**
     * Returns the dropdown info text for a radio option.
     * 
     * @param int $optionIndex Radio option index
     * 
     * @return string
     */
    public function getRadioOptionQuantityDropdownText(int $optionIndex) : string
    {
        return $this->getProductRelation()->getUseCustomQuantityText($optionIndex);
    }
    
    /**
     * Returns the maximum quantity value for a radio option.
     * 
     * @return int
     */
    public function getRadioMaximumQuantity() : int
    {
        return $this->getProductRelation()->getMaximumQuantity();
    }
    
    /**
     * Returns the singular title for a radio option's quantity picker.
     * 
     * @param int $optionIndex Radio option index
     * 
     * @return string
     */
    public function getRadioQuantitySingular(int $optionIndex) : string
    {
        $singular  = '';
        $singulars = $this->getProductRelation()->getUseCustomQuantitySingulars();
        if (array_key_exists($optionIndex, $singulars)) {
            $singular = (string) $singulars[$optionIndex];
        }
        return $singular;
    }
    
    /**
     * Returns the plural title for a radio option's quantity picker.
     * 
     * @param int $optionIndex Radio option index
     * 
     * @return string
     */
    public function getRadioQuantityPlural(int $optionIndex) : string
    {
        $plural  = '';
        $plurals = $this->getProductRelation()->getUseCustomQuantityPlurals();
        if (array_key_exists($optionIndex, $plurals)) {
            $plural = (string) $plurals[$optionIndex];
        }
        return $plural;
    }
    
    /**
     * Returns the product quantity for a radio option.
     * 
     * @param int $optionIndex Radio option index
     * 
     * @return int
     */
    public function getRadioQuantity(int $optionIndex) : int
    {
        $quantity = 0;
        if ($this->hasCustomQuantity($optionIndex)) {
            $quantity = $this->getRadioOptionQuantity($optionIndex);
        } else {
            $relation = $this->getProductRelation();
            $quantity = $relation->getQuantity();
        }
        return $quantity;
    }
    
    /**
     * Returns the quantity dropdown values for a custom quantity radio option.
     * 
     * @param int $optionIndex Radio option index
     * 
     * @return ArrayData
     */
    public function getRadioOptionQuantityDropdownValues(int $optionIndex) : ArrayData
    {
        $currentQuantity = 1;
        $current         = ArrayData::create();
        $values          = ArrayList::create();
        if ($this->OptionType === self::OPTION_TYPE_RADIO) {
            $currentQuantity = $this->getRadioQuantity($optionIndex);
            for ($x = 1; $x <= $this->getRadioQuantityDropdownMax($optionIndex); $x++) {
                if ($x === 1) {
                    $title = $this->getRadioQuantitySingular($optionIndex);
                } else {
                    $title = $this->getRadioQuantityPlural($optionIndex);
                }
                $item = ArrayData::create([
                    'Title'    => $title,
                    'Quantity' => $x,
                ]);
                if ($x === $currentQuantity) {
                    $current = $item;
                }
                $values->push($item);
            }
        }
        return ArrayData::create([
            'CurrentValue' => $current,
            'Values'       => $values,
        ]);
    }
    
    /**
     * Returns the quantity for this radio option with the given $optionIndex. If
     * no $optionIndex is given, the currently set value will be taken out of
     * session.
     * 
     * @return int
     */
    public function getRadioOptionQuantity(int $optionIndex = null) : int
    {
        $quantity = 1;
        $step     = null;
        if (is_null($optionIndex)) {
            $optionIndex = $this->getValue();
        }
        if ($this->Step()->exists()) {
            $step = $this->Step();
        } elseif ($this->StepOptionSet()->Step()->exists()) {
            $step = $this->StepOptionSet()->Step();
        }
        if ($step instanceof Step) {
            $page     = $step->ProductWizardStepPage();
            $postVars = $page->getPostVarsFor($step);
            if (array_key_exists('StepOptions', $postVars)
             && is_array($postVars['StepOptions'])
             && array_key_exists('Quantity', $postVars['StepOptions'])
             && is_array($postVars['StepOptions']['Quantity'])
             && array_key_exists($this->ID, $postVars['StepOptions']['Quantity'])
             && is_array($postVars['StepOptions']['Quantity'][$this->ID])
             && array_key_exists($optionIndex, $postVars['StepOptions']['Quantity'][$this->ID])
            ) {
                $quantity = $postVars['StepOptions']['Quantity'][$this->ID][$optionIndex];
            }
        }
        if ($quantity > $this->getRadioMaximumQuantity()) {
            $quantity = $this->getRadioMaximumQuantity();
        }
        return $quantity;
    }
    
    /**
     * Returns the dropdown values as ArrayData to render in a template.
     * 
     * @return ArrayData
     */
    public function getProductQuantityDropdownValues(int $productID = 0) : ArrayData
    {
        $currentQuantity = $this->getProductQuantityValue($productID);
        $current         = ArrayData::create();
        $values          = ArrayList::create();
        $firstValue      = 1;
        if ($this->IsOptional) {
            $firstValue = 0;
        }
        for ($x = $firstValue; $x <= $this->ProductQuantityDropdownMax; $x++) {
            if ($x === 1) {
                $title = $this->ProductQuantitySingular;
            } else {
                $title = $this->ProductQuantityPlural;
            }
            $item = ArrayData::create([
                'Title'    => $title,
                'Quantity' => $x,
            ]);
            if ($x === $currentQuantity) {
                $current = $item;
            }
            $values->push($item);
        }
        return ArrayData::create([
            'CurrentValue' => $current,
            'Values'       => $values,
        ]);
    }
    
    /**
     * Returns the option-product relation.
     * 
     * @return OptionProductRelation
     */
    public function getProductRelation() : OptionProductRelation
    {
        return OptionProductRelation::createByString((string) $this->ProductRelationData, $this);
    }
    
    /**
     * Returns the related product.
     * 
     * @return Product|null
     */
    public function getProduct() : ?Product
    {
        $product = null;
        if ($this->OptionType === self::OPTION_TYPE_NUMBER) {
            $products  = $this->getProductRelation()->getProductsMap();
            if (array_key_exists(0, $products)) {
                $productID = (int) $products[0];
                $product   = Product::get()->byID($productID);
            }
        }
        return $product;
    }
    
    /**
     * Returns the value of ProductViewIsReadonly.
     * If the option type is self::OPTION_TYPE_NUMBER, it's always false.
     * 
     * @return bool
     */
    public function getProductViewIsReadonly() : bool
    {
        $isReadonly = $this->getField('ProductViewIsReadonly');
        if ($this->OptionType === self::OPTION_TYPE_NUMBER) {
            $isReadonly = true;
        }
        return (bool) $isReadonly;
    }
    
    /**
     * Returns the related product.
     * 
     * @return \SilverStripe\ORM\ManyManyList|ArrayList
     */
    public function getProductsToDisplay() : SS_List
    {
        $products    = $this->Products();
        $variantData = $this->getProductVariantData();
        if (!empty($variantData)) {
            $productIDs = array_keys($variantData);
            $variantIDs = array_values($variantData);
            $filtered   = $products->filter('ID', array_merge($productIDs, $variantIDs));
            if ($filtered->exists()) {
                $newProducts = ArrayList::create();
                foreach ($products as $product) {
                    $variant = $product;
                    if (in_array($product->ID, $productIDs)) {
                        $variantID = $variantData[$product->ID];
                        $variant   = Product::get()->byID($variantID);
                    } elseif (in_array($product->ID, $variantIDs)) {
                        $variantID = array_search($product->ID, $variantData);
                        $variant   = Product::get()->byID($variantID);
                    }
                    $newProducts->push($variant);
                }
                $products = $newProducts;
            }
        }
        return $products;
    }
    
    /**
     * Returns the related product ID for the given product variant's $productID.
     * 
     * @param int $productID Product ID
     * 
     * @return int
     */
    public function getRelatedProductIDForVariant(int $productID) : int
    {
        $related = $this->Products()->byID($productID);
        if (!($related instanceof Product)
         || !$related->exists()
        ) {
            $variantData = $this->getProductVariantData();
            if (in_array($productID, $variantData)) {
                $productID = (int) array_search($productID, $variantData);
            }
        }
        return $productID;
    }
    
    /**
     * Returns the Text as DBHTMLText.
     * 
     * @return DBHTMLText
     */
    public function getTextHtml() : DBHTMLText
    {
        return DBHTMLText::create()->setOptions(['shortcodes' => true])->setValue($this->Text);
    }
    
    /**
     * Returns the value for this option.
     * 
     * @return string|array
     */
    public function getValue()
    {
        $value = (string) $this->DefaultValue;
        $step  = null;
        if ($this->Step()->exists()) {
            $step = $this->Step();
        } elseif ($this->StepOptionSet()->Step()->exists()) {
            $step = $this->StepOptionSet()->Step();
        }
        if ($step instanceof Step) {
            $page     = $step->ProductWizardStepPage();
            $postVars = $page->getPostVarsFor($step);
            if (array_key_exists('StepOptions', $postVars)
             && is_array($postVars['StepOptions'])
             && array_key_exists($this->ID, $postVars['StepOptions'])
            ) {
                $value = $postVars['StepOptions'][$this->ID];
            }
        }
        if ($this->OptionType === self::OPTION_TYPE_BINARY
         && empty($value)
        ) {
            $value = '0';
        } elseif ($this->IsProductView()
               && empty($value)
               && $this->IsPreselected
        ) {
            $product = $this->Products()->first();
            if ($product instanceof Product) {
                $page     = $step->ProductWizardStepPage();
                $postVars = $page->getPostVarsFor($step);
                if (!is_array($postVars)) {
                    $postVars = [];
                }
                if (!array_key_exists('StepOptions', $postVars)) {
                    $postVars['StepOptions'] = [];
                }
                if (!array_key_exists($this->ID, $postVars['StepOptions'])) {
                    $postVars['StepOptions'][$this->ID] = [];
                }
                $postValue = [
                    'Select'   => "1",
                    'Quantity' => "1",
                ];
                $postVars['StepOptions'][$this->ID][$product->ID] = $postValue;
                $value = [$product->ID => $postValue];
                $page->setPostVarsFor($postVars, $step);
            }
        }
        return $value;
    }
    
    /**
     * Returns the options as an ArrayList.
     * 
     * @return ArrayList
     * 
     * @see self::$option_delimiter
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 27.02.2019
     */
    public function getOptionList() : ArrayList
    {
        if (is_null($this->optionList)) {
            $options    = explode($this->config()->option_delimiter, $this->Options);
            $optionList = [];
            $plainValue = $this->getValue();
            if (is_array($plainValue)) {
                $intValues = [];
                foreach ($plainValue as $value) {
                    $intValues[] = (int) $value;
                }
            } else {
                $intValues = [(int) $plainValue];
            }
            $products   = $this->getProductRelation()->getProducts();
            $behaviors  = $this->getProductRelation()->getBehaviors();
            $descriptions = $this->getProductRelation()->getDescriptions();
            $longDescriptions = $this->getProductRelation()->getLongDescriptions();
            foreach ($options as $key => $option) {
                $product         = null;
                $behavior        = null;
                $description     = null;
                $longDescription = null;
                if (array_key_exists($key, $products)) {
                    $product  = $products[$key];
                }
                if (array_key_exists($key, $behaviors)) {
                    $behavior = $behaviors[$key];
                }
                if (array_key_exists($key, $descriptions)) {
                    $description = $descriptions[$key];
                }
                if (array_key_exists($key, $longDescriptions)) {
                    $longDescription = $longDescriptions[$key];
                }
                $checked   = '';
                $isChecked = false;
                foreach ($intValues as $intValue) {
                    if ($plainValue !== ''
                     && $intValue === $key
                    ) {
                        $checked   = 'checked';
                        $isChecked = true;
                        break;
                    }
                }
                $optionList[] = ArrayData::create([
                    'Step'          => $this->Step(),
                    'StepOptionSet' => $this->StepOptionSet(),
                    'StepOption'    => $this,
                    'Value'         => $key,
                    'Checked'       => $checked,
                    'IsChecked'     => $isChecked,
                    'Title'         => trim($option),
                    'Product'       => $product,
                    'Behavior'      => $behavior,
                    'Description'   => $description,
                    'LongDescription' => DBHTMLText::create()->setValue($longDescription),
                ]);
            }
            $this->optionList = ArrayList::create($optionList);
        }
        return $this->optionList;
    }
    
    /**
     * Returns the radio option with the given int value.
     * 
     * @param int $value Value to get option for
     * 
     * @return ArrayData|null
     */
    public function getOption(int $value) : ?ArrayData
    {
        return $this->getOptionList()->filter('Value', $value)->first();
    }
    
    /**
     * Returns the currently checked radio option.
     * 
     * @return ArrayData|null
     */
    public function getCheckedOption() : ?ArrayData
    {
        return $this->getOptionList()->filter('Checked', 'checked')->first();
    }
    
    /**
     * Returns the currently checked radio options.
     * 
     * @return ArrayList
     */
    public function getCheckedOptions() : ArrayList
    {
        return $this->getOptionList()->filter('Checked', 'checked');
    }
    
    /**
     * Returns a list of context options.
     * 
     * @return array
     */
    protected function getGroupedContextOptions() : array
    {
        $options = [''];
        $steps   = $this->getContextSteps();
        if ($steps->exists()) {
            foreach ($steps as $step) {
                $stepOptions = $step->StepOptions();
                if ($stepOptions->exists()) {
                    $this->addGroupedDropdownOptions($options, $step->Title, $stepOptions);
                } elseif ($step->StepOptionSets()->exists()) {
                    foreach ($step->StepOptionSets() as $optionSet) {
                        $stepOptions = $optionSet->StepOptions();
                        $this->addGroupedDropdownOptions($options, "{$step->Title}: {$optionSet->Title}", $stepOptions);
                    }
                }
            }
        }
        return $options;
    }
    
    /**
     * Adds options to the grouped dropdown option map.
     * 
     * @param array    &$options      Options
     * @param string   $baseGroupName Base group name
     * @param DataList $stepOptions   Step options
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 26.02.2019
     */
    protected function addGroupedDropdownOptions(array &$options, string $baseGroupName, DataList $stepOptions)
    {
        $groupName   = $baseGroupName;
        $index       = 2;
        while (array_key_exists($groupName, $options)) {
            $groupName = "{$baseGroupName} [{$index}]";
            $index++;
        }
        $options[$groupName] = $stepOptions->map()->toArray();
    }
    
    /**
     * Returns the context step.
     * 
     * @return ArrayList
     */
    public function getContextSteps() : ArrayList
    {
        $step  = Step::singleton();
        $steps = ArrayList::create();
        if ($this->Step()->exists()) {
            $step = $this->Step();
        } elseif ($this->StepOptionSet()->exists()) {
            $step = $this->StepOptionSet()->Step();
        }
        while ($step->exists()) {
            $steps->add($step);
            $step = $step->getPreviousStep();
        }
        return $steps->reverse();
    }
    
    /**
     * Returns whether this option has a checked radio option.
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 08.04.2019
     */
    public function IsRadioCheckedClass() : string
    {
        return $this->getValue() !== '' ? 'picked' : 'not-picked';
    }
    
    /**
     * Returns whether this option has a CSS class for multiple choice.
     * 
     * @return string
     */
    public function AllowMultipleChoicesClass() : string
    {
        return $this->AllowMultipleChoices ? 'allow-multiple-choices' : '';
    }
    
    /**
     * Returns the CSS class for the product's (with the given $productID) picked 
     * status.
     * 
     * @param int $productID Product ID
     * 
     * @return string
     */
    public function getProductIsSelectedClass(int $productID) : string
    {
        return $this->getProductSelectValue($productID) === 1 ? 'picked' : 'not-picked';
    }
    
    /**
     * Returns whether the product (with the given $productID) is selected.
     * 
     * @param int $productID Product ID
     * 
     * @return bool
     */
    public function getProductIsSelected(int $productID) : bool
    {
        return $this->getProductSelectValue($productID) === 1;
    }
    
    /**
     * Returns the CSS class for the options's pickable status (IsOptional).
     * 
     * @return string
     */
    public function getIsOptionalClass() : string
    {
        return $this->IsOptional ? 'pickable' : 'not-pickable';
    }
    
    /**
     * Returns the CSS class for the options's readonly status (getProductViewIsReadonly).
     * 
     * @return string
     */
    public function getProductViewIsReadonlyClass() : string
    {
        return $this->getProductViewIsReadonly() ? 'readonly' : '';
    }
    
    /**
     * Returns the whether the product with the given $productID was picked by the
     * customer.
     * 
     * @param int $productID Product ID
     * 
     * @return int
     */
    public function getProductSelectValue(int $productID = 0) : int
    {
        if (!$this->IsOptional
         && $this->isVisible()
         && $this->Products()->count() < 2
        ) {
            return 1;
        }
        if ($productID === 0) {
            $first = $this->Products()->first();
            if ($first instanceof Product) {
                $productID = $first->ID;
            }
        }
        $postedValues = (array) $this->getValue();
        $value        = 0;
        if (array_key_exists($productID, $postedValues)
         && array_key_exists('Select', $postedValues[$productID])
        ) {
            $value = (int) $postedValues[$productID]['Select'];
        }
        return (int) $value;
    }
    
    /**
     * Returns the chosen product quantity for the related product with the given 
     * $productID.
     * 
     * @param int $index Product ID / radio option index
     * 
     * @return int
     */
    public function getProductQuantityValue(int $index = null) : int
    {
        $postedValues = (array) $this->getValue();
        $value        = 1;
        if ($this->IsOptional) {
            $value = 0;
        }
        switch ($this->OptionType) {
            case self::OPTION_TYPE_PRODUCT_VIEW:
                $productID = $index;
                if (is_null($productID)) {
                    $productID = 0;
                    $first = $this->Products()->first();
                    if ($first instanceof Product) {
                        $productID = $first->ID;
                    }
                }
                if (array_key_exists($productID, $postedValues)
                 && array_key_exists('Quantity', $postedValues[$productID])
                ) {
                    $value = (int) $postedValues[$productID]['Quantity'];
                }
                break;
            case self::OPTION_TYPE_RADIO:
                $optionIndex = $index;
                if (is_null($optionIndex)) {
                    $optionIndex = $this->getValue();
                }
                $value = (int) $this->getRadioQuantity($optionIndex);
                break;
            default:
                if ($this->IsProductView()) {
                    $productID = $index;
                    if (is_null($productID)) {
                        $productID = 0;
                        $first = $this->Products()->first();
                        if ($first instanceof Product) {
                            $productID = $first->ID;
                        }
                    }
                    if (array_key_exists($productID, $postedValues)
                     && array_key_exists('Quantity', $postedValues[$productID])
                    ) {
                        $value = (int) $postedValues[$productID]['Quantity'];
                    }
                } else {
                    $value = 0;
                }
                break;
        }
        return (int) $value;
    }
    
    /**
     * Returns the picked product variant data.
     * 
     * @return array
     */
    public function getProductVariantData() : array
    {
        return (array) Tools::Session()->get(self::SESSION_KEY_PRODUCT_VARIANTS . ".{$this->ID}");
    }
    
    /**
     * Returns whether this has only short options.
     * 
     * @return bool
     * 
     * @see self::$short_option_max_length
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 18.02.2019
     */
    public function HasOnlyShortOptions() : bool
    {
        $hasOnlyShortOptions = true;
        foreach ($this->getOptionList() as $option) {
            if (strlen($option->Title) > $this->config()->short_option_max_length) {
                $hasOnlyShortOptions = false;
            }
        }
        return $hasOnlyShortOptions;
    }
    
    /**
     * Returns whether this option is a product view.
     * 
     * @return bool
     */
    public function IsProductView() : bool
    {
        $is = $this->OptionType === self::OPTION_TYPE_PRODUCT_VIEW;
        $this->extend('updateIsProductView', $is);
        return $is;
    }
    
    /**
     * Returns a custom JS POST callback function name.
     * 
     * @return string
     */
    public function JSPostCallback() : string
    {
        $callback = '';
        $this->extend('updateJSPostCallback', $callback);
        return $callback;
    }
    
    /**
     * Stores the picked variant data in session.
     * 
     * @param int $productID Related product ID
     * @param int $variantID Product variant ID
     * 
     * @return \SilverCart\ProductWizard\Model\Wizard\StepOption
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 23.05.2019
     */
    public function pickVariant(int $productID, int $variantID) : StepOption
    {
        self::pickVariantBy($this->ID, $productID, $variantID);
        return $this;
    }
    
    /**
     * Executes the shopping cart transformation for this option.
     * 
     * @return array
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 27.02.2019
     */
    public function executeCartTransformation() : array
    {
        $positionIDs = [];
        if ($this->isVisible()) {
            $cartData = [];
            $quantity = 0;
            $relation = $this->getProductRelation();
            if ($this->OptionType === self::OPTION_TYPE_NUMBER
             && (int) $this->getValue() > 0
            ) {
                $products = $relation->getProducts();
                $product  = array_shift($products);
                $quantity = (int) $this->getValue();
                $this->addCartData($cartData, $quantity, $product);
            } elseif ($this->OptionType === self::OPTION_TYPE_RADIO) {
                $products = $relation->getProducts();
                if (array_key_exists($this->getValue(), $products)) {
                    $product  = $products[$this->getValue()];
                    $quantity = $this->getRadioQuantity($this->getValue());
                    $this->addCartData($cartData, $quantity, $product);
                }
            } elseif ($this->IsProductView()) {
                $products = $this->Products();
                foreach ($products as $product) {
                    if ($this->getProductSelectValue($product->ID) === 1) {
                        $this->addCartData($cartData, $this->getProductQuantityValue($product->ID), $product);
                    }
                    if ($product->hasMethod('hasVariants')
                     && $product->hasVariants()
                    ) {
                        foreach ($product->getVariants() as $variant) {
                            if ($variant->ID === $product->ID) {
                                continue;
                            }
                            if ($this->getProductSelectValue($variant->ID) === 1) {
                                $this->addCartData($cartData, $this->getProductQuantityValue($variant->ID), $variant);
                            }
                        }
                    }
                }
            }
            $wizard      = $this->ProductWizardStepPage();
            if ($wizard instanceof ProductWizardStepPage) {
                foreach ($cartData as $cartPositionData) {
                    $position = ProductWizardShoppingCartPosition::getWizardPosition($cartPositionData, $wizard);
                    if ($position instanceof ShoppingCartPosition) {
                        $position->Quantity = $cartPositionData['productQuantity'];
                        $position->write();
                        $positionIDs[] = $position->ID;
                    } else {
                        $position = ShoppingCart::addProduct($cartPositionData, true);
                        if ($position instanceof ShoppingCartPosition) {
                            $position->ProductWizardID = $wizard->ID;
                            $position->write();
                            $positionIDs[] = $position->ID;
                        }
                    }
                }
            }
        }
        return $positionIDs;
    }
    
    /**
     * Returns the (by step / step option set) related ProductWizardStepPage.
     * 
     * @return ProductWizardStepPage|null
     */
    public function ProductWizardStepPage() : ?ProductWizardStepPage
    {
        if ($this->productWizardStepPage === null) {
            $step = null;
            $page = null;
            if ($this->Step()->exists()) {
                $step = $this->Step();
            } elseif ($this->StepOptionSet()->Step()->exists()) {
                $step = $this->StepOptionSet()->Step();
            }
            if ($step instanceof Step) {
                $page = $step->ProductWizardStepPage();
            }
            $this->productWizardStepPage = $page;
        }
        return $this->productWizardStepPage;
    }
    
    /**
     * Returns the cart summary data for this option.
     * 
     * @return array
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 27.02.2019
     */
    public function getCartSummary() : array
    {
        $cartData = [];
        $quantity = 0;
        $relation = $this->getProductRelation();
        if ($this->OptionType === self::OPTION_TYPE_NUMBER
         && (int) $this->getValue() > 0
        ) {
            $products = $relation->getProducts();
            $product  = array_shift($products);
            $quantity = (int) $this->getValue();
            $this->addCartData($cartData, $quantity, $product);
        } elseif ($this->OptionType === self::OPTION_TYPE_RADIO) {
            $products = $relation->getProducts();
            if ($this->AllowMultipleChoices) {
                $values = (array) $this->getValue();
            } else {
                $values = [$this->getValue()];
            }
            foreach ($values as $value) {
                if ((is_int($value)
                  || is_string($value))
                 && array_key_exists($value, $products)
                ) {
                    $product  = $products[$value];
                    $quantity = $this->getRadioQuantity($value);
                    $this->addCartData($cartData, $quantity, $product);
                }
            }
        } elseif ($this->IsProductView()) {
            $products = $this->Products();
            foreach ($products as $product) {
                if ($this->getProductSelectValue($product->ID) === 1) {
                    $this->addCartData($cartData, $this->getProductQuantityValue($product->ID), $product);
                }
                if ($product->hasMethod('hasVariants')
                 && $product->hasVariants()
                ) {
                    foreach ($product->getVariants() as $variant) {
                        if ($variant->ID === $product->ID) {
                            continue;
                        }
                        if ($this->getProductSelectValue($variant->ID) === 1) {
                            $this->addCartData($cartData, $this->getProductQuantityValue($variant->ID), $variant);
                        }
                    }
                }
            }
        }
        return $cartData;
    }
    
    /**
     * Addes the given $quantity and $product to the $cartData.
     * 
     * @param array   &$cartData Cart data to manipulate
     * @param int     $quantity  Quantity to add to cart data
     * @param Product $product   Product to add to cart data
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 27.02.2019
     */
    protected function addCartData(array &$cartData, int $quantity, Product $product = null) : void
    {
        if ($product instanceof Product
         && $product->exists()
         && $quantity > 0
        ) {
            $cartData[] = $data = self::getCartPositionData($quantity, $product);
            $product->extend('updateAfterProductWizardStepOptionAddCartData', $cartData, $data, $quantity);
        }
    }
    
    /**
     * Returns the cart position data for the given $quantity and $product.
     * 
     * @param int     $quantity Quantity
     * @param Product $product  Product
     * 
     * @return array
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 10.04.2019
     */
    public static function getCartPositionData(int $quantity, Product $product) : array
    {
        $priceTotal = DBMoney::create()->setCurrency($product->getPrice()->getCurrency())->setAmount($product->getPrice()->getAmount() * $quantity);
        $data       = [
            'productID'       => $product->ID,
            'productQuantity' => $quantity,
            'productTitle'    => $product->Title,
            'priceSingle'     => [
                'Amount'   => $product->getPrice()->getAmount(),
                'Currency' => $product->getPrice()->getCurrency(),
                'Nice'     => $product->getPrice()->Nice(),
            ],
            'priceTotal'      => [
                'Amount'   => $priceTotal->getAmount(),
                'Currency' => $priceTotal->getCurrency(),
                'Nice'     => $priceTotal->Nice(),
            ],
        ];
        if ($product->hasMethod('getBillingPeriodNice')) {
            if ($product->HasConsequentialCosts
             && $product->BillingPeriod !== $product->BillingPeriodConsequentialCosts
            ) {
                $priceSingleConsequential = $product->getPriceConsequentialCosts();
                $priceTotalConsequential  = DBMoney::create()->setCurrency($priceSingleConsequential->getCurrency())->setAmount($priceSingleConsequential->getAmount() * $quantity);
                $data = array_merge($data, [
                    'priceSingleConsequential' => [
                        'Amount'   => $priceSingleConsequential->getAmount(),
                        'Currency' => $priceSingleConsequential->getCurrency(),
                        'Nice'     => $priceSingleConsequential->Nice(),
                    ],
                    'priceTotalConsequential'      => [
                        'Amount'   => $priceTotalConsequential->getAmount(),
                        'Currency' => $priceTotalConsequential->getCurrency(),
                        'Nice'     => $priceTotalConsequential->Nice(),
                    ],
                    'BillingPeriod'                  => $product->BillingPeriod,
                    'BillingPeriodNice'              => $product->getBillingPeriodNice(),
                    'BillingPeriodConsequential'     => $product->BillingPeriodConsequentialCosts,
                    'BillingPeriodConsequentialNice' => $product->getBillingPeriodConsequentialCostsNice(),
                ]);
            } else {
                $data = array_merge($data, [
                    'BillingPeriod'     => $product->BillingPeriod,
                    'BillingPeriodNice' => $product->getBillingPeriodNice(),
                ]);
            }
        }
        return $data;
    }
}