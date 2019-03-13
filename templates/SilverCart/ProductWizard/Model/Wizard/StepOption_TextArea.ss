<div class="card w-100 mb-4 info-on-hover" data-info-content="{$Text.ATT}" data-info-heading="{$Title.ATT}">
    <div class="card-body">
        <h5 class="card-title">{$Title}</h5>
        <div class="form-group row">
            <label for="StepOptions-{$ID}" class="col-sm-12 col-form-label">{$Title}</label>
            <div class="col-sm-12">
                <textarea name="StepOptions[{$ID}]" class="form-control" id="StepOptions-{$ID}" rows="3" placeholder="" required="required">{$Value}</textarea>
            </div>
        </div>
    <% if $Text %>
        <span class="fa fa-info-circle p-absolute t-0 l-0 ml-1 mt-1"></span>
    <% end_if %>
    </div>
</div>