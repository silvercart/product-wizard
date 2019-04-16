<div class="card rounded-0 w-100 shadow wizard-option">
    <div class="card-header rounded-0 bg-blue text-white px-10 py-6">{$Title}</div>
    <div class="card-body pt-0 pb-10 px-10 p-relative">
    <% if $Text %>
        <% if $Content %>
        <div class="mt-10" style="height: calc(100% - 115px);">{$Content}</div>
        <% end_if %>
        <hr>
        <p class="mb-0 text-center">{$Text}</p>
    <% else_if $Content %>
        <div class="mt-10">{$Content}</div>
    <% end_if %>
    </div>
</div>