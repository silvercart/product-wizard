<div class="panel panel-default w-100 my-10 info-on-hover" data-info-content="{$Text.ATT}" data-info-heading="{$Title.ATT}">
    <div class="panel-heading h-100 text-center d-none">
        <h3 class="panel-title">{$Title}</h3>
    </div>
    <div class="panel-body py-5">
        <div class="row d-flex">
        <% loop $Products %>
            <div class="col-xs-6">
                <div class="product-box {$Up.getProductIsSelectedClass($ID)}">
                    <input type="hidden" name="StepOptions[{$Up.ID}][{$ID}][Select]" value="{$Up.getProductSelectValue($ID)}" />
                    <img class="img-responsive" src="{$ListImage.Pad(214,145).URL}" alt="{$Title}" />
                    <div class="spinner-field spinner-field-xs clearfix my-5">
                        <input type="text" name="StepOptions[{$Up.ID}][{$ID}][Quantity]" value="{$Up.getProductQuantityValue($ID)}" />
                        <a href="javascript:;" class="btn btn-xs btn-primary select-product" data-option-id="{$Up.ID}" data-product-id="{$ID}"><%t ProductWizard.Choose 'Choose' %></a>
                    </div>
                    {$Title}
                    <div class="mt-10">
                        <span class="text-lg font-weight-normal lh-15">{$PriceNice}</span>
                    <% if $PriceIsLowerThanMsr %>
                        <span class="text-line-through text-gray">{$MSRPrice.Nice}</span>
                    <% end_if %>
                    </div>
                    <span class="fa fa-check p-absolute b-0 r-0 mr-5 mb-5 text-lg text-white"></span>
                </div>
            </div>
        <% end_loop %>
        </div>
    </div>
<% if $Text %>
    <span class="fa fa-info-circle p-absolute t-0 l-0 ml-20 mt-20"></span>
<% end_if %>
</div>