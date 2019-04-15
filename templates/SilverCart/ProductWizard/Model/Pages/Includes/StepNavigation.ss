<% if $NavigationSteps %>
    <% if $NavigationSteps.count == 1 %>
<div class="mb-10 text-blue text-center">
    <% with $NavigationSteps.first %>
        <% if $FontAwesomeIcon %>
    <span class="fa fa-2x fa-{$FontAwesomeIcon} border border-blue rounded-circle p-10"></span>
        <% end_if %>
    <span class="h1 p-relative l-8 b-2">{$Title}</span>
    <% end_with %>
</div>
<div class="progress mb-20"><div class="progress-bar bg-success" role="progressbar" style="width: {$NavigationStepProgressPercentage}%" aria-valuenow="1" aria-valuemin="0" aria-valuemax="{$NavigationSteps.count}"></div></div>
<div class="border-top border-bottom mb-20 py-10">
    <% if $PreviousStep %>
    <a href="{$BackLink}"><span class="fa fa-angle-double-left"></span> <%t SilverCart\ProductWizard\Model\Pages\ProductWizardStepPage.BackTo 'Back to {step}' step=$PreviousStep.Title %></a>
    <% end_if %>
    <span class="ml-20 fa fa-{$CurrentStep.FontAwesomeIcon} border border-black rounded-circle p-6"></span> {$CurrentStep.Title}: {$CurrentStep.InfoBoxTitle}
</div>
    <% else %>
<div class="row mb-10 text-center d-none d-sm-flex">
    <% loop $NavigationSteps %>
        <% if $IsFinished %>
    <div class="col-4 text-blue-dark">
        <a href="{$Link}">
            <% if $FontAwesomeIcon %>
            <span class="fa fa-1-5x fa-{$FontAwesomeIcon} border border-blue-dark rounded-circle p-10"></span>
            <% end_if %>
            <span class="h2 p-relative l-8 b-2">{$Pos}. {$Title}</span>
            <span class="fa fa-1-5x fa-check p-10"></span>
        </a>
    </div>
        <% else_if $IsCurrent %>
    <div class="col-4 text-blue">
            <% if $FontAwesomeIcon %>
        <span class="fa fa-1-5x fa-{$FontAwesomeIcon} border border-blue rounded-circle p-10"></span>
            <% end_if %>
        <span class="h2 p-relative l-8 b-2">{$Pos}. {$Title}</span>
    </div>
        <% else %>
    <div class="col-4 text-gray">
            <% if $FontAwesomeIcon %>
        <span class="fa fa-1-5x fa-{$FontAwesomeIcon} border border-gray rounded-circle p-10"></span>
            <% end_if %>
        <span class="h2 p-relative l-8 b-2">{$Pos}. {$Title}</span>
    </div>
        <% end_if %>
    <% end_loop %>
</div>
<div class="progress mb-20"><div class="progress-bar bg-success" role="progressbar" style="width: {$NavigationStepProgressPercentage}%" aria-valuenow="1" aria-valuemin="0" aria-valuemax="{$NavigationSteps.count}"></div></div>
<div class="border-top border-bottom mb-20 py-10">
    <% if $PreviousStep %>
    <a href="{$BackLink}"><span class="fa fa-angle-double-left"></span> <%t SilverCart\ProductWizard\Model\Pages\ProductWizardStepPage.BackTo 'Back to {step}' step=$PreviousStep.Title %></a>
    <% end_if %>
    <span class="ml-20 fa fa-{$CurrentStep.FontAwesomeIcon} border border-black rounded-circle p-6"></span> {$fieldLabel('Step')} {$CurrentStep.NavigationStepNumber}: {$CurrentStep.InfoBoxTitle}
</div>
    <% end_if %>
<% end_if %>