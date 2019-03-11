<div class="row d-flex" id="product-wizard-step">
<% loop $VisibleStepOptionSets %>
    <div class="col-xs-12 col-md-4 ProductWizardStepOptionSet" id="ProductWizardStepOptionSet{$ID}">
        <div class="ProductWizardStepOptionSetBox">
            <h2 class="option-set-title">{$Title}</h2>
            <% include ProductWizardStepOptionSetOptions %>
        </div>
    </div>
<% end_loop %>
</div>
<div class="clearfix">
<% if $BackLink %>
    <a class="btn btn-default pull-left" href="{$BackLink}" id="ProductWizardStepBackButton"><span class="fa fa-caret-left"></span> {$ProductWizardStepPage.fieldLabel('Back')}</a>
<% end_if %>
    <button class="btn btn-primary pull-right" type="submit">{$ButtonTitle} <span class="fa fa-caret-right"></span></button>
</div>