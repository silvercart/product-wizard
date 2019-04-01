<% if $Products.count == 1 %>
    <% loop $Products %>
        {$setCurrentOptionID($Up.ID)}
        <% include SilverCart\ProductWizard\Model\Wizard\ProductBox %>
    <% end_loop %>
<% else %>
    <% loop $Products %>
        {$setCurrentOptionID($Up.ID)}
        <% include SilverCart\ProductWizard\Model\Wizard\ProductBox_Tile %>
    <% end_loop %>
<% end_if %>