<div class="modal-sidebar" id="modal-sidebar-product-wizard-option-{$ID}">
    <div class="modal-sidebar-header">
        <h5 class="modal-sidebar-title">{$Title}</h5>
        <button type="button" class="close" data-dismiss="modal-sidebar" aria-label="<%t ProductWizard.Close 'Close' %>"><span aria-hidden="true">&times;</span></button>
    </div>
    <div class="modal-sidebar-body">
    <% loop $OptionList %>
        <% include SilverCart\ProductWizard\Model\Wizard\RadioOptionPicker %>
    <% end_loop %>
    </div>
</div>