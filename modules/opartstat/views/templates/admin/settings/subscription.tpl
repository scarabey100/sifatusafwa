{if isset($displayManageSubscriptionAndInformationslink) && $displayManageSubscriptionAndInformationslink == true}
    <div class="manageSubscriptionAndInformationslinkContainer">
        <a href="#"  onclick="openOpartSaasIframe('{$saasDomain|escape:'htmlall':'UTF-8'}/controllers/front/getStripePortal.php?isoLang={$langIsoCode|escape:'htmlall':'UTF-8'}'); return false;" id="manageSubscriptionAndInformationsLink">{l s='Manage my subscription and my invoices' mod='opartstat'}</a>
    </div>
{*     <div class="hideMe panel" id="manageSubscriptionAndInformationsContainer">
        <div class="panel-heading">
            <i class="icon-cogs"></i> {l s='Manage informations' mod='opartstat'}
        </div>
        <div id="manageSubscriptionAndInformations"></div>
        <div id="ps-billing-invoice"></div>
    </div>
    <div id="ps-modal"></div> *}
{/if}

<div class="panel" id="fieldset_0">
    {if !$shopTokenIsValid}
        <div class="panel-heading">
            <i class="icon-cogs"></i>
            {l s='[Step 1/3]' mod='opartstat'} {l s='Get your security token to link your shop to the Op\'art stat remote server' mod='opartstat'}
        </div>
        <a id="getShoptokenBtn"
            onclick="openOpartSaasIframe('{$saasDomain|escape:'htmlall':'UTF-8'}/controllers/front/getShopToken.php?shopUrl={$shopUrl|escape:'htmlall':'UTF-8'}&isoLang={$langIsoCode|escape:'htmlall':'UTF-8'}'); return false;"
            class="btn btn-primary pointer" href="#">
            <i class="material-icons mi-vpn_key"><span>vpn_key</span></i>
            <span class="label">{l s='Click here to get your shop token' mod='opartstat'}</label>
        </a>
        <div id="shopTokenFormContainer">
            {$shopTokenForm|escape:'htmlall':'UTF-8'}
        </div>
    {/if}

{*     {if isset($displayDepencies) && $displayDepencies==true}
        <script src="https://assets.prestashop3.com/dst/mbo/v1/mbo-cdc-dependencies-resolver.umd.js"></script>

        <!-- cdc container -->
        <div id="cdc-container"></div>

        <script defer>
        const renderMboCdcDependencyResolver = window.mboCdcDependencyResolver.render
        const context = {
            ...{$dependencies|json_encode},
            onDependenciesResolved: () => location.reload(),
            onDependencyResolved: (dependencyData) => console.log('Dependency installed', dependencyData), // name, displayName, version
            onDependencyFailed: (dependencyData) => console.log('Failed to install dependency', dependencyData),
            onDependenciesFailed: () => console.log('There are some errors'),
        }
        renderMboCdcDependencyResolver(context, '#cdc-container')
        </script>
    {/if}      *}

