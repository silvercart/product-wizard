<% if $DisplayType == 'tile' %>
    <% include SilverCart\ProductWizard\Model\Wizard\Radio_Tile %>
<% else %>
    <% include SilverCart\ProductWizard\Model\Wizard\Radio_List %>
<% end_if %>