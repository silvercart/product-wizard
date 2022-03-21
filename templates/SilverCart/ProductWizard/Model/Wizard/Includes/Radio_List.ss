<div class="w-100 {$ExtraClasses}" data-wizard-option-type="{$OptionType}">
    <h2>{$Title}</h2>
    {$Content}
    <div class="card rounded-0 w-100 shadow wizard-option pickable {$IsRadioCheckedClass} {$AllowMultipleChoicesClass}" data-option-id="{$ID}">
    <% if $AllowMultipleChoices %>
        <% loop $OptionList %>
            <input class="d-none" type="checkbox" name="StepOptions[{$StepOption.ID}][{$Value}]" data-option-id="{$StepOption.ID}" data-behavior="{$Behavior}" id="StepOptions-{$StepOption.ID}-{$Value}" value="{$Value}" {$Checked}>
        <% end_loop %>
    <% else %>
        <% loop $OptionList %>
            <input class="d-none" type="radio" name="StepOptions[{$StepOption.ID}]" data-option-id="{$StepOption.ID}" data-behavior="{$Behavior}" id="StepOptions-{$StepOption.ID}-{$Value}" value="{$Value}" {$Checked} required="required">
        <% end_loop %>
    <% end_if %>
        <div class="card-body pt-0 pb-10 px-10 p-relative">
            <div <% if $OptionList.count > 3 %>class="has-additional-options"<% end_if %>>
        <% if $CheckedOptions %>
            <% loop $CheckedOptions %>
                <% include SilverCart\ProductWizard\Model\Wizard\RadioOptionPicker_List %>
            <% end_loop %>
        <% end_if %>
        <% loop $OptionList.filter('Checked', '') %>
            <% include SilverCart\ProductWizard\Model\Wizard\RadioOptionPicker_List %>
        <% end_loop %>
            </div>
        <% if $OptionList.count > 3 %>
            <div class="btn-additional-options" data-toggle="modal-sidebar" data-target="#modal-sidebar-product-wizard-option-{$ID}"><span class="fa fa-plus-circle"></span> <%t ProductWizard.ShowAdditionalOptions 'Show additional options' %> <span class="fa fa-chevron-right"></span></div>
        <% end_if %>
        <% if $Text %>
            <hr>
            <p class="mb-0 text-center">{$Text}</p>
            <a href="#" class="btn btn-secondary btn-block">{$ButtonTitle}</a>
        <% end_if %>
        <% loop $OptionList %>
            <% if $Up.hasCustomQuantity($Value) %>
            <div class="product-quantity-picker <% if not $IsChecked %>d-none<% end_if %> pos-{$Value}" data-option-value-id="{$Up.ID}-{$Value}" data-option-value="{$Value}" data-product-id="{$Product.ID}">
                <hr>
                <p class="mb-0 text-center pick-button-label">{$Up.getRadioOptionQuantityDropdownText($Value)}</p>
                <div class="dropdown <% if $Up.getProductQuantityValue($Value) >= $Up.getRadioQuantityDropdownMax($Value) %>d-none<% end_if %>" id="pick-quantity-{$StepOption.ID}-{$Value}">
                    <% with $Up.getRadioOptionQuantityDropdownValues($Value) %>
                    <button class="btn btn-primary btn-block dropdown-toggle" type="button" id="product-quantity-dropdown-{$Up.ID}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        {$CurrentValue.Quantity} {$CurrentValue.Title}
                    </button>
                    <div class="dropdown-menu w-100 rounded-0 mt--1" aria-labelledby="product-quantity-dropdown-{$Up.ID}">
                        <% loop $Values %>
                        <a class="dropdown-item pick-quantity" href="javascript:;" data-quantity="{$Quantity}">{$Quantity} {$Title}</a>
                        <% end_loop %>
                    <% end_with %>
                        <a class="dropdown-item pick-more-quantity" href="javascript:;" data-option-id="{$Up.ID}" data-option-value="{$Value}"><%t SilverCart\ProductWizard\Model\Wizard\StepOption.moreThanMax 'more than {max} {maxTitle}...' max=$Up.ProductQuantityDropdownMax maxTitle=$Up.ProductQuantityPlural %></a>
                    </div>
                </div>
                <div class="spinner-field clearfix text-nowrap <% if $Up.getProductQuantityValue($Value) < $Up.getRadioQuantityDropdownMax($Value) %>d-none<% end_if %>" id="pick-more-quantity-{$Up.ID}-{$Value}">
                    <input type="text" name="StepOptions[Quantity][{$Up.ID}][{$Value}]" value="{$Up.getProductQuantityValue($Value)}" class="pick-more-quantity-field" data-option-id="{$Up.ID}" data-product-id="{$Product.ID}" />
                    <a href="javascript:;" style="width: calc(100% - 70px);" class="btn btn-xs btn-primary select-product" data-option-id="{$Up.ID}" data-product-id="{$Product.ID}"><span class="d-inline d-md-none d-lg-inline"><%t ProductWizard.Choose 'Choose' %></span><span class="d-none d-md-inline d-lg-none fa fa-check"></span></a>
                </div>
            </div>
            <% end_if %>
        <% end_loop %>
        </div>
    </div>
</div>


<% loop $OptionList %>
    <% if $Product %>
        <% with $Product %>
            {$setCurrentOptionID}
            <% include SilverCart\ProductWizard\Model\Wizard\ProductDetailModal %>
        <% end_with %>
    <% else_if $LongDescription %>
        <% include SilverCart\ProductWizard\Model\Wizard\OptionDescriptionModal %>
    <% end_if %>
<% end_loop %>
<% if $OptionList.count > 3 %>
    <% include SilverCart\ProductWizard\Model\Wizard\RadioOptionModalSidebar %>
<% end_if %>