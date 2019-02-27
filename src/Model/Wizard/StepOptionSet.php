<?php

//namespace SilverCart\ProductWizard\Model\Wizard;

use SilvercartProductWizardDisplayCondition as DisplayCondition;
use SilvercartProductWizardStep as Step;
use SilvercartProductWizardStepOption as StepOption;
use DataObject as DataObject;
use HTMLText as DBHTMLText;

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
class SilvercartProductWizardStepOptionSet extends DataObject
{
    use SilverCart\ORM\ExtensibleDataObject;
    use SilverCart\ProductWizard\Model\Wizard\DisplayConditional;
    
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
        'DisplayConditionType'      => 'Enum(",Show,Hide","")',
        'DisplayConditionOperation' => 'Enum(",And,Or","")',
        'Sort'                      => 'Int',
    ];
    /**
     * Has one relations.
     *
     * @var array
     */
    private static $has_one = [
        'Step' => 'SilvercartProductWizardStep',
    ];
    /**
     * Has many relations.
     *
     * @var array
     */
    private static $has_many = [
        'StepOptions'       => 'SilvercartProductWizardStepOption',
        'DisplayConditions' => 'SilvercartProductWizardDisplayCondition',
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
                /* @var $stepOptionsField GridField */
                $stepOptionsField->setList($stepOptionsField->getList()->sort('Sort ASC'));
                $stepOptionsConfig = $stepOptionsField->getConfig();
                if (class_exists('GridFieldOrderableRows')) {
                    $stepOptionsConfig->addComponent(new GridFieldOrderableRows('Sort'));
                } elseif (class_exists('GridFieldSortableRows')) {
                    $stepOptionsConfig->addComponent(new GridFieldSortableRows('Sort'));
                }
                $fields->removeByName('ProductWizardStepPageID');
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
     * Returns the default rendering for this object.
     * 
     * @return DBHTMLText
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 24.02.2019
     */
    public function forTemplate()
    {
        return $this->renderWith("StepOptionSet");
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