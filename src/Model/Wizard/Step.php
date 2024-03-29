<?php

namespace SilverCart\ProductWizard\Model\Wizard;

use SilverCart\Model\Product\Product;
use SilverCart\ProductWizard\Model\Pages\ProductWizardStepPage;
use SilverCart\ProductWizard\Model\Pages\ProductWizardStepPageController;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldFilterHeader;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\FieldType\DBInt;
use SilverStripe\ORM\FieldType\DBVarchar;
use SilverStripe\View\Requirements;

/**
 * A step on a SilverCart ProductWizardStepPage.
 * 
 * @package SilverCart
 * @subpackage ProductWizard\Model\Wizard
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 15.02.2019
 * @copyright 2019 pixeltricks GmbH
 * @license see license file in modules root directory
 * 
 * @property string $Title                     Title
 * @property string $DescriptionTitle          Description Title
 * @property string $DescriptionContent        Description Content
 * @property string $InfoBoxTitle              Info Box Title
 * @property string $InfoBoxContent            Info Box Content
 * @property string $FontAwesomeIcon           Font Awesome Icon
 * @property string $ButtonTitle               Button Title
 * @property bool   $ShowInStepNavigation      Show In Step Navigation
 * @property string $Template                  Template
 * @property int    $Sort                      Sort
 * @property string $DisplayConditionType      Display Condition Type
 * @property string $DisplayConditionOperation Display Condition Operation
 * 
 * @method ProductWizardStepPage ProductWizardStepPage() Returns the related ProductWizardStepPage
 * @method SiteTree              RedirectTo()            Returns the related RedirectTo page
 * 
 * @method \SilverStripe\ORM\HasManyList StepOptions()       Returns the related StepOptions.
 * @method \SilverStripe\ORM\HasManyList StepOptionSets()    Returns the related StepOptionSets.
 * @method \SilverStripe\ORM\HasManyList DisplayConditions() Returns the related DisplayConditions.
 */
class Step extends DataObject
{
    use \SilverCart\ORM\ExtensibleDataObject;
    use DisplayConditional;
    
    const ACTION_TYPE_LINK_TO_EXTERNAL = 'LinkToExternal';
    const ACTION_TYPE_LINK_TO_INTERNAL = 'LinkToInternal';
    const SKIP_TYPE_PARENT_NO          = 'ParentNo';
    const SKIP_TYPE_PARENT_YES         = 'ParentYes';
    const TEMPLATE_OPTIONS_WITH_PROGRESS = 'OptionsWithProgress';
    const TEMPLATE_OPTIONS_WITH_INFO     = 'OptionsWithInfo';
    const TEMPLATE_REDIRECTION           = 'Redirection';
    
