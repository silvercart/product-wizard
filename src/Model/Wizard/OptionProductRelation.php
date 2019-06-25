<?php

namespace SilverCart\ProductWizard\Model\Wizard;

use SilverCart\Model\Product\Product;

/**
 * Represents the dynamic relation of a step option (especially radio) and one or
 * more products.
 * 
 * @package SilverCart
 * @subpackage ProductWizard\Model\Wizard
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 26.02.2019
 * @copyright 2019 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class OptionProductRelation
{
    /**
     * Related StepOption.
     *
     * @var StepOption
     */
    protected $relatedOption = null;
    /**
     * Quantity to add to cart.
     *
     * @var int
     */
    protected $quantity = 0;
    /**
     * Maximum quantity to add to cart.
     *
     * @var int
     */
    protected $maximumQuantity = 0;
    /**
     * Minimum quantity to add to cart.
     *
     * @var int
     */
    protected $minimumQuantity = 1;
    /**
     * Quantity by option to add to cart.
     *
     * @var int
     */
    protected $quantityByOption = 0;
    /**
     * Option to get the dynamic quantity to add to cart.
     *
     * @var StepOption
     */
    protected $dynamicQuantityOption = null;
    /**
     * Key value pair of option index and product.
     *
     * @var Product[]
     */
    protected $products = [];
    /**
     * Key value pair of option index and description.
     *
     * @var string[]
     */
    protected $descriptions = [];
    /**
     * Key value pair of option index and long description.
     *
     * @var string[]
     */
    protected $longDescriptions = [];
    /**
     * Key value pair of option index and useCustomQuantity property.
     *
     * @var bool[]
     */
    protected $useCustomQuantities = [];
    /**
     * Key value pair of option index and useCustomQuantity dropdown maximum.
     *
     * @var int[]
     */
    protected $useCustomQuantityDropdownMaxima = [];
    /**
     * Key value pair of option index and useCustomQuantity plural title.
     *
     * @var string[]
     */
    protected $useCustomQuantityPlurals = [];
    /**
     * Key value pair of option index and useCustomQuantity singular title.
     *
     * @var string[]
     */
    protected $useCustomQuantitySingulars = [];
    /**
     * Key value pair of option index and useCustomQuantity info text.
     *
     * @var string[]
     */
    protected $useCustomQuantityTexts = [];
    
    /**
     * Creates a new instance of OptionProductRelation by the given array $data.
     * 
     * @param array      $data          Relation data
     * @param StepOption $relatedOption Related StepOption
     * 
     * @return \SilverCart\ProductWizard\Model\Wizard\OptionProductRelation
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 26.02.2019
     */
    public static function createByArray(array $data, StepOption $relatedOption) : OptionProductRelation
    {
        return new OptionProductRelation($data, $relatedOption);
    }

    /**
     * Creates a new instance of OptionProductRelation by the given $serializedData.
     * 
     * @param string     $serializedData Serialized relation data
     * @param StepOption $relatedOption  Related StepOption
     * 
     * @return \SilverCart\ProductWizard\Model\Wizard\OptionProductRelation
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 26.02.2019
     */
    public static function createByString(string $serializedData, StepOption $relatedOption) : OptionProductRelation
    {
        $data = unserialize($serializedData);
        if (!is_array($data)) {
            $data = [];
        }
        return self::createByArray($data, $relatedOption);
    }

    /**
     * Constructor.
     * 
     * @param array      $data          Relation data
     * @param StepOption $relatedOption Related StepOption
     * 
     * @return \SilverCart\ProductWizard\Model\Wizard\OptionProductRelation
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 26.02.2019
     */
    public function __construct(array $data, StepOption $relatedOption)
    {
        $this->relatedOption = $relatedOption;
        if (is_array($data)) {
            if (array_key_exists('MinimumQuantity', $data)) {
                $this->setMinimumQuantity((int) $data['MinimumQuantity']);
            }
            if (array_key_exists('DynamicQuantityOption', $data)) {
                $stepOption = StepOption::get()->byID((int) $data['DynamicQuantityOption']);
                if ($stepOption instanceof StepOption) {
                    $this->setDynamicQuantityOption($stepOption);
                }
            }
            if (array_key_exists('Products', $data)) {
                $products = [];
                foreach ($data['Products'] as $optionID => $productID) {
                    $products[$optionID] = Product::get()->byID((int) $productID);
                }
                $this->setProducts($products);
            }
            if (array_key_exists('Descriptions', $data)) {
                $this->setDescriptions((array) $data['Descriptions']);
            }
            if (array_key_exists('LongDescriptions', $data)) {
                $this->setLongDescriptions((array) $data['LongDescriptions']);
            }
            if (array_key_exists('UseCustomQuantities', $data)) {
                $this->setUseCustomQuantities((array) $data['UseCustomQuantities']);
            }
            if (array_key_exists('UseCustomQuantityDropdownMaxima', $data)) {
                $this->setUseCustomQuantityDropdownMaxima((array) $data['UseCustomQuantityDropdownMaxima']);
            }
            if (array_key_exists('UseCustomQuantityPlurals', $data)) {
                $this->setUseCustomQuantityPlurals((array) $data['UseCustomQuantityPlurals']);
            }
            if (array_key_exists('UseCustomQuantitySingulars', $data)) {
                $this->setUseCustomQuantitySingulars((array) $data['UseCustomQuantitySingulars']);
            }
            if (array_key_exists('UseCustomQuantityTexts', $data)) {
                $this->setUseCustomQuantityTexts((array) $data['UseCustomQuantityTexts']);
            }
        }
    }
    
    /**
     * Returns a serialized string containing all relevant relation data.
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 26.02.2019
     */
    public function serialize() : string
    {
        $data = [
            'MinimumQuantity'                 => $this->getMinimumQuantity(),
            'DynamicQuantityOption'           => $this->getDynamicQuantityOption()->ID,
            'Products'                        => $this->getProductsMap(),
            'Descriptions'                    => $this->getDescriptions(),
            'LongDescriptions'                => $this->getLongDescriptions(),
            'UseCustomQuantities'             => $this->getUseCustomQuantities(),
            'UseCustomQuantityDropdownMaxima' => $this->getUseCustomQuantityDropdownMaxima(),
            'UseCustomQuantityPlurals'        => $this->getUseCustomQuantityPlurals(),
            'UseCustomQuantitySingulars'      => $this->getUseCustomQuantitySingulars(),
            'UseCustomQuantityTexts'          => $this->getUseCustomQuantityTexts(),
        ];
        return serialize($data);
    }
    
    /**
     * Returns the quantity.
     * 
     * @param int $optionIndex Optional option index
     * 
     * @return int
     */
    public function getQuantity(int $optionIndex = null) : int
    {
        if ($this->quantity === 0) {
            $this->quantity = $this->getMinimumQuantity();
            $option         = $this->getDynamicQuantityOption();
            if (is_null($optionIndex)) {
                $optionIndex = $this->getRelatedOption()->getValue();
            }
            $useCustomQuantity = $this->getUseCustomQuantity($optionIndex);
            if ($option instanceof StepOption
             && $option->exists()
            ) {
                if ($useCustomQuantity) {
                    $maximumQuantity = $this->getMaximumQuantity();
                    $quantity        = 1;
                    if ($quantity <= $maximumQuantity) {
                        $this->quantity = $quantity;
                    } else {
                        $this->quantity = $maximumQuantity;
                    }
                } else {
                    $value = $option->getValue();
                    if (is_array($value)
                     && !empty($value)
                    ) {
                        $firstChoice = array_shift($value);
                        $quantity    = (int) $firstChoice['Quantity'];
                    } else {
                        $quantity = (int) $value;
                    }
                    if ($quantity > $this->quantity) {
                        $this->quantity = $quantity;
                    }
                }
            }
        }
        return $this->quantity;
    }

    /**
     * Returns the minimum quantity.
     * 
     * @return int
     */
    public function getMaximumQuantity() : int
    {
        if ($this->maximumQuantity === 0) {
            $this->maximumQuantity = $this->getMinimumQuantity();
            $option = $this->getDynamicQuantityOption();
            if ($option instanceof StepOption
             && $option->exists()
            ) {
                $value = $option->getValue();
                if (is_array($value)
                 && !empty($value)
                ) {
                    $firstChoice = array_shift($value);
                    $quantity    = (int) $firstChoice['Quantity'];
                } else {
                    $quantity = (int) $value;
                }
                if ($quantity > $this->maximumQuantity) {
                    $this->maximumQuantity = $quantity;
                }
            }
        }
        return $this->maximumQuantity;
    }

    /**
     * Returns the minimum quantity.
     * 
     * @return int
     */
    public function getMinimumQuantity() : int
    {
        return $this->minimumQuantity;
    }

    /**
     * Returns the quantity by option.
     * 
     * @return int
     */
    public function getQuantityByOption() : int
    {
        return $this->quantityByOption;
    }

    /**
     * Returns the related descriptions.
     * 
     * @return string[]
     */
    public function getDescriptions() : array
    {
        return $this->descriptions;
    }

    /**
     * Returns the option to use for dynamic quantity.
     * 
     * @return StepOption
     */
    public function getDynamicQuantityOption() : StepOption
    {
        if (is_null($this->dynamicQuantityOption)) {
            $this->dynamicQuantityOption = StepOption::singleton();
        }
        return $this->dynamicQuantityOption;
    }

    /**
     * Returns the related long descriptions.
     * 
     * @return string[]
     */
    public function getLongDescriptions() : array
    {
        return $this->longDescriptions;
    }

    /**
     * Returns the related StepOption.
     * 
     * @return StepOption
     */
    public function getRelatedOption() : StepOption
    {
        return $this->relatedOption;
    }

    /**
     * Returns the related products.
     * 
     * @return Product[]
     */
    public function getProducts() : array
    {
        return $this->products;
    }

    /**
     * Returns a map of option index and product ID.
     * 
     * @return array
     */
    public function getProductsMap() : array
    {
        $map = [];
        foreach ($this->getProducts() as $optionID => $product) {
            $ID = 0;
            if ($product instanceof Product
             && $product->exists()
            ) {
                $ID = $product->ID;
            }
            $map[$optionID] = $ID;
        }
        return $map;
    }

    /**
     * Returns the related useCustomQuantity properties.
     * 
     * @return bool[]
     */
    public function getUseCustomQuantities() : array
    {
        return (array) $this->useCustomQuantities;
    }

    /**
     * Returns the related useCustomQuantity property for the given $optionIndex.
     * 
     * @return bool
     */
    public function getUseCustomQuantity(int $optionIndex) : bool
    {
        $use = false;
        if (array_key_exists($optionIndex, $this->useCustomQuantities)) {
            $use = (bool) $this->useCustomQuantities[$optionIndex];
        }
        return $use;
    }

    /**
     * Returns the related useCustomQuantity dropdown maxima.
     * 
     * @return int[]
     */
    public function getUseCustomQuantityDropdownMaxima() : array
    {
        return (array) $this->useCustomQuantityDropdownMaxima;
    }

    /**
     * Returns the related useCustomQuantity plural titles.
     * 
     * @return string[]
     */
    public function getUseCustomQuantityPlurals() : array
    {
        return (array) $this->useCustomQuantityPlurals;
    }

    /**
     * Returns the related useCustomQuantity singular titles.
     * 
     * @return string[]
     */
    public function getUseCustomQuantitySingulars() : array
    {
        return (array) $this->useCustomQuantitySingulars;
    }

    /**
     * Returns the related useCustomQuantity ibfo texts.
     * 
     * @return string[]
     */
    public function getUseCustomQuantityText(int $optionIndex) : string
    {
        $text = '';
        if (array_key_exists($optionIndex, $this->useCustomQuantityTexts)) {
            $text = (string) $this->useCustomQuantityTexts[$optionIndex];
        }
        return $text;
    }

    /**
     * Returns the related useCustomQuantity ibfo texts.
     * 
     * @return string[]
     */
    public function getUseCustomQuantityTexts() : array
    {
        return (array) $this->useCustomQuantityTexts;
    }

    /**
     * Sets the quantity.
     * 
     * @param int $quantity Qunatity
     * 
     * @return \SilverCart\ProductWizard\Model\Wizard\OptionProductRelation
     */
    public function setQuantity(int $quantity) : OptionProductRelation
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * Sets the minimum quantity.
     * 
     * @param int $minimumQuantity Minimum quantity.
     * 
     * @return \SilverCart\ProductWizard\Model\Wizard\OptionProductRelation
     */
    public function setMinimumQuantity(int $minimumQuantity) : OptionProductRelation
    {
        $this->minimumQuantity = $minimumQuantity;
        return $this;
    }

    /**
     * Sets the quantity by option.
     * 
     * @param int $quantityByOption Quantity by option
     * 
     * @return \SilverCart\ProductWizard\Model\Wizard\OptionProductRelation
     */
    public function setQuantityByOption(int $quantityByOption) : OptionProductRelation
    {
        $this->quantityByOption = $quantityByOption;
        return $this;
    }

    /**
     * Sets the related descriptions.
     * 
     * @param array $descriptions Descriptions
     * 
     * @return \SilverCart\ProductWizard\Model\Wizard\OptionProductRelation
     */
    public function setDescriptions(array $descriptions) : OptionProductRelation
    {
        $this->descriptions = $descriptions;
        return $this;
    }

    /**
     * Sets the option to use for dynamic quantity.
     * 
     * @param StepOption $dynamicQuantityOption Option to use for dynamic quantity
     * 
     * @return \SilverCart\ProductWizard\Model\Wizard\OptionProductRelation
     */
    public function setDynamicQuantityOption(StepOption $dynamicQuantityOption) : OptionProductRelation
    {
        $this->dynamicQuantityOption = $dynamicQuantityOption;
        return $this;
    }

    /**
     * Sets the related long descriptions.
     * 
     * @param array $longDescriptions Long descriptions
     * 
     * @return \SilverCart\ProductWizard\Model\Wizard\OptionProductRelation
     */
    public function setLongDescriptions(array $longDescriptions) : OptionProductRelation
    {
        $this->longDescriptions = $longDescriptions;
        return $this;
    }

    /**
     * Sets the related StepOption.
     * 
     * @param StepOption $relatedOption Long descriptions
     * 
     * @return \SilverCart\ProductWizard\Model\Wizard\OptionProductRelation
     */
    public function setRelatedOption(StepOption $relatedOption) : OptionProductRelation
    {
        $this->relatedOption = $relatedOption;
        return $this;
    }

    /**
     * Sets the related products.
     * 
     * @param array $products Products
     * 
     * @return \SilverCart\ProductWizard\Model\Wizard\OptionProductRelation
     */
    public function setProducts(array $products) : OptionProductRelation
    {
        $this->products = $products;
        return $this;
    }

    /**
     * Sets the related useCustomQuantity properties.
     * 
     * @return \SilverCart\ProductWizard\Model\Wizard\OptionProductRelation
     */
    public function setUseCustomQuantities(array $useCustomQuantities) : OptionProductRelation
    {
        $this->useCustomQuantities = $useCustomQuantities;
        return $this;
    }

    /**
     * Sets the related useCustomQuantity dropdown maxima.
     * 
     * @return \SilverCart\ProductWizard\Model\Wizard\OptionProductRelation
     */
    public function setUseCustomQuantityDropdownMaxima(array $useCustomQuantityDropdownMaxima) : OptionProductRelation
    {
        $this->useCustomQuantityDropdownMaxima = $useCustomQuantityDropdownMaxima;
        return $this;
    }

    /**
     * Sets the related useCustomQuantity plural titles.
     * 
     * @return \SilverCart\ProductWizard\Model\Wizard\OptionProductRelation
     */
    public function setUseCustomQuantityPlurals(array $useCustomQuantityPlurals) : OptionProductRelation
    {
        $this->useCustomQuantityPlurals = $useCustomQuantityPlurals;
        return $this;
    }

    /**
     * Sets the related useCustomQuantity singular titles.
     * 
     * @return \SilverCart\ProductWizard\Model\Wizard\OptionProductRelation
     */
    public function setUseCustomQuantitySingulars(array $useCustomQuantitySingulars) : OptionProductRelation
    {
        $this->useCustomQuantitySingulars = $useCustomQuantitySingulars;
        return $this;
    }

    /**
     * Sets the related useCustomQuantity info texts.
     * 
     * @return \SilverCart\ProductWizard\Model\Wizard\OptionProductRelation
     */
    public function setUseCustomQuantityTexts(array $useCustomQuantityTexts) : OptionProductRelation
    {
        $this->useCustomQuantityTexts = $useCustomQuantityTexts;
        return $this;
    }
}