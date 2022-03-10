<% if $DisplayType == 'tile' %>
    <% include SilverCart\ProductWizard\Model\Wizard\Button_Tile %>
<% else %>
    <% include SilverCart\ProductWizard\Model\Wizard\Button_List %>
<% end_if %>