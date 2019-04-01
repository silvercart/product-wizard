<div class="card rounded-0 w-100 shadow">
    <div class="card-header rounded-0 bg-blue text-white px-10 py-6" style="height: 55px; overflow: hidden;">{$Title}</div>
    <div class="card-body pt-0 pb-10 px-10 p-relative">
    <% if $Content %>
        <div class="mt-10" style="height: calc(100% - 115px);">{$Content}</div>
    <% end_if %>
        <hr>
        <p class="mb-0 text-center">{$Text}</p>
        <input type="number" name="StepOptions[{$ID}]" value="{$Value}" class="form-control" id="StepOptions-{$ID}" placeholder="" required="required">
    </div>
</div>
<% if $Product %>
    <% with $Product %>
        {$setCurrentOptionID($Up.ID)}
        <% include SilverCart\ProductWizard\Model\Wizard\ProductBox %>
    <% end_with %>
<% end_if %>