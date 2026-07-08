/**
 * Prestashop module : OpartStat
 *
 * @author Olivier CLEMENCE <contact@store-opart.fr>
 * @copyright  Op'art
 * @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 */

async function displayList(result, metricName) {
  $("#" + metricName + "KillDisplay").val("true");
  //await sleep(100); //avoid browser freezing and let time to empt storedData field
  var storedData = $("#" + metricName + "StoredData").val();
  if (storedData == "") {
    var dataToStore = result;
  } else {    
    storedData = JSON.parse(storedData);
    var dataToStore = storedData;
    var initialValue = result["initial"]["value"];

    for (key in initialValue) {
      if (typeof storedData["initial"]["value"][key] !== "undefined") {
        dataToStore["initial"]["value"][key]["total"] = parseFloat(storedData["initial"]["value"][key]["total"]) + parseFloat(initialValue[key]["total"])
      } else {
        dataToStore["initial"]["value"][key] = initialValue[key];
      }
    }
    compareValue = [];
    if (typeof result["compare"] !== "undefined")
      var compareValue = result["compare"]["value"];
    
    for (key in compareValue) {
      if (typeof storedData["compare"]["value"][key] !== "undefined") {
        dataToStore["compare"]["value"][key]["total"] = parseFloat(storedData["compare"]["value"][key]["total"]) + parseFloat(compareValue[key]["total"]);
      } else {
        dataToStore["compare"]["value"][key] = compareValue[key];
      }
    }
  }

  if(typeof dataToStore !== "undefined")
    $("#" + metricName + "StoredData").val(JSON.stringify(dataToStore));

  if (result.conf["allDataLoaded"] == false) {
    dateFrom = mysqlToHumanDate(result.conf["dateFrom"], dateFormat);
    dateTo = mysqlToHumanDate(result.conf["dateTo"], dateFormat);
    dateFromCompare = mysqlToHumanDate(
      result.conf["dateFromCompare"],
      dateFormat
    );
    dateToCompare = mysqlToHumanDate(result.conf["dateToCompare"], dateFormat);

    /* var initialFilters = result.conf["initialFilters"]
    var compareFilters = result.conf["compareFilters"] */

    var initialFilters = prepareFiltersData('initial')
    var compareFilters = prepareFiltersData('compare') 

    //isCustom = (typeof result.conf["isCustom"] !== "undefined")?result.conf["isCustom"]:false;
    dir = $("#" + metricName + "Dir").val();
    ajaxCallBack = lowerFirstLetter(result.conf["ajaxCallBack"])  
    
    osLoadStat(
      metricName,
      dir,
      displayList,
      ajaxCallBack,
      dateFrom,
      dateTo,
      dateFromCompare,
      dateToCompare,
      initialFilters,
      compareFilters,
      result.conf["otherVars"]
    );
    return false;
  } else {
    $("#" + metricName + "KillDisplay").val("false");
    if(result.conf["otherVars"]["getCsv"] == 'true') {
      var csvDatas = createArrayForCsv(metricName)
      $('#'+metricName+'CsvExportButton').removeClass('isLoading');
      osExportToCsv(metricName,csvDatas);
    }
    else {
      $("#" + metricName + "LastLineDisplayedNumber").val(0)
      showResultList(metricName);
    }
      
  }
}

