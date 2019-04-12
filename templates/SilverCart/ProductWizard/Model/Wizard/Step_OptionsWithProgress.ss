<form action="{$Link}" method="POST" name="ProductWizardStepForm" id="ProductWizardStepOptionsWithProgress">
    <div class="row" id="product-wizard-step">
        <div class="col-12 col-sm-6 col-md-8 col-xl-9">
            <div class="row" id="product-wizard-step-options">
            <% if $VisibleStepOptions.exists %>
                <% loop $VisibleStepOptions %>
                <div class="col-12 col-md-6 col-xl-4 d-flex mb-20">
                    {$Me}
                </div>
                <% end_loop %>
            <% end_if %>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-xl-3">
            <div class="bg-gray-light p-20 mb-20 sticky" id="ProductWizardCartSummary">
                <h3><%t SilverCart\ProductWizard\Model\Pages\ProductWizardStepPage.YourChoices 'Your choices' %></h3>
            <% with $ProductWizardStepPage %>
                <% if $NavigationSteps %>
                <div id="ProductWizardCartSummaryPositions">
                    <% loop $NavigationSteps %>
                        <% if $IsCurrent %>
                    <a class="h3 d-block mb-10 ml-10 current" href="javascript:;" data-step-id="{$ID}"><span class="fa fa-angle-right"></span> {$Title}</a>
                        <% else_if $IsFinished %>
                    <a class="h3 d-block mb-10 ml-10" href="javascript:;" data-step-id="{$ID}"><span class="fa fa-angle-right"></span> {$Title}</a>
                        <% else %>
                    <span class="h3 d-block mb-10 ml-10 text-muted"><span class="fa fa-angle-right"></span> {$Title}</span>
                        <% end_if %>
                    <div class="mt--5 mb-10 clearfix" style="display: none;" id="ProductWizardCartSummaryStep-{$ID}"></div>
                    <% end_loop %>
                </div>
                <hr>
                <% end_if %>
            <% end_with %>
                <table class="table table-sm" id="ProductWizardCartSummaryAmounts">
                    <% if $ProductWizardStepPage.CartSummaryForTemplate.Amounts %>
                        <% loop $ProductWizardStepPage.CartSummaryForTemplate.Amounts %>
                    <tr class="h2 text-blue-dark">
                        <td>{$Interval}</td>
                        <td class="text-right">{$Nice}</td>
                    </tr>
                        <% end_loop %>
                    <% else %>
                    <tr class="h2 text-blue-dark">
                        <td><%t SilverCart\Model\Pages\Page.TOTAL 'Total' %></td>
                        <td class="text-right">0,00 €</td>
                    </tr>
                    <% end_if %>
                </table>
                <button class="btn btn-primary btn-block" type="submit">{$ButtonTitle} <span class="fa fa-angle-double-right"></span></button>
            </div>
        </div>
    </div>
</form>