<% if $VisibleStepOptions.exists %>
<div class="row option-set-options">
    <% loop $VisibleStepOptions %>
    <div class="col-md-12">
        {$forTemplate}
    </div>
    <% end_loop %>
</div>
<% end_if %>