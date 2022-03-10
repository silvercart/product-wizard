<tr class="{$CurrentOption.getProductIsSelectedClass($ID)} wizard-option" data-option-id="{$CurrentOption.ID}" data-product-id="{$ID}" data-service-ids="{$ServicesAsIDString}">
    <td>
    <% if not $CurrentOption.getProductViewIsReadonly %>
        <div class="spinner-field spinner-field-xs-">
            <input class="form-control text-right update-quantity-on-change" type="number" name="StepOptions[{$CurrentOption.ID}][{$ID}][Quantity]" value="{$CurrentOption.getProductQuantityValue($ID)}" />
        </div>
    <% end_if %>
    </td>
    <td>
    <% if $LongDescription %>
        <a href="javascript:;" data-toggle="modal" data-target="#modal-product-{$ID}"><img class="img-fluid" src="{$ListImage.Pad(94,94).URL}" alt="{$Title}" /></a>
    <% else %>
        <img class="img-fluid" src="{$ListImage.Pad(94,94).URL}" alt="{$Title}" />
    <% end_if %>
    </td>
    <td class="">
        <input type="hidden" name="StepOptions[{$CurrentOption.ID}][{$ID}][Select]" value="{$CurrentOption.getProductSelectValue($ID)}" />
    <% if $LongDescription %>
        <a href="javascript:;" data-toggle="modal" data-target="#modal-product-{$ID}">{$Title}</a>
    <% else %>
        {$Title}
    <% end_if %>
    <% if $LongDescription %>
        <% include SilverCart\ProductWizard\Model\Wizard\ProductDetailModal %>
    <% end_if %>
    </td>
    <td>{$ProductGroup.Title}</td>
    <td class="text-right">
    <% if $PriceIsLowerThanMsr %>
        <span class="text-line-through text-gray">{$MSRPrice.Nice}</span><br/>
    <% end_if %>
        <span class="text-lg font-weight-normal lh-15">{$PriceNice}</span>
        <br/>
        <% if $CurrentPage.showPricesGross %>
            <small class="text-gray"><%t SilverCart.InclTax 'incl. {amount}% VAT' amount=$TaxRate %></small>
        <% else_if $CurrentPage.showPricesNet %>
            <small class="text-gray"><%t SilverCart.PlusTax 'plus {amount}% VAT' amount=$TaxRate %></small>
        <% end_if %>
    </td>
</tr>