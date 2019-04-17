<div class="modal fade" tabindex="-1" role="dialog" id="modal-description-{$StepOption.ID}-{$Value}">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{$Title}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="<%t ProductWizard.Close 'Close' %>"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body clearfix">
                {$LongDescription}
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-gray" data-dismiss="modal"><%t ProductWizard.Close 'Close' %></button>
            </div>
        </div>
    </div>
</div>