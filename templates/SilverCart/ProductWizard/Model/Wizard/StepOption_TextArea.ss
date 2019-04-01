<div class="card rounded-0 w-100 shadow">
    <div class="card-header rounded-0 bg-blue text-white px-10 py-6" style="height: 55px; overflow: hidden;">{$Title}</div>
    <div class="card-body pt-0 pb-10 px-10 p-relative">
        <div class="mt-10">{$Content}</div>
        <div class="form-group row">
            <div class="col-sm-12">
                <textarea name="StepOptions[{$ID}]" class="form-control" id="StepOptions-{$ID}" rows="3" placeholder="" required="required">{$Value}</textarea>
            </div>
        </div>
    <% if $Text %>
        <hr>
        <p class="mb-0 text-center">{$Text}</p>
    <% end_if %>
    </div>
</div>