{*     {if isset($displayPsAccount) && $displayPsAccount==true && $isLinkedToPsAccount == false}
        <div class="panel-heading">
            <i class="icon-cogs"></i>
            {l s='Link your shop to your PrestaShop account' mod='opartstat'}
        </div>
        <prestashop-accounts>{l s='Verification of the connection with PrestaShop account ...' mod='opartstat'}
        </prestashop-accounts>
    {/if} *}

    {* {if isset($displayPsBilling) && $displayPsBilling==true}        
        <div class="panel-heading">
            <i class="icon-cogs"></i>
            {l s='Subscribe to op\'art Stat Premium' mod='opartstat'}
        </div>
        <div class="opartStatPremiumProductContainer">
            <div class="leftContainer">
                <div class="productName">{$product['details']['name']}</div>
                {foreach $product['details']['features'] as $feature}
                    <div>
                        <i class="material-icons mi-check"><span>check</span></i>
                        {$feature}
                    </div>
                {/foreach}
            </div>

            <div class="rightContainer">
                <div class="price">{$product['price']/100}€/{$product['details']['billingPeriodUnitTranslated']} <span
                        class="then">{l s='Then :' mod='opartstat'}</span></div>
                {foreach $product['details']['pricingText'] as $price}
                    <div>{$price}</div>
                {/foreach}
            </div>
        </div>
        <div class="opartStatPremiumBtnContainer">
            <button class="btn btn-primary pointer" onclick="openCheckout('{$product['id']}'); disableAndCheckIfShopIsActive($(this));">
                {l s='Subscribe' mod='opartstat'}
            </button>
            <div id="checkIfShopIsActiveLoadingPhrase" class="hideMe">
                <i class="material-icons mi-history"><span>history</span></i>
                <i class="icon-check-circle hideMe"></i>
                {l s='Please wait while we check your subscription' mod='opartstat'}
            </div>
        </div>

        <div id="ps-billing-wrapper" style="display:none">
            <div id="ps-billing" class="hideMe expectedElement"></div>
        </div>
        <div id="ps-modal"></div>
    {/if} *}

    {if isset($displaySubscriptionBtn) && $displaySubscriptionBtn==true}        
        <div class="panel-heading">
            <i class="icon-cogs"></i>
            {l s='[Step 2/3]' mod='opartstat'} {l s='Subscribe to op\'art Stat Premium' mod='opartstat'}
        </div>
        {* <div class="opartStatPremiumProductContainer">
            <div class="leftContainer">
                <div class="productName">[PRODUCT NAME HERE]</div>
                
                    <div>
                        <i class="material-icons mi-check"><span>check</span></i>
                        [FEATURE LIST HERE]
                    </div>
                
            </div>

            <div class="rightContainer">
                <div class="price">[PRICE / PERIOD] here<span
                        class="then">{l s='Then :' mod='opartstat'}</span></div>
                
                    <div>price</div>
                
            </div>
        </div> *}
        <div class="opartStatPremiumBtnContainer">
            <a onclick="openOpartSaasIframe('{$saasDomain|escape:'htmlall':'UTF-8'}/controllers/front/subscribe.php?shopUrl={$shopUrl|escape:'htmlall':'UTF-8'}&isoLang={$langIsoCode|escape:'htmlall':'UTF-8'}'); disableAndCheckIfShopIsActive($(this)); return false;"
                class="btn btn-primary pointer" href="#">
                <i class="material-icons mi-how_to_reg"><span>check_circle</span></i>
                <span class="label">{l s='Click here to verify and confirm your subscription' mod='opartstat'}</label>
            </a>
            {* <button class="btn btn-primary pointer" onclick="openOpartSaasIframe('{$saasDomain|escape:'htmlall':'UTF-8'}/controllers/front/subscribe.php?shopUrl={$shopUrl|escape:'htmlall':'UTF-8'}&isoLang={$langIsoCode|escape:'htmlall':'UTF-8'}'); disableAndCheckIfShopIsActive($(this)); return false; ">
                {l s='Subscribe' mod='opartstat'}
            </button> *}
            <div id="checkIfShopIsActiveLoadingPhrase" class="hideMe">
                <i class="material-icons mi-history"><span>history</span></i>
                <i class="icon-check-circle hideMe"></i>
                {l s='Please wait while we check your subscription' mod='opartstat'}
            </div>
        </div>

        <div id="ps-billing-wrapper" style="display:none">
            <div id="ps-billing" class="hideMe expectedElement"></div>
        </div>
        <div id="ps-modal"></div>
    {/if}
    
    {* {if isset($oldOpartStatSessionsAreSaved) && $oldOpartStatSessionsAreSaved==false}
        <div class="panel-heading">
            <i class="icon-cogs"></i> {l s='Import sessions' mod='opartstat'}
        </div>
        {l s='Visits stored on your server are being copied to the remote server' mod='opartstat'}<br />
        {l s='Do not close this page until the process is complete' mod='opartstat'}<br />
        {l s='Progress' mod='opartstat'} :
        <div id="savedSessionsProgressBar" style="width: 100%; background-color: #f3f3f3;"
            data-lastopartstatsessionid="{$lastOpartStatSessionId}">
            <div id="savedSessionsProgressBarStatus" style="height: 25px; width: 0%; background-color: #4CAF50;"></div>
        </div>

        <div id="oldSessionSavedConfirmMsg" class="hideMe module_confirmation conf confirm alert alert-success">
            <span>{l s='All visits have been copied to the remote server' mod='opartstat'} 👍</span>
            <button type="button" style="vertical-align:middle" class="btn btn-primary pointer"
                onclick="location.reload();">{l s='Go to the Next step' mod='opartstat'}</button>
        </div>
    {/if} *}

    {if isset($currentGoogleAdsAccount) && $currentGoogleAdsAccount==false}
        <div class="panel-heading">
            <i class="icon-cogs"></i> 
            {l s='[Step 3/3]' mod='opartstat'} {l s='Connect your Google Ads account' mod='opartstat'}
        </div>
        <div>
            <a onclick="openOpartSaasIframe('{$saasDomain|escape:'htmlall':'UTF-8'}controllers/front/chooseGoogleAdsAccount.php?shopUrl={$shopUrl|escape:'htmlall':'UTF-8'}&isoLang={$langIsoCode|escape:'htmlall':'UTF-8'}',true); return false;"
                class="btn btn-primary pointer"
                href="#">{l s='Click here to connect your google Ads Account' mod='opartstat'} </a>
        </div>
    {/if}

    {if isset($allIsConfiguredCorrectly) && $allIsConfiguredCorrectly==true}
        <div class="congratulationMsg">
            {$successConfetti|escape:'htmlall':'UTF-8'}
            <span>{l s='Congratulation, Op\'art Stat Premium is now activated' mod='opartstat'}</span>
        </div>
    {/if}
