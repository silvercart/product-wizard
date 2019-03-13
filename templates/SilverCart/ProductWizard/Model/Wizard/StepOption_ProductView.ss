<div class="card w-100 mb-4 panel-products info-on-hover" data-info-content="{$Text.ATT}" data-info-heading="{$Title.ATT}">
    <div class="card-body">
        <div class="row">
    <% if $Products.count == 1 %>
        <% loop $Products %>
            {$setCurrentOptionID($Up.ID)}
            <% include SilverCart\ProductWizard\Model\Wizard\ProductBox %>
        <% end_loop %>
    <% else %>
        <% loop $Products %>
            <div class="col-6 col-sm-3 col-md-6">
                {$setCurrentOptionID($Up.ID)}
                <% include SilverCart\ProductWizard\Model\Wizard\ProductBox_Tile %>
            </div>
        <% end_loop %>
    <% end_if %>
        </div>
    </div>
<% if $Text %>
    <span class="fa fa-info-circle p-absolute t-0 l-0 ml-20 mt-20"></span>
<% end_if %>
</div>