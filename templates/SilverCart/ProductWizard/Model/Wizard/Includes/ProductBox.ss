<div class="product-box row {$CurrentOption.getProductIsSelectedClass($ID)}">
    <% if $CurrentOption.IsProductView %>
    <input type="hidden" name="StepOptions[{$CurrentOption.ID}][{$ID}][Select]" value="{$CurrentOption.getProductSelectValue($ID)}" />
    <% end_if %>
    <div class="col-4">
    <% if $LongDescription %>
        <a href="javascript:;" data-toggle="modal" data-target="#modal-{$ID}"><img class="img-fluid" src="{$ListImage.Pad(214,145).URL}" alt="{$Title}" /></a>
    <% else %>
        <img class="img-fluid" src="{$ListImage.Pad(214,145).URL}" alt="{$Title}" />
    <% end_if %>
    </div>
    <div class="col-8">
    <% if not $CurrentOption.getProductViewIsReadonly %>
        <div class="spinner-field spinner-field-xs clearfix my-5 text-nowrap">
            <input type="text" name="StepOptions[{$CurrentOption.ID}][{$ID}][Quantity]" value="{$CurrentOption.getProductQuantityValue($ID)}" />
            <a href="javascript:;" class="btn btn-xs btn-primary select-product" data-option-id="{$CurrentOption.ID}" data-product-id="{$ID}"><span class="d-inline d-md-none d-lg-inline"><%t ProductWizard.Choose 'Choose' %></span><span class="d-none d-md-inline d-lg-none fa fa-check"></span></a>
        </div>
    <% end_if %>
    <% if $LongDescription %>
        <a href="javascript:;" data-toggle="modal" data-target="#modal-{$ID}">{$Title}</a>
    <% else %>
        {$Title}
    <% end_if %>
        <div class="mt-10">
            <span class="text-lg font-weight-normal lh-15">{$PriceNice}</span>
        <% if $PriceIsLowerThanMsr %>
            <span class="text-line-through text-gray">{$MSRPrice.Nice}</span>
        <% end_if %>
        </div>
    <% if $CurrentOption.IsProductView %>
        <% if $CurrentOption.getProductViewIsReadonly %>
        <span class="fa fa-check p-absolute b-0 r-0 mr-1 mb-1 text-lg d-block"></span>
        <% else %>
        <span class="fa fa-check p-absolute b-0 r-0 mr-1 mb-1 text-lg text-white"></span>
        <% end_if %>
    <% end_if %>
    </div>
</div>
<% if $LongDescription %>
    <% include SilverCart\ProductWizard\Model\Wizard\ProductDetailModal %>
<% end_if %>