async function showResultList(metricName,lineToDisplay = null) {
  if(lineToDisplay == null)
    var lineToDisplay = $("#"+metricName + "DefaultNumberOfLines").val()    
  var target = metricName + "Container";
  var loader = metricName + "Loader";
  var itemListTemplateNode = $("#" + target).find(".osListItemTemplate");
  var listContainerNode = $("#" + target).find(".osListContainer");  
  var itemListHeaderNode = $("#" + target).find(".osListHeader");
  var totalContainer = $("#" + metricName + "TotalContainer");
  var htmlTemplate = itemListTemplateNode.get(0).outerHTML;

  var storedData = $("#" + metricName + "StoredData").val();

  var result = JSON.parse(storedData);

  $("#" + target)
    .find(".osListItem")
    .remove();

  var i =0;
  var thLabels = result.initial["conf"]["thLabels"]
  for(key in thLabels) {
    itemListHeaderNode.find(':contains("%'+key+'%")').each(function() {
      $(this).html($(this).html().replace('%'+key+'%', translateJsObject[thLabels[key]]));
    });
    //itemListHeaderNode.replace('%thLabel'+i+'%',thLabel)
  }

  var initialValue = [];
  var totals = [];

  if(result.initial.value != '')
    var initialColNamesWichNeedsTotal = getColNamesWichNeedsTotal(result.initial.value,totalContainer);

  for (key in result.initial.value) {
    initialValue.push(result.initial.value[key]);
    initialColNamesWichNeedsTotal.forEach(columnName => {
      if(typeof totals[columnName] === "undefined") 
        totals[columnName] = { value: 0 };

      totals[columnName]["value"] += parseFloat(result.initial.value[key][columnName])         
      totals[columnName]["type"] = result.initial["conf"][columnName];
    });
  }

  if(typeof result.initial["conf"]["sortColumn"] === "undefined") 
    sortColumn = "total"
  else
    sortColumn = result.initial["conf"]["sortColumn"]   

  initialValue.sort(dynamicSort(sortColumn, -1));
  var compareValue = null;

  if(result.compare.value != '') {
    compareValue = result.compare["value"];

    var compareColNamesWichNeedsTotal = getColNamesWichNeedsTotal(compareValue,totalContainer);

    for (key in compareValue) {
      compareColNamesWichNeedsTotal.forEach(columnName => {
        compareColumnName = columnName+"Compare";
        if(typeof totals[compareColumnName] === "undefined") 
          totals[compareColumnName] = { value: 0 };
  
        totals[compareColumnName]["value"] += parseFloat(compareValue[key][columnName])         
        totals[compareColumnName]["type"] = result.compare["conf"][columnName];
      });
    }
  }

  $("#" + target).fadeIn("slow");

  var expanderNode = listContainerNode.parent();
  //var expanderParent = expanderNode.parent();

  var loop = 0;
  var nbLinesAdded = 0;
  var lastLineDisplayedNumber = parseInt($("#" + metricName + "LastLineDisplayedNumber").val(),10);

  if(lineToDisplay == -1)
    var maxLineToDisplay = -1;
  else
    var maxLineToDisplay = lastLineDisplayedNumber + lineToDisplay;

  var packHtmlToAdd = "";

  compareValueWithoutInititalValueCorrespondance = compareValue;

  for (var key in initialValue) {
    if (maxLineToDisplay != -1 && nbLinesAdded >= maxLineToDisplay) {
      $("#" + metricName + "LastLineDisplayedNumber").val(nbLinesAdded);
      break;
    }     
    
    if ($("#" + metricName + "KillDisplay").val() == "true") {
      return false;
    }  

    htmlToAdd = getHtmlToAdd(result,htmlTemplate,initialValue[key],'initial',compareValue);

    /* var htmlToAdd = htmlTemplate;

    htmlToAdd = htmlToAdd.replace("osListItemTemplate", "osListItem");
    for (kw in initialValue[key]) {
      val = displayDataToHuman(initialValue[key][kw],result.initial["conf"][kw])
      htmlToAdd = htmlToAdd.replace("%" + kw + "%", val);
    }

    if (compareValue != null) {
      var itemId = initialValue[key]["id"];

      if (typeof compareValue[itemId] !== "undefined") {
        for (kw in compareValue[itemId]) {
          val = displayDataToHuman(compareValue[itemId][kw],result.compare["conf"][kw])
          htmlToAdd = htmlToAdd.replace("%compare_" + kw + "%", val);
        }
        var percentVariation = "-";
        percentVariationHtml = "";
        let areSameSign = (initialValue[key]["total"] * compareValue[itemId]["total"]) > 0;
        if (initialValue[key]["total"] != 0 && compareValue[itemId]["total"] != 0 && areSameSign) {
          var percentVariation = calcPercentVariation(
            initialValue[key]["total"],
            compareValue[itemId]["total"]
          );

          percentVariationHtml = getPercentVariationHtml(percentVariation,result);
        }

        htmlToAdd = htmlToAdd.replace(
          "%compare_percent_variation%",
          percentVariationHtml
        );

        delete compareValueWithoutInititalValueCorrespondance[itemId];
      } else {
        var i = 0;
        while (i < 1) {
          htmlToAdd = htmlToAdd.replace(/%compare_(.*?)%/, "-");
          if (htmlToAdd.indexOf("%compare_") == -1) i = i + 1;
        }
      }
    } */

    packHtmlToAdd += htmlToAdd;    
    if(loop >= 99) {
      listContainerNode.append(packHtmlToAdd);
      loop = 0;
      packHtmlToAdd = "";
    }
    loop++;
    nbLinesAdded++;
  }

  if(loop < 99)
      listContainerNode.append(packHtmlToAdd);

  var packHtmlToAdd = "";

  if (compareValue != null) {
    for(key in compareValueWithoutInititalValueCorrespondance) {
      if (maxLineToDisplay != -1 && nbLinesAdded >= maxLineToDisplay) {
        $("#" + metricName + "LastLineDisplayedNumber").val(nbLinesAdded);
        break;
      }     
      
      if ($("#" + metricName + "KillDisplay").val() == "true") {
        return false;
      }    
      htmlToAdd = getHtmlToAdd(result,htmlTemplate,compareValueWithoutInititalValueCorrespondance[key],'compare');

      packHtmlToAdd += htmlToAdd;    
      if(loop >= 99) {
        listContainerNode.append(packHtmlToAdd);
        loop = 0;
        packHtmlToAdd = "";
      }
      loop++;
      nbLinesAdded++;
    }

    

    $("#" + target).find(".osCompareCol").show();  
    for (totalKw in totals) {    
      if (totals.hasOwnProperty(totalKw + "Compare")) {
        percentVariation = calcPercentVariation(totals[totalKw]["value"],totals[totalKw + "Compare"]["value"]);
        percentVariationHtml = getPercentVariationHtml(percentVariation,result);
        totals[totalKw + "ComparePercentVariation"] = { 
          value: percentVariationHtml, 
          type : "html"
        }        
      }
    }
  }

  if(loop < 99)
    listContainerNode.append(packHtmlToAdd);

  for (totalKw in totals) {    
    if(totals[totalKw]["type"]!='html')
      val = displayDataToHuman(totals[totalKw]["value"],totals[totalKw]["type"])    
    else
      val = totals[totalKw]["value"]
    totalContainer.find("."+totalKw+"Total").html(val)
    totalContainer.show("fast");
  }

  if(initialValue.length > maxLineToDisplay) {
    $("#" + target).parent().find(".osShowMoreLink").show();
    $("#" + target).parent().find(".osShowAllLink").show();
  }
  if (expanderNode.get(0).scrollHeight > expanderNode.height()) {
    var heightToGo = expanderNode.get(0).scrollHeight;
    expanderNode.animate(
      {
        height: heightToGo,
      },
      1000
    );
  }

  $("#" + loader).fadeOut("fast");
}

