<div class="w-100 {$ExtraClasses}" data-wizard-option-type="{$OptionType}">
<% if $Title %>
    <h2>{$Title}</h2>
<% end_if %>
<% if $Content %>
    {$Content}
<% end_if %>
    <div class="row wizard-option pickable {$IsRadioCheckedClass} {$AllowMultipleChoicesClass}" data-option-id="{$ID}">
<% if $AllowMultipleChoices %>
    <% loop $OptionList %>
        <input class="d-none" type="checkbox" name="StepOptions[{$StepOption.ID}][{$Value}]" data-option-id="{$StepOption.ID}" data-behavior="{$Behavior}" id="StepOptions-{$StepOption.ID}-{$Value}" value="{$Value}" {$Checked}>
    <% end_loop %>
<% else %>
    <% loop $OptionList %>
        <input class="d-none" type="radio" name="StepOptions[{$StepOption.ID}]" data-option-id="{$StepOption.ID}" data-behavior="{$Behavior}" id="StepOptions-{$StepOption.ID}-{$Value}" value="{$Value}" {$Checked} required="required">
    <% end_loop %>
<% end_if %>
        <div class="col-12">
            <table class="table w-100 border shadow">
                <thead>
                    <tr>
                        <th>&nbsp;</th>
                        <th>{$ColumnTitleProducts}</th>
                        <th>&nbsp;</th>
                        <th>{$ProductSingleton.fieldLabel('Art')}</th>
                        <th class="text-right">{$ProductSingleton.fieldLabel('Price')}</th>
                    </tr>
                </thead>
                <tbody>
                <% loop $OptionList %>
                    <% include SilverCart\ProductWizard\Model\Wizard\RadioOptionPicker_TableRow %>
                <% end_loop %>
                </tbody>
            </table>
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


</div>
<div class="col-12 d-flex">
                    <%-- include SilverCart\ProductWizard\Model\Wizard\Radio_ListAlternative --%>