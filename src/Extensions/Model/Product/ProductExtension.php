<?php

namespace SilverCart\ProductWizard\Extensions\Model\Product;

use SilverCart\Model\Product\Product;
use SilverCart\ProductWizard\Model\Wizard\StepOption;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;

/**
 * Extension for a SilverCart product.
 * 
 * @package SilverCart
 * @subpackage ProductWizard\Extensions\Model\Product
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 24.02.2019
 * @copyright 2019 pixeltricks GmbH
 * @license see license file in modules root directory
 * 
 * @property Product $owner Owner
 */
class ProductExtension extends DataExtension
{
    /**
     * ID of the current option.
     *
     * @var int 
     */
    protected $currentOptionID = null;
    /**
     * Belongs many many relations.
     *
     * @var array
     */
    private static array $belongs_many_many = [
        'ProductWizardStepOption' => StepOption::class . '.Products',
    ];
    
    /**
     * Updates the CMS fiedls.
     * 
     * @param FieldList $fields Fields to update
     * 
     * @return void
     */
    public function updateCMSFields(FieldList $fields) : void
    {
        $fields->removeByName('ProductWizardStepOption');
    }
    
    /**
     * Sets the current option ID.
     * 
     * @param int $optionID Option ID
     * 
     * @return void
     */
    public function setCurrentOptionID(int $optionID = null) : void
    {
        $this->currentOptionID = $optionID;
    }
    
    /**
     * Returns the current option ID.
     * 
     * @return int
     */
    public function getCurrentOptionID() : ?int
    {
        return $this->currentOptionID;
    }
    
    /**
     * Returns the current step option context.
     * 
     * @return StepOption|null
     */
    public function getCurrentOption() : ?StepOption
    {
        $option   = null;
        $optionID = $this->getCurrentOptionID();
        if (is_numeric($optionID)) {
            $option = StepOption::get()->byID($optionID);
        }
        return $option;
    }
}