function createArrayForCsv(metricName) {
  var storedData = $("#" + metricName + "StoredData").val();
  var result = JSON.parse(storedData);

  var datasForCsv = [];
  var initRawTotalValue = [];
  for (var key in result.initial.value) {
    var dataToPush = {};
    for(var kw in result.initial.value[key]) {
        if(kw == "total")
          initRawTotalValue[key] = result.initial.value[key][kw];
        
        if(result.initial["conf"][kw] == "price") 
          dataToPush[kw] = formatPriceForCsv(result.initial.value[key][kw]); 
        else
          dataToPush[kw] = displayDataToHuman(result.initial.value[key][kw],result.initial["conf"][kw]);       
    }
    datasForCsv.push(dataToPush);
  }

  if(typeof result.initial["conf"]["sortColumn"] === "undefined") 
    sortColumn = "total"
  else
    sortColumn = result.initial["conf"]["sortColumn"]   

  datasForCsv.sort(dynamicSort(sortColumn, -1));
  var compareValue = null;

  if(result.compare.value != '') {
    compareValue = result.compare["value"];
  }

  for (var key in datasForCsv) {
    datasForCsv[key]["compareTotal"] = "";
    datasForCsv[key]["percentVariation"] = "";  
    
    if (compareValue != null) {
      var itemId = datasForCsv[key]["id"];
      if (typeof compareValue[itemId] !== "undefined") {
        if(result.initial["conf"][kw] == "price") 
            datasForCsv[key]["compareTotal"] = formatPriceForCsv(compareValue[itemId]["total"] );
          else
            datasForCsv[key]["compareTotal"] = displayDataToHuman(compareValue[itemId]["total"],result.compare["conf"]["total"]); 

        var percentVariation = "-";
        if (initRawTotalValue[key] != 0 && compareValue[itemId]["total"] != 0) {
          var percentVariation = calcPercentVariation(
            initRawTotalValue[itemId],
            compareValue[itemId]["total"]
          );                
           
          datasForCsv[key]["percentVariation"] = percentVariation  
        }  
      }
    }
  }
  return datasForCsv; 
}

