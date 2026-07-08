function openOpartSaasIframe(iframeLink, checkClose = false) {
  console.log(iframeLink);
  var popup = window.open(
    iframeLink,
    "openOpartSaasIframe",
    "width=900,height=780"
  );

  if (checkClose == true) {
    var checkPopupClosed = setInterval(function () {
      if (popup.closed) {
        clearInterval(checkPopupClosed);
        window.location.href = window.location.href; //avoid to post form data when reloading the page
        //window.location.reload();
      }
    }, 250);
  }
}

function openPopupWithAnimation(popupId) {
  $("#" + popupId).fadeIn("slow");
}

function openPopupWithAnimation(popupId) {
  $("#" + popupId).fadeIn("slow");
}

async function disableAndCheckIfShopIsActive(el) {
  var loadingEl = $("#checkIfShopIsActiveLoadingPhrase");
  setTimeout(function () {
    el.hide("fast", function () {
      loadingEl.show("fast");
    });
  }, 2000);

  const shopIsActive = await shopIsactive();
  if (shopIsActive == true) {
    loadingIco = loadingEl.find(".mi-history");
    validIco = loadingEl.find(".icon-check-circle");
    loadingIco.hide("fast", function () {
      validIco.show("fast");
    });
    setTimeout(function () {
      //location.reload();
      window.location.href = window.location.href; //avoid to post form data when reloading the page
    }, 1000);
  } else {
    setTimeout(function () {
      disableAndCheckIfShopIsActive(el);
    }, 5000);
  }
}

async function shopIsactive() {
  var result = await $.ajax({
    type: "POST",
    url: ajaxUrl + "&action=checkIfShopIsActive",
    dataType: "JSON",
    success: function (result) {
      return result;
    },
    error: function (XMLHttpRequest, textStatus, errorThrown) {
      console.log(textStatus);
      console.log(XMLHttpRequest);
      if (XMLHttpRequest.responseText) {
        console.log(XMLHttpRequest.responseText);
      } else {
        console.log("Undefined error");
      }
      return false;
    },
  });
  return result;
}

function calcMaxBudget() {
  maxOpartStatSession = parseInt(
    $("#maxOpartStatSession").val().replace(/\s/g, "")
  );
  maxGoogleAdsClicks = parseInt(
    $("#maxGoogleAdsClicks").val().replace(/\s/g, "")
  );

  if (maxOpartStatSession < -1 || isNaN(maxOpartStatSession)) {
    maxOpartStatSession = -1;
    $("#maxOpartStatSession").val(-1);
  }

  if (maxGoogleAdsClicks < -1 || isNaN(maxGoogleAdsClicks)) {
    maxGoogleAdsClicks = -1;
    $("#maxGoogleAdsClicks").val(-1);
  }

  target = $("#maxBudget span");

  if (maxOpartStatSession == -1 || maxGoogleAdsClicks == -1) {
    target.text("no limits");
    return;
  }

  pricePerLine = JSON.parse($("#pricePerLineInput").val());
  nbLines = maxOpartStatSession + maxGoogleAdsClicks;
  linesAlreadyCharged = 0;
  totalCost = 0;

  for (const [linesThreshold, costPerLine] of Object.entries(pricePerLine)) {
    console.log(linesThreshold);
    if (nbLines <= linesThreshold || linesThreshold == -1) {
      let linesToCharge = nbLines - linesAlreadyCharged;
      totalCost += linesToCharge * costPerLine;
      console.log("if");
      console.log(linesToCharge * costPerLine);
      break;
    } else {
      let linesToCharge = linesThreshold - linesAlreadyCharged;
      totalCost += linesToCharge * costPerLine;
      console.log("else");
      console.log(linesToCharge + "*" + costPerLine);
    }
    linesAlreadyCharged = linesThreshold;
  }
  totalCost = totalCost.toFixed(2);
  target.text(totalCost);
}

function formatNumberWithSpaces(x) {
  return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
}
