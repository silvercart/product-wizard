<div class="row d-flex d-flex-nowrap" id="product-wizard-step">
    <div class="col-xs-12 col-sm-8 col-md-6">
        <div class="row d-flex" id="product-wizard-step-options">
        <% if $VisibleStepOptions.exists %>
            <% loop $VisibleStepOptions %>
            <div class="col-xxs-12 col-xs-6 d-flex">
                {$forTemplate}
            </div>
            <% end_loop %>
        <% end_if %>
        </div>
        </div>
    <div class="col-xs-12 col-sm-4 col-md-6">
        <div class="info-box mt-15 mb-20">
            <h2 class="info-box-heading" data-original="{$InfoBoxTitle.ATT}">{$InfoBoxTitle}</h2>
            <div class="info-box-content text-lg" data-original="{$InfoBoxContent.ATT}">{$InfoBoxContent}</div>
        </div>
        <div class="clearfix">
        <% if $BackLink %>
            <a class="btn btn-default pull-left" href="{$BackLink}"><span class="fa fa-caret-left"></span> {$ProductWizardStepPage.fieldLabel('Back')}</a>
        <% end_if %>
            <button class="btn btn-primary pull-right" type="submit">{$ButtonTitle} <span class="fa fa-caret-right"></span></button>
        </div>
    </div>
</div>