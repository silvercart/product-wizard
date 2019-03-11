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
            <% if $CurrentOptionID %>
                <a href="javascript:;" class="btn btn-primary select-product" data-option-id="{$CurrentOptionID}" data-product-id="{$ID}" data-dismiss="modal"><%t ProductWizard.Choose 'Choose' %></a>
            <% end_if %>
            </div>
        </div>
    </div>
</div>