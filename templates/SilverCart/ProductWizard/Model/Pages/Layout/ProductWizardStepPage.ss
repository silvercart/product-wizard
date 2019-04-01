<% require css('silvercart/product-wizard:client/css/wizard.css') %>
<% require javascript('silvercart/product-wizard:client/javascript/silvercart.steppedproductwizard.js') %>
<div class="row py-20 bg-white">
<% if $CurrentStep.ShowInStepNavigation %>
    <div class="col-12">
        <% include SilverCart\ProductWizard\Model\Pages\StepNavigation %>
    </div>
<% end_if %>
<% if $InsertWidgetArea('Sidebar') %>
    <aside class="col-12 col-sm-3">
        {$InsertWidgetArea('Sidebar')}
    </aside>
<% end_if %>
    <div class="col-12 <% if $InsertWidgetArea('Sidebar') %>col-sm-9<% end_if %>">
        {$CurrentStep}
        {$InsertWidgetArea('Content')}
    </div>
</div>