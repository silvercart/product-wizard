<% require css('silvercart/product-wizard:client/css/wizard.css') %>
<% require javascript('silvercart/product-wizard:client/javascript/silvercart.steppedproductwizard.js') %>
<% if not $CurrentStep.StepOptionSets.exists %>
<div class="row bg-white mb-40 pb-20">
<% if $InsertWidgetArea('Sidebar') %>
    <aside class="col-sm-3">
        {$InsertWidgetArea('Sidebar')}
    </aside>
<% end_if %>
    <div class="<% if $InsertWidgetArea('Sidebar') %>col-sm-9<% else %>col-xs-12<% end_if %>">
        <% with $CurrentStep %>
        <form action="{$Link}" method="POST" name="ProductWizardStepForm">
            <% if $StepOptionSets.exists %>
                <% include SilverCart\ProductWizard\Model\Pages\StepOptionSets %>
            <% else %>
                <% include SilverCart\ProductWizard\Model\Pages\StepOptions %>
            <% end_if %>
        </form>
        <% end_with %>
        {$InsertWidgetArea('Content')}
    </div>
</div>
<% else %>
<div class="row bg-white mb-40 pb-20" id="ProductWizardStepMain">
    <aside class="col-xs-12 col-sm-3 col-md-2 d-none" id="ProductWizardStepSidebar">
        <ul class="nav nav-pills nav-stacked mt-60" id="ProductWizardStepOptionSetNavigation">
    <% loop $CurrentStep.VisibleStepOptionSets %>
            <li role="presentation"><a href="javascript:;" data-target="#ProductWizardStepOptionSet{$ID}">{$Title}</a></li>
    <% end_loop %>
            <li role="presentation"><a href="javascript:;"><%t ProductWizard.Overview 'Overview' %></a></li>
        </ul>
    <% if $InsertWidgetArea('Sidebar') %>
        {$InsertWidgetArea('Sidebar')}
    <% end_if %>
    </aside>
    <div class="col-xs-12" data-add-css-class="col-sm-9 col-lg-10" id="ProductWizardStepContent">
        <% with $CurrentStep %>
        <form action="{$Link}" method="POST" name="ProductWizardStepForm">
            <% if $StepOptionSets.exists %>
                <% include SilverCart\ProductWizard\Model\Pages\StepOptionSets %>
            <% else %>
                <% include SilverCart\ProductWizard\Model\Pages\StepOptions %>
            <% end_if %>
        </form>
        <% end_with %>
        {$InsertWidgetArea('Content')}
    </div>
</div>
<% end_if %>