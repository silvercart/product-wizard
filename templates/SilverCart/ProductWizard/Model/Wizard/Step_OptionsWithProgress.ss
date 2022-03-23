<form action="{$Link}" method="POST" name="ProductWizardStepForm" id="ProductWizardStepOptionsWithProgress">
    <div class="row" id="product-wizard-step">
    <% if $InfoBoxContent %>
        <div class="col-12">
            <div class="bg-gray-light pt-20 pr-20 pl-30 pb-1 mb-20 p-relative"><span class="fa fa-info-circle p-absolute l-12 t-22"></span> {$InfoBoxContent}</div>
        </div>
    <% end_if %>
        <div class="col-12 col-sm-6 col-md-8 col-xl-9">
            <div class="row" id="product-wizard-step-options">
            <% if $VisibleStepOptions.exists %>
                <% loop $VisibleStepOptions %>
                    <% if $IsProductView && $Products.count > 1 %>
                <div class="col-12 d-flex mb-20">
                    {$Me}
                </div>
                    <% else_if $DisplayType == 'list' %>
                <div class="col-12 d-flex mb-20">
                    {$Me}
                </div>
                    <% else %>
                <div class="col-12 col-md-6 col-xl-4 d-flex mb-20">
                    {$Me}
                </div>
                    <% end_if %>
                <% end_loop %>
            <% end_if %>
            <% if $PreviousStep %>
                <div class="text-left mt-40">
                    <a class="btn btn-outline-blue-dark" href="{$PreviousStep.Link}"><span class="fa fa-angle-double-left"></span> <%t SilverCart\ProductWizard\Model\Pages\ProductWizardStepPage.BackTo 'Back to {step}' step=$PreviousStep.Title %></a>
                </div>
            <% end_if %>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-xl-3">
            <% include SilverCart\ProductWizard\Model\Wizard\CartSummary %>
        </div>
    </div>
</form>