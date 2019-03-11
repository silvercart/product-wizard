<?php

namespace SilverCart\ProductWizard\Model\Wizard;

use SilvercartProduct as Product;
use SilvercartProductWizardStepOption as StepOption;
use ArrayList as ArrayList;

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
     * Quantity to add to cart.
     *
     * @var int
     */
    protected $quantity = 0;
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
     * Creates a new instance of OptionProductRelation by the given array $data.
     * 
     * @param array $data Relation data
     * 
     * @return \SilverCart\ProductWizard\Model\Wizard\OptionProductRelation
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 26.02.2019
     */
    public static function createByArray(array $data) : OptionProductRelation
    {
        return new OptionProductRelation($data);
    }

    /**
     * Creates a new instance of OptionProductRelation by the given $serializedData.
     * 
     * @param string $serializedData Serialized relation data
     * 
     * @return \SilverCart\ProductWizard\Model\Wizard\OptionProductRelation
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 26.02.2019
     */
    public static function createByString(string $serializedData) : OptionProductRelation
    {
        $data = unserialize($serializedData);
        if (!is_array($data)) {
            $data = [];
        }
        return self::createByArray($data);
    }

    /**
     * Constructor.
     * 
     * @param array $data Relation data
     * 
     * @return \SilverCart\ProductWizard\Model\Wizard\OptionProductRelation
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 26.02.2019
     */
    public function __construct(array $data)
    {
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
            'MinimumQuantity'       => $this->getMinimumQuantity(),
            'DynamicQuantityOption' => $this->getDynamicQuantityOption()->ID,
            'Products'              => $this->getProductsMap(),
        ];
        return serialize($data);
    }
    
    /**
     * Returns the quantity.
     * 
     * @return int
     */
    public function getQuantity() : int
    {
        if ($this->quantity === 0) {
            $this->quantity = $this->getMinimumQuantity();
            $option = $this->getDynamicQuantityOption();
            if ($option instanceof StepOption
             && $option->exists()
            ) {
                $this->quantity = (int) $option->getValue();
            }
        }
        return $this->quantity;
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
}