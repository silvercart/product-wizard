<?php

//namespace SilverCart\ProductWizard\Model\Wizard;

use SilvercartProductWizardStep as Step;
use SilvercartProductWizardStepOptionSet as StepOptionSet;
use DataObject as DataObject;
use DropdownField as DropdownField;

/**
 * A display condition for a StepOption or a StepOptionSet.
 * 
 * @package SilverCart
 * @subpackage ProductWizard\Model\Wizard
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 15.02.2019
 * @copyright 2019 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class SilvercartProductWizardDisplayCondition extends DataObject
{
    use SilverCart\ORM\ExtensibleDataObject;
    
    const TYPE_IS_EQUAL                 = 'IsEqual';
    const TYPE_IS_NOT_EQUAL             = 'IsNotEqual';
    const TYPE_IS_GREATER_THAN          = 'IsGreaterThan';
    const TYPE_IS_LIGHTER_THAN          = 'IsLighterThan';
    const TYPE_IS_GREATER_THAN_OR_EQUAL = 'IsGreaterThanOrEqual';
    const TYPE_IS_LIGHTER_THAN_OR_EQUAL = 'IsLighterThanOrEqual';
    
    /**
     * DB table name.
     *
     * @var array
     */
    private static $table_name = 'SilvercartProductWizardDisplayCondition';
    /**
     * DB attributes.
     *
     * @var array
     */
    private static $db = [
        'StepOptionID' => 'Int',
        'Type'         => 'Enum("IsEqual,IsNotEqual,IsGreaterThan,IsLighterThan,IsGreaterThanOrEqual,IsLighterThanOrEqual","IsEqual")',
        'TargetValue'  => 'Varchar(256)',
        'Sort'         => 'Int',
    ];
    /**
     * Has one relations.
     *
     * @var array
     */
    private static $has_one = [
        'ParentStepOption' => 'SilvercartProductWizardStepOption',
        'StepOptionSet'    => 'SilvercartProductWizardStepOptionSet',
    ];
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
            $fields->removeByName('ParentStepOptionID');
            $fields->removeByName('StepOptionSetID');
            $fields->removeByName('StepOptionID');
            $fields->removeByName('Sort');
            $types = [];
            foreach ($this->dbObject('Type')->enumValues() as $enumValue) {
                $types[$enumValue] = _t(self::class . ".Type{$enumValue}", $enumValue);
            }
            $fields->dataFieldByName('Type')
                    ->setSource($types);
            $fields->addFieldToTab('Root.Main', DropdownField::create('StepOptionID', $this->fieldLabel('StepOptionID'), $this->getOptions(), $this->OptionID), 'Type');
        });
        return parent::getCMSFields();
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
            'Sort'           => '#',
            'StepOptionNice' => $this->fieldLabel('StepOptionID'),
            'TypeNice'       => $this->fieldLabel('Type'),
            'TargetValue'    => $this->fieldLabel('TargetValue'),
        ];
        $this->extend('updateSummaryFields', $summaryFields);
        return $summaryFields;
    }
    
    /**
     * Returns a list of context options.
     * 
     * @return array
     */
    protected function getOptions() : array
    {
        $options = [];
        $step    = $this->getContextStep();
        if ($step->exists()) {
            $stepOptions = $step->StepOptions();
            $options     = $stepOptions->map()->toArray();
        }
        return $options;
    }
    
    /**
     * Returns the option string
     * 
     * @return string
     */
    public function getStepOptionNice() : string
    {
        $option  = '---';
        $options = $this->getOptions();
        if (array_key_exists($this->StepOptionID, $options)) {
            $option = $options[$this->StepOptionID];
        }
        return $option;
    }
    
    /**
     * Returns the type string
     * 
     * @return string
     */
    public function getTypeNice() : string
    {
        return _t(self::class . ".Type{$this->Type}", $this->Type);;
    }
    
    /**
     * Returns whether this condition is matching.
     * 
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 25.02.2019
     */
    public function isMatching() : bool
    {
        $isMatching   = false;
        $stepOptionID = $this->StepOptionID;
        $step         = $this->getContextStep();
        $stepPage     = $step->ProductWizardStepPage();
        /* @var $step SilvercartProductWizardStep */
        /* @var $stepPage SilvercartProductWizardStepPage */
        $postVars     = $stepPage->getPostVarsFor($step);
        if (array_key_exists('StepOptions', $postVars)
         && is_array($postVars['StepOptions'])
         && array_key_exists($stepOptionID, $postVars['StepOptions'])
         && (($postVars['StepOptions'][$stepOptionID] == $this->TargetValue
           && $this->Type === self::TYPE_IS_EQUAL)
          || ($postVars['StepOptions'][$stepOptionID] != $this->TargetValue
           && $this->Type === self::TYPE_IS_NOT_EQUAL)
          || ($postVars['StepOptions'][$stepOptionID] > $this->TargetValue
           && $this->Type === self::TYPE_IS_GREATER_THAN)
          || ($postVars['StepOptions'][$stepOptionID] < $this->TargetValue
           && $this->Type === self::TYPE_IS_LIGHTER_THAN)
          || ($postVars['StepOptions'][$stepOptionID] >= $this->TargetValue
           && $this->Type === self::TYPE_IS_GREATER_THAN_OR_EQUAL)
          || ($postVars['StepOptions'][$stepOptionID] <= $this->TargetValue
           && $this->Type === self::TYPE_IS_LIGHTER_THAN_OR_EQUAL))
        ) {
            $isMatching = true;
        }
        return $isMatching;
    }
    
    /**
     * Returns the context step.
     * 
     * @return Step
     */
    public function getContextStep() : Step
    {
        $step = Step::singleton();
        if ($this->StepOptionSet()->exists()
         || $this->ParentStepOption()->exists()
        ) {
            if ($this->StepOptionSet()->exists()) {
                $step = $this->StepOptionSet()->Step()->getPreviousStep();
            } elseif ($this->ParentStepOption()->Step()->exists()) {
                $step = $this->ParentStepOption()->Step()->getPreviousStep();
            } elseif ($this->ParentStepOption()->StepOptionSet()->exists()) {
                $step = $this->ParentStepOption()->StepOptionSet()->Step()->getPreviousStep();
            }
        }
        return $step;
    }
}