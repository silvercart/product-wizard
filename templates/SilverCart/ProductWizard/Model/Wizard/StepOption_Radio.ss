<div class="card rounded-0 w-100 shadow">
    <div class="card-header rounded-0 bg-blue text-white px-10 py-6" style="height: 55px; overflow: hidden;">{$Title}</div>
    <div class="card-body pt-0 pb-10 px-10 p-relative">
    <% loop $OptionList %>
        <label class="d-block cursor-pointer border rounded mt-10 p-10 p-relative clearfix" for="StepOptions-{$StepOption.ID}-{$Value}">
            <div class="float-left" style="width: calc(100% - 20px);">{$Title}
            <% if $Product %>
                <% with $Product %>
                <a class="d-inline" href="javascript:;" data-toggle="modal" data-target="#modal-product-{$ID}"><span class="fa fa-info-circle"></span></a>
                <% end_with %>
            <% end_if %>
            </div>
            <input class="float-right mb-20" type="radio" name="StepOptions[{$StepOption.ID}]" id="StepOptions-{$StepOption.ID}-{$Value}" value="{$Value}" {$Checked} required="required">
            <% if $Product %>
                <% with $Product %>
                <a class="p-absolute r-0 b-0 mr-5px" href="javascript:;" data-toggle="modal" data-target="#modal-product-{$ID}">{$PriceNice}</a>
                <% end_with %>
            <% end_if %>
        </label>
        <% if $Product %>
            <% with $Product %>
                {$setCurrentOptionID}
                <% include SilverCart\ProductWizard\Model\Wizard\ProductDetailModal %>
            <% end_with %>
        <% end_if %>
    <% end_loop %>
    <% if $Text %>
        <hr>
        <p class="mb-0 text-center">{$Text}</p>
        <a href="#" class="btn btn-secondary btn-block">{$ButtonTitle}</a>
    <% end_if %>
    </div>
</div>