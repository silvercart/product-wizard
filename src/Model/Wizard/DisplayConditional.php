<?php

namespace SilverCart\ProductWizard\Model\Wizard;

use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Security\Permission;

/**
 * Trait to add some SilverCart ProductWizard DisplayCondition related features
 * to a DataObject.
 * I know, it's not a word..
 * 
 * @package SilverCart
 * @subpackage SubPackage
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 26.02.2019
 * @copyright 2019 pixeltricks GmbH
 * @license see license file in modules root directory
 */
trait DisplayConditional
{
    private static $DISPLAY_CONDITION_TYPE_SHOW     = 'Show';
    private static $DISPLAY_CONDITION_TYPE_HIDE     = 'Hide';
    private static $DISPLAY_CONDITION_OPERATION_AND = 'And';
    private static $DISPLAY_CONDITION_OPERATION_OR  = 'Or';
    
    /**
     * Adds the display condition fields to the CMS $fields.
     * 
     * @param FieldList $fields CMS fields
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 26.02.2019
     */
    public function addDisplayConditionalCMSFields(FieldList $fields) : void
    {
        $fields->removeByName('DisplayConditionType');
        $fields->removeByName('DisplayConditionOperation');
        if ($this->exists()) {
            $displayConditionsField = $fields->dataFieldByName('DisplayConditions');
            /* @var $displayConditionsField \SilverStripe\Forms\GridField\GridField */
            $displayConditionsField->setList($displayConditionsField->getList()->sort('Sort ASC'));
            $displayConditionsConfig = $displayConditionsField->getConfig();
            if (class_exists('\Symbiote\GridFieldExtensions\GridFieldOrderableRows')) {
                $displayConditionsConfig->addComponent(new \Symbiote\GridFieldExtensions\GridFieldOrderableRows('Sort'));
            } elseif (class_exists('\UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows')) {
                $displayConditionsConfig->addComponent(new \UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows('Sort'));
            }
            $conditionTypes = [];
            foreach ($this->dbObject('DisplayConditionType')->enumValues() as $enumValue) {
                if (empty($enumValue)) {
                    $conditionTypes[$enumValue] = '';
                    continue;
                }
                $conditionTypes[$enumValue] = _t(self::class . ".DisplayConditionType{$enumValue}", $enumValue);
            }
            $conditionOperations = [];
            foreach ($this->dbObject('DisplayConditionOperation')->enumValues() as $enumValue) {
                if (empty($enumValue)) {
                    $conditionOperations[$enumValue] = '';
                    continue;
                }
                $conditionOperations[$enumValue] = _t(self::class . ".DisplayConditionOperation{$enumValue}", $enumValue);
            }
            $field = _t(self::class . '.DisplayConditionText', '{type} this option-set when matching {operation} of the following conditions', [
                'type'      => DropdownField::create('DisplayConditionType', '', $conditionTypes, $this->DisplayConditionType)->setAttribute('style', 'width:auto;')->Field(),
                'operation' => DropdownField::create('DisplayConditionOperation', '', $conditionOperations, $this->DisplayConditionOperation)->setAttribute('style', 'width:auto;')->Field(),
            ]);
            $fields->addFieldToTab('Root.DisplayConditions', LiteralField::create('DisplayConditionLiteral', $field), 'DisplayConditions');
        }
    }
    
    /**
     * On before write.
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 24.02.2019
     */
    protected function onBeforeWriteDisplayCondition() : void
    {
        if (Permission::check('ADMIN')
         && array_key_exists('DisplayConditionType', $_POST)
         && array_key_exists('DisplayConditionOperation', $_POST)
        ) {
            $this->DisplayConditionType      = $_POST['DisplayConditionType'];
            $this->DisplayConditionOperation = $_POST['DisplayConditionOperation'];
        }
    }
    
    /**
     * Returns whether this option-set is visible.
     * 
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 25.02.2019
     */
    public function isVisible() : bool
    {
        $isVisible  = true;
        $isMatching = true;
        $conditions = $this->DisplayConditions();
        if ($conditions->exists()) {
            if ($this->DisplayConditionOperation === self::$DISPLAY_CONDITION_OPERATION_AND) {
                foreach ($conditions as $condition) {
                    if (!$condition->isMatching()) {
                        $isMatching = false;
                        break;
                    }
                }
            } elseif ($this->DisplayConditionOperation === self::$DISPLAY_CONDITION_OPERATION_OR) {
                $isMatching = false;
                foreach ($conditions as $condition) {
                    if ($condition->isMatching()) {
                        $isMatching = true;
                        break;
                    }
                }
            }
        }
        if ($this->DisplayConditionType === self::$DISPLAY_CONDITION_TYPE_SHOW) {
            $isVisible = $isMatching;
        } elseif ($this->DisplayConditionType === self::$DISPLAY_CONDITION_TYPE_HIDE) {
            $isVisible = !$isMatching;
        }
        return $isVisible;
    }
}
