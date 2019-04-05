<label class="d-block cursor-pointer border rounded mt-10 p-10 p-relative clearfix radio-option-picker {$Checked}" data-option-id="{$StepOption.ID}" data-value="{$Value}" for="StepOptions-{$StepOption.ID}-{$Value}">
    <div class="label">{$Title}
    <% if $Product %>
        <% with $Product %>
        <a class="d-inline" href="javascript:;" data-toggle="modal" data-target="#modal-product-{$ID}"><span class="fa fa-info-circle"></span></a>
        <% end_with %>
    <% end_if %>
    <% if $Description %><br/><span class="text-muted">{$Description}</span><% end_if %>
    </div>
    <span class="fa fa-2x fa-check text-blue border border-blue rounded-circle p-6 p-absolute r-6 t-6" data-option-id="{$StepOption.ID}"></span>
    <% if $Product %>
        <% with $Product %>
        <a class="p-absolute r-0 b-0 mr-5px" href="javascript:;" data-toggle="modal" data-target="#modal-product-{$ID}">{$PriceNice}</a>
        <% end_with %>
    <% end_if %>
</label>