<?php

//namespace SilverCart\ProductWizard\Model\Wizard;

use SilverCart\ProductWizard\Model\Wizard\OptionProductRelation;
use SilvercartProductWizardDisplayCondition as DisplayCondition;
use SilvercartProductWizardStep as Step;
use SilvercartProduct as Product;
use SilvercartShoppingCart as ShoppingCart;
use DataList as DataList;
use DataObject as DataObject;
use HTMLText as DBHTMLText;
use DropdownField as DropdownField;
use GroupedDropdownField as GroupedDropdownField;
use TextField as TextField;

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
class SilvercartProductWizardStepOption extends DataObject
{
    use SilverCart\ORM\ExtensibleDataObject;
    use SilverCart\ProductWizard\Model\Wizard\DisplayConditional;
    
    const OPTION_TYPE_BINARY              = 'BinaryQuestion';
    const OPTION_TYPE_BUTTON              = 'Button';
    const OPTION_TYPE_LABEL               = 'Label';
    const OPTION_TYPE_NUMBER              = 'Number';
    const OPTION_TYPE_PRODUCT_VIEW        = 'ProductView';
    const OPTION_TYPE_RADIO               = 'Radio';
    const OPTION_TYPE_TEXTAREA            = 'TextArea';
    const OPTION_TYPE_TEXTFIELD           = 'TextField';
    
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
        'Title'                     => 'Varchar(256)',
        'Text'                      => 'Text',
        'OptionType'                => 'Enum("BinaryQuestion,Number,TextField,TextArea,Radio,Label,Button,ProductView","BinaryQuestion")',
        'DefaultValue'              => 'Varchar(256)',
        'Options'                   => 'Text',
        'ButtonTitle'               => 'Varchar',
        'DisplayConditionType'      => 'Enum(",Show,Hide","")',
        'DisplayConditionOperation' => 'Enum(",And,Or","")',
        'ProductRelationData'       => 'Text',
        'Sort'                      => 'Int',
    ];
    /**
     * Has one relations.
     *
     * @var array
     */
    private static $has_one = [
        'Step'          => 'SilvercartProductWizardStep',
        'StepOptionSet' => 'SilvercartProductWizardStepOptionSet',
    ];
    /**
     * Has many relations.
     *
     * @var array
     */
    private static $has_many = [
        'DisplayConditions' => 'SilvercartProductWizardDisplayCondition',
    ];
    /**
     * Has one relations.
     *
     * @var array
     */
    private static $many_many = [
        'Products' => 'SilvercartProduct',
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
        $this->beforeUpdateCMSFields(function(FieldList $fields) {
            $fields->removeByName('StepID');
            $fields->removeByName('StepOptionSetID');
            $fields->removeByName('ProductRelationData');
            $fields->removeByName('Sort');
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
                $fields->findOrMakeTab('Root.OptionProductRelation', $this->fieldLabel('OptionProductRelation'));
                $productsSource         = Product::get()->map()->toArray();
                $products               = $this->getProductRelation()->getProductsMap();
                $minQuantityValue       = $this->getProductRelation()->getMinimumQuantity();
                $dynQuantityOptionValue = $this->getProductRelation()->getDynamicQuantityOption()->ID;
                $fields->addFieldToTab('Root.OptionProductRelation', TextField::create('OptionProductRelation[MinimumQuantity]', $this->fieldLabel('ProductMinQuantity'), $minQuantityValue)->setDescription($this->fieldLabel('ProductMinQuantityDesc')));
                $fields->addFieldToTab('Root.OptionProductRelation', GroupedDropdownField::create('OptionProductRelation[DynamicQuantityOption]', $this->fieldLabel('ProductDynQuantityStepOptionID'), $this->getGroupedContextOptions(), $dynQuantityOptionValue)->setEmptyString('')->setDescription($this->fieldLabel('ProductDynQuantityStepOptionIDDesc')));
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
                    $fields->addFieldToTab('Root.OptionProductRelation', DropdownField::create("OptionProductRelation[Products][{$option->Value}]", $title, $productsSource, $productsValue)->setDescription($description)->setEmptyString(''));
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
            'Sort'     => '#',
            'Title'    => $this->fieldLabel('Title'),
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
        return $this->renderWith("StepOption_{$this->OptionType}");
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
     * @since 18.02.2019
     */
    public function getOptionList() : ArrayList
    {
        if (is_null($this->optionList)) {
            $options    = explode($this->config()->option_delimiter, $this->Options);
            $optionList = [];
            foreach ($options as $key => $option) {
                $optionList[] = ArrayData::create([
                    'Step'          => $this->Step(),
                    'StepOptionSet' => $this->StepOptionSet(),
                    'StepOption'    => $this,
                    'Value'         => $key,
                    'Checked'       => (int) $this->getValue() === $key ? 'checked' : '',
                    'Title'         => $option,
                ]);
            }
            $this->optionList = ArrayList::create($optionList);
        }
        return $this->optionList;
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
     * Returns the CSS class for the product's (with the given $productID) picked 
     * status.
     * 
     * @param int $productID Product ID
     * 
     * @return string
     */
    public function getProductIsSelectedClass(int $productID) : string
    {
        return $this->getProductSelectValue($productID) === 1 ? 'picked' : '';
    }
    
    /**
     * Returns the whether the product with the given $productID was picked by the
     * customer.
     * 
     * @param int $productID Product ID
     * 
     * @return int
     */
    public function getProductSelectValue(int $productID) : int
    {
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
    public function getProductQuantityValue(int $productID) : int
    {
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
     * Executes the shopping cart transformation for this option.
     * 
     * @return \SilvercartProductWizardStepOption
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 27.02.2019
     */
    public function executeCartTransformation() : SilvercartProductWizardStepOption
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
            $cartData[] = [
                'productID'       => $product->ID,
                'productQuantity' => $quantity,
            ];
        }
    }
}