    /**
     * FontAwesome CSS file source.
     *
     * @var string
     */
    private static $admin_font_awesome_css = 'silvercart/silvercart:client/css/font-awesome.css';
    /**
     * DB table name.
     *
     * @var string
     */
    private static $table_name = 'SilvercartProductWizardStep';
    /**
     * DB attributes.
     *
     * @var array
     */
    private static $db = [
        'Title'                => 'Varchar(256)',
        'DescriptionTitle'     => 'Varchar(256)',
        'DescriptionContent'   => DBHTMLText::class,
        'InfoBoxTitle'         => 'Varchar(256)',
        'InfoBoxContent'       => DBHTMLText::class,
        'FontAwesomeIcon'      => 'Varchar(25)',
        'ButtonTitle'          => DBVarchar::class,
        'ShowInStepNavigation' => 'Boolean(0)',
        'Template'             => 'Enum("OptionsWithProgress,OptionsWithInfo,Redirection","OptionsWithProgress")',
        'Sort'                 => DBInt::class,
        'DisplayConditionType'       => 'Enum(",Show,Hide","")',
        'DisplayConditionOperation'  => 'Enum(",And,Or","")',
    ];
    /**
     * Has one relations.
     *
     * @var array
     */
    private static $has_one = [
        'ProductWizardStepPage' => ProductWizardStepPage::class,
        'RedirectTo'            => SiteTree::class,
    ];
    /**
     * Has many relations.
     *
     * @var array
     */
    private static $has_many = [
        'StepOptions'    => StepOption::class . '.Step',
        'StepOptionSets' => StepOptionSet::class . '.Step',
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
     * Next link
     * 
     * @var string|null
     */
    protected $nextLink = null;

    /**
     * 
     * @return bool
     */
    public function canAccess() : bool
    {
        $can = false;
        if ($this->exists()) {
            if ($this->ID === $this->ProductWizardStepPage()->Steps()->first()->ID) {
                $can = $this->ProductWizardStepPage()->canView();
            } else {
                $completedStepIDs = $this->ProductWizardStepPage()->getCompletedStepIDs();
                if (in_array($this->ID, $completedStepIDs)
                 || in_array($this->getPreviousStep()->ID, $completedStepIDs)
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
            'Step'     => _t(self::class . '.Step', 'Step'),
        ]);
    }
    
    /**
     * Returns the CMS fields.
     * 
     * @return FieldList
     */
    public function getCMSFields() : FieldList
    {
        if (!empty($this->config()->admin_font_awesome_css)) {
            Requirements::css($this->config()->admin_font_awesome_css);
        }
        $this->beforeUpdateCMSFields(function(FieldList $fields) {
            $fields->dataFieldByName('ButtonTitle')
                    ->setDescription($this->fieldLabel('ButtonTitleDesc'));
            $fields->dataFieldByName('DescriptionTitle')
                    ->setDescription($this->fieldLabel('DescriptionTitleDesc'));
            $fields->dataFieldByName('DescriptionContent')
                    ->setDescription($this->fieldLabel('DescriptionContentDesc'))
                    ->setRows(3);
            $fields->dataFieldByName('InfoBoxTitle')
                    ->setDescription($this->fieldLabel('InfoBoxTitleDesc'));
            $fields->dataFieldByName('InfoBoxContent')
                    ->setDescription($this->fieldLabel('InfoBoxContentDesc'))
                    ->setRows(3);
            $fields->dataFieldByName('FontAwesomeIcon')
                    ->setDescription($this->fieldLabel('FontAwesomeIconDesc'))
                    ->setRightTitle($this->getStepIcon());
            $fields->removeByName('Sort');
            if ($this->exists()) {
                $fields->removeByName('StepOptionSets');
                $stepOptionsField    = $fields->dataFieldByName('StepOptions');
                /* @var $stepOptionsField GridField */
                $stepOptionsField->setList($stepOptionsField->getList()->sort('Sort ASC'));
                $stepOptionsConfig = $stepOptionsField->getConfig();
                if (class_exists('\Symbiote\GridFieldExtensions\GridFieldOrderableRows')) {
                    $stepOptionsConfig->addComponent(new \Symbiote\GridFieldExtensions\GridFieldOrderableRows('Sort'));
                } elseif (class_exists('\UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows')) {
                    $stepOptionsConfig->addComponent(new \UndefinedOffset\SortableGridField\Forms\GridFieldSortableRows('Sort'));
                }
                $fields->dataFieldByName('StepOptions')->getConfig()->removeComponentsByType(GridFieldAddExistingAutocompleter::class);
                $fields->dataFieldByName('StepOptions')->getConfig()->removeComponentsByType(GridFieldFilterHeader::class);
                $fields->dataFieldByName('DisplayConditions')->getConfig()->removeComponentsByType(GridFieldAddExistingAutocompleter::class);
                $fields->dataFieldByName('DisplayConditions')->getConfig()->removeComponentsByType(GridFieldFilterHeader::class);
                $fields->removeByName('ProductWizardStepPageID');
                if ($this->Template === self::TEMPLATE_OPTIONS_WITH_PROGRESS) {
                    $fields->removeByName('DescriptionTitle');
                    $fields->removeByName('DescriptionContent');
                    $fields->removeByName('RedirectToID');
                } elseif ($this->Template === self::TEMPLATE_REDIRECTION) {
                    $fields->removeByName('ButtonTitle');
                    $fields->removeByName('DescriptionTitle');
                    $fields->removeByName('DescriptionContent');
                    $fields->removeByName('InfoBoxContent');
                    $fields->removeByName('StepOptions');
                    $fields->removeByName('RedirectToID');
                    $redirectToField = TreeDropdownField::create('RedirectToID', $this->owner->fieldLabel('RedirectTo'), SiteTree::class)
                            ->setDescription($this->owner->fieldLabel('RedirectToDesc'));
                    $fields->addFieldToTab('Root.Main', $redirectToField);
                } else {
                    $fields->removeByName('RedirectToID');
                }
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
     * @since 25.02.2019
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
            'Sort'                      => '#',
            'Title'                     => $this->fieldLabel('Title'),
            'ShowInStepNavigation.Nice' => $this->fieldLabel('ShowInStepNavigation'),
            'DisplayConditionSummary'   => $this->fieldLabel('DisplayConditions'),
        ];
        $this->extend('updateSummaryFields', $summaryFields);
        return $summaryFields;
    }
    
    /**
     * Resets the submitted option data related to the given $product.
     * 
     * @param Product $product Product
     * 
     * @return Step
     */
    public function resetDataForProduct(Product $product) : Step
    {
        foreach ($this->StepOptionSets() as $optionSet) {
            /* @var $optionSet StepOptionSet */
            $optionSet->resetDataForProduct($product);
        }
        foreach ($this->StepOptions() as $option) {
            /* @var $option StepOption */
            $option->resetDataForProduct($product);
        }
        return $this;
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
     * Returns the FontAwesome step icon.
     * 
     * @return DBHTMLText
     */
    public function getStepIcon() : DBHTMLText
    {
        $icon = DBHTMLText::create();
        if (!empty($this->FontAwesomeIcon)) {
            $icon->setValue("<span class=\"fa fa-{$this->FontAwesomeIcon}\"></span>");
        }
        return $icon;
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
     * @return Step
     */
    public function getNextStep() : Step
    {
        $next = $this->ProductWizardStepPage()->Steps()->where("Sort > {$this->Sort}")->first();
        if (!($next instanceof Step)) {
            $next = self::singleton();
        }
        return $next;
    }
    
    /**
     * Returns the next visible step.
     * 
     * @return Step
     */
    public function getNextVisibleStep() : Step
    {
        $next = $this->getNextStep();
        while ($next instanceof Step
            && !$next->isVisible()
        ) {
            $next = $next->getNextStep();
        }
        return $next;
    }
    
    /**
     * Returns the previous step.
     * 
     * @return Step
     */
    public function getPreviousStep() : Step
    {
        $prev  = null;
        $steps = $this->ProductWizardStepPage()->Steps();
        if ($steps->hasMethod('where')) {
            $prev = $this->ProductWizardStepPage()->Steps()->where("Sort < {$this->Sort}")->last();
        }
        if (!($prev instanceof Step)) {
            $prev = self::singleton();
        } elseif (!$prev->isVisible()) {
            $prev = $prev->getPreviousStep();
        }
        return $prev;
    }
    
    /**
     * Returns the step number to show in the step navigation context.
     * 
     * @return int
     */
    public function getNavigationStepNumber() : int
    {
        $number = 0;
        foreach ($this->ProductWizardStepPage()->getNavigationSteps() as $pos => $step) {
            if ($step->ID === $this->ID) {
                $number = ++$pos;
                break;
            }
        }
        return $number;
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
        if ($this->nextLink === null) {
            $link     = null;
            $nextStep = $this->getNextStep();
            while ($nextStep !== null
                && $nextStep->exists()
            ) {
                if ($nextStep->isVisible()) {
                    $link     = $this->PageLink("step/{$nextStep->Sort}");
                    $nextStep = null;
                    break;
                }
                $nextStep = $nextStep->getNextStep();
            }
            if ($link === null) {
                $link = $this->PageLink('createOffer');
            }
            $this->nextLink = $link;
        }
        return $this->nextLink;
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
    
    /**
     * Returns whether this is the current step.
     * 
     * @return bool
     */
    public function IsCurrent() : bool
    {
        $isCurrent   = false;
        $currentStep = $this->ProductWizardStepPage()->getCurrentStep();
        if ($currentStep instanceof Step
         && $currentStep->ID === $this->ID
        ) {
            $isCurrent = true;
        }
        return $isCurrent;
    }
    
    /**
     * Returns whether this step is completed.
     * 
     * @return bool
     */
    public function IsCompleted() : bool
    {
        $isCompleted      = false;
        $completedStepIDs = $this->ProductWizardStepPage()->getCompletedStepIDs();
        if (in_array($this->ID, $completedStepIDs)) {
            $isCompleted = true;
        }
        return $isCompleted;
    }
    
    /**
     * Returns whether this step is completed.
     * Alias for $this->IsCompleted().
     * 
     * @return bool
     * 
     * @see $this->IsCompleted()
     */
    public function IsFinished() : bool
    {
        return $this->IsCompleted();
    }
    
    /**
     * Returns the rendered step.
     * 
     * @return DBHTMLText
     */
    public function forTemplate() : DBHTMLText
    {
        return $this->renderWith(self::class . "_{$this->Template}");
    }
}