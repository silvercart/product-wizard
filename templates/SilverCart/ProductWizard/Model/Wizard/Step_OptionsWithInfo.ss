<form action="{$Link}" method="POST" name="ProductWizardStepForm" id="ProductWizardStepOptionsWithInfo">
    <% if $DescriptionTitle %><h1>{$DescriptionTitle}</h1><% end_if %>
    <% if $DescriptionContent %>{$DescriptionContent}<hr/><% end_if %>
    <div class="row" id="product-wizard-step">
        <div class="d-flex col-xs-12 col-sm-8 col-md-6">
            <div class="row" id="product-wizard-step-options">
            <% if $VisibleStepOptions.exists %>
                <% loop $VisibleStepOptions %>
                <div class="d-flex col-xxs-12 col-6">
                    {$forTemplate}
                </div>
                <% end_loop %>
            <% end_if %>
            </div>
        </div>
        <div class="d-flex col-xs-12 col-sm-4 col-md-6">
            <div>
                <div class="info-box mt-15 mb-20">
                    <h2 class="info-box-heading" data-original="{$InfoBoxTitle.ATT}">{$InfoBoxTitle}</h2>
                    <div class="info-box-content text-lg" data-original="{$InfoBoxContent.ATT}">{$InfoBoxContent}</div>
                </div>
                <div class="clearfix">
                <% if $BackLink %>
                    <a class="btn btn-default float-left" href="{$BackLink}"><span class="fa fa-caret-left"></span> {$ProductWizardStepPage.fieldLabel('Back')}</a>
                <% end_if %>
                    <button class="btn btn-primary float-right" type="submit">{$ButtonTitle} <span class="fa fa-caret-right"></span></button>
                </div>
            </div>
        </div>
    </div>
</form>