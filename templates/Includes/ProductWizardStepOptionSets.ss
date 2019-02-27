<div class="row d-flex" id="product-wizard-step">
<% loop $VisibleStepOptionSets %>
    <div class="col-xs-12 col-md-4">
        <h2>{$Title}</h2>
        <% include ProductWizardStepOptionSetOptions %>
    </div>
<% end_loop %>
</div>
<div class="clearfix">
<% if $BackLink %>
    <a class="btn btn-default pull-left" href="{$BackLink}"><span class="fa fa-caret-left"></span> {$ProductWizardStepPage.fieldLabel('Back')}</a>
<% end_if %>
    <button class="btn btn-primary pull-right" type="submit">{$ButtonTitle} <span class="fa fa-caret-right"></span></button>
</div>