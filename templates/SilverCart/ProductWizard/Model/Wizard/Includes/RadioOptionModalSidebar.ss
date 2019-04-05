<div class="modal-sidebar" id="modal-sidebar-product-wizard-option-{$ID}">
    <div class="modal-sidebar-header">
        <h2>{$Title}</h2>
    </div>
    <div class="modal-sidebar-body">
    <% loop $OptionList %>
        <% include SilverCart\ProductWizard\Model\Wizard\RadioOptionPicker %>
    <% end_loop %>
    </div>
</div>