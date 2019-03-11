<?php

//namespace SilverCart\ProductWizard\Model\Wizard;

use SilvercartProductWizardStepPage_Controller as ProductWizardStepPageController;
use DataObject as DataObject;
use ArrayList as ArrayList;
use Controller as Controller;

/**
 * A step on a SilverCart ProductWizardStepPage.
 * 
 * @package SilverCart
 * @subpackage ProductWizard\Model\Wizard
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 15.02.2019
 * @copyright 2019 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class SilvercartProductWizardStep extends DataObject
{
    use SilverCart\ORM\ExtensibleDataObject;
    
    const ACTION_TYPE_LINK_TO_EXTERNAL = 'LinkToExternal';
    const ACTION_TYPE_LINK_TO_INTERNAL = 'LinkToInternal';
    const SKIP_TYPE_PARENT_NO          = 'ParentNo';
    const SKIP_TYPE_PARENT_YES         = 'ParentYes';
    
    /**
     * DB table name.
     *
     * @var array
     */
    private static $table_name = 'SilvercartProductWizardStep';
    /**
     * DB attributes.
     *
     * @var array
     */
    private static $db = [
        'Title'          => 'Varchar(256)',
        'InfoBoxTitle'   => 'Varchar(256)',
        'InfoBoxContent' => 'HTMLText',
        'ButtonTitle'    => 'Varchar',
        'Sort'           => 'Int',
    ];
    /**
     * Has one relations.
     *
     * @var array
     */
    private static $has_one = [
        'ProductWizardStepPage' => 'SilvercartProductWizardStepPage',
        'StepIcon'              => 'Image',
    ];
    /**
     * Has many relations.
     *
     * @var array
     */
    private static $has_many = [
        'StepOptions'    => 'SilvercartProductWizardStepOption.Step',
        'StepOptionSets' => 'SilvercartProductWizardStepOptionSet.Step',
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
     * 
     * @return bool
     */
    public function canAccess() : bool
    {
        $can = false;
        if ($this->exists()) {
            if (!$this->ID === $this->ProductWizardStepPage()->Steps()->first()->ID) {
                $can = $this->ProductWizardStepPage()->canView();
            } else {
                $completedStepIDs = $this->ProductWizardStepPage()->getCompletedStepIDs();
                if (in_array($this->ID, $completedStepIDs)
                 || $this->ID === $this->ProductWizardStepPage()->getCurrentStep()->ID
                ) {
                    $can = true;
                }
            }
        }
        return $can;
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
            'Continue' => _t(self::class . '.Continue', 'Continue'),
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
            $fields->dataFieldByName('ButtonTitle')
                    ->setDescription($this->fieldLabel('ButtonTitleDesc'));
            $fields->dataFieldByName('InfoBoxTitle')
                    ->setDescription($this->fieldLabel('InfoBoxTitleDesc'));
            $fields->dataFieldByName('InfoBoxContent')
                    ->setDescription($this->fieldLabel('InfoBoxContentDesc'))
                    ->setRows(3);
            $fields->removeByName('Sort');
            if ($this->exists()) {
                $stepOptionsField    = $fields->dataFieldByName('StepOptions');
                $stepOptionSetsField = $fields->dataFieldByName('StepOptionSets');
                /* @var $stepOptionsField GridField */
                /* @var $stepOptionSetsField GridField */
                $stepOptionsField->setList($stepOptionsField->getList()->sort('Sort ASC'));
                $stepOptionSetsField->setList($stepOptionSetsField->getList()->sort('Sort ASC'));
                $stepOptionsConfig = $stepOptionsField->getConfig();
                $stepOptionSetsConfig = $stepOptionSetsField->getConfig();
                if (class_exists('GridFieldOrderableRows')) {
                    $stepOptionsConfig->addComponent(new GridFieldOrderableRows('Sort'));
                    $stepOptionSetsConfig->addComponent(new GridFieldOrderableRows('Sort'));
                } elseif (class_exists('GridFieldSortableRows')) {
                    $stepOptionsConfig->addComponent(new GridFieldSortableRows('Sort'));
                    $stepOptionSetsConfig->addComponent(new GridFieldSortableRows('Sort'));
                }
                $fields->removeByName('ProductWizardStepPageID');
            }
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
            'Sort'     => '#',
            'Title'    => $this->fieldLabel('Title'),
            'StepIcon' => $this->fieldLabel('StepIcon'),
        ];
        $this->extend('updateSummaryFields', $summaryFields);
        return $summaryFields;
    }
    
    /**
     * Returns the button title.
     * If the button title is empty and the current controller context is a
     * ProductWizardPageController, the field label for "Continue" will be returned.
     * 
     * @return string
     */
    public function getButtonTitle() : string
    {
        $buttonTitle = $this->getField('ButtonTitle');
        if (empty($buttonTitle)
         && Controller::curr() instanceof ProductWizardStepPageController
        ) {
            $buttonTitle = $this->fieldLabel('Continue');
        }
        return (string) $buttonTitle;
    }
    
    /**
     * Returns the i18n of 'No'.
     * 
     * @return string
     */
    public function getButtonTitleNo() : string
    {
        return _t('Boolean.NOANSWER', 'No');
    }
    
    /**
     * Returns the i18n of 'Yes'.
     * 
     * @return string
     */
    public function getButtonTitleYes() : string
    {
        return _t('Boolean.YESANSWER', 'Yes');
    }
    
    /**
     * Returns a comma separated list of the user input option titles.
     * 
     * @return string
     */
    public function getStepOptionTitles() : string
    {
        $options = $this->StepOptions();
        /* @var $options HasManyList */
        return implode(',', $options->map('ID', 'Title')->toArray());
    }
    
    /**
     * Returns the next step.
     * 
     * @return \SilvercartProductWizardStep
     */
    public function getNextStep() : SilvercartProductWizardStep
    {
        $next = $this->ProductWizardStepPage()->Steps()->where("Sort > {$this->Sort}")->first();
        if (!($next instanceof SilvercartProductWizardStep)) {
            $next = self::singleton();
        }
        return $next;
    }
    
    /**
     * Returns the previous step.
     * 
     * @return \SilvercartProductWizardStep
     */
    public function getPreviousStep() : SilvercartProductWizardStep
    {
        $prev = $this->ProductWizardStepPage()->Steps()->where("Sort < {$this->Sort}")->last();
        if (!($prev instanceof SilvercartProductWizardStep)) {
            $prev = self::singleton();
        }
        return $prev;
    }
    
    /**
     * Returns the visible option sets.
     * 
     * @return ArrayList
     */
    public function getVisibleStepOptionSets() : ArrayList
    {
        $optionSets        = $this->StepOptionSets();
        $visibleOptionSets = ArrayList::create();
        foreach ($optionSets as $optionSet) {
            if ($optionSet->isVisible()) {
                $visibleOptionSets->add($optionSet);
            }
        }
        return $visibleOptionSets;
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
    
    /**
     * Returns the back link.
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 15.02.2019
     */
    public function BackLink() : string
    {
        $link = '';
        if ($this->getPreviousStep()->exists()) {
            $link = $this->PageLink("step/{$this->getPreviousStep()->Sort}");
        }
        return $link;
    }
    
    /**
     * Returns the link to the next step.
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 15.02.2019
     */
    public function NextLink() : string
    {
        $link = $this->PageLink('createOffer');
        if ($this->getNextStep()->exists()) {
            $link = $this->PageLink("step/{$this->getNextStep()->Sort}");
        }
        return $link;
    }
    
    /**
     * Returns the link to this step.
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 24.02.2019
     */
    public function Link() : string
    {
        $link = $this->PageLink();
        if ($this->exists()) {
            $link = $this->PageLink("step/{$this->Sort}");
        }
        return $link;
    }
    
    /**
     * Returns the link of the related ProductWizardPage of this step.
     * 
     * @param string $action Action
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 15.02.2019
     */
    public function PageLink(string $action = null) : string
    {
        $link = '';
        if ($this->ProductWizardStepPage()->exists()) {
            $link = $this->ProductWizardStepPage()->Link($action);
        }
        return $link;
    }
}