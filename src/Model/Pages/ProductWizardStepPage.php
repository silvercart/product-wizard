<?php

namespace SilverCart\ProductWizard\Model\Pages;

use Page;
use SilverCart\Dev\Tools;
use SilverCart\Model\Pages\CheckoutStepController;
use SilverCart\Model\Product\Product;
use SilverCart\ProductWizard\Model\Wizard\Step;
use SilverCart\ProductWizard\Model\Wizard\StepOption;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\FieldType\DBHTMLText;
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
 * 
 * @property bool   $SkipShoppingCart      SkipShoppingCart
 * @property bool   $DisplayCheckoutAsStep DisplayCheckoutAsStep
 * @property string $CheckoutStepTitle     CheckoutStepTitle
 * 
 * @method \SilverStripe\ORM\HasManyList Steps() Returns the related Steps.
 */
class ProductWizardStepPage extends Page
{
    use \SilverCart\ORM\ExtensibleDataObject;
    
    const SESSION_KEY                   = 'SilverCart.ProductWizard.ProductWizardStepPage';
    const SESSION_KEY_COMPLETED_STEPS   = self::SESSION_KEY . '.CompletedSteps';
    const SESSION_KEY_CURRENT_STEP      = self::SESSION_KEY . '.CurrentStep';
    const SESSION_KEY_POST_VARS         = self::SESSION_KEY . '.PostVars';
    const SESSION_KEY_VALIDATION_ERRORS = self::SESSION_KEY . '.ValidationErrors';
    
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
     * List of custom cart data to display in summary.
     * 
     * @var array
     */
    protected $customCartData = [];
    /**
     * Determines whether to hide the cart summary submit button.
     * 
     * @var bool
     */
    protected $hideCartSummarySubmitButton = false;
    
