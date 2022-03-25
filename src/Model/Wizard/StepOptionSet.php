<?php

namespace SilverCart\ProductWizard\Model\Wizard;

use SilverCart\Model\Product\Product;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldFilterHeader;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\FieldType\DBInt;

/**
 * An option set to combine different options into one step section.
 * 
 * @package SilverCart
 * @subpackage ProductWizard\Model\Wizard
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 15.02.2019
 * @copyright 2019 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class StepOptionSet extends DataObject
{
    use \SilverCart\ORM\ExtensibleDataObject;
    use DisplayConditional;
    
    /**
     * DB table name.
     *
     * @var array
     */
    private static $table_name = 'SilvercartProductWizardStepOptionSet';
    /**
     * DB attributes.
     *
     * @var array
     */
    private static $db = [
        'Title'                     => 'Varchar(256)',
        'Description'               => 'Text',
        'FontAwesomeIcon'           => 'Varchar(25)',
        'DisplayConditionType'      => 'Enum(",Show,Hide","")',
        'DisplayConditionOperation' => 'Enum(",And,Or","")',
        'Sort'                      => DBInt::class,
    ];
    /**
     * Has one relations.
     *
     * @var array
     */
    private static $has_one = [
        'Step' => Step::class,
    ];
    /**
     * Has many relations.
     *
     * @var array
     */
    private static $has_many = [
        'StepOptions'       => StepOption::class,
        'DisplayConditions' => DisplayCondition::class,
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
        return $this->defaultFieldLabels($includerelations, []);
    }
    
    /**
     * Returns the CMS fields.
     * 
     * @return FieldList
     */
    public function getCMSFields() : FieldList
    {
        $this->beforeUpdateCMSFields(function(FieldList $fields) {
            $fields->removeByName('Sort');
            if ($this->exists()) {
                $stepOptionsField = $fields->dataFieldByName('StepOptions');
                /* @var $stepOptionsField \SilverStripe\Forms\GridField\GridField */
                $stepOptionsField->setList($stepOptionsField->getList()->sort('Sort ASC'));
                $stepOptionsConfig = $stepOptionsField->getConfig();
                if (class_exists('\Symbiote\GridFieldExtensions\GridFieldOrderableRows')) {
                    $stepOptionsConfig->addComponent(new \Symbiote\GridFieldExtensions\GridFieldOrderableRows('Sort'));
                } elseif (class_exists('\UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows')) {
                    $stepOptionsConfig->addComponent(new \UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows('Sort'));
                }
                $fields->removeByName('ProductWizardStepPageID');
                $fields->dataFieldByName('StepOptions')->getConfig()->removeComponentsByType(GridFieldAddExistingAutocompleter::class);
                $fields->dataFieldByName('StepOptions')->getConfig()->removeComponentsByType(GridFieldFilterHeader::class);
                $fields->dataFieldByName('DisplayConditions')->getConfig()->removeComponentsByType(GridFieldAddExistingAutocompleter::class);
                $fields->dataFieldByName('DisplayConditions')->getConfig()->removeComponentsByType(GridFieldFilterHeader::class);
            }
            $this->addDisplayConditionalCMSFields($fields);
        });
        return parent::getCMSFields();
    }
    
    /**
     * On before write.
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 24.02.2019
     */
    protected function onBeforeWrite()
    {
        parent::onBeforeWrite();
        $this->onBeforeWriteDisplayCondition();
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
     * Resets the submitted option data related to the given $product.
     * 
     * @param Product $product Product
     * 
     * @return StepOptionSet
     */
    public function resetDataForProduct(Product $product) : StepOptionSet
    {
        foreach ($this->StepOptions() as $option) {
            /* @var $option StepOption */
            $option->resetDataForProduct($product);
        }
        return $this;
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
        return $this->renderWith(self::class);
    }
    
    /**
     * Returns the visible options.
     * 
     * @return ArrayList
     */
    public function getVisibleStepOptions() : ArrayList
    {
        $options        = $this->StepOptions();
        $visibleOptions = ArrayList::create();
        foreach ($options as $option) {
            if ($option->isVisible()) {
                $visibleOptions->add($option);
            }
        }
        return $visibleOptions;
    }
}