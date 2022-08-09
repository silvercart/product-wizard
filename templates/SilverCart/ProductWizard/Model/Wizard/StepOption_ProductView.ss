<% if $ProductsToDisplay.count == 1 && $DisplayType == 'tile' %>
    <% loop $ProductsToDisplay %>
        {$setCurrentOptionID($Up.ID)}
        <% include SilverCart\ProductWizard\Model\Wizard\ProductBox %>
    <% end_loop %>
<% else %>
<div class="w-100 {$ExtraClasses}">
    <h2>{$Title}</h2>
    {$Content}
    <% if $AllowMultipleChoices %>
        <% if $DisplayType == 'tile' %>
    <div class="row" data-title="{$Title.ATT}">
        <% loop $ProductsToDisplay %>
            {$setCurrentOptionID($Up.ID)}
        <div class="col-12 col-md-6 col-lg-3">
            <% include SilverCart\ProductWizard\Model\Wizard\ProductBox_OptionMulti %>
        </div>
        <% end_loop %>
    </div>
        <% else %>
    <div class="row">
        <div class="col-12">
            <table class="table w-100 border shadow">
                <thead>
                    <tr>
                        <th>{$ProductsToDisplay.first.fieldLabel('Quantity')}</th>
                        <th>{$ColumnTitleProducts}</th>
                        <th>&nbsp;</th>
                        <th>{$ProductsToDisplay.first.fieldLabel('Art')}</th>
                        <th class="text-right">{$ProductsToDisplay.first.fieldLabel('Price')}</th>
                    </tr>
                </thead>
                <tbody>
                <% loop $ProductsToDisplay %>
                    {$setCurrentOptionID($Up.ID)}
                    <% include SilverCart\ProductWizard\Model\Wizard\ProductBox_TableRow %>
                <% end_loop %>
                </tbody>
            </table>
        </div>
    </div>
        <% end_if %>
    <% else %>
    <div class="row <% if not $IsOptional %>wizard-required-product-option<% end_if %>" data-title="{$Title.ATT}">
        <% loop $ProductsToDisplay %>
            {$setCurrentOptionID($Up.ID)}
        <div class="col-12 col-md-6 col-lg-3">
            <% include SilverCart\ProductWizard\Model\Wizard\ProductBox_Option %>
        </div>
        <% end_loop %>
    </div>
    <% end_if %>
</div>
<% end_if %>