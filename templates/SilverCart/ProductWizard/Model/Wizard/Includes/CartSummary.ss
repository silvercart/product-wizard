<div class="sticky">
    <div class="bg-gray-light p-20 mb-20" id="ProductWizardCartSummary" data-base-url="{$CurrentPage.Link}">
        <h3><%t SilverCart\ProductWizard\Model\Pages\ProductWizardStepPage.YourChoices 'Your choices' %></h3>
    <% with $ProductWizardStepPage %>
        <% if $NavigationSteps %>
        <div id="ProductWizardCartSummaryPositions">
            <% loop $NavigationSteps %>
                <% if $IsCurrent %>
            <a class="text-lg d-block mb-10 ml-10 current" href="javascript:;" data-step-id="{$ID}"><span class="fa fa-angle-right"></span> {$Title}</a>
                <% else_if $IsFinished %>
            <a class="text-lg d-block mb-10 ml-10" href="javascript:;" data-step-id="{$ID}"><span class="fa fa-angle-right"></span> {$Title}</a>
                <% else %>
            <span class="text-lg d-block mb-10 ml-10 text-muted"><span class="fa fa-angle-right"></span> {$Title}</span>
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
            <tr class="text-lg text-blue-dark">
                <td>{$Interval}</td>
                <td class="text-right">{$Nice}</td>
            </tr>
                <% end_loop %>
            <% else %>
            <tr class="text-lg text-blue-dark">
                <td><%t SilverCart\Model\Pages\Page.TOTAL 'Total' %></td>
                <td class="text-right">0,00 €</td>
            </tr>
            <% end_if %>
        </table>
        <% if $CurrentPage.HasValidationErrors %>
        <div class="alert alert-danger alert-submit-button-error-message">
            <span class="fa fa-exclamation-circle"></span>
            <% loop $CurrentPage.ValidationErrors %>
                {$Message}<br/>
            <% end_loop %>
        </div>
        <% end_if %>
        <% if not $ProductWizardStepPage.HideCartSummarySubmitButton %>
            <button class="btn btn-primary btn-block" type="submit">{$ButtonTitle} <span class="fa fa-angle-double-right"></span></button>
        <% end_if %>
    </div>
    {$ProductWizardStepPage.AfterProductWizardCartSummaryContent}
</div>