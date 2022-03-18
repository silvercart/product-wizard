<% if $DisplayType == 'tile' %>
    <% include SilverCart\ProductWizard\Model\Wizard\Label_Tile %>
<% else %>
    <% include SilverCart\ProductWizard\Model\Wizard\Label_List %>
<% end_if %>