</div>

{* {if isset($allIsConfiguredCorrectly) && $allIsConfiguredCorrectly==true}
    <div class="panel">
        <div class="panel-heading">
            <i class="icon-cogs"></i> {l s='Manage your max budget' mod='opartstat'}
        </div>
        <div class="infoBudget">
            <i class="icon-info-circle"></i>
            <span>{l s='Below you can specify the amounts of data that will be stored on our server and thus fixed a maximum budget spent each month.' mod='opartstat'}</span>
        </div>
        <form id="maxLinesForm" class="defaultForm form-horizontal" action="{$adminControllerLink}" method="post" enctype="multipart/form-data" novalidate="">
            <div class="form-wrapper">
                <div class="form-group">
                    <label class="control-label col-lg-4">{l s='Max page views stored' mod='opartstat'}</label>
                    <div class="col-lg-8">
                        <input type="text" name="maxOpartStatSession" id="maxOpartStatSession" value="{$maxLinesPerTables['maxOpartStatSession']['limit']}" class="" />
                        <p class="help-block">
                            {l s='Type here the maximum number of page views that will be stored (-1 means no limit)' mod='opartstat'}
                        </p>
                    </div>
                    <label class="control-label col-lg-4">{l s='Max Google Ads Clicks stored' mod='opartstat'}</label>
                    <div class="col-lg-8">
                        <input type="text" name="maxGoogleAdsClicks" id="maxGoogleAdsClicks" value="{$maxLinesPerTables['maxGoogleAdsClicks']['limit']}" class="" />
                        <p class="help-block">
                            {l s='Type here the maximum number of Google Ads clicks that will be stored (-1 means no limit)' mod='opartstat'}
                        </p>
                    </div>
                    <input type="hidden" id="pricePerLineInput" value='{"{$opartStatPremiumDatas['pricePerLine']|json_encode}"}' />
                    <input type="hidden" name="submitMaxLinesPerTables" value="1">
                    <div class="maxBudgetContainer">
                        {l s='Maximum monthly budget' mod='opartstat'} : 
                        <div id="maxBudget">
                            {$opartStatPremiumDatas['flatPrice']} € + <span></span> €
                        </div>
                    </div>
                </div>            
            </div>
            <div class="panel-footer">
                <button type="submit" value="1" id="submitMaxLinesPerTables" name="submitMaxLinesPerTables" class="btn btn-default pull-right">
                    <i class="process-icon-save"></i> {l s='save' mod='opartstat'}
                </button>
            </div>
        </form>
    </div>
    <div class="panel">
    <strong>{l s='Pricing reminder:' mod='opartstat'}</strong>
        {l s='Fixed subscription of %s € per month (30 000 lines free) + ' sprintf=[$opartStatPremiumDatas['flatPrice']] mod='opartstat'}
        <ul>
        {foreach $opartStatPremiumDatas['pricingText'] as $price}
            <li>{$price}</li>
        {/foreach}
        </ul>
    </div>
{/if} *}

