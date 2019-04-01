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
}