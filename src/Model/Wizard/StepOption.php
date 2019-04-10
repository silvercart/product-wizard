<?php

namespace SilverCart\ProductWizard\Model\Wizard;

use SilverCart\Forms\FormFields\TextField;
use SilverCart\Model\Order\ShoppingCart;
use SilverCart\Model\Product\Product;
use SilverCart\ProductWizard\Model\Wizard\OptionProductRelation;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GroupedDropdownField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\FieldType\DBInt;
use SilverStripe\ORM\FieldType\DBMoney;
use SilverStripe\ORM\FieldType\DBText;
use SilverStripe\ORM\FieldType\DBVarchar;
use SilverStripe\View\ArrayData;

/**
 * A step option a customer can pick on a SilverCart ProductWizardStepPage.
 * 
 * @package SilverCart
 * @subpackage ProductWizard\Model\Wizard
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 15.02.2019
 * @copyright 2019 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class StepOption extends DataObject
{
    use \SilverCart\ORM\ExtensibleDataObject;
    use DisplayConditional;
    
    const OPTION_TYPE_BINARY       = 'BinaryQuestion';
    const OPTION_TYPE_BUTTON       = 'Button';
    const OPTION_TYPE_LABEL        = 'Label';
    const OPTION_TYPE_NUMBER       = 'Number';
    const OPTION_TYPE_PRODUCT_VIEW = 'ProductView';
    const OPTION_TYPE_RADIO        = 'Radio';
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
        'Title'                      => 'Varchar(256)',
        'Content'                    => DBHTMLText::class,
        'Text'                       => DBText::class,
        'OptionType'                 => 'Enum("BinaryQuestion,Number,TextField,TextArea,Radio,Label,Button,ProductView","BinaryQuestion")',
        'DefaultValue'               => 'Varchar(256)',
        'Options'                    => DBText::class,
        'ButtonTitle'                => DBVarchar::class,
        'DisplayConditionType'       => 'Enum(",Show,Hide","")',
        'DisplayConditionOperation'  => 'Enum(",And,Or","")',
        'ProductRelationData'        => DBText::class,
        'ProductViewIsReadonly'      => 'Boolean(0)',
        'ProductQuantityDropdownMax' => 'Int(5)',
        'ProductQuantitySingular'    => 'Varchar(24)',
        'ProductQuantityPlural'      => 'Varchar(24)',
        'ProductPriceLabels'         => 'Text',
        'Sort'                       => DBInt::class,
    ];
    /**
     * Has one relations.
     *
     * @var array
     */
    private static $has_one = [
        'Step'          => Step::class,
        'StepOptionSet' => StepOptionSet::class,
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
            'ProductMinQuantity'                 => _t(self::class . '.ProductMinQuantity', 'Minimum quantity'),
            'ProductMinQuantityDesc'             => _t(self::class . '.ProductMinQuantityDesc', 'Minimum quantity to add to cart.'),
            'ProductDynQuantityStepOptionID'     => _t(self::class . '.ProductDynQuantityStepOptionID', 'Step Option'),
            'ProductDynQuantityStepOptionIDDesc' => _t(self::class . '.ProductDynQuantityStepOptionIDDesc', 'The user input value of this step option will be used as cart quantity.'),
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
            $optionTypes = [];
            foreach ($this->dbObject('OptionType')->enumValues() as $enumValue) {
                $optionTypes[$enumValue] = _t(self::class . ".OptionType{$enumValue}", $enumValue);
            }
            $fields->dataFieldByName('OptionType')
                    ->setSource($optionTypes);
            if ($this->OptionType !== self::OPTION_TYPE_NUMBER
             && $this->OptionType !== self::OPTION_TYPE_RADIO
             && $this->OptionType !== self::OPTION_TYPE_TEXTAREA
             && $this->OptionType !== self::OPTION_TYPE_TEXTFIELD
            ) {
                $fields->removeByName('DefaultValue');
            } else {
                $fields->dataFieldByName('DefaultValue')->setDescription($this->fieldLabel('DefaultValueDesc'));
            }
            if ($this->OptionType !== self::OPTION_TYPE_PRODUCT_VIEW) {
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
            if ($this->OptionType !== self::OPTION_TYPE_BUTTON
             && $this->OptionType !== self::OPTION_TYPE_LABEL
             && $this->OptionType !== self::OPTION_TYPE_NUMBER
             && $this->OptionType !== self::OPTION_TYPE_TEXTAREA
             && $this->OptionType !== self::OPTION_TYPE_TEXTFIELD
            ) {
                $fields->removeByName('Content');
            } else {
                $fields->dataFieldByName('Content')->setRows(3);
            }
            if ($this->OptionType !== self::OPTION_TYPE_BUTTON) {
                $fields->removeByName('ButtonTitle');
            }
            $this->addFieldsForOptionTypeNumber($fields);
            $this->addFieldsForOptionTypeRadio($fields);
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
        } else {
            $fields->dataFieldByName('Options')->setDescription($this->fieldLabel('OptionsDesc'));
            $optionList = $this->getOptionList();
            if ($optionList->exists()) {
                $fields->findOrMakeTab('Root.Advanced', $this->fieldLabel('Advanced'));
                $productsSource         = Product::get()->map()->toArray();
                $products               = $this->getProductRelation()->getProductsMap();
                $minQuantityValue       = $this->getProductRelation()->getMinimumQuantity();
                $dynQuantityOptionValue = $this->getProductRelation()->getDynamicQuantityOption()->ID;
                $descriptions           = $this->getProductRelation()->getDescriptions();
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
                    $fields->addFieldToTab('Root.Advanced', TextField::create("OptionProductRelation[Descriptions][{$option->Value}]", $descTitle, $descValue)->setDescription($descDescription));
                    $fields->addFieldToTab('Root.Advanced', DropdownField::create("OptionProductRelation[Products][{$option->Value}]", $title, $productsSource, $productsValue)->setDescription($description)->setEmptyString(''));
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
            $relation = OptionProductRelation::createByArray($_POST['OptionProductRelation']);
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
     * Returns the default rendering for this object.
     * 
     * @return DBHTMLText
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 24.02.2019
     */
    public function forTemplate()
    {
        return $this->renderWith(self::class . "_{$this->OptionType}");
    }
    
    /**
     * Returns the display condition summary text.
     * 
     * @return DBHTMLText
     */
    public function getDisplayConditionSummary() : DBHTMLText
    {
        $summary = DBHTMLText::create();
        if ($this->DisplayConditions()->exists()) {
            foreach ($this->DisplayConditions() as $condition) {
                $summary->setValue("{$summary->getValue()}• {$condition->getSummary()}<br/>");
            }
        }
        return $summary;
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
     * Returns the dropdown values as ArrayData to render in a template.
     * 
     * @return ArrayData
     */
    public function getProductQuantityDropdownValues(int $productID = 0) : ArrayData
    {
        $currentQuantity = $this->getProductQuantityValue($productID);
        $current = ArrayData::create();
        $values  = ArrayList::create();
        for ($x = 1; $x <= $this->ProductQuantityDropdownMax; $x++) {
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
        return OptionProductRelation::createByString((string) $this->ProductRelationData);
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
            $intValue   = (int) $plainValue;
            $products   = $this->getProductRelation()->getProducts();
            $descriptions = $this->getProductRelation()->getDescriptions();
            foreach ($options as $key => $option) {
                if (array_key_exists($key, $products)) {
                    $product  = $products[$key];
                } else {
                    $product = null;
                }
                if (array_key_exists($key, $descriptions)) {
                    $description = $descriptions[$key];
                } else {
                    $description = null;
                }
                $optionList[] = ArrayData::create([
                    'Step'          => $this->Step(),
                    'StepOptionSet' => $this->StepOptionSet(),
                    'StepOption'    => $this,
                    'Value'         => $key,
                    'Checked'       => $plainValue !== '' && $intValue === $key ? 'checked' : '',
                    'Title'         => trim($option),
                    'Product'       => $product,
                    'Description'   => $description,
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
     * Returns the CSS class for the options's pickable status (IsOptional).
     * 
     * @return string
     */
    public function getIsOptionalClass() : string
    {
        return $this->IsOptional ? 'pickable' : 'not-pickable';
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
     * @param int $productID Product ID
     * 
     * @return int
     */
    public function getProductQuantityValue(int $productID = 0) : int
    {
        if ($productID === 0) {
            $first = $this->Products()->first();
            if ($first instanceof Product) {
                $productID = $first->ID;
            }
        }
        $postedValues = (array) $this->getValue();
        $value        = 1;
        if (array_key_exists($productID, $postedValues)
         && array_key_exists('Quantity', $postedValues[$productID])
        ) {
            $value = (int) $postedValues[$productID]['Quantity'];
        }
        return (int) $value;
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
        return $this->OptionType === self::OPTION_TYPE_PRODUCT_VIEW;
    }
    
    /**
     * Executes the shopping cart transformation for this option.
     * 
     * @return StepOption
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 27.02.2019
     */
    public function executeCartTransformation() : StepOption
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
            if (array_key_exists($this->getValue(), $products)) {
                $product  = $products[$this->getValue()];
                $quantity = $relation->getQuantity();
                $this->addCartData($cartData, $quantity, $product);
            }
        } elseif ($this->OptionType === self::OPTION_TYPE_PRODUCT_VIEW) {
            $products = $this->Products();
            foreach ($products as $product) {
                if ($this->getProductSelectValue($product->ID) === 1) {
                    $this->addCartData($cartData, $this->getProductQuantityValue($product->ID), $product);
                }
            }
        }
        foreach ($cartData as $cartPositionData) {
            ShoppingCart::addProduct($cartPositionData, true);
        }
        return $this;
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
            if (array_key_exists($this->getValue(), $products)) {
                $product  = $products[$this->getValue()];
                $quantity = $relation->getQuantity();
                $this->addCartData($cartData, $quantity, $product);
            }
        } elseif ($this->OptionType === self::OPTION_TYPE_PRODUCT_VIEW) {
            $products = $this->Products();
            foreach ($products as $product) {
                if ($this->getProductSelectValue($product->ID) === 1) {
                    $this->addCartData($cartData, $this->getProductQuantityValue($product->ID), $product);
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
            $data = array_merge($data, [
                'BillingPeriod'     => $product->BillingPeriod,
                'BillingPeriodNice' => $product->getBillingPeriodNice(),
            ]);
        }
        return $data;
    }
}