    /**
     * Returns the current step ID from Session.
     * 
     * @param int $pageID Page ID to get data for
     * 
     * @return int
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 15.02.2019
     */
    public static function getCurrentStepIDFromSession(int $pageID) : int
    {
        return (int) Tools::Session()->get(self::SESSION_KEY_CURRENT_STEP . ".{$pageID}");
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
     * @param int $pageID Page ID to set
     * @param int $stepID Step ID to set
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 29.03.2019
     */
    public static function setCurrentStepIDToSession(int $pageID, int $stepID) : void
    {
        Tools::Session()->set(self::SESSION_KEY_CURRENT_STEP . ".{$pageID}", $stepID);
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
     * DB attributes.
     *
     * @var array
     */
    private static $db = [
        'SkipShoppingCart'      => 'Boolean',
        'DisplayCheckoutAsStep' => 'Boolean',
        'CheckoutStepTitle'     => 'Varchar',
    ];
    /**
     * Has many relations.
     *
     * @var array
     */
    private static $has_many = [
        'Steps' => Step::class,
    ];
    /**
     * The HTTP get var name to use for wizard rdirection pages.
     * Default: pwp (Product Wizard Page)
     *
     * @var string
     */
    private static $http_get_var_name = 'pwp';
    
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
            'Back'                             => _t(self::class . '.Back', 'Back'),
            'ErrorPleaseCompleteYourSelection' => _t(self::class . '.ErrorPleaseCompleteYourSelection', 'Please complete your selection.'),
            'Step'                             => _t(self::class . '.Step', 'Step'),
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
            $fields->insertAfter('Steps', TextField::create('CheckoutStepTitle', $this->fieldLabel('CheckoutStepTitle'), $this->CheckoutStepTitle));
            $fields->insertAfter('Steps', CheckboxField::create('DisplayCheckoutAsStep', $this->fieldLabel('DisplayCheckoutAsStep'), $this->DisplayCheckoutAsStep));
            $fields->insertAfter('Steps', CheckboxField::create('SkipShoppingCart', $this->fieldLabel('SkipShoppingCart'), $this->SkipShoppingCart));
        });
        return parent::getCMSFields();
    }
    
    /**
     * Returns whether the current step can be completed.
     * 
     * @return bool
     */
    public function canCompleteCurrentStep() : bool
    {
        return $this->canCompleteStep($this->getCurrentStep());
    }
    
    /**
     * Returns whether the given $step can be completed.
     * 
     * @param Step $step Step to check
     * 
     * @return bool
     */
    public function canCompleteStep(Step $step) : bool
    {
        if ($step->StepOptionSets()->exists()) {
            foreach ($step->StepOptionSets() as $optionSet) {
                if (!$this->validateStepOptions($optionSet->getVisibleStepOptions())) {
                    return false;
                }
            }
        }
        return $this->validateStepOptions($step->getVisibleStepOptions());
    }
    
    /**
     * Returns whether there are validation errors.
     * 
     * @return bool
     */
    public function HasValidationErrors() : bool
    {
        return count((array) Tools::Session()->get(self::SESSION_KEY_VALIDATION_ERRORS . ".{$this->ID}")) > 0;
    }
    
    /**
     * Returns the validation errors.
     * 
     * @return ArrayList
     */
    public function getValidationErrors() : ArrayList
    {
        $errors   = ArrayList::create();
        $messages = (array) Tools::Session()->get(self::SESSION_KEY_VALIDATION_ERRORS . ".{$this->ID}");
        Tools::Session()->set(self::SESSION_KEY_VALIDATION_ERRORS . ".{$this->ID}", []);
        Tools::Session()->clear(self::SESSION_KEY_VALIDATION_ERRORS . ".{$this->ID}");
        Tools::saveSession();
        foreach ($messages as $message) {
            $errors->push(ArrayData::create([
                'Message' => $message,
            ]));
        }
        return $errors;
    }
    
    /**
     * Adds a validation error $message.
     * 
     * @param string $message Message to add
     * 
     * @return ProductWizardStepPage
     */
    public function addValidationError(string $message) : ProductWizardStepPage
    {
        $messages   = (array) Tools::Session()->get(self::SESSION_KEY_VALIDATION_ERRORS . ".{$this->ID}");
        $messages[] = $message;
        Tools::Session()->set(self::SESSION_KEY_VALIDATION_ERRORS . ".{$this->ID}", $messages);
        Tools::saveSession();
        return $this;
    }
    
    /**
     * Validates the given $stepOptions.
     * 
     * @param ArrayList $stepOptions Step options to validate
     * 
     * @return bool
     */
    public function validateStepOptions(ArrayList $stepOptions) : bool
    {
        $error = false;
        foreach ($stepOptions as $option) {
            /* @var $option \SilverCart\ProductWizard\Model\Wizard\StepOption */
            if ($option->OptionType !== StepOption::OPTION_TYPE_NUMBER
             && $option->OptionType !== StepOption::OPTION_TYPE_RADIO
             && $option->OptionType !== StepOption::OPTION_TYPE_TEXTAREA
             && $option->OptionType !== StepOption::OPTION_TYPE_TEXTFIELD
             && !($option->IsProductView()
               && (bool) $option->IsOptional === false)
            ) {
                // no input required
                continue;
            }
            $data = $option->getCartSummary();
            if (empty($data)) {
                $error = true;
                break;
            }
        }
        if ($error) {
            $this->addValidationError($this->fieldLabel('ErrorPleaseCompleteYourSelection'));
        }
        return !$error;
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
            if (!$step->isVisible()) {
                continue;
            }
            $stepData[$step->ID] = [];
            if ($step->StepOptionSets()->exists()) {
                foreach ($step->StepOptionSets() as $optionSet) {
                    $this->loadStepAndAmountData($optionSet->getVisibleStepOptions(), $step, $stepData, $amountData);
                }
            } else {
                $this->loadStepAndAmountData($step->getVisibleStepOptions(), $step, $stepData, $amountData);
            }
        }
        return [
            'Steps'   => $stepData,
            'Amounts' => $amountData,
        ];
    }

    /**
     * Sets the custom cart data for the given $step.
     * 
     * @param Step    $step     Step
     * @param int     $quantity Quantity
     * @param Product $product  Product
     * 
     * @return ProductWizardStepPage
     */
    public function setCustomCartData(Step $step, int $quantity, Product $product) : ProductWizardStepPage
    {
        $this->customCartData[$step->ID] = [StepOption::getCartPositionData($quantity, $product)];
        return $this;
    }
    
    /**
     * Returns the custom cart data.
     * 
     * @return array
     */
    public function getCustomCartData() : array
    {
        return $this->customCartData;
    }
    
    /**
     * Loads the step and amount data for the given $stepOptions and $step into 
     * the given $stepData and $amountData array.
     * 
     * @param ArrayList $stepOptions Step options to load data for
     * @param Step      $step        Step context
     * @param array     &$stepData   Step data store
     * @param array     &$amountData Amount data store
     * 
     * @return \SilverCart\ProductWizard\Model\Pages\ProductWizardStepPage
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 02.04.2019
     */
    public function loadStepAndAmountData(ArrayList $stepOptions, Step $step, array &$stepData, array &$amountData) : ProductWizardStepPage
    {
        foreach ($stepOptions as $option) {
            /* @var $option StepOption */
            $data = $option->getCartSummary();
            if (!empty($data)) {
                $stepData[$step->ID][$option->ID] = $data;
                foreach ($data as $positionData) {
                    $this->loadAmountData($positionData, $amountData);
                }
            }
        }
        $customCartData = $this->getCustomCartData();
        if (array_key_exists($step->ID, $customCartData)) {
            $customStepData = $customCartData[$step->ID];
            $stepData[$step->ID][0] = $customStepData;
            foreach ($customStepData as $positionData) {
                $this->loadAmountData($positionData, $amountData);
            }
        }
        return $this;
    }
    
    /**
     * Loads the amount data for the given $positionData.
     * 
     * @param array $positionData Position data
     * @param array &$amountData  Amount data to mutate
     * 
     * @return \SilverCart\ProductWizard\Model\Pages\ProductWizardStepPage
     */
    public function loadAmountData(array $positionData, array &$amountData) : ProductWizardStepPage
    {
        $billingPeriod  = _t('SilverCart\Model\Pages\Page.TOTAL', 'Total');
        $billingPeriods = [$billingPeriod];
        $prices         = [$billingPeriod => $positionData['priceTotal']];
        if (Product::singleton()->hasMethod('getBillingPeriodNice')) {
            $billingPeriod  = $positionData['BillingPeriodNice'];
            $billingPeriods = [$billingPeriod];
            $prices         = [$billingPeriod => $positionData['priceTotal']];
            if (array_key_exists('BillingPeriodConsequential', $positionData)
             && !in_array($positionData['BillingPeriodConsequentialNice'], $billingPeriods)
            ) {
                $billingPeriod    = $positionData['BillingPeriodConsequentialNice'];
                $billingPeriods[] = $billingPeriod;
                $prices[$billingPeriod] = $positionData['priceTotalConsequential'];
                $positionData['priceSingleConsequential'];
                $positionData['BillingPeriodConsequential'];
            }
        }
        foreach ($billingPeriods as $billingPeriod) {
            $price = $prices[$billingPeriod];
            if (!array_key_exists($billingPeriod, $amountData)) {
                $amountData[$billingPeriod] = [
                    'Amount'   => $price['Amount'],
                    'Currency' => $price['Currency'],
                    'Nice'     => $price['Nice'],
                ];
            } else {
                $amountData[$billingPeriod]['Amount'] += $price['Amount'];
                $amountData[$billingPeriod]['Nice']    = DBMoney::create()
                        ->setCurrency($amountData[$billingPeriod]['Currency'])
                        ->setAmount($amountData[$billingPeriod]['Amount'])
                        ->Nice();
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
        self::setCurrentStepIDToSession($this->ID, $step->ID);
        return $this;
    }
    
    /**
     * Returns the current step.
     * 
     * @return DataList
     */
    public function getNavigationSteps() : ArrayList
    {
        $steps           = $this->Steps()->filter('ShowInStepNavigation', true);
        $navigationSteps = ArrayList::create();
        foreach ($steps as $step) {
            if ($step->isVisible()) {
                $navigationSteps->push($step);
            }
        }
        if ($this->DisplayCheckoutAsStep
         && !empty($this->CheckoutStepTitle)
        ) {
            $navigationSteps->push(ArrayData::create([
                'IsFinished'      => false,
                'IsCurrent'       => Controller::curr() instanceof CheckoutStepController,
                'FontAwesomeIcon' => '',
                'Title'           => $this->CheckoutStepTitle,
            ]));
            [
                'SkipShoppingCart'      => 'Boolean',
                'DisplayCheckoutAsStep' => 'Boolean',
                'CheckoutStepTitle'     => 'Varchar',
            ];
        }
        return $navigationSteps;
    }
    
    /**
     * Returns the navigation progress as percentage.
     * 
     * @var Step $current Optional step context
     * 
     * @return float
     */
    public function getNavigationStepProgressPercentage(Step $current = null) : float
    {
        $percentage   = 0;
        $current      = $current === null ? $this->getCurrentStep() : $current;
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
        $idBySession = self::getCurrentStepIDFromSession($this->ID);
        $currentStep = Step::get()->byID($idBySession);
        if (!($currentStep instanceof Step)
         || !$currentStep->exists()
        ) {
            $currentStep = $this->Steps()->first();
        }
        if ($currentStep instanceof Step) {
            $this->setCurrentStep($currentStep);
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
    public function resetPostVars() : ProductWizardStepPage
    {
        foreach ($this->Steps() as $step) {
            $this->setPostVarsFor([], $step);
        }
        return $this;
    }
    
    /**
     * Resets the submitted step data related to the given $product.
     * 
     * @return ProductWizardStepPage
     */
    public function resetPostVarsForProduct($product) : ProductWizardStepPage
    {
        foreach ($this->Steps() as $step) {
            /* @var $step Step */
            $step->resetDataForProduct($product);
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
        self::setCurrentStepIDToSession($this->ID, 0);
        return $this;
    }
    
    /**
     * Returns whether to hide the cart summary submit button.
     * 
     * @return bool
     */
    public function getHideCartSummarySubmitButton() : bool
    {
        return $this->hideCartSummarySubmitButton;
    }
    
    /**
     * Sets whether to hide the cart summary submit button.
     * 
     * @param bool $hide Hide or not?
     * 
     * @return ProductWizardStepPage
     */
    public function setHideCartSummarySubmitButton(bool $hide) : ProductWizardStepPage
    {
        $this->hideCartSummarySubmitButton = $hide;
        return $this;
    }
    
    /**
     * Provides an extension hook to display content right after the cart summary
     * content.
     * 
     * @return DBHTMLText
     */
    public function AfterProductWizardCartSummaryContent() : DBHTMLText
    {
        $content = '';
        $this->extend('updateAfterProductWizardCartSummaryContent', $content);
        return DBHTMLText::create()->setValue($content);
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