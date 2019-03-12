<div class="panel panel-default panel-products w-100 my-10 info-on-hover" data-info-content="{$Text.ATT}" data-info-heading="{$Title.ATT}">
    <div class="panel-heading h-100 text-center d-none">
        <h3 class="panel-title">{$Title}</h3>
    </div>
    <div class="panel-body py-5">
        <div class="row d-flex">
    <% if $Products.count == 1 %>
        <% loop $Products %>
            <div class="col-xs-12">
                <div class="product-box row {$Up.getProductIsSelectedClass($ID)}">
                    <input type="hidden" name="StepOptions[{$Up.ID}][{$ID}][Select]" value="{$Up.getProductSelectValue($ID)}" />
                    <div class="col-xs-5">
                    <% if $LongDescription %>
                        <a href="javascript:;" data-toggle="modal" data-target="#modal-{$ID}"><img class="img-responsive" src="{$ListImage.Pad(214,145).URL}" alt="{$Title}" /></a>
                    <% else %>
                        <img class="img-responsive" src="{$ListImage.Pad(214,145).URL}" alt="{$Title}" />
                    <% end_if %>
                    </div>
                    <div class="col-xs-7">
                    <% if not $Up.ProductViewIsReadonly %>
                        <div class="spinner-field spinner-field-xs clearfix my-5 text-nowrap">
                            <input type="text" name="StepOptions[{$Up.ID}][{$ID}][Quantity]" value="{$Up.getProductQuantityValue($ID)}" />
                            <a href="javascript:;" class="btn btn-xs btn-primary select-product" data-option-id="{$Up.ID}" data-product-id="{$ID}"><span class="d-inline d-md-none d-lg-inline"><%t ProductWizard.Choose 'Choose' %></span><span class="d-none d-md-inline d-lg-none fa fa-check"></span></a>
                        </div>
                    <% end_if %>
                    <% if $LongDescription %>
                        <a href="javascript:;" data-toggle="modal" data-target="#modal-{$ID}">{$Title}</a>
                    <% else %>
                        {$Title}
                    <% end_if %>
                        <div class="mt-10">
                            <span class="text-lg font-weight-normal lh-15">{$PriceNice}</span>
                        <% if $PriceIsLowerThanMsr %>
                            <span class="text-line-through text-gray">{$MSRPrice.Nice}</span>
                        <% end_if %>
                        </div>
                    <% if $Up.ProductViewIsReadonly %>
                        <span class="fa fa-check p-absolute b-0 r-0 mr-5 mb-5 text-lg d-block"></span>
                    <% else %>
                        <span class="fa fa-check p-absolute b-0 r-0 mr-5 mb-5 text-lg text-white"></span>
                    <% end_if %>
                    </div>
                </div>
            <% if $LongDescription %>
                <% if $Up.ProductViewIsReadonly %>
                    {$setCurrentOptionID}
                <% else %>
                    {$setCurrentOptionID($Up.ID)}
                <% end_if %>
                <% include ProductWizardProductDetailModal %>
            <% end_if %>
            </div>
        <% end_loop %>
    <% else %>
        <% loop $Products %>
            <div class="col-xs-6 col-sm-3 col-md-6">
                <div class="product-box {$Up.getProductIsSelectedClass($ID)}">
                    <input type="hidden" name="StepOptions[{$Up.ID}][{$ID}][Select]" value="{$Up.getProductSelectValue($ID)}" />
                <% if $LongDescription %>
                    <a href="javascript:;" data-toggle="modal" data-target="#modal-{$ID}"><img class="img-responsive" src="{$ListImage.Pad(214,145).URL}" alt="{$Title}" /></a>
                <% else %>
                    <img class="img-responsive" src="{$ListImage.Pad(214,145).URL}" alt="{$Title}" />
                <% end_if %>
                <% if not $Up.ProductViewIsReadonly %>
                    <div class="spinner-field spinner-field-xs clearfix my-5 text-nowrap">
                        <input type="text" name="StepOptions[{$Up.ID}][{$ID}][Quantity]" value="{$Up.getProductQuantityValue($ID)}" />
                        <a href="javascript:;" class="btn btn-xs btn-primary select-product" data-option-id="{$Up.ID}" data-product-id="{$ID}"><span class="d-inline d-md-none d-lg-inline"><%t ProductWizard.Choose 'Choose' %></span><span class="d-none d-md-inline d-lg-none fa fa-check"></span></a>
                    </div>
                <% end_if %>
                <% if $LongDescription %>
                    <a href="javascript:;" data-toggle="modal" data-target="#modal-{$ID}">{$Title}</a>
                <% else %>
                    {$Title}
                <% end_if %>
                    <div class="mt-10">
                        <span class="text-lg font-weight-normal lh-15">{$PriceNice}</span>
                    <% if $PriceIsLowerThanMsr %>
                        <span class="text-line-through text-gray">{$MSRPrice.Nice}</span>
                    <% end_if %>
                    </div>
                    <span class="fa fa-check p-absolute b-0 r-0 mr-5 mb-5 text-lg text-white"></span>
                </div>
            <% if $LongDescription %>
                {$setCurrentOptionID($Up.ID)}
                <% include ProductWizardProductDetailModal %>
            <% end_if %>
            </div>
        <% end_loop %>
    <% end_if %>
        </div>
    </div>
<% if $Text %>
    <span class="fa fa-info-circle p-absolute t-0 l-0 ml-20 mt-20"></span>
<% end_if %>
</div>