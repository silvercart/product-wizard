<div class="card w-100 mb-4 info-on-hover" data-info-content="{$Text.ATT}" data-info-heading="{$Title.ATT}">
    <div class="card-body">
        <h5 class="card-title">{$Title}</h5>
    <% loop $OptionList %>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="StepOptions[{$StepOption.ID}]" id="StepOptions-{$StepOption.ID}-{$Value}" value="{$Value}" {$Checked} required="required">
            <label class="form-check-label" for="StepOptions-{$StepOption.ID}-{$Value}">{$Title}
            <% if $Product %>
                <% with $Product %>
                <a class="d-inline" href="javascript:;" data-toggle="modal" data-target="#modal-{$ID}"><span class="fa fa-info-circle"></span></a>
                <% end_with %>
            <% end_if %>
            </label>
        </div>
        <% if $Product %>
            <% with $Product %>
                {$setCurrentOptionID}
                <% include SilverCart\ProductWizard\Model\Wizard\ProductDetailModal %>
            <% end_with %>
        <% end_if %>
    <% end_loop %>
    <% if $Text %>
        <span class="fa fa-info-circle p-absolute t-0 l-0 ml-1 mt-1"></span>
    <% end_if %>
    </div>
</div>