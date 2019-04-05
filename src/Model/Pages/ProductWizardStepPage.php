<?php

namespace SilverCart\ProductWizard\Model\Pages;

use Page;
use SilverCart\Dev\Tools;
use SilverCart\Model\Product\Product;
use SilverCart\ProductWizard\Model\Wizard\Step;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\FieldType\DBMoney;
use SilverStripe\View\ArrayData;

/**
 * Page type to guide a cutomer through a stepped product wizard.
 * 
 * @package SilverCart
 * @subpackage ProductWizard\Model\Pages
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 15.02.2019
 * @copyright 2019 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class ProductWizardStepPage extends Page
{
    use \SilverCart\ORM\ExtensibleDataObject;
    
    const SESSION_KEY                 = 'SilverCart.ProductWizard.ProductWizardStepPage';
    const SESSION_KEY_COMPLETED_STEPS = self::SESSION_KEY . '.CompletedSteps';
    const SESSION_KEY_CURRENT_STEP    = self::SESSION_KEY . '.CurrentStep';
    const SESSION_KEY_POST_VARS       = self::SESSION_KEY . '.PostVars';
    
    /**
     * The completed steps.
     * 
     * @var ArrayList
     */
    protected $completedSteps = null;
    /**
     * The currently chosen step.
     * 
     * @var Step
     */
    protected $currentStep = null;
    
    /**
     * Returns the current step ID from Session.
     * 
     * @return int
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 15.02.2019
     */
    public static function getCurrentStepIDFromSession() : int
    {
        return (int) Tools::Session()->get(self::SESSION_KEY_CURRENT_STEP);
    }
    
    /**
     * Returns the post vars for all wizards.
     * 
     * @return void
     */
    public static function getPostVars() : array
    {
        $postVars = Tools::Session()->get(self::SESSION_KEY_POST_VARS);
        if (!is_array($postVars)) {
            $postVars = [];
        }
        return $postVars;
    }
    
    /**
     * Returns the post vars for the given $page and $step.
     * 
     * @param SiteTree $page Page context
     * @param Step     $step Step context
     * 
     * @return void
     */
    public static function getPostVarsForPage(SiteTree $page, Step $step = null) : array
    {
        $sessionKey = self::SESSION_KEY_POST_VARS . ".{$page->ID}";
        if ($step instanceof Step) {
            $sessionKey .= ".{$step->ID}";
        }
        $postVars = Tools::Session()->get($sessionKey);
        if (!is_array($postVars)) {
            $postVars = [];
        }
        return $postVars;
    }
    
    /**
     * Returns the current step ID from Session.
     * 
     * @param int $stepID Step ID to set
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 29.03.2019
     */
    public static function setCurrentStepIDToSession(int $stepID) : void
    {
        Tools::Session()->set(self::SESSION_KEY_CURRENT_STEP, $stepID);
        Tools::saveSession();
    }
    
    /**
     * Sets the $postVars for the given $page and $step.
     * 
     * @param array    $postVars Post vars
     * @param SiteTree $page     Page context
     * @param Step     $step     Step context
     * 
     * @return void
     */
    public static function setPostVarsForPage(array $postVars, SiteTree $page, Step $step) : void
    {
        Tools::Session()->set(self::SESSION_KEY_POST_VARS . ".{$page->ID}.{$step->ID}", null);
        Tools::saveSession();
        Tools::Session()->set(self::SESSION_KEY_POST_VARS . ".{$page->ID}.{$step->ID}", $postVars);
        Tools::saveSession();
    }
    
    /**
     * Description of this page.
     *
     * @var string
     */
    private static $description = 'Page to display a step based product configuration wizard.';
    /**
     * DB table name.
     *
     * @var array
     */
    private static $table_name = 'SilvercartProductWizardStepPage';
    /**
     * Has many relations.
     *
     * @var array
     */
    private static $has_many = [
        'Steps' => Step::class,
    ];
    
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
            'Back' => _t(self::class . '.Back', 'Back'),
            'Step' => _t(self::class . '.Step', 'Step'),
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
            $config = GridFieldConfig_RelationEditor::create(30);
            $field  = GridField::create('Steps', $this->fieldLabel('Steps'), $this->Steps(), $config);
            $fields->addFieldToTab('Root.Main', $field, 'Content');
            $fields->removeByName('Content');
            $config->removeComponentsByType(GridFieldAddExistingAutocompleter::class);
            if (class_exists('\Symbiote\GridFieldExtensions\GridFieldOrderableRows')) {
                $config->addComponent(new \Symbiote\GridFieldExtensions\GridFieldOrderableRows('Sort'));
            } elseif (class_exists('\UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows')) {
                $config->addComponent(new \UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows('Sort'));
            }
        });
        return parent::getCMSFields();
    }
    
    /**
     * Adds a $step to the list of completed ones.
     * 
     * @param Step $step Step to add
     * 
     * @return ProductWizardStepPage
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 25.02.2019
     */
    public function addCompletedStep(Step $step) : ProductWizardStepPage
    {
        $completedStepIDs = $this->getCompletedStepIDs();
        if (!in_array($step->ID, $completedStepIDs)) {
            $this->getCompletedSteps()->add($step);
            $completedStepIDs[] = $step->ID;
            $this->setCompletedStepIDs($completedStepIDs);
        }
        return $this;
    }
    
    /**
     * Returns the cart summary.
     * 
     * @return array
     */
    public function getCartSummary() : array
    {
        $stepData   = [];
        $amountData = [];
        foreach ($this->Steps() as $step) {
            $stepData[$step->ID] = [];
            if ($step->StepOptionSets()->exists()) {
                foreach ($step->StepOptionSets() as $optionSet) {
                    $this->loadStepAndAmountData($optionSet->StepOptions(), $step, $stepData, $amountData);
                }
            } else {
                $this->loadStepAndAmountData($step->StepOptions(), $step, $stepData, $amountData);
            }
        }
        return [
            'Steps'   => $stepData,
            'Amounts' => $amountData,
        ];
    }
    
    /**
     * Loads the step and amount data for the given $stepOptions and $step into 
     * the given $stepData and $amountData array.
     * 
     * @param DataList $stepOptions Step options to load data for
     * @param Step     $step        Step context
     * @param array    &$stepData   Step data store
     * @param array    &$amountData Amount data store
     * 
     * @return \SilverCart\ProductWizard\Model\Pages\ProductWizardStepPage
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 02.04.2019
     */
    public function loadStepAndAmountData(DataList $stepOptions, Step $step, array &$stepData, array &$amountData) : ProductWizardStepPage
    {
        foreach ($stepOptions as $option) {
            $data = $option->getCartSummary();
            if (!empty($data)) {
                $stepData[$step->ID][$option->ID] = $data;
                foreach ($data as $positionData) {
                    $billingPeriod = _t('SilverCart\Model\Pages\Page.TOTAL', 'Total');
                    if (Product::singleton()->hasMethod('getBillingPeriodNice')) {
                        $billingPeriod = $positionData['BillingPeriodNice'];
                    }
                    if (!array_key_exists($billingPeriod, $amountData)) {
                        $amountData[$billingPeriod] = [
                            'Amount'   => $positionData['priceTotal']['Amount'],
                            'Currency' => $positionData['priceTotal']['Currency'],
                            'Nice'     => $positionData['priceTotal']['Nice'],
                        ];
                    } else {
                        $amountData[$billingPeriod]['Amount'] += $positionData['priceTotal']['Amount'];
                        $amountData[$billingPeriod]['Nice']    = DBMoney::create()
                                ->setCurrency($amountData[$billingPeriod]['Currency'])
                                ->setAmount($amountData[$billingPeriod]['Amount'])
                                ->Nice();
                    }
                }
            }
        }
        return $this;
    }
    
    /**
     * Returns the cart summary to use in a template.
     * 
     * @return ArrayData
     */
    public function getCartSummaryForTemplate() : ArrayData
    {
        $summary = $this->getCartSummary();
        $steps   = ArrayList::create();
        $amounts = ArrayList::create();
        foreach ($summary['Amounts'] as $interval => $amountData) {
            $amount = ArrayData::create($amountData);
            $amount->Interval = $interval;
            $amounts->push($amount);
        }
        return ArrayData::create([
            'Steps'   => $steps,
            'Amounts' => $amounts,
        ]);
    }
    
    /**
     * Returns the ID list of completed steps.
     * 
     * @return array
     */
    public function getCompletedStepIDs() : array
    {
        return (array) Tools::Session()->get(self::SESSION_KEY_COMPLETED_STEPS . ".{$this->ID}");
    }
    
    /**
     * Sets the ID list of completed steps.
     * 
     * @param array $ids ID list to set
     * 
     * @return ProductWizardStepPage
     */
    public function setCompletedStepIDs(array $ids) : ProductWizardStepPage
    {
        Tools::Session()->set(self::SESSION_KEY_COMPLETED_STEPS . ".{$this->ID}", null);
        Tools::saveSession();
        Tools::Session()->set(self::SESSION_KEY_COMPLETED_STEPS . ".{$this->ID}", $ids);
        Tools::saveSession();
        return $this;
    }
    
    /**
     * Returns a list of completed steps.
     * 
     * @return ArrayList
     */
    public function getCompletedSteps() : ArrayList
    {
        if (is_null($this->completedSteps)) {
            $completedSteps   = [];
            $completedStepIDs = $this->getCompletedStepIDs();
            foreach ($completedStepIDs as $stepID) {
                $step = $this->Steps()->byID($stepID);
                if ($step instanceof Step
                 && $step->exists()
                ) {
                    $completedSteps[] = $step;
                }
            }
            $this->completedSteps = ArrayList::create($completedSteps);
        }
        return $this->completedSteps;
    }
    
    /**
     * Returns the current step.
     * 
     * @return Step|null
     */
    public function getCurrentStep() : ?Step
    {
        if (is_null($this->currentStep)) {
            $this->initCurrentStep();
        }
        return $this->currentStep;
    }
    
    /**
     * Sets the current step.
     * 
     * @param Step $step Step to set
     * 
     * @return ProductWizardStepPage
     */
    public function setCurrentStep(Step $step) : ProductWizardStepPage
    {
        $this->currentStep = $step;
        self::setCurrentStepIDToSession($step->ID);
        return $this;
    }
    
    /**
     * Returns the current step.
     * 
     * @return DataList
     */
    public function getNavigationSteps() : DataList
    {
        return $this->Steps()->filter('ShowInStepNavigation', true);
    }
    
    /**
     * Returns the navigation progress as percentage.
     * 
     * @return float
     */
    public function getNavigationStepProgressPercentage() : float
    {
        $percentage   = 0;
        $current      = $this->getCurrentStep();
        $steps        = $this->getNavigationSteps();
        $total        = $steps->count();
        $completedIDs = $this->getCompletedStepIDs();
        foreach ($steps as $index => $navStep) {
            $currentStepNumber = $index + 1;
            if ($current->ID === $navStep->ID) {
                $percentage = $currentStepNumber / ($total / 100);
                break;
            } elseif (in_array($navStep->ID, $completedIDs)) {
                $percentage = $currentStepNumber / ($total / 100);
            }
        }
        return $percentage;
    }
    
    /**
     * Returns the previous step.
     * 
     * @return Step|null
     */
    public function getPreviousStep() : ?Step
    {
        $prev    = null;
        $current = $this->getCurrentStep();
        if ($current instanceof Step) {
            $prev = $current->getPreviousStep();
        }
        return $prev;
    }
    
    /**
     * Returns the post vars for the given $step.
     * 
     * @param Step $step Step context
     * 
     * @return void
     */
    public function getPostVarsFor(Step $step = null) : array
    {
        return self::getPostVarsForPage($this, $step);
    }
    
    /**
     * Sets the $postVars for the given $step.
     * 
     * @param array $postVars Post vars
     * @param Step  $step     Step context
     * 
     * @return ProductWizardStepPage
     */
    public function setPostVarsFor(array $postVars, Step $step) : ProductWizardStepPage
    {
        self::setPostVarsForPage($postVars, $this, $step);
        return $this;
    }
    
    /**
     * Initializes the current step by session or sets the first step as current
     * step.
     * 
     * @return ProductWizardStepPage
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 24.02.2019
     */
    public function initCurrentStep() : ProductWizardStepPage
    {
        $idBySession = self::getCurrentStepIDFromSession();
        $currentStep = Step::get()->byID($idBySession);
        if (!($currentStep instanceof Step)
         || !$currentStep->exists()
        ) {
            $currentStep = $this->Steps()->first();
        }
        $this->setCurrentStep($currentStep);
        return $this;
    }
    
    /**
     * Resets the submitted step data.
     * 
     * @return ProductWizardStepPage
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 27.02.2019
     */
    public function resetPostVars() : ProductWizardStepPage
    {
        foreach ($this->Steps() as $step) {
            $this->setPostVarsFor([], $step);
        }
        return $this;
    }
    
    /**
     * Resets the submitted step data.
     * 
     * @return ProductWizardStepPage
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 27.02.2019
     */
    public function resetCurrentStep() : ProductWizardStepPage
    {
        self::setCurrentStepIDToSession(0);
        return $this;
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
        $backLink     = '';
        $previousStep = $this->getPreviousStep();
        if ($previousStep instanceof Step
         && $previousStep->canAccess()
        ) {
            $backLink = $previousStep->Link();
        }
        return $backLink;
    }
    
    /**
     * Returns whether this wizard is completed.
     * 
     * @return bool
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 26.02.2019
     */
    public function WizardIsCompleted() : bool
    {
        $wizardIsCompleted = true;
        $completedSteps    = $this->getCompletedStepIDs();
        foreach ($this->Steps() as $step) {
            if (!in_array($step->ID, $completedSteps)) {
                $wizardIsCompleted = false;
            }
        }
        return $wizardIsCompleted;
    }
}