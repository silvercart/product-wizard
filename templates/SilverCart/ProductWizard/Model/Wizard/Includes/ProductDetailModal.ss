<div class="modal fade" tabindex="-1" role="dialog" id="modal-product-{$ID}">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{$Title}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="<%t ProductWizard.Close 'Close' %>"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body clearfix">
                <img class="img-fluid float-left mr-2 mb-2" src="{$ListImage.Pad(214,145).URL}" alt="{$Title}" />
                <div class="mb-2 text-right">
                    <span class="text-lg font-weight-normal lh-15">{$PriceNice}</span>
                <% if $PriceIsLowerThanMsr %>
                    <span class="text-line-through text-gray">{$MSRPrice.Nice}</span>
                <% end_if %>
                </div>
                <% if $ShortDescription %>
                    <div class="text-muted mb-1">{$ShortDescription}</div>
                <% end_if %>
                <div class="text-justify">{$LongDescription}</div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-gray" data-dismiss="modal"><%t ProductWizard.Close 'Close' %></button>
            <% if $CurrentOption.IsOptional %>
                <a href="javascript:;" class="btn btn-primary select-product" data-option-id="{$CurrentOptionID}" data-product-id="{$ID}" data-dismiss="modal"><%t ProductWizard.Choose 'Choose' %></a>
            <% end_if %>
            </div>
        </div>
    </div>
</div>