<div class="col">
    <div class="card customer-addresses-card">
        <h3 class="card-header" data-toggle="collapse" data-target="#opartStatUserVisitsContainer">
            <i class="material-icons">pageview</i>
            [Op'art Stat] {l s='Viewed pages' mod='opartstat'}
            <span class="badge badge-primary rounded" id="osTotalPages"> - </span>
        </h3>
        <div class="card-body collapse" id="opartStatUserVisitsContainer"></div>
    </div>
</div>

<div class="osListItemTemplate" style="display:none;">
    <div class="osCustomerJourneyDate">%createdAt%</div> - 
    <div class="osCustomerJourneyPageUrl"><a href="#" class="osDiplayUrlDetailBtn">%pageUrl%</a></div>
    <div class="osUrlInfosDetails" style="display:none">
        <strong>{l s='Source' mod='opartstat'}</strong> : %referrer%</br>
        <strong>{l s='Campaign' mod='opartstat'}</strong> : %utm_campaign%</br>
        <strong>{l s='Medium' mod='opartstat'}</strong> : %utm_medium%</br>   
        <strong>{l s='idCart' mod='opartstat'}</strong> : %idCart%</br>      
    </div>
</div>

<div class="osNewDayTemplate" style="display:none;">%newDay%</div>

<style type="text/css">
    .osListItem {
        clear:both;
        padding:8px 0;
        border-left:1px solid #6c868e;
    }
    .osNewDay {
        font-weight:bold;
        color: #25b9d7;
        margin:10px 0 0 -3px;
    }
    .osCustomerJourneyDate {
        display:inline-block;
        width:68px;
        color: #6c868e;
        padding-left:12px;
        position:relative;
    }
    .osCustomerJourneyDate::before {
        content: '•';
        color: #6c868e;
        font-size: 64px;
        position:absolute;
        top:-37px;
        left:-12px;
    }
    .osCustomerJourneyPageUrl {
        display:inline-block;
    }
    .osUrlInfosDetails {
        display:block;
        padding-left:25px;
        color:#6c868e;
    }
</style>

<script type="text/javascript">
    var userId = "{if isset($userId)}{$userId|escape:'javascript':'UTF-8'}{/if}";
    var cartId = "{if isset($cartId)}{$cartId|escape:'javascript':'UTF-8'}{/if}";
    var ajaxUrl = "{$ajaxUrl|escape:'javascript':'UTF-8'}";

    async function getVisits() {
        var result = await $.ajax({
            type: "POST",
            url: ajaxUrl,
            dataType: "JSON",
            data: {
                userId: userId,
                cartId: cartId
            },
            success: function(result) {
                displayVisits(result);
                return true;
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log(textStatus);
                console.log(XMLHttpRequest);
                console.log(XMLHttpRequest.responseText);
                return false;
            },
        });
    }

    function displayVisits(visits) {
        $('#osTotalPages').html(visits.length);
        var htmlTemplate = $(".osListItemTemplate").get(0).outerHTML;
        var htmlNewDayTemplate = $(".osNewDayTemplate").get(0).outerHTML;
        var htmlNewDayTemplate = htmlNewDayTemplate.replace("osNewDayTemplate", "osNewDay").replace("style=\"display:none;\"", "")
        var listContainerNode = $("#opartStatUserVisitsContainer");
        for (var key in visits) {
            var htmlToAdd = htmlTemplate;
            
            htmlToAdd = htmlToAdd.replace("osListItemTemplate", "osListItem").replace("style=\"display:none;\"", "");
            for (kw in visits[key]) {
                if (kw === "createdAt") {
                    var newDay = visits[key][kw].split(' ')[0];                    
                    if(key == 0) {
                        htmlToAdd = htmlNewDayTemplate.replace("%newDay%",newDay) + htmlToAdd;
                    }                        
                    else {
                        var oldDay = visits[key - 1][kw].split(' ')[0];
                        if (newDay !== oldDay) {                        
                            htmlToAdd = htmlNewDayTemplate.replace("%newDay%",newDay) + htmlToAdd;
                        }
                    } 
                val = visits[key][kw].split(' ')[1];              
                }
                else
                    val = visits[key][kw]
                htmlToAdd = htmlToAdd.replace("%" + kw + "%", val);                
            }
            if(visits[key]['referrer'] == "" && visits[key]['utm_medium'] == "" && visits[key]['utm_campaign'] == "" && visits[key]['idCart'] == "") {
                htmlToAdd = htmlToAdd.replace(/<div[^>]*class="osUrlInfosDetails"[^>]*>.*?<\/div>/gs, "");
                htmlToAdd = htmlToAdd.replace(/<a[^>]*class="osDiplayUrlDetailBtn"[^>]*>(.*?)<\/a>/gs, "$1");
            }
            listContainerNode.append(htmlToAdd);
        }
    }

    $(document).ready(function() {
        getVisits();

        $('#opartStatUserVisitsContainer').on('click', '.osDiplayUrlDetailBtn', function(e) {
            e.preventDefault();
            var divToToggle = $(this).parent().parent().find('.osUrlInfosDetails')
            if(divToToggle.is(':visible'))
                divToToggle.hide('fast');
            else
                divToToggle.show('fast');
        });
    });
</script>