<label class="d-block cursor-pointer border rounded mt-10 mb-0 p-10 pb-15 p-relative clearfix radio-option-picker {$Checked}" data-option-id="{$StepOption.ID}" data-value="{$Value}" data-behavior="{$Behavior}" for="StepOptions-{$StepOption.ID}-{$Value}" style="min-height: 0;">
    <div class="label pl-50">
    <% if $StepOption.DisplayProductImage && $Product %>
        <img class="float-left mr-15 mt--6 mb--10" src="{$Product.ListImage.Pad(50,50).URL}" />
    <% end_if %>
        <span class="font-weight-bold">{$Title}</span>
    <% if $Product %>
        <% with $Product %>
        <a class="d-inline" href="javascript:;" data-toggle="modal" data-target="#modal-product-{$ID}"><span class="fa fa-info-circle"></span></a>
        <% end_with %>
    <% else_if $LongDescription %>
        <a class="d-inline" href="javascript:;" data-toggle="modal" data-target="#modal-description-{$StepOption.ID}-{$Value}"><span class="fa fa-info-circle"></span></a>
    <% end_if %>
    <% if $Description %>
        <% if $Product %>
            <% with $Product %>
        <br/><a href="javascript:;" class="text-muted" data-toggle="modal" data-target="#modal-product-{$ID}">{$Up.Description}</a>
            <% end_with %>
        <% else_if $LongDescription %>
        <br/><a href="javascript:;" class="text-muted" data-toggle="modal" data-target="#modal-description-{$StepOption.ID}-{$Value}">{$Description}</a>
        <% else %>
        <br/><span class="text-muted">{$Description}</span>
        <% end_if %>
    <% end_if %>
    </div>
    <span class="fa fa-2x fa-check text-blue border border-blue rounded-circle p-6 p-absolute l-6 t-6" data-option-id="{$StepOption.ID}"></span>
    <% if $Product %>
        <% with $Product %>
            <% if not $isInProductChain && $ChainedProductPriceLabel %>
        <a class="p-absolute r-0 t-10 mr-5px" href="javascript:;" data-toggle="modal" data-target="#modal-product-{$ID}">{$ChainedProductPriceLabel} {$PriceNice}</a>
            <% else %>
        <a class="p-absolute r-0 t-10 mr-5px" href="javascript:;" data-toggle="modal" data-target="#modal-product-{$ID}">{$PriceNice}</a>
            <% end_if %>
        <% end_with %>
    <% else_if $LongDescription && not $StepOption.DisableLabelForFree %>
        <a class="p-absolute r-0 t-10 mr-5px" href="javascript:;" data-toggle="modal" data-target="#modal-description-{$StepOption.ID}-{$Value}"><%t ProductWizard.free 'free' %></a>
    <% else_if $LongDescription %>
        <a class="p-absolute r-0 t-10 mr-5px" href="javascript:;" data-toggle="modal" data-target="#modal-description-{$StepOption.ID}-{$Value}"><span class="fa fa-info-circle"></span> <%t ProductWizard.more 'more' %></a>
    <% else_if not $StepOption.DisableLabelForFree %>
        <span class="p-absolute r-0 t-10 mr-5px text-muted"><%t ProductWizard.free 'free' %></span>
    <% end_if %>
</label>