{if !isset($allIsConfiguredCorrectly) || $allIsConfiguredCorrectly==false}
<div class="panel stepsContainer" id="fieldset_0">
    <div class="panel-heading">
        <i class="icon-cogs"></i>{l s='Configuration Steps' mod='opartstat'}
    </div>
    <p>
        {if !$shopTokenIsValid}
            <i class="icon-arrow-circle-right {if $currentStep == 'getShopToken'}activeStep{/if}"></i>
            <span>{l s='Get a security shop token to link your shop to the Op\'art stat remote server' mod='opartstat'}</span>
        {else}
            <i class="icon-check-circle"></i>
            <span>{l s='Your shop is linked to the Op\'art stat remote server' mod='opartstat'}</span>
        {/if}
    </p>
    <p>
        {if !isset($shopHasActiveSubscription) || $shopHasActiveSubscription!=true}
            <i class="icon-arrow-circle-right {if $currentStep == 'chooseSubscriptionPlan'}activeStep{/if}"></i>
            <span>{l s='Subscribe to Op\'art Stat Premium' mod='opartstat'}</span>
        {else}
            <i class="icon-check-circle"></i>
            <span>{l s='Your subscription to Op\'art Stat Premium is active' mod='opartstat'}</span>
        {/if}
    </p>
    <p>
        {if !isset($currentGoogleAdsAccount) || $currentGoogleAdsAccount!=true}
            <i class="icon-arrow-circle-right {if $currentStep == 'linkGoogleAdsAccount'}activeStep{/if}"></i>
            <span>{l s='Connect your Google Ads Account' mod='opartstat'}</span>
        {else}
            <i class="icon-check-circle"></i>
            <span>{l s='Op\'art stat is connected to Google Ads using account id : %s' sprintf=[$currentGoogleAdsAccount] mod='opartstat'}</span>
        {/if}
    </p>
</div>
{/if}

{if isset($displayPsAccount) && $displayPsAccount==true}
    <script src="{$urlAccountsCdn|escape:'htmlall':'UTF-8'}" rel=preload></script>
    <script>
        window?.psaccountsVue?.init();
    </script>
{/if}

{if isset($urlBilling) && $urlBilling!=null}
    <script src="{$urlBilling|escape:'htmlall':'UTF-8'}" rel=preload></script>
{/if}

{* {if isset($displayPsBilling) && $displayPsBilling==true}
    <script src="{$urlBillingJs|escape:'htmlall':'UTF-8'}" rel=preload></script>
{/if} *}

{* {if isset($displayManageSubscriptionAndInformationslink) && $displayManageSubscriptionAndInformationslink == true}
<script>
    const onCloseModal = async (data) => {
        await Promise.all([currentModal.close(), updateCustomerProps(data)]);
    };

    const onOpenModal = (type, data) => {
        currentModal = new window.psBilling.ModalContainerComponent({
            type,
            context: {
                ...context,
                ...data,
            },
            onCloseModal,
        });

        currentModal.render('#ps-modal');
    };

    const updateCustomerProps = (data) => {
        return customer.updateProps({
            context: {
                ...context,
                ...data,
            },
        });
    };

    let currentModal;
    let context = psBillingContext.context;

    const customer = new window.psBilling.CustomerComponent({
        context,
        hideInvoiceList: true,
        onOpenModal,
    });

    $('#manageSubscriptionAndInformationsLink').click(async function(event) {
        event.preventDefault();
        $('#manageSubscriptionAndInformationsContainer').show('slow');
        customer.render('#manageSubscriptionAndInformations');          
    });
    window.psBilling.initializeInvoiceList(
	  window.psBillingContext.context,
	  "#ps-billing-invoice"
	);
</script>
{/if} *}

{if isset($oldOpartStatSessionsAreSaved) && $oldOpartStatSessionsAreSaved==false}
    <script type="text/javascript">
        saveOldOpartStatSessions();
    </script>
{/if}

{if isset($allIsConfiguredCorrectly) && $allIsConfiguredCorrectly==true}
    <script type="text/javascript">
    calcMaxBudget();

    $('#maxLinesForm input').change(function() {
        calcMaxBudget();
    }); 

    $('#maxLinesForm input').on('input', function(e) {
        var numberWithoutSpaces = $(this).val().replace(/\s/g, '');
        var number = parseInt(numberWithoutSpaces, 10);
        if (!isNaN(number) || numberWithoutSpaces === '-') {
            $(this).val(formatNumberWithSpaces(numberWithoutSpaces));
        } else {
            $(this).val('');
        }
    });
    </script>
{/if}