function collapseListContent(metricName) {
  var listItems = $("#" + metricName + "Container .osListItem");
  var defaultNumberOfLines = $("#"+metricName + "DefaultNumberOfLines").val()
  var nbItemsToRemove = listItems.length - defaultNumberOfLines;
  var itemsToRemove = listItems.slice(-nbItemsToRemove);
  itemsToRemove.remove();

  $("#" + metricName + "Container").parent().find(".osShowLessLink").hide();
  
  var listContainerNode = $("#" + metricName + "Container").find(".osListContainer");  
  var heightToGo = listContainerNode.height();
  var expanderNode = listContainerNode.parent();
  expanderNode.css('height', heightToGo + 'px');
  $("#" + metricName + "LastLineDisplayedNumber").val(defaultNumberOfLines);
}

function getColNamesWichNeedsTotal(values,totalContainer) {
  var firstKey = Object.keys(values)[0]; 
  var firstLine = values[firstKey]; 
  var keysToTest = Object.keys(firstLine);
  var keys = [];
  keysToTest.forEach(function(key) {
    if (totalContainer.find('.' + key + 'Total').length > 0) {
      keys.push(key);
    }
  });
  return keys;
}

function getPercentVariationHtml(percentVariation,result) {
  if(percentVariation==0) {
    percentVariationClass = 'osFlatColor'
    var trendIco = "trending_flat"
  }
  else if(percentVariation>0 && result.conf.superiorIsBetter == true) {
    percentVariationClass = 'osUpColor'
    var trendIco = "trending_up"
  }          
  else if(percentVariation<0 && result.conf.superiorIsBetter == false) {
    percentVariationClass = 'osUpColor'
    var trendIco = "trending_up"
  }
  else {
    percentVariationClass = 'osDownColor'
    var trendIco = "trending_down"
  }

  percentVariationHtml = "<span class='"+percentVariationClass+"'>(<i class='material-icons "+trendIco+"'><span>"+trendIco+"</span></i>"+percentVariation+"%)</span>";
  return percentVariationHtml;
}

function getHtmlToAdd(result,htmlTemplate,values,type,compareValue = null) {
  var htmlToAdd = htmlTemplate;
  htmlToAdd = htmlToAdd.replace("osListItemTemplate", "osListItem");
  for (kw in values) {
    val = displayDataToHuman(values[kw],result[type]["conf"][kw])
    if(type == 'compare') {
      htmlToAdd = htmlToAdd.replace("%compare_percent_variation%", "-"); 
      if(kw == "total") 
        htmlToAdd = htmlToAdd.replace("%compare_total%", val);      
      if(kw != "name")
        val = "-";
    }    
    htmlToAdd = htmlToAdd.replace("%" + kw + "%", val);  
  }

  if (compareValue != null) {
    var itemId = values["id"];

    if (typeof compareValue[itemId] !== "undefined") {
      for (kw in compareValue[itemId]) {
        val = displayDataToHuman(compareValue[itemId][kw],result.compare["conf"][kw])
        htmlToAdd = htmlToAdd.replace("%compare_" + kw + "%", val);
      }
      var percentVariation = "-";
      percentVariationHtml = "";
      let areSameSign = (values["total"] * compareValue[itemId]["total"]) > 0;
      if (values["total"] != 0 && compareValue[itemId]["total"] != 0 && areSameSign) {
        var percentVariation = calcPercentVariation(
          values["total"],
          compareValue[itemId]["total"]
        );

        percentVariationHtml = getPercentVariationHtml(percentVariation,result);
      }

      htmlToAdd = htmlToAdd.replace(
        "%compare_percent_variation%",
        percentVariationHtml
      );

      delete compareValueWithoutInititalValueCorrespondance[itemId];
    } else {
      var i = 0;
      while (i < 1) {
        htmlToAdd = htmlToAdd.replace(/%compare_(.*?)%/, "-");
        if (htmlToAdd.indexOf("%compare_") == -1) i = i + 1;
      }
    }
  }
  return htmlToAdd;
}
