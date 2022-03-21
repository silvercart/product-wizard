<div class="card rounded-0 w-100 h-100 shadow wizard-option {$CurrentOption.getProductIsSelectedClass($ID)}" id="wizard-option-{$CurrentOption.ID}-{$ID}" data-option-id="{$CurrentOption.ID}" data-product-id="{$ID}" data-service-ids="{$ServicesAsIDString}" data-post-callback="{$CurrentOption.JSPostCallback}">
    <input type="hidden" name="StepOptions[{$CurrentOption.ID}][{$ID}][Select]" value="{$CurrentOption.getProductSelectValue($ID)}" />
    <input type="hidden" name="StepOptions[{$CurrentOption.ID}][{$ID}][Quantity]" value="{$CurrentOption.getProductQuantityValue($ID)}" />
<% if $LongDescription %>
    <div class="card-header rounded-0 px-20 py-10 border-bottom-0 h3 wizard-option-picker"><a href="javascript:;" data-toggle="modal" data-target="#modal-product-{$ID}">{$Title}</a></div>
<% else %>
    <div class="card-header rounded-0 px-20 py-10 border-bottom-0 h3 wizard-option-picker">{$Title}</div>
<% end_if %>
    <div class="card-body pt-0 pb-10 px-10 p-relative">
        <div class="text-center">
        <% if $LongDescription %>
            <a href="javascript:;" data-toggle="modal" data-target="#modal-product-{$ID}"><img class="img-fluid" src="{$ListImage.Pad(240,150).URL}" alt="{$Title}" /></a>
        <% else %>
            <img class="img-fluid" src="{$ListImage.Pad(240,150).URL}" alt="{$Title}" />
        <% end_if %>
        </div>
        <div class="pb-40 pt-44"></div>
        <div class="p-absolute b-10" style="width:calc(100% - 20px);">
            <div class="my-3 text-center">
                <span class="text-lg font-weight-normal lh-15">{$PriceNice}</span>
                <br/>
                <% if $CurrentPage.showPricesGross %>
                    <small class="text-gray"><%t SilverCart.InclTax 'incl. {amount}% VAT' amount=$TaxRate %></small>
                <% else_if $CurrentPage.showPricesNet %>
                    <small class="text-gray"><%t SilverCart.PlusTax 'plus {amount}% VAT' amount=$TaxRate %></small>
                <% end_if %>
            <% if $PriceIsLowerThanMsr %>
                <span class="text-line-through text-gray">{$MSRPrice.Nice}</span>
            <% end_if %>
            </div>
        <% if not $CurrentOption.getProductViewIsReadonly %>
            <% if $CurrentOption.getProductSelectValue($ID) == 1 %>
            <a href="javascript:;" class="btn btn-primary btn-block select-product-option" data-option-id="{$CurrentOption.ID}" data-product-id="{$ID}" data-alternate-label="<%t ProductWizard.Choose 'Choose' %>"><%t ProductWizard.Chosen 'Chosen' %></a>
            <% else %>
            <a href="javascript:;" class="btn btn-outline-primary btn-block select-product-option" data-option-id="{$CurrentOption.ID}" data-product-id="{$ID}" data-alternate-label="<%t ProductWizard.Chosen 'Chosen' %>"><%t ProductWizard.Choose 'Choose' %></a>
            <% end_if %>
        <% end_if %>
        </div>
    </div>
</div>
<% if $LongDescription %>
    <% include SilverCart\ProductWizard\Model\Wizard\ProductDetailModal %>
<% end_if %>