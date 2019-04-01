<form action="{$Link}" method="POST" name="ProductWizardStepForm">
    <div class="row" id="product-wizard-step">
        <div class="col-12 col-sm-6 col-md-8 col-lg-9">
            <div class="row" id="product-wizard-step-options">
            <% if $VisibleStepOptions.exists %>
                <% loop $VisibleStepOptions %>
                <div class="col-12 col-md-6 col-lg-4 d-flex mb-20">
                    {$Me}
                </div>
                <% end_loop %>
            <% end_if %>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="bg-gray-light p-20 mb-20 sticky">
                <h3><%t SilverCart\ProductWizard\Model\Pages\ProductWizardStepPage.YourChoices 'Your choices' %></h3>
            <% with $ProductWizardStepPage %>
                <% if $NavigationSteps %>
                <div class="pl-10">
                    <% loop $NavigationSteps %>
                        <% if $IsFinished %>
                    <a class="h3 d-block mb-10" href="javascript:;"><span class="fa fa-angle-down"></span> <%t SilverCart\ProductWizard\Model\Pages\ProductWizardStepPage.YourStep 'Your {step}' step=$Title %></a>
                        <% else_if $IsCurrent %>
                    <a class="h3 d-block mb-10 current" href="javascript:;"><span class="fa fa-angle-down"></span> <%t SilverCart\ProductWizard\Model\Pages\ProductWizardStepPage.YourStep 'Your {step}' step=$Title %></a>
                        <% else %>
                    <span class="h3 d-block mb-10 text-muted"><span class="fa fa-angle-down"></span> <%t SilverCart\ProductWizard\Model\Pages\ProductWizardStepPage.YourStep 'Your {step}' step=$Title %></span>
                        <% end_if %>
                    <% end_loop %>
                </div>
                <hr>
                <% end_if %>
            <% end_with %>
                <table class="table table-sm">
                    <tr class="h2 text-blue-dark">
                        <td>einmalig</td>
                        <td class="text-right">1.362,00 €</td>
                    </tr>
                    <tr class="h2 text-blue-dark">
                        <td>monatlich</td>
                        <td class="text-right">234,00 €</td>
                    </tr>
                </table>
                <button class="btn btn-primary btn-block" type="submit">{$ButtonTitle} <span class="fa fa-angle-double-right"></span></button>
            </div>
        </div>
    </div>
</form>