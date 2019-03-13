<div class="row bg-white mb-40 pb-20">
    <div class="col-xs-12">
        <h2>{$Title}</h2>
        <% if $WizardIsCompleted %>
        <div class="alert alert-info clearfix">
            <%t ProductWizard.RedirectionInProgress 'You will be redirected to the shopping cart in a few seconds. If the redirection doesn\'t work for any reason, please press the button below.' %><br/>
            <a href="{$PageByIdentifierCode('SilvercartCartPage').Link}" class="btn btn-primary float-right"><%t ProductWizard.GoToCart 'Go to cart' %></a>
        </div>
        <% else %>
        <div class="alert alert-info clearfix">
            <%t ProductWizard.RedirectionInProgress2 'You will be redirected to the wizard in a few seconds. If the redirection doesn\'t work for any reason, please press the button below.' %><br/>
            <a href="{$Link}" class="btn btn-primary float-right"><%t ProductWizard.GoToWizard 'Go to wizard' %></a>
        </div>
        <% end_if %>
    </div>
</div>