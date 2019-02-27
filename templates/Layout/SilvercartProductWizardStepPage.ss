<% require css('silvercart-product-wizard/client/css/wizard.css') %>
<% require javascript('silvercart-product-wizard/client/javascript/silvercart.steppedproductwizard.js') %>
<div class="row bg-white mb-40 pb-20">
<% if $InsertWidgetArea('Sidebar') %>
    <aside class="col-sm-3">
        {$InsertWidgetArea('Sidebar')}
    </aside>
<% end_if %>
    <div class="<% if $InsertWidgetArea('Sidebar') %>col-sm-9<% else %>col-xs-12<% end_if %>">
        <h2>{$Title}</h2>
        {$Content}
        <% with $CurrentStep %>
        {$Content}
        <form action="{$Link}" method="POST" name="ProductWizardStepForm">
            <% if $StepOptionSets.exists %>
                <% include ProductWizardStepOptionSets %>
            <% else %>
                <% include ProductWizardStepOptions %>
            <% end_if %>
        </form>
        <% end_with %>
        {$InsertWidgetArea('Content')}
    </div>
</div>