<div class="card rounded-0 w-100 shadow">
    <div class="card-header rounded-0 bg-blue text-white px-10 py-6" style="height: 55px; overflow: hidden;">{$Title}</div>
    <% loop $OptionList %>
        <input class="d-none" type="radio" name="StepOptions[{$StepOption.ID}]" data-option-id="{$StepOption.ID}" id="StepOptions-{$StepOption.ID}-{$Value}" value="{$Value}" {$Checked} required="required">
    <% end_loop %>
    <div class="card-body pt-0 pb-10 px-10 p-relative">
        <div>
    <% if $CheckedOption %>
        <% with $CheckedOption %>
            <% include SilverCart\ProductWizard\Model\Wizard\RadioOptionPicker %>
        <% end_with %>
    <% end_if %>
    <% loop $OptionList.filter('Checked', '').limit(3) %>
        <% include SilverCart\ProductWizard\Model\Wizard\RadioOptionPicker %>
    <% end_loop %>
    <% loop $OptionList.filter('Checked', '').limit(0,3) %>
        <% include SilverCart\ProductWizard\Model\Wizard\RadioOptionPicker %>
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
    </div>
</div>
<% loop $OptionList %>
    <% if $Product %>
        <% with $Product %>
            {$setCurrentOptionID}
            <% include SilverCart\ProductWizard\Model\Wizard\ProductDetailModal %>
        <% end_with %>
    <% end_if %>
<% end_loop %>
<% if $OptionList.count > 3 %>
    <% include SilverCart\ProductWizard\Model\Wizard\RadioOptionModalSidebar %>
<% end_if %>