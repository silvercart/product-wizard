<div class="panel panel-default w-100 my-10 info-on-hover" data-info-content="{$Text.ATT}" data-info-heading="{$Title.ATT}">
    <div class="panel-heading h-100 text-center">
        <h3 class="panel-title">{$Title}</h3>
    </div>
    <div class="panel-body py-5">
    <% loop $OptionList %>
        <div class="radio">
            <label>
                <input type="radio" name="StepOptions[{$StepOption.ID}]" value="{$Value}" {$Checked} required="required">
                {$Title}
            </label>
        </div>
    <% end_loop %>
    </div>
<% if $Text %>
    <span class="fa fa-info-circle p-absolute t-0 l-0 ml-20 mt-20"></span>
<% end_if %>
</div>