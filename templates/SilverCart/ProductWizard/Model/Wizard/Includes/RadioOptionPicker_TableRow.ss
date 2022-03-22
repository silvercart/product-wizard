<% if $Product %>
<tr class="radio-option-picker {$Checked}" data-option-id="{$StepOption.ID}" data-value="{$Value}" data-behavior="{$Behavior}" data-label-for="StepOptions-{$StepOption.ID}-{$Value}">
    <td>
        <span class="fa fa-2x fa-check text-blue border border-blue rounded-circle p-6 cursor-pointer" data-option-id="{$StepOption.ID}"></span>
    </td>
    <td>
<% with $Product %>
        <a href="javascript:;" data-toggle="modal" data-target="#modal-product-{$ID}"><img class="img-fluid-" src="{$ListImage.Pad(94,94).URL}" alt="{$Title}" /></a>
    </td>
    <td class="">
        <a href="javascript:;" data-toggle="modal" data-target="#modal-product-{$ID}">{$Title}</a>
    </td>
    <td>{$ProductGroup.Title}</td>
    <td class="text-right">
    <% if $PriceIsLowerThanMsr %>
        <span class="text-line-through text-gray">{$MSRPrice.Nice}</span><br/>
    <% end_if %>
        <span class="text-lg font-weight-normal lh-15">{$PriceNice}</span>
        <br/>
        <% if $CurrentPage.showPricesGross %>
            <small class="text-gray text-nowrap"><%t SilverCart.InclTax 'incl. {amount}% VAT' amount=$TaxRate %></small>
        <% else_if $CurrentPage.showPricesNet %>
            <small class="text-gray text-nowrap"><%t SilverCart.PlusTax 'plus {amount}% VAT' amount=$TaxRate %></small>
        <% end_if %>
    </td>
<% end_with %>
</tr>
<% else %>
<tr class="radio-option-picker {$Checked}" data-option-id="{$StepOption.ID}" data-value="{$Value}" data-behavior="{$Behavior}" data-label-for="StepOptions-{$StepOption.ID}-{$Value}">
    <td>
        <span class="fa fa-2x fa-check text-blue border border-blue rounded-circle p-6 cursor-pointer" data-option-id="{$StepOption.ID}"></span>
    </td>
    <td>&nbsp;</td>
    <td class="">
    <% if $LongDescription %>
        <a href="javascript:;" data-toggle="modal" data-target="#modal-product-{$ID}">{$Title}</a>
    <% else %>
        {$Title}
    <% end_if %>
    </td>
    <td>{$Description}</td>
    <td class="text-right">
    <% if $LongDescription && not $StepOption.DisableLabelForFree %>
        <a class="text-lg font-weight-normal lh-15" href="javascript:;" data-toggle="modal" data-target="#modal-description-{$StepOption.ID}-{$Value}"><%t ProductWizard.free 'free' %></a>
    <% else_if $LongDescription %>
        <a class="text-lg font-weight-normal lh-15" href="javascript:;" data-toggle="modal" data-target="#modal-description-{$StepOption.ID}-{$Value}"><span class="fa fa-info-circle"></span> <%t ProductWizard.more 'more' %></a>
    <% else_if not $StepOption.DisableLabelForFree %>
        <span class="text-lg font-weight-normal lh-15 text-muted"><%t ProductWizard.free 'free' %></span>
    <% end_if %>
    </td>
</tr>
<% end_if %>