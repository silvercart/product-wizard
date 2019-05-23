<% if $ProductsToDisplay.count == 1 %>
    <% loop $ProductsToDisplay %>
        {$setCurrentOptionID($Up.ID)}
        <% include SilverCart\ProductWizard\Model\Wizard\ProductBox %>
    <% end_loop %>
<% else %>
    <% loop $ProductsToDisplay %>
        {$setCurrentOptionID($Up.ID)}
        <% include SilverCart\ProductWizard\Model\Wizard\ProductBox_Tile %>
    <% end_loop %>
<% end_if %>