<?php

namespace SilverCart\ProductWizard\Model\Pages;

use PageController;
use SilverCart\ProductWizard\Model\Wizard\Step;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\ORM\FieldType\DBHTMLText;

/**
 * Controller for SilverCart ProductWizardStepPage.
 * 
 * @package SilverCart
 * @subpackage ProductWizard\Model\Pages
 * @author Sebastian Diel <sdiel@pixeltricks.de>
 * @since 15.02.2019
 * @copyright 2019 pixeltricks GmbH
 * @license see license file in modules root directory
 */
class ProductWizardStepPageController extends PageController
{
    /**
     * List of allowed actions.
     *
     * @var array
     */
    private static $allowed_actions = [
        'step',
        'createOffer',
        'getCartSummaryData',
        'deleteOptionData',
        'postOptionData',
    ];
    
    /**
     * Default action.
     * 
     * @return void
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 25.02.2019
     */
    public function index() : void
    {
        $currentStep = $this->data()->getCurrentStep();
        $this->redirect($currentStep->Link());
    }
    
    /**
     * Action to show the resulting options of the given step.
     * The given step is determined by the URL parameter 'ID'.
     * 
     * @param HTTPRequest $request Request
     * 
     * @return DBHTMLText
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 15.02.2019
     */
    public function step(HTTPRequest $request) : DBHTMLText
    {
        $stepSort = $request->param('ID');
        if (!is_numeric($stepSort)) {
            $this->redirectBack();
            return $this->render();
        }
        $step = $this->data()->Steps()->filter('Sort', $stepSort)->first();
        if (!($step instanceof Step)
         || !$step->exists()
        ) {
            $this->redirectBack();
            return $this->render();
        }
        if (!$step->canAccess()) {
            $this->redirect($this->data()->getCurrentStep()->Link());
            return $this->render();
        }
        if ($request->isPOST()) {
            $postVars = $request->postVars();
            $this->data()->setPostVarsFor($postVars, $step);
            $this->data()->addCompletedStep($step);
            $this->redirect($step->NextLink());
        }
        $this->data()->setCurrentStep($step);
        return $this->render();
    }
    
    /**
     * Action to create an offer after completing the wizard.
     * This action will add the resulting products to the cart.
     * 
     * @param HTTPRequest $request Request
     * 
     * @return DBHTMLText
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 26.02.2019
     */
    public function createOffer(HTTPRequest $request) : DBHTMLText
    {
        foreach ($this->data()->Steps() as $step) {
            if ($step->StepOptionSets()->exists()) {
                foreach ($step->StepOptionSets() as $optionSet) {
                    foreach ($optionSet->StepOptions() as $option) {
                        $option->executeCartTransformation();
                    }
                }
            } else {
                foreach ($step->StepOptions() as $option) {
                    $option->executeCartTransformation();
                }
            }
        }
        $this->data()->resetPostVars();
        $this->redirect($this->PageByIdentifierCodeLink('SilvercartCartPage'));
        return $this->render();
    }
    
    /**
     * Action to return the cart summary data as JSON.
     * 
     * @param HTTPRequest $request Request
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 02.04.2019
     */
    public function getCartSummaryData() : string
    {
        return json_encode($this->data()->getCartSummary());
    }
    
    /**
     * Action to handle the deletion of option data.
     * Returns the cart summary data as JSON.
     * 
     * @param HTTPRequest $request Request
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 02.04.2019
     */
    public function deleteOptionData(HTTPRequest $request) : string
    {
        return $this->handlePostedOptionData($request);
    }
    
    /**
     * Action to handle the posted option data.
     * Returns the cart summary data as JSON.
     * 
     * @param HTTPRequest $request Request
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 02.04.2019
     */
    public function postOptionData(HTTPRequest $request) : string
    {
        return $this->handlePostedOptionData($request);
    }
    
    /**
     * Handles an option post request.
     * Returns the cart summary data as JSON.
     * 
     * @param HTTPRequest $request Request
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 02.04.2019
     */
    protected function handlePostedOptionData(HTTPRequest $request) : string
    {
        if ($request->isPOST()) {
            $page       = $this->data();
            $step       = $page->getCurrentStep();
            $storedVars = $page->getPostVarsFor($step);
            $optionID   = $request->postVar('OptionID');
            $productID  = $request->postVar('ProductID');
            $quantity   = $request->postVar('Quantity');
            $this->prepareStoredVars($storedVars, $optionID, $productID);
            if (is_numeric($quantity)) {
                $storedVars['StepOptions'][$optionID][$productID]['Select']   = $quantity > 0 ? '1' : '0';
                $storedVars['StepOptions'][$optionID][$productID]['Quantity'] = $quantity;
            } else {
                $storedVars['StepOptions'][$optionID][$productID]['Select'] = '0';
            }
            $page->setPostVarsFor($storedVars, $step);
        }
        return json_encode($this->data()->getCartSummary());
    }
    
    /**
     * Prepars the given $storedVars to fit with the given $optionID and $productID.
     * 
     * @param array &$storedVars Session stored vars
     * @param int   $optionID    Option ID
     * @param int   $productID   Product ID
     * 
     * @return \SilverCart\ProductWizard\Model\Pages\ProductWizardStepPageController
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 02.04.2019
     */
    protected function prepareStoredVars(array &$storedVars, int $optionID, int $productID) : ProductWizardStepPageController
    {
        if (!is_array($storedVars)) {
            $storedVars = [];
        }
        if (!array_key_exists('StepOptions', $storedVars)) {
            $storedVars['StepOptions'] = [];
        }
        if (!array_key_exists($optionID, $storedVars['StepOptions'])) {
            $storedVars['StepOptions'][$optionID] = [];
        }
        if (!array_key_exists($productID, $storedVars['StepOptions'][$optionID])) {
            $storedVars['StepOptions'][$optionID][$productID] = [];
        }
        return $this;
    }
}