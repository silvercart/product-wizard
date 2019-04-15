<label class="d-block cursor-pointer border rounded mt-10 mb-0 p-10 p-relative clearfix radio-option-picker {$Checked}" data-option-id="{$StepOption.ID}" data-value="{$Value}" for="StepOptions-{$StepOption.ID}-{$Value}">
    <div class="label"><span class="font-weight-bold">{$Title}</span>
    <% if $Product %>
        <% with $Product %>
        <a class="d-inline" href="javascript:;" data-toggle="modal" data-target="#modal-product-{$ID}"><span class="fa fa-info-circle"></span></a>
        <% end_with %>
    <% else_if $LongDescription %>
        <a class="d-inline" href="javascript:;" data-toggle="modal" data-target="#modal-description-{$ID}"><span class="fa fa-info-circle"></span></a>
    <% end_if %>
    <% if $Description %>
        <% if $Product %>
            <% with $Product %>
        <br/><a href="javascript:;" class="text-muted" data-toggle="modal" data-target="#modal-product-{$ID}">{$Up.Description}</a>
            <% end_with %>
        <% else_if $LongDescription %>
        <br/><a href="javascript:;" class="text-muted" data-toggle="modal" data-target="#modal-description-{$ID}">{$Description}</a>
        <% else %>
        <br/><span class="text-muted">{$Description}</span>
        <% end_if %>
    <% end_if %>
    </div>
    <span class="fa fa-2x fa-check text-blue border border-blue rounded-circle p-6 p-absolute r-6 t-6" data-option-id="{$StepOption.ID}"></span>
    <% if $Product %>
        <% with $Product %>
        <a class="p-absolute r-0 b-0 mr-5px" href="javascript:;" data-toggle="modal" data-target="#modal-product-{$ID}">{$PriceNice}</a>
        <% end_with %>
    <% else_if $LongDescription %>
        <a class="p-absolute r-0 b-0 mr-5px" href="javascript:;" data-toggle="modal" data-target="#modal-description-{$ID}"><%t ProductWizard.MoreInformation 'more information' %></a>
    <% end_if %>
</label>