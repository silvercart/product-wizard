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
                <% if $LongDescription %>
                    <a href="javascript:;" data-toggle="modal" data-target="#modal-{$ID}"><img class="img-responsive" src="{$ListImage.Pad(214,145).URL}" alt="{$Title}" /></a>
                <% else %>
                    <img class="img-responsive" src="{$ListImage.Pad(214,145).URL}" alt="{$Title}" />
                <% end_if %>
                    <div class="spinner-field spinner-field-xs clearfix my-5">
                        <input type="text" name="StepOptions[{$Up.ID}][{$ID}][Quantity]" value="{$Up.getProductQuantityValue($ID)}" />
                        <a href="javascript:;" class="btn btn-xs btn-primary select-product" data-option-id="{$Up.ID}" data-product-id="{$ID}"><%t ProductWizard.Choose 'Choose' %></a>
                    </div>
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
                <div class="modal fade mt-50" tabindex="-1" role="dialog" id="modal-{$ID}">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="<%t ProductWizard.Close 'Close' %>"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title">{$Title}</h4>
                            </div>
                            <div class="modal-body">
                                <img class="img-responsive pull-left mr-5 mb-5" src="{$ListImage.Pad(214,145).URL}" alt="{$Title}" />
                                <p>{$LongDescription}</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><%t ProductWizard.Close 'Close' %></button>
                                <a href="javascript:;" class="btn btn-primary select-product" data-option-id="{$Up.ID}" data-product-id="{$ID}" data-dismiss="modal"><%t ProductWizard.Choose 'Choose' %></a>
                            </div>
                        </div>
                    </div>
                </div>
            <% end_if %>
            </div>
        <% end_loop %>
        </div>
    </div>
<% if $Text %>
    <span class="fa fa-info-circle p-absolute t-0 l-0 ml-20 mt-20"></span>
<% end_if %>
</div>