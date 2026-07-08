function createAndCopyTrackableLink() {
    $('#trackableLinkContainer').hide();    
    $('#trackableLinkContainer').removeClass('trackableLinkOK');
    $('#trackableLinkContainer').removeClass('trackableLinkKO');

    url = $('#trackableUrl').val()
    source = $('#trackableSource').val()
    medium = $('#trackableMedium').val()
    campaign = $('#trackableCampaign').val()

    urlExp = /https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&//=]*)/gi
    urlRegExp = new RegExp(urlExp)
    errors = []
    
    if (!url.match(urlRegExp)) 
        errors.push(trackableLink_jsErrorMsg_url)

    urlArray = url.split('#')

    if (!/^([a-zA-Z0-9_\-\.]*)$/.test(source)) 
        errors.push(trackableLink_jsErrorMsg_source)    

    if (!/^([a-zA-Z0-9_\-\.]*)$/.test(medium)) 
        errors.push(trackableLink_jsErrorMsg_medium)

    if (!/^([a-zA-Z0-9_\-\.]*)$/.test(campaign)) 
        errors.push(trackableLink_jsErrorMsg_campaign)

    if(errors.length > 0) {
        errorMsg = "";
        errors.forEach(error => {
            errorMsg += error+"<br />"
        });
        $('#trackableLinkContainer').html(errorMsg);
        $('#trackableLinkContainer').addClass('trackableLinkKO');
        $('#trackableLinkContainer').show('slow');
        return false;
    }
    trackableLink = urlArray[0]
    if(source!="")
        trackableLink = (trackableLink.includes('?'))?trackableLink+"&utm_source="+source:trackableLink+"?utm_source="+source;
    
    if(medium!="")
        trackableLink = (trackableLink.includes('?'))?trackableLink+"&utm_medium="+medium:trackableLink+"?utm_medium="+medium;
            
    if(campaign!="")
        trackableLink = (trackableLink.includes('?'))?trackableLink+"&utm_campaign="+campaign:trackableLink+"?utm_campaign="+campaign;

    if(urlArray.hasOwnProperty(1))
        trackableLink = trackableLink+'#'+urlArray[1]

    console.log(navigator);
    navigator.clipboard.writeText(trackableLink);

    htmlReturn = trackableLink+"<br /><br />"+trackableLink_jsValidMsg

    $('#trackableLinkContainer').html(htmlReturn);
    $('#trackableLinkContainer').addClass('trackableLinkOK');
    $('#trackableLinkContainer').show('slow');

}

function populateTrackableFields(el) {
    console.log(el)
    values = el.val().split('|')
    source = values[0]
    medium = values[1]
    campaign = values[2]

    $('#trackableSource').val(source)
    $('#trackableMedium').val(medium)
    $('#trackableCampaign').val(campaign)
}