<div class="product-box {$CurrentOption.getProductIsSelectedClass($ID)}">
    <input type="hidden" name="StepOptions[{$CurrentOption.ID}][{$ID}][Select]" value="{$CurrentOption.getProductSelectValue($ID)}" />
<% if $LongDescription %>
    <a href="javascript:;" data-toggle="modal" data-target="#modal-{$ID}"><img class="img-fluid" src="{$ListImage.Pad(214,145).URL}" alt="{$Title}" /></a>
<% else %>
    <img class="img-fluid" src="{$ListImage.Pad(214,145).URL}" alt="{$Title}" />
<% end_if %>
<% if not $CurrentOption.getProductViewIsReadonly %>
    <div class="input-group input-group-sm spinner-field spinner-field-xs clearfix my-2 text-nowrap">
        <input class="form-control text-right" type="number" name="StepOptions[{$CurrentOption.ID}][{$ID}][Quantity]" value="{$CurrentOption.getProductQuantityValue($ID)}" />
        <span class="input-group-append">
            <a href="javascript:;" class="btn btn-primary select-product" data-option-id="{$CurrentOption.ID}" data-product-id="{$ID}"><span class="d-inline d-md-none d-lg-inline"><%t ProductWizard.Choose 'Choose' %></span><span class="d-none d-md-inline d-lg-none fa fa-check"></span></a>
        </span>
    </div>
<% end_if %>
<% if $LongDescription %>
    <a href="javascript:;" data-toggle="modal" data-target="#modal-{$ID}">{$Title}</a>
<% else %>
    {$Title}
<% end_if %>
    <div class="mt-1">
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
<% if $LongDescription %>
    <% include SilverCart\ProductWizard\Model\Wizard\ProductDetailModal %>
<% end_if %>