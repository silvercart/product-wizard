<?php

namespace SilverCart\ProductWizard\Model\Pages;

use PageController;
use SilverCart\Model\Product\Product;
use SilverCart\ProductServices\Model\Product\Service;
use SilverCart\ProductWizard\Extensions\Model\Order\ShoppingCartPositionExtension as ProductWizardShoppingCartPosition;
use SilverCart\ProductWizard\Model\Wizard\Step;
use SilverCart\ProductWizard\Model\Wizard\StepOption;
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
        'pickVariant',
        'postOptionData',
        'postPlainOptionData',
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
        if (!$step->isVisible()) {
            $this->redirect($step->NextLink());
            return $this->render();
        }
        if ($request->isPOST()) {
            $postVars = $request->postVars();
            $this->data()->setPostVarsFor($postVars, $step);
            $this->data()->addCompletedStep($step);
            $this->redirect($step->NextLink());
        }
        if ($step->Template === Step::TEMPLATE_REDIRECTION
         && $step->RedirectTo()->exists()
         && $this->redirectedTo() === false
        ) {
            $this->transformToCart();
            $this->redirect("{$step->RedirectTo()->Link()}?{$this->data()->config()->http_get_var_name}={$this->ID}");
            if ($step->RedirectTo()->hasMethod('ShowProductWizardStepNavigation')) {
                $this->data()->setCurrentStep($step);
            }
        } else {
            $this->data()->setCurrentStep($step);
        }
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
        $this->transformToCart();
        if (!$this->redirectedTo()) {
            $this->redirect($this->PageByIdentifierCodeLink('SilvercartCartPage'));
        }
        return $this->render();
    }
    
    /**
     * Executes the cart transformation.
     * 
     * @return void
     */
    public function transformToCart() : void
    {
        $positionIDs = [];
        foreach ($this->data()->Steps() as $step) {
            if (!$step->isVisible()) {
                continue;
            }
            if ($step->StepOptionSets()->exists()) {
                foreach ($step->StepOptionSets() as $optionSet) {
                    foreach ($optionSet->StepOptions() as $option) {
                        $positionIDs = array_merge($positionIDs, $option->executeCartTransformation());
                    }
                }
            } else {
                foreach ($step->StepOptions() as $option) {
                    $positionIDs = array_merge($positionIDs, $option->executeCartTransformation());
                }
            }
        }
        ProductWizardShoppingCartPosition::deleteWizardPositions($this->data(), $positionIDs);
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
     * Action to pick a product variant.
     * 
     * @param HTTPRequest $request Request
     * 
     * @return DBHTMLText
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 23.05.2019
     */
    public function pickVariant(HTTPRequest $request) : DBHTMLText
    {
        $result = DBHTMLText::create();
        if ($request->isPOST()) {
            $optionID   = $request->postVar('OptionID');
            $productID  = $request->postVar('ProductID');
            $variantID  = $request->postVar('VariantID');
            $option     = StepOption::get()->byID($optionID);
            if ($option instanceof StepOption
             && $option->exists()
            ) {
                StepOption::pickVariantBy($optionID, $productID, $variantID);
                $result = $option->forTemplate();
            }
        }
        return $result;
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
     * Action to handle the posted option data.
     * Returns the cart summary data as JSON.
     * 
     * @param HTTPRequest $request Request
     * 
     * @return string
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 05.04.2019
     */
    public function postPlainOptionData(HTTPRequest $request) : string
    {
        if ($request->isPOST()) {
            $page          = $this->data();
            $step          = $page->getCurrentStep();
            $storedVars    = $page->getPostVarsFor($step);
            $postedOptions = $request->postVar('StepOptions');
            if (is_array($postedOptions)) {
                foreach ($postedOptions as $optionID => $optionValue) {
                    $storedVars['StepOptions'][$optionID] = $optionValue;
                }
                $page->setPostVarsFor($storedVars, $step);
            }
        }
        return json_encode($this->data()->getCartSummary());
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
        $services        = [];
        $serviceProducts = [];
        if ($request->isPOST()) {
            $page       = $this->data();
            $step       = $page->getCurrentStep();
            $storedVars = $page->getPostVarsFor($step);
            $optionID   = $request->postVar('OptionID');
            $productID  = $request->postVar('ProductID');
            $quantity   = (int) $request->postVar('Quantity');
            $option     = StepOption::get()->byID($optionID);
            if ($option instanceof StepOption) {
                $services        = $this->handleServicesFor($step, $storedVars, $productID, $quantity);
                $serviceProducts = $this->handleServiceProductsFor($step, $storedVars, $productID, $quantity);
                $this->handleStoredVars($storedVars, $optionID, $productID, $option, $quantity);
            }
            $page->setPostVarsFor($storedVars, $step);
        }
        return json_encode($this->data()->getCartSummary()
                + [
                    'Services'        => $services,
                    'ServiceProducts' => $serviceProducts,
                ]);
    }
    
    /**
     * Handles services within the current step.
     * 
     * @param Step  $step        Step
     * @param array &$storedVars Stored vars
     * @param int   $productID   Product ID
     * @param int   $quantity    Quantity
     * 
     * @return array
     */
    public function handleServicesFor(Step $step, array &$storedVars, int $productID, int $quantity) : array
    {
        $handledServices = [];
        if (class_exists(Service::class)) {
            $product = Product::get()->byID($productID);
            if ($product instanceof Product
             && $product->hasMethod('Services')
             && $product->Services()->exists()
            ) {
                $serviceIDMap = $product->Services()->map('ID', 'ID')->toArray();
                foreach ($step->StepOptions() as $serviceOption) {
                    /* @var $serviceOption StepOption */
                    $services = $serviceOption->Products()->filter('ID', $serviceIDMap);
                    if ($services->exists()) {
                        foreach ($services as $service) {
                            /* @var $service Service */
                            $serviceQuantity = $quantity;
                            if (!$service->IsRequiredForEachServiceProduct) {
                                $serviceQuantity = 1;
                            }
                            $handledServices[$service->ID] = $serviceQuantity;
                            $this->handleStoredVars($storedVars, $serviceOption->ID, $service->ID, $serviceOption, $serviceQuantity);
                        }
                    }
                }
            }
        }
        return $handledServices;
    }
    
    /**
     * Handles service products within the current step.
     * 
     * @param Step  $step        Step
     * @param array &$storedVars Stored vars
     * @param int   $productID   Product ID
     * @param int   $quantity    Quantity
     * 
     * @return array
     */
    public function handleServiceProductsFor(Step $step, array &$storedVars, int $productID, int $quantity) : array
    {
        $handledServices = [];
        if (class_exists(Service::class)) {
            $service = Service::get()->byID($productID);
            if ($service instanceof Service
             && $service->hasMethod('ServiceProducts')
             && $service->ServiceProducts()->exists()
            ) {
                $serviceProductsIDMap = $service->ServiceProducts()->map('ID', 'ID')->toArray();
                foreach ($step->StepOptions() as $serviceOption) {
                    /* @var $serviceOption StepOption */
                    $serviceProducts = $serviceOption->Products()->filter('ID', $serviceProductsIDMap);
                    if ($serviceProducts->exists()) {
                        foreach ($serviceProducts as $serviceProduct) {
                            /* @var $serviceProduct Product */
                            if ($service->IsRequiredForEachServiceProduct) {
                                $handledServices[$serviceProduct->ID] = $quantity;
                                $this->handleStoredVars($storedVars, $serviceOption->ID, $serviceProduct->ID, $serviceOption, $quantity);
                            }
                        }
                    }
                }
            }
        }
        return $handledServices;
    }
    
    /**
     * Handles the session stored vars.
     * 
     * @param array      &$storedVars Stored vars
     * @param int        $optionID    Option ID
     * @param int        $productID   Product ID
     * @param StepOption $option      Option
     * @param int        $quantity    Quantity
     * 
     * @return void
     */
    public function handleStoredVars(array &$storedVars, int $optionID, int $productID, StepOption $option, int $quantity) : void
    {
        $this->prepareStoredVars($storedVars, $optionID, $productID, $option);
        if ($option->OptionType === StepOption::OPTION_TYPE_PRODUCT_VIEW) {
            if (is_numeric($quantity)) {
                $storedVars['StepOptions'][$optionID][$productID]['Select']   = $quantity > 0 ? '1' : '0';
                $storedVars['StepOptions'][$optionID][$productID]['Quantity'] = $quantity;
            } else {
                $storedVars['StepOptions'][$optionID][$productID]['Select'] = '0';
            }
        } elseif ($option->OptionType === StepOption::OPTION_TYPE_RADIO) {
            $pickedOption = $storedVars['StepOptions'][$optionID];
            if (is_numeric($quantity)) {
                $storedVars['StepOptions']['Quantity'][$optionID][$pickedOption] = $quantity;
            } else {
                $storedVars['StepOptions']['Quantity'][$optionID][$pickedOption] = '0';
            }
        }
    }
    
    /**
     * Prepars the given $storedVars to fit with the given $optionID and $productID.
     * 
     * @param array      &$storedVars Session stored vars
     * @param int        $optionID    Option ID
     * @param int        $productID   Product ID
     * @param StepOption $option      Option
     * 
     * @return \SilverCart\ProductWizard\Model\Pages\ProductWizardStepPageController
     * 
     * @author Sebastian Diel <sdiel@pixeltricks.de>
     * @since 02.04.2019
     */
    protected function prepareStoredVars(array &$storedVars, int $optionID, int $productID, StepOption $option) : ProductWizardStepPageController
    {
        if ($option->OptionType === StepOption::OPTION_TYPE_PRODUCT_VIEW) {
            if (!is_array($storedVars)) {
                $storedVars = [];
            }
            if (!array_key_exists('StepOptions', $storedVars)) {
                $storedVars['StepOptions'] = [];
            }
            if (!array_key_exists($optionID, $storedVars['StepOptions'])
             || !is_array($storedVars['StepOptions'][$optionID])
            ) {
                $storedVars['StepOptions'][$optionID] = [];
            }
            if (!array_key_exists($productID, $storedVars['StepOptions'][$optionID])) {
                $storedVars['StepOptions'][$optionID][$productID] = [];
            }
        } elseif ($option->OptionType === StepOption::OPTION_TYPE_RADIO) {
            if (!is_array($storedVars)) {
                $storedVars = [];
            }
            if (!array_key_exists('StepOptions', $storedVars)) {
                $storedVars['StepOptions'] = [];
            }
            if (!array_key_exists('Quantity', $storedVars['StepOptions'])) {
                $storedVars['StepOptions']['Quantity'] = [];
            }
            if (!array_key_exists($optionID, $storedVars['StepOptions']['Quantity'])) {
                $storedVars['StepOptions']['Quantity'][$optionID] = [];
            }
        }
        return $this;
    }
}