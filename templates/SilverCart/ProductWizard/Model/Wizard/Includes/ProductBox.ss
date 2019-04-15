<div class="card rounded-0 w-100 shadow wizard-option {$CurrentOption.IsOptionalClass} {$CurrentOption.getProductIsSelectedClass($ID)}" id="wizard-option-{$CurrentOption.ID}" data-option-id="{$CurrentOption.ID}" data-product-id="{$ID}">
<% if $CurrentOption.IsProductView %>
    <input type="hidden" name="StepOptions[{$CurrentOption.ID}][{$ID}][Select]" value="{$CurrentOption.getProductSelectValue($ID)}" />
<% end_if %>
    <div class="card-header rounded-0 bg-blue text-white px-10 py-6 wizard-option-picker" style="height: 55px; overflow: hidden;">{$CurrentOption.Title}</div>
    <div class="card-body pt-0 pb-10 px-10 p-relative">
        <span class="fa fa-2x fa-check text-blue border border-blue rounded-circle p-6 p-absolute r-6 t-6 wizard-option-picker"></span>
        <a class="d-inline-block px-40" href="javascript:;" data-toggle="modal" data-target="#modal-product-{$ID}"><img class="img-fluid" alt="{$Title}" src="{$ListImage.Pad(260,220).URL}" /></a>
        <a class="h5 card-title d-inline-block mb-0 text-truncate mw-100" href="javascript:;" data-toggle="modal" data-target="#modal-product-{$ID}" title="{$Title.ATT}">{$Title}</a>
        <% if $hasVariants %>
        <p class="card-text text-muted mb-6 product-description-sm">{$ShortDescription.LimitCharactersToClosestWord(70)}</p>
        <small>{$VariantAttributesNice}:</small>
        <div class="dropdown">
            <button class="btn btn-light btn-block dropdown-toggle px-6" type="button" id="product-variant-dropdown-{$CurrentOption.ID}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="text-truncate d-inline-block mb--8" style="max-width: calc(100% - 14px);">{$Title}</span>
            </button>
            <div class="dropdown-menu w-100 rounded-0 mt--1" aria-labelledby="product-variant-dropdown-{$CurrentOption.ID}">
                <% loop $Variants %>
                <a class="dropdown-item" href="javascript:;" data-product-id="{$ID}">{$Title}</a>
                <% end_loop %>
            </div>
        </div>
        <% else_if $hasChainedProduct %>
        <p class="card-text text-muted mb-16 product-description-md">{$ShortDescription.LimitCharactersToClosestWord(95)}</p>
        <% else %>
        <p class="card-text text-muted mb-16 product-description-lg">{$ShortDescription.LimitCharactersToClosestWord(130)}</p>
        <% end_if %>
        <div class="text-right pt-6">
        <% if $PriceIsLowerThanMsr %>
            <span class="text-line-through text-gray">{$MSRPrice.Nice}</span>
        <% end_if %>
        <% if $CurrentOption.getProductPriceLabel($ID) %>
        <a class="price h3" href="javascript:;" data-toggle="modal" data-target="#modal-product-{$ID}"><small>{$CurrentOption.getProductPriceLabel($ID)}</small> {$PriceNice}</a>
        <% else %>
            <a class="price h3" href="javascript:;" data-toggle="modal" data-target="#modal-product-{$ID}">{$PriceNice}</a>
        <% end_if %>
        </div>
    <% if not $CurrentOption.getProductViewIsReadonly %>
        <hr>
        <% with $CurrentOption %>
        <p class="mb-0 text-center pick-button-label">{$Text}</p>
        <div class="dropdown <% if $ProductQuantityValue > $ProductQuantityDropdownMax %>d-none<% end_if %>" id="pick-quantity-{$ID}">
            <button class="btn btn-primary btn-block dropdown-toggle" type="button" id="product-quantity-dropdown-{$ID}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                {$ProductQuantityDropdownValues.CurrentValue.Quantity} {$ProductQuantityDropdownValues.CurrentValue.Title}
            </button>
            <div class="dropdown-menu w-100 rounded-0 mt--1" aria-labelledby="product-quantity-dropdown-{$ID}">
                <% loop $ProductQuantityDropdownValues.Values %>
                <a class="dropdown-item pick-quantity" href="javascript:;" data-quantity="{$Quantity}">{$Quantity} {$Title}</a>
                <% end_loop %>
                <a class="dropdown-item pick-more-quantity" href="javascript:;" data-option-id="{$ID}"><%t SilverCart\ProductWizard\Model\Wizard\StepOption.moreThanMax 'more than {max} {maxTitle}...' max=$ProductQuantityDropdownMax maxTitle=$ProductQuantityPlural %></a>
            </div>
        </div>
        <div class="spinner-field clearfix text-nowrap <% if $ProductQuantityValue <= $ProductQuantityDropdownMax %>d-none<% end_if %>" id="pick-more-quantity-{$ID}">
            <input type="text" name="StepOptions[{$ID}][{$Up.ID}][Quantity]" value="{$getProductQuantityValue($Up.ID)}" class="pick-more-quantity-field" data-option-id="{$ID}" data-product-id="{$Up.ID}" />
            <a href="javascript:;" style="width: calc(100% - 70px);" class="btn btn-xs btn-primary select-product" data-option-id="{$ID}" data-product-id="{$Up.ID}"><span class="d-inline d-md-none d-lg-inline"><%t ProductWizard.Choose 'Choose' %></span><span class="d-none d-md-inline d-lg-none fa fa-check"></span></a>
        </div>
        <% end_with %>
    <% else_if $CurrentOption.IsOptional %>
        <hr>
        <p class="mb-0 text-center pick-button-label">{$CurrentOption.Text}</p>
        <button class="btn btn-primary btn-block wizard-option-picker" type="button" ><%t ProductWizard.Choose 'Choose' %></button>
        <input type="hidden" name="StepOptions[{$CurrentOption.ID}][{$ID}][Quantity]" value="{$CurrentOption.getProductQuantityValue($ID)}" />
    <% else %>
        <hr>
        <p class="mb-0 text-center">{$CurrentOption.Text}</p>
        <input type="hidden" name="StepOptions[{$CurrentOption.ID}][{$ID}][Quantity]" value="{$CurrentOption.getProductQuantityValue($ID)}" />
    <% end_if %>
    </div>
</div>
<% include SilverCart\ProductWizard\Model\Wizard\ProductDetailModal %>