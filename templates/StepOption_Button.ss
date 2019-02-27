<div class="panel panel-default w-100 my-10 info-on-hover" data-info-content="{$Text.ATT}" data-info-heading="{$Title.ATT}">
    <div class="panel-heading h-100 text-center d-none">
        <h3 class="panel-title">{$Title}</h3>
    </div>
    <div class="panel-body p-relative clearfix">
        {$Title}<br/>
        <a href="#" class="btn btn-secondary pull-right">{$ButtonTitle}</a>
    </div>
<% if $Text %>
    <span class="fa fa-info-circle p-absolute t-0 l-0 ml-20 mt-20"></span>
<% end_if %>
</div>