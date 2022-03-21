<div class="card rounded-0 w-100 shadow wizard-option {$ExtraClasses}" data-wizard-option-type="{$OptionType}">
    <div class="card-header rounded-0 bg-blue text-white px-10 py-6">{$Title}</div>
    <div class="card-body pt-0 pb-10 px-10 p-relative">
        <div class="mt-10">{$Content}</div>
        <div class="form-group row">
            <div class="col-sm-12">
                <input type="text" name="StepOptions[{$ID}]" value="{$Value}" class="form-control" id="StepOptions-{$ID}" placeholder="" required="required">
            </div>
        </div>
    <% if $Text %>
        <hr>
        <p class="mb-0 text-center">{$Text}</p>
    <% end_if %>
    </div>
</div>