<div class="card w-100 mb-4 info-on-hover" data-info-content="{$Text.ATT}" data-info-heading="{$Title.ATT}">
    <div class="card-body">
        <div class="form-group row">
            <label for="StepOptions-{$ID}" class="col-sm-9 col-form-label">{$Title}</label>
            <div class="col-sm-3">
                <input type="number" name="StepOptions[{$ID}]" value="{$Value}" class="form-control" id="StepOptions-{$ID}" placeholder="" required="required">
            </div>
        </div>
    <% if $Text %>
        <span class="fa fa-info-circle p-absolute t-0 l-0 ml-1 mt-1"></span>
    <% end_if %>
    <% if $Product %>
        <% with $Product %>
            {$setCurrentOptionID($Up.ID)}
            <% include SilverCart\ProductWizard\Model\Wizard\ProductBox %>
        <% end_with %>
    <% end_if %>
    </div>
</div>