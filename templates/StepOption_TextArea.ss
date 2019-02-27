<div class="panel panel-default w-100 my-10 info-on-hover" data-info-content="{$Text.ATT}" data-info-heading="{$Title.ATT}">
    <div class="panel-heading h-100 text-center d-none">
        <h3 class="panel-title">{$Title}</h3>
    </div>
    <div class="panel-body p-relative clearfix">
        <div class="form-group">
            <label for="StepOptions-{$ID}" class="col-xs-12 control-label">{$Title}</label>
            <div class="col-xs-12 col-sm-10 col-sm-offset-2">
                <textarea name="StepOptions[{$ID}]" class="form-control" id="StepOptions-{$ID}" rows="3" required="required">{$Value}</textarea>
            </div>
        </div>
    </div>
<% if $Text %>
    <span class="fa fa-info-circle p-absolute t-0 l-0 ml-20 mt-20"></span>
<% end_if %>
</div>