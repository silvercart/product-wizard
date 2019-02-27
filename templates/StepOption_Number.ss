<div class="panel panel-default w-100 my-10 info-on-hover" data-info-content="{$Text.ATT}" data-info-heading="{$Title.ATT}">
    <div class="panel-heading h-100 text-center d-none">
        <h3 class="panel-title">{$Title}</h3>
    </div>
    <div class="panel-body p-relative clearfix">
        <label class="d-inline-block mr-70 lh-31" for="StepOptions-{$ID}">{$Title}</label>
        <div class="spinner-field p-absolute t-0 r-0 mt-15 mr-5">
            <input type="text" class="text" name="StepOptions[{$ID}]" value="{$Value}" id="StepOptions-{$ID}" required="required">
        </div>
    </div>
<% if $Text %>
    <span class="fa fa-info-circle p-absolute t-0 l-0 ml-20 mt-20"></span>
<% end_if %>
</div>