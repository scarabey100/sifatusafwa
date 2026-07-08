/**
 * Prestashop module : OpartStat
 *
 * @author Olivier CLEMENCE <contact@store-opart.fr>
 * @copyright  Op'art
 * @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 */

function osChangeInputDateValue(inputId, value) {
  $("#" + inputId).val(value);
  /* $("#span_" + inputId).text(value); */
}

function saveSettings(reportName, initialStatsToLoad) {
  preset1 = $("#osPresetDateInitial").val();
  preset2 = $("#osPresetDateCompare").val();
  initialFilters = prepareFiltersData("initial");
  compareFilters = prepareFiltersData("compare");
  if (initialFilters == false || compareFilters == false) return false;

  if (preset1 == "custom") {
    var dateFrom = $("#dateFrom").val();
    var dateTo = $("#dateTo").val();
  } else {
    var dateFrom = null;
    var dateTo = null;
  }
  if (preset2 == "custom") {
    var dateFromCompare = $("#dateFromCompare").val();
    var dateToCompare = $("#dateToCompare").val();
  } else {
    var dateFromCompare = null;
    var dateToCompare = null;
  }

  $.ajax({
    type: "POST",
    url: ajaxUrl + "&action=saveSettings",
    dataType: "JSON",
    data: {
      preset1: preset1,
      preset2: preset2,
      initialFilters,
      compareFilters,
      dateFrom,
      dateTo,
      dateFromCompare,
      dateToCompare,
      reportName: reportName,
    },
    success: function (result) {
      console.log(result);
      target = "saveThisSettings";
      if (result["errorMsg"] != "") {
        $("#" + target).removeClass("isLoading");
        alert(result["errorMsg"]);
      } else {
        displaySavedOkIcon(target);
        setTimeout(function () {
          osSubmitCalendar(initialStatsToLoad);
        }, 1000);
      }
      return true;
    },
    error: function (XMLHttpRequest, textStatus, errorThrown) {
      console.log(textStatus);
      console.log(XMLHttpRequest);
      console.log(XMLHttpRequest.responseText);
      return false;
    },
  });
}

function displaySavedOkIcon(target) {
  var originalText = $("#" + target + " i").text();
  $("#" + target).fadeOut("fast", function () {
    $("#" + target).removeClass("isLoading");
    $("#" + target + " i span").text("check_circle");
    $("#" + target).fadeIn("fast", function () {
      setTimeout(function () {
        $("#" + target).fadeOut("fast", function () {
          $("#" + target + " i span").text(originalText);
          $("#" + target).fadeIn("fast");
        });
      }, 1000);
    });
  });
}

/* function displayLoaders(statsToLoad) {
  statsToLoad.forEach((item) => {
    displayLoader(item["metricName"]);
  });  
} */

function displayLoader(metricName) {
  var target = metricName + "Container";
  var loader = metricName + "Loader";
  $("#" + target).hide();
  $("#" + loader).fadeIn("slow");
  $("#" + target)
    .parent()
    .find(".osErrorMsg")
    .remove();
}

/* for test purpose only use it to test effect when loading is long */
function sleep(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

let currentLoadingId = 0;

function osSubmitDate(
  dateFrom,
  dateTo,
  dateFromCompare,
  dateToCompare,
  initialStatsToLoad
) {
  let statsToLoad = [...initialStatsToLoad];
  var errors = [];

  if (dateFrom != -1) {
    var dateInputsToCheck = [
      "dateFrom",
      "dateTo",
      "dateFromCompare",
      "dateToCompare",
    ];

    dateInputsToCheck.forEach(function (elId) {
      var el = $("#" + elId);
      if (!dateIsValid(el.val(), dateFormat, el.data("allowNull"))) {
        var msg = el.data("errorMsg");
        el.addClass("osDateInputError");
        errors.push(msg);
      }
    });
  }

  if (isItemReport == true) {
    var el = $("#selectedItems");
    if (!isJson(el.val())) {
      var msg = el.data("errorMsg");
      $("#searchItem").addClass("osDateInputError");
      errors.push(msg);
    }
  }

  if (errors.length > 0) {
    var errorMsg = "<ul>";
    errors.forEach(function (txt) {
      errorMsg = errorMsg + "<li>" + txt + "</li>";
    });
    errorMsg = errorMsg + "</ul>";
    showErrorMessage(errorMsg);
    return false;
  }

  initialFilters = prepareFiltersData("initial");
  compareFilters = prepareFiltersData("compare");
  //displayLoaders(statsToLoad)
  currentLoadingId++;
  osChainLoadStats(
    statsToLoad,
    dateFrom,
    dateTo,
    dateFromCompare,
    dateToCompare,
    initialFilters,
    compareFilters,
    currentLoadingId
  );

  displayFiltersSummary(initialFilters, "initial");
  if ($("#compareOrNot").is(":checked")) {
    displayFiltersSummary(compareFilters, "compare");
  }
}

async function osChainLoadStats(
  statsToLoad,
  dateFrom,
  dateTo,
  dateFromCompare,
  dateToCompare,
  initialFilters = null,
  compareFilters = null,
  loadingId
) {
  if (statsToLoad.length == 0 || loadingId !== currentLoadingId) return false;

  /* console.log(currentLoadingId); */
  /* test purpose only */
  /* if(statsToLoad[0].metricName!="bestRevenuesProducts")
    return false */

  await osLoadStat(
    statsToLoad[0].metricName,
    statsToLoad[0].dir,
    statsToLoad[0].callBack,
    statsToLoad[0].ajaxCallBack,
    dateFrom,
    dateTo,
    dateFromCompare,
    dateToCompare,
    initialFilters,
    compareFilters
  )
    .then((result) => {
      statsToLoad.shift();
      if (statsToLoad.length > 0)
        osChainLoadStats(
          statsToLoad,
          dateFrom,
          dateTo,
          dateFromCompare,
          dateToCompare,
          initialFilters,
          compareFilters,
          loadingId
        );
    })
    .catch((error) => {
      //return false;//remove it to load stat even if last metric fail
      statsToLoad.shift();
      if (statsToLoad.length > 0)
        osChainLoadStats(
          statsToLoad,
          dateFrom,
          dateTo,
          dateFromCompare,
          dateToCompare,
          initialFilters,
          compareFilters,
          loadingId
        );
    });
}

async function osLoadStat(
  metricName,
  dir,
  callBack,
  ajaxCallBack,
  dateFrom,
  dateTo,
  dateFromCompare,
  dateToCompare,
  initialFilters,
  compareFilters,
  otherVars = {}
) {
  /* await sleep(500) */ //for test purpose only
  if (otherVars["getCsv"] != "true") {
    displayLoader(metricName);
  }
  var selectedItemsId = $("#selectedItems").val();

  if (!isJson(selectedItemsId)) selectedItemsId = null;

  var result = await $.ajax({
    type: "POST",
    url: ajaxUrl + "&action=loadStat",
    dataType: "JSON",
    data: {
      dir: dir,
      ajaxCallBack: ajaxCallBack,
      metricName: metricName,
      dateFrom: dateFrom,
      dateTo: dateTo,
      dateFromCompare: dateFromCompare,
      dateToCompare: dateToCompare,
      dateFormat: dateFormat,
      selectedItemsId: selectedItemsId,
      initialFilters: initialFilters,
      compareFilters: compareFilters,
      otherVars: otherVars,
      //idShop : idShop
    },
    success: function (result) {
      if ("action" in result.initial) {
        executeRequiredAction(result.initial.action, metricName);
        return false;
      }
      if ("compare" in result) {
        if ("action" in result.compare) {
          executeRequiredAction(result.compare.action, metricName);
          return false;
        }
      }
      callBack(result, metricName);
      target = $("#" + metricName + "Container");
      reloadBtn = target.closest(".panel").find(".reloadBtn");
      reloadBtn.removeClass("isLoading");
      return true;
    },
    error: function (XMLHttpRequest, textStatus, errorThrown) {
      console.log(textStatus);
      console.log(XMLHttpRequest);
      console.log(XMLHttpRequest.responseText);
      displayMetricError(metricName);
      return false;

      /*console.log(errorThrown); */
    },
  });
  return result;
}

function getFilters() {
  var filters = [];
  filters["dateFrom"] = $("#dateFrom").val();

  filters["dateTo"] = $("#dateTo").val();

  filters["dateFromCompare"] =
    $("#dateFromCompare").val() == "" ? null : $("#dateFromCompare").val();
  filters["dateToCompare"] =
    $("#dateToCompare").val() == "" ? null : $("#dateToCompare").val();

  filters["initialFilters"] = prepareFiltersData("initial");
  filters["compareFilters"] = prepareFiltersData("compare");
  filters["otherVars"] = {};

  return filters;
}

function reloadMetric(metric) {
  $("#" + metric["metricName"] + "StoredData").val("");

  var filters = getFilters();

  filters["otherVars"]["useCache"] = false;

  var selectedItemsId = $("#selectedItems").val();
  if (!isJson(selectedItemsId)) selectedItemsId = null;

  osLoadStat(
    metric["metricName"],
    metric["dir"],
    metric["callBack"],
    metric["ajaxCallBack"],
    filters["dateFrom"],
    filters["dateTo"],
    filters["dateFromCompare"],
    filters["dateToCompare"],
    filters["initialFilters"],
    filters["compareFilters"],
    filters["otherVars"]
  );
}

function getCsvDatas(metric) {
  $("#" + metric["metricName"] + "StoredData").val("");

  var filters = getFilters();
  filters["otherVars"].getCsv = "true";

  var selectedItemsId = $("#selectedItems").val();
  if (!isJson(selectedItemsId)) selectedItemsId = null;

  osLoadStat(
    metric["metricName"],
    metric["dir"],
    metric["callBack"],
    metric["ajaxCallBack"],
    filters["dateFrom"],
    filters["dateTo"],
    filters["dateFromCompare"],
    filters["dateToCompare"],
    filters["initialFilters"],
    filters["compareFilters"],
    filters["otherVars"]
  );
}

/* function getCsvDatas(metric) {
  var metricName = metric['metricName'];
  $("#" + metricName + "StoredData").val('');
  var dir = metric['dir'];
  var callBack = metric['callBack'];
  var ajaxCallBack = metric['ajaxCallBack'];

  var dateFrom = $("#dateFrom").val();
  var dateTo = $("#dateTo").val();

  var dateFromCompare =
    $("#dateFromCompare").val() == "" ? null : $("#dateFromCompare").val();
  var dateToCompare =
    $("#dateToCompare").val() == "" ? null : $("#dateToCompare").val();

  initialFilters = prepareFiltersData('initial')
  compareFilters = prepareFiltersData('compare')

  var selectedItemsId = $("#selectedItems").val();

  if (!isJson(selectedItemsId)) selectedItemsId = null;
  otherVars = {};
  otherVars.getCsv = 'true';

  osLoadStat(
    metricName,
    dir,
    callBack,
    ajaxCallBack,
    dateFrom,
    dateTo,
    dateFromCompare,
    dateToCompare,
    initialFilters,
    compareFilters,
    otherVars
  );
} */

function displayMetricError(metricName) {
  var target = metricName + "Container";
  var loader = metricName + "Loader";
  $("#" + loader).fadeOut("fast", function () {
    /* $("#" + target).replaceWith(osErrorMsg); */
    $("#" + target)
      .parent()
      .append(osErrorMsg);
    //$("#" + target).fadeIn("slow");
  });
}

/* function toggleContentHeight(e) {
  e.preventDefault();
  var el = $(this).parent().find(".osListExpander");
  if (el.data("expanded") == true) {
    el.data("expanded", false);
    var heightToGo = el.data("initalHeight");
    el.removeData("initalHeight");
    $(this).text($(this).data("openedText"));
  } else {
    el.data("expanded", true);
    el.data("initalHeight", el.height());
    var heightToGo = el.get(0).scrollHeight;
    $(this).text($(this).data("closedText"));
  }
  el.animate(
    {
      height: heightToGo,
    },
    1000,
    function () {
      if (el.data("expanded") == true) {
        el.css("height", "auto");
      }
    }
  );
} */

/* function expandListContent(e) {
  e.preventDefault();
  var el = $(this).parent().find(".osListExpander");
  if (el.data("expanded") == true) {
    el.data("expanded", false);
    var heightToGo = el.data("initalHeight");
    el.removeData("initalHeight");
    $(this).text($(this).data("openedText"));
  } else {
    el.data("expanded", true);
    el.data("initalHeight", el.height());
    var heightToGo = el.get(0).scrollHeight;
    $(this).text($(this).data("closedText"));
  }
  el.animate(
    {
      height: heightToGo,
    },
    1000,
    function () {
      if (el.data("expanded") == true) {
        el.css("height", "auto");
      }
    }
  );
} */

async function saveMetricConfig(
  metrics,
  reportName,
  removeAllOldMetric = false
) {
  var result = await $.ajax({
    type: "POST",
    url: ajaxUrl + "&action=saveConfig",
    dataType: "JSON",
    data: {
      metrics: metrics,
      reportName: reportName,
      removeAllOldMetric: removeAllOldMetric,
      //opartStatToken: opartStatToken,
    },
    success: function (result) {
      //location.reload();
      var reloadUrl =
        ajaxUrl.replace("&ajax=1", "") + "&reportName=" + reportName;
      window.location.href = reloadUrl;
      return true;
    },
    error: function (XMLHttpRequest, textStatus, errorThrown) {
      console.log(textStatus);
      console.log(XMLHttpRequest);
      console.log(XMLHttpRequest.responseText);
      return false;
      /*console.log(errorThrown); */
    },
  });
}

async function saveMetricSelectedPeriod(metric, reportName) {
  var result = await $.ajax({
    type: "POST",
    url: ajaxUrl + "&action=SaveMetricSelectedPeriod",
    dataType: "JSON",
    data: {
      metric: metric,
      reportName: reportName,
    },
    success: function (result) {
      //location.reload();
      displaySavedOkIcon(result + "SavePeriodBtn");
      /* $('#'+result+'SavePeriodBtn').removeClass('isLoading') */
      return true;
    },
    error: function (XMLHttpRequest, textStatus, errorThrown) {
      console.log(textStatus);
      console.log(XMLHttpRequest);
      console.log(XMLHttpRequest.responseText);
      return false;
      /*console.log(errorThrown); */
    },
  });
}

function calcPercentVariation(var1, var2) {
  var variationPercent = (var1 / var2 - 1) * 100;
  variationPercent =
    Math.round((variationPercent + Number.EPSILON) * 100) / 100;
  return variationPercent;
}

function dateIsValid(date, dateFormat, allowNull) {
  if (allowNull == true && (date == null || date == "")) return true;

  var dateArray = date.split("/");
  var y = dateArray[2];
  if (y.length != 4) return false;
  if (dateFormat == "dd/mm/yy") {
    var m = dateArray[1];
    var d = dateArray[0];
  } else {
    var m = dateArray[0];
    var d = dateArray[1];
  }
  if (m > 12 || m < 1 || d < 1) return false;

  if (m == 2 && d > 29)
    //allow 29 days in february for bisextil
    return false;

  if (
    (m == 1 || m == 3 || m == 5 || m == 7 || m == 8 || m == 10 || m == 31) &&
    d > 31
  )
    return false;

  if ((m == 4 || m == 6 || m == 9 || m == 11) && d > 30) return false;

  d = new Date(y, m, d);
  if (Object.prototype.toString.call(d) !== "[object Date]") return false;
  if (isNaN(d.getTime())) return false;
  return true;
}

function mysqlToHumanDate(date, dateFormat) {
  var jsDate = new Date(date);
  var d = jsDate.getDate();
  var m = jsDate.getMonth() + 1;
  var y = jsDate.getFullYear();
  if (dateFormat == "dd/mm/yy") return d + "/" + m + "/" + y;
  else return m + "/" + d + "/" + y;
}

function dynamicSort(property, sortOrder = 1) {
  if (property[0] === "-") {
    sortOrder = -1;
    property = property.substr(1);
  }
  return function (a, b) {
    var result =
      parseFloat(a[property]) < parseFloat(b[property])
        ? -1
        : parseFloat(a[property]) > parseFloat(b[property])
        ? 1
        : 0;
    return result * sortOrder;
  };
}

function displayDataToHuman(data, dataFormat) {
  //if (dataFormat == "price") var val = getCurrencyFormatter().format(data);
  if (dataFormat == "price") var val = osDisplayPriceToHuman(data);
  else if (dataFormat == "float0") var val = parseFloat(data).toFixed(0);
  else if (dataFormat == "float2") var val = parseFloat(data).toFixed(2);
  else if (dataFormat == "float4") var val = parseFloat(data).toFixed(4);
  else if (dataFormat == "percent") var val = parseFloat(data).toFixed(2) + "%";
  else var val = data;

  return val;
}
//formatCurrency(price, currencyFormat, currencySign, currencyBlank)
function osSubmitCalendar(initialStatsToLoad) {
  $(".storeDataField").val("");
  $(".osDisplayListBtn").each(function () {
    toggleListPieChart($(this), true);
  });

  var dateFrom = $("#dateFrom").val();
  var dateTo = $("#dateTo").val();

  var dateFromCompare =
    $("#dateFromCompare").val() == "" ? null : $("#dateFromCompare").val();
  var dateToCompare =
    $("#dateToCompare").val() == "" ? null : $("#dateToCompare").val();

  $("#filtersContainer").hide("fast");

  osSubmitDate(
    dateFrom,
    dateTo,
    dateFromCompare,
    dateToCompare,
    initialStatsToLoad
  );
}

function setCalendarDate(calendarId, startDate, endDate) {
  if (calendarId == "datepickerInitial") {
    osChangeInputDateValue("dateFrom", startDate);
    osChangeInputDateValue("dateTo", endDate);
  } else {
    osChangeInputDateValue("dateFromCompare", startDate);
    osChangeInputDateValue("dateToCompare", endDate);
  }
  $("#" + calendarId).datepicker("setDate", endDate);
  $("#" + calendarId).datepicker("refresh");
}

function jsDateToHumanDate(date, dateFormat) {
  var day = ("0" + date.getDate()).slice(-2);
  var month = ("0" + (date.getMonth() + 1)).slice(-2);
  var year = date.getFullYear();

  if (dateFormat == "dd/mm/yy") return day + "/" + month + "/" + year;
  else return month + "/" + day + "/" + year;
}

function getPresetPeriod(period, forHuman) {
  let d = new Date();
  let startDate = new Date();
  let endDate = new Date();
  if (period == "today") {
    startDate = d;
    endDate = d;
  } else if (period == "yesterday") {
    d.setDate(d.getDate() - 1);
    startDate = d;
    endDate = d;
  } else if (
    period == "last7" ||
    period == "last30" ||
    period == "last90" ||
    period == "last365"
  ) {
    const daysNb = parseInt(period.replace(/^\D+/g, ""));
    startDate.setDate(d.getDate() - daysNb);
    endDate.setDate(d.getDate() - 1);
  } else if (period == "lastWeek") {
    var day = d.getDay();
    var diffToMonday = d.getDate() - day + (day == 0 ? -6 : 1); // adjust when day is Sunday
    startDate = new Date(d.getFullYear(), d.getMonth(), diffToMonday - 7);
    endDate = new Date(d.getFullYear(), d.getMonth(), diffToMonday - 1);
  } else if (period == "lastMonth") {
    startDate = new Date(d.getFullYear(), d.getMonth() - 1, 1);
    endDate = new Date(d.getFullYear(), d.getMonth(), 0);
  } else if (period == "weekToDate") {
    var day = d.getDay();
    diff = d.getDate() - day + (day == 0 ? -6 : 1); // adjust when day is sunday
    startDate = new Date();
    startDate.setDate(diff);
    endDate = d;
  } else if (period == "monthToDate") {
    startDate = new Date(d.getFullYear(), d.getMonth(), 1);
    endDate = d;
  } else if (period == "lastYear") {
    startDate = new Date(d.getFullYear() - 1, 0, 1);
    endDate = new Date(d.getFullYear() - 1, 12, 0);
  } else if (period == "yearToDate") {
    startDate = new Date(d.getFullYear(), 0, 1);
    endDate = d;
  } else if (period == "samePeriod") {
    startDate = $.datepicker.parseDate(dateFormat, $("#dateFrom").val());
    startDate.setFullYear(startDate.getFullYear() - 1);

    endDate = $.datepicker.parseDate(dateFormat, $("#dateTo").val());
    endDate.setFullYear(endDate.getFullYear() - 1);
  } else if (period == "previousPeriod") {
    var dateFrom = $.datepicker.parseDate(dateFormat, $("#dateFrom").val());
    var dateTo = $.datepicker.parseDate(dateFormat, $("#dateTo").val());

    var diffTime = Math.abs(dateTo - dateFrom);
    var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    startDate = new Date(dateFrom);
    startDate.setDate(startDate.getDate() - diffDays - 1);
    endDate = new Date(dateTo);
    endDate.setDate(endDate.getDate() - diffDays - 1);
  }
  if (forHuman == true) {
    startDate = jsDateToHumanDate(startDate, dateFormat);
    endDate = jsDateToHumanDate(endDate, dateFormat);
  }
  return [startDate, endDate];
}

function prepareFiltersData(filterType) {
  filtersData = {};
  $(".displayFilterContainer")
    .find(".tempoFiltersValues")
    .each(function () {
      if ($(this).data("filterType") == filterType) {
        let excludeInclude = $(this).data("filterExcludeInclude");
        if (typeof filtersData[excludeInclude] === "undefined")
          filtersData[excludeInclude] = {};

        //filtersData[excludeInclude]["categories"]["getAllChildren"] = {};

        filterName = $(this).data("filterName");

        if (
          filtersData[excludeInclude][filterName] &&
          (!filtersData[excludeInclude][filterName]["values"] ||
            filtersData[excludeInclude][filterName]["values"].length == 0)
        )
          return; //in jquery each return without false is the same as continue

        //console.log(filtersData[excludeInclude]["categories"]["values"].length)
        if (filterName == "getAllChildrenCategories") {
          if (
            !filtersData[excludeInclude]["categories"] ||
            filtersData[excludeInclude]["categories"]["values"].length == 0
          )
            return;
          filterValue = $(this).is(":checked") ? 1 : 0;
          filtersData[excludeInclude]["categories"]["getAllChildren"] =
            filterValue;
          return; //in jquery each return without false is the same as continue
        }

        /* if (filterName.endsWith("_useAnd")) {
          filterValue = $(this).val();
          if (!filtersData[excludeInclude]["attributes"]) {
            filtersData[excludeInclude]["attributes"] = {};
          }
          filtersData[excludeInclude]["attributes"]["useAnd"] = filterValue;
        } */

        if (filterName.endsWith("_useAnd")) {
          var previousFilterName = filterName.slice(
            0,
            filterName.lastIndexOf("_useAnd")
          );
          if (!filtersData[excludeInclude][previousFilterName]) return;

          if (
            filtersData[excludeInclude][previousFilterName]["values"].length ==
            0
          )
            return;

          filterValue = $(this).val();
          filtersData[excludeInclude][previousFilterName]["useAnd"] =
            filterValue;

          return; //in jquery each return without false is the same as continue
        }

        if ($(this).val() != "" && $(this).val() != []) {
          var value = $(this).val();
          if (isJson(value) || value.includes("%"))
            var filterValue = value.includes("%") ? value : JSON.parse(value);
          else {
            alert(filterName + " is invalid");
            throw new Error(filterName + " is invalid");
          }
          //filterValue = JSON.parse($(this).val());

          if (filterValue.length == 0) return;

          filtersData[excludeInclude][filterName] = {};
          filtersData[excludeInclude][filterName]["values"] = filterValue;
        }
      }
    });

  return filtersData;
}

function displayTopMenuContainer(el) {
  var containerId = el.id.substr(5);

  if ($(".topContainerOpened").length && !$("#" + containerId).is(":visible")) {
    $(".topContainerOpened").hide("fast", function () {
      $(".topContainerOpened").removeClass("topContainerOpened");
      $("#" + containerId).toggle("fast");
      $("#" + containerId).addClass("topContainerOpened");
    });
  } else {
    $("#" + containerId).toggle("fast");
    $("#" + containerId).addClass("topContainerOpened");
  }
}

function lowerFirstLetter(string) {
  return string.charAt(0).toLowerCase() + string.slice(1);
}

function isJson(str) {
  try {
    JSON.parse(str);
  } catch (e) {
    return false;
  }
  return true;
}

function refreshPage() {
  var allowedToReload = true;
  allowedToReload = $("#reportsMenuContainer").is(":hidden");

  if (allowedToReload == true)
    allowedToReload = $("#addMetricsMenuContainer").is(":hidden");

  if (allowedToReload == true)
    allowedToReload = $("#helpContainer").is(":hidden");

  if (allowedToReload == true) allowedToReload = $("#osSideBar").is(":hidden");

  if (allowedToReload == true) location.reload();
  else setTimeout(refreshPage, 1000);
}

function toggleListPieChart(el, forceDisplayList = false) {
  metricName = el.data("metricName");
  container = metricName + "Container";
  pieContainer = metricName + "PieChartContainer";

  if (
    $("#" + container + " > .osListExpander").is(":visible") &&
    forceDisplayList == false
  ) {
    $("#" + metricName + "DisplayPieChartBtn").addClass("osDisplayChartActiv");
    $("#" + metricName + "DisplayListBtn").removeClass("osDisplayChartActiv");
    displayPie(metricName);
  } else {
    $("#" + metricName + "DisplayPieChartBtn").removeClass(
      "osDisplayChartActiv"
    );
    $("#" + metricName + "DisplayListBtn").addClass("osDisplayChartActiv");
    if ($("#" + pieContainer).length) $("#" + pieContainer).hide();
    $("#" + container + " > .osListExpander").show();
  }
}

function displayFilterOption(
  el,
  filterName = null,
  filterType = null,
  excludeInclude = null
) {
  if (filterName == null) filterName = el.val();
  if (filterName == "" || filterName == null) return false;
  if (filterType == null) filterType = el.data("filterType");
  if (excludeInclude == null) excludeInclude = el.data("excludeInclude");
  htmlToAdd = $("#" + filterName + "filterOptions").html();

  newNode = $("<div>", {
    id: "displayFilter_" + filterName + "_" + filterType + "_" + excludeInclude,
    class: "displayFilterContainer",
    "data-filter-type": filterType,
    "data-filter-name": filterName,
    "data-exclude-include": excludeInclude,
  }).appendTo(
    el
      .parents(".addFilterContainer")
      .find("." + excludeInclude + "FiltersContainer")
  );

  newNode.html(htmlToAdd);
  newNode.find(".tempoFiltersValues").attr("data-filter-type", filterType);
  newNode
    .find(".tempoFiltersValues")
    .attr("data-filter-exclude-include", excludeInclude);
  el.find('option[value="' + filterName + '"]').attr("disabled", true);
  el.val(el.find("option:first").val());

  isSearchBox = newNode.find(".searchBox").length > 0;

  if (isSearchBox) {
    createSearchItemBox(filterName, filterType, excludeInclude);
    newNode
      .find(".checkboxDisplayKeywordInput")
      .attr(
        "id",
        "checkboxDisplayKeywordInput_" +
          filterName +
          "_" +
          filterType +
          "_" +
          excludeInclude
      );
    newNode
      .find(".labelDisplayKeywordInput")
      .attr(
        "for",
        "checkboxDisplayKeywordInput_" +
          filterName +
          "_" +
          filterType +
          "_" +
          excludeInclude
      );
    if (filterName == "categories") {
      newNode
        .find(".getAllChildrenCategoriesCheckbox")
        .attr(
          "id",
          "getAllChildrenCategoriesCheckbox_" +
            filterName +
            "_" +
            filterType +
            "_" +
            excludeInclude
        );
      newNode
        .find(".getAllChildrenCategoriesLabel")
        .attr(
          "for",
          "getAllChildrenCategoriesCheckbox_" +
            filterName +
            "_" +
            filterType +
            "_" +
            excludeInclude
        );
    }
    newNode.find(".searchBox").focus();
  }

  isUseKeyword = newNode.find(".checkboxDisplayKeywordInput").length > 0;
  if (isUseKeyword) {
    createUseKeywordCheckBoxAction(
      newNode.find(".checkboxDisplayKeywordInput")
    );
    createUseKeywordInputAction(newNode.find(".useKeywordInput"));
  }

  isCheckbox = newNode.find(".checkboxFilterContainer").length > 0;
  if (isCheckbox) {
    var timeNow = Date.now();
    newNode.find(".checkboxFilter").each(function () {
      let currentId = $(this).attr("id");
      let newId = currentId + "_" + timeNow;
      $(this).attr("id", newId);
      newNode.find("#" + currentId + "Label").attr("for", newId);
    });
    newNode.find(".checkboxFilter").change(function () {
      populateHiddenInputForFilterCheckbox($(this));
    });
  }
  return newNode;
}

function removeFilterOption(el, animate = true) {
  var parentElement = el.parents(".displayFilterContainer");
  var parentId = parentElement.attr("id");
  var filterName = parentId.split("_")[1];

  var addFilterSelect = el
    .parents(".addFilterContainer")
    .find(".addFilterSelect");
  var optionToEnable = addFilterSelect.find(
    'option[value="' + filterName + '"]'
  );
  optionToEnable.attr("disabled", false);
  if (animate == true)
    parentElement.hide("slow", function () {
      parentElement.remove();
    });
  else parentElement.remove();
}

function createUseKeywordCheckBoxAction(el) {
  el.on("change", function () {
    var parentEl = $(this).parent();
    parentEl.find(".tempoFiltersValues").val("");
    parentEl.find(".searchBox").val("");
    parentEl
      .closest(".displayFilterContainer")
      .find(".displaySelectedItems")
      .html("");
    parentEl.find(".useKeywordInput").val("");
    parentEl.find(".getAllChildrenCategoriesCheckbox").prop("checked", false);

    if ($(this).is(":checked")) {
      parentEl.find(".containerGetAllChildrenCategories").hide("fast");
      parentEl.find(".searchBox").hide("fast", function () {
        parentEl.find(".useKeywordInputContainer").show("fast");
      });
    } else {
      parentEl.find(".useKeywordInputContainer").hide("fast", function () {
        parentEl.find(".containerGetAllChildrenCategories").show("fast");
        parentEl.find(".searchBox").show("fast");
      });
    }
  });
}

function createUseKeywordInputAction(el) {
  el.on("change", function () {
    var parentEl = $(this).closest(".searchBoxContainer");
    parentEl.find(".tempoFiltersValues").val($(this).val());
  });
}

function getItemIdsAlreadySelected(filterName, filterType, excludeInclude) {
  ids = [];
  $(
    "#displayFilter_" +
      filterName +
      "_" +
      filterType +
      "_" +
      excludeInclude +
      " .selectedItemLabel"
  ).each(function () {
    /*  ids.push(parseInt($(this).data('idToRemove'))) */
    ids.push($(this).data("idToRemove").toString());
  });
  return ids;
}

function createSearchItemBox(filterName, filterType, excludeInclude) {
  el = $(
    "#displayFilter_" + filterName + "_" + filterType + "_" + excludeInclude
  ).find(".searchBox_" + filterName);

  minChars = 3;

  if (filterName == "attributes" || filterName == "features") minChars = 1;

  let allItems = [];

  el.autocomplete(ajaxUrl, {
    minChars: minChars,
    autoFill: true,
    max: 200,
    matchContains: true,
    scroll: false,
    dataType: "JSON",
    cacheLength: 0,
    extraParams: {
      ajax: true,
      action: "SearchItem",
      searchItemType: filterName,
    },
    parse: function (items) {
      items.unshift({ id: "-1", name: allTranslatedTxt });
      items = items.filter((item) => !item.is_category);
      var formated_items = new Array();
      for (var i = 0; i < items.length; i++) {
        if (items[i].id == "-1") value = "--- " + allTranslatedTxt + " ---";
        else value = (items[i].id + " - " + items[i].name).trim();

        formated_items[i] = {
          data: items[i],
          value: value,
        };
      }
      allItems = formated_items;
      return formated_items;
    },
    formatItem: function (data, i, max, value, term) {
      itemIdsAlreadySelected = getItemIdsAlreadySelected(
        filterName,
        filterType,
        excludeInclude
      );
      if (itemIdsAlreadySelected.includes(data.id)) return false;
      return value;
    },
  }).result(function (e, item) {
    if (item["id"] == "-1") {
      allItems.forEach((itemToAdd) => {
        if (itemToAdd["data"]["id"] != "-1")
          populateItemField(
            filterName,
            filterType,
            excludeInclude,
            itemToAdd["data"]["id"],
            itemToAdd["data"]["name"],
            itemToAdd["data"]["reference"]
          );
      });
    } else if (item != undefined) {
      populateItemField(
        filterName,
        filterType,
        excludeInclude,
        item["id"],
        item["name"],
        item["reference"]
      );
    }
    $(this).val("");
  });
}

function populateItemField(
  filterName,
  filterType,
  excludeInclude,
  itemId,
  name,
  reference = null
) {
  el = $(
    "#displayFilter_" + filterName + "_" + filterType + "_" + excludeInclude
  ).find(".searchBox_" + filterName);
  parentElement = el
    .parents(".addFilterContainer")
    .find("." + excludeInclude + "FiltersContainer");
  elHiddenItems = parentElement.find(".hidden_selected_" + filterName);
  elDisplayItems = parentElement.find(".displaySelected_" + filterName);
  elTemplate = parentElement.find(".selectedItemHtmlTpl_" + filterName);

  refOrId = reference == null ? itemId : reference;

  selectedItems = elHiddenItems.val();

  if (selectedItems == "") selectedItems = [];
  else {
    selectedItems = JSON.parse(selectedItems);
  }
  /* selectedItems.push(itemId); */
  selectedItems.push(itemId.toString());
  elHiddenItems.val(JSON.stringify(selectedItems));

  el.removeClass("osDateInputError");

  var htmlToAdd = elTemplate.get(0).innerHTML;
  htmlToAdd = htmlToAdd.replace("%selectedItem%", "[" + refOrId + "] " + name);
  htmlToAdd = htmlToAdd.replaceAll("%itemId%", itemId);

  previousContent = elDisplayItems.html();
  elDisplayItems.html(previousContent + htmlToAdd);
}

function removeItemField(el) {
  elParent = el.parents(".selectedItemLabel");
  filterName = elParent.data("filterName");
  selectedItemsEl = elParent
    .parents(".displayFilterContainer")
    .find(".hidden_selected_" + filterName);

  selectedItems = selectedItemsEl.val();
  //selectedItems = $("#selectedItems").val()
  if (selectedItems == "") return false;

  selectedItems = JSON.parse(selectedItems);

  const index = selectedItems.indexOf(elParent.data("idToRemove").toString());

  if (index > -1) selectedItems.splice(index, 1);
  selectedItemsEl.val(JSON.stringify(selectedItems));

  elParent.hide("slow", function () {
    elParent.remove();
  });
}

function populateHiddenInputForFilterCheckbox(el) {
  var parentElement = el.parents(".displayFilterContainer");
  var elHiddenItems = parentElement.find(".tempoFiltersValues");
  var checkboxValues = [];
  parentElement.find(".checkboxFilter:checked").each(function () {
    checkboxValues.push($(this).val());
  });
  elHiddenItems.val(JSON.stringify(checkboxValues));
}

function copyInitialFilters() {
  event.preventDefault();
  $(".displayFilterContainer").each(function () {
    if ($(this).data("filterType") == "compare") {
      el = $(this).find(".removeFilterOption");
      removeFilterOption(el, false);
    }
  });
  $(".initialFiltersContainer")
    .find(".displayFilterContainer")
    .each(function () {
      filterName = $(this).data("filterName");
      excludeInclude = $(this).data("excludeInclude");

      el = $(".compareFiltersContainer").find(
        "[data-exclude-include=" + excludeInclude + "]"
      );

      newNode = displayFilterOption(el, filterName, "compare", excludeInclude);
      copyInitialFiltersValues(newNode, excludeInclude);
    });
}

function copyInitialFiltersValues(el, excludeInclude) {
  filterName = el.data("filterName");
  initialParentContainer = $(
    "#displayFilter_" + filterName + "_initial_" + excludeInclude
  );

  isCheckbox =
    initialParentContainer.find(".checkboxFilterContainer").length > 0;

  isKeyword = initialParentContainer
    .find(".checkboxDisplayKeywordInput")
    .is(":checked");

  if (isCheckbox) {
    i = 0;
    checkboxChecked = [];
    initialParentContainer.find(".checkboxFilter").each(function () {
      checkboxChecked[i] = $(this).prop("checked");
      i++;
    });
    i = 0;
    el.find(".checkboxFilter").each(function () {
      if (checkboxChecked[i]) {
        $(this).prop("checked", true);
        $(this).trigger("change");
      }
      i++;
    });
  } else if (isKeyword) {
    el.find(".checkboxDisplayKeywordInput").prop("checked", true);
    el.find(".checkboxDisplayKeywordInput").trigger("change");
    let valueToCopy = initialParentContainer.find(".useKeywordInput").val();
    el.find(".useKeywordInput").val(valueToCopy);
  } else if (el.find(".displaySelectedItems").length > 0) {
    htmlToAdd = initialParentContainer.find(".displaySelectedItems").html();
    el.find(".displaySelectedItems").html(htmlToAdd);

    hiddenInputValue = initialParentContainer
      .find(".hidden_selected_" + filterName)
      .val();
    console.log(hiddenInputValue);
    el.find(".hidden_selected_" + filterName).val(hiddenInputValue);
  }
  if (initialParentContainer.find("select").length > 0) {
    var initialSelectValue = initialParentContainer.find("select").val();
    el.find("select").val(initialSelectValue).trigger("change");
  }
  if (el.find(".getAllChildrenCategoriesCheckbox").length > 0) {
    checkBoxValue = initialParentContainer
      .find(".getAllChildrenCategoriesCheckbox")
      .is(":checked");
    el.find(".getAllChildrenCategoriesCheckbox").prop("checked", checkBoxValue);
  }
}

function displaySavedFilters() {
  for (filterType in filtersDatas) {
    for (excludeInclude in filtersDatas[filterType]) {
      for (filterName in filtersDatas[filterType][excludeInclude]) {
        el = $("." + filterType + "FiltersContainer").find(
          "[data-exclude-include=" + excludeInclude + "]"
        );
        displayFilterOption(el, filterName, filterType, excludeInclude);
        populateSavedFilter(
          filtersDatas[filterType][excludeInclude][filterName],
          filterName,
          filterType,
          excludeInclude
        );
      }
    }
  }
}

function populateSavedFilter(filters, filterName, filterType, excludeInclude) {
  parentEl = $(
    "#displayFilter_" + filterName + "_" + filterType + "_" + excludeInclude
  );
  if (filterName == "device") {
    $.each(filters[0], function (index, value) {
      let checkbox = parentEl.find('.checkboxFilter[value="' + value + '"]');
      checkbox.prop("checked", true);
      checkbox.trigger("change");
    });
  } else if (typeof filters[0] === "string" && filters[0].includes("%")) {
    //parentEl = $('#displayFilter_'+filterName+'_'+filterType+'_'+excludeInclude)
    parentEl.find(".checkboxDisplayKeywordInput").prop("checked", true);
    parentEl.find(".checkboxDisplayKeywordInput").trigger("change");
    parentEl.find(".useKeywordInput").val(filters[0]);
    parentEl.find(".tempoFiltersValues").val(filters[0]);
  } else {
    for (key in filters) {
      if (key == "getAllChildren") {
        el = $("." + filterType + "FiltersContainer").find(".addFilterSelect");
        parentEl = el
          .parents(".addFilterContainer")
          .find(".getAllChildrenCategoriesCheckbox")
          .prop(
            "checked",
            Number(
              filtersDatas[filterType][excludeInclude][filterName][
                "getAllChildren"
              ]
            )
          );
      } else if (key == "useAnd") {
        el = $("." + excludeInclude + "FiltersContainer")
          .find("." + filterName + "SearchBoxContainer ")
          .find(".useAndSelect");
        el.val(filtersDatas[filterType][excludeInclude][filterName]["useAnd"]);
      } else {
        reference = filters[key].hasOwnProperty("reference")
          ? filters[key]["reference"]
          : null;
        populateItemField(
          filterName,
          filterType,
          excludeInclude,
          filters[key]["id"],
          filters[key]["name"],
          reference
        );
      }
    }
  }
}

function toggleDisplayCompareCalendar() {
  checkboxEl = $("#compareOrNot");
  if (checkboxEl.is(":checked")) {
    $("#compareCalenderHidder").show("fast");
  } else {
    $("#compareCalenderHidder").hide("fast");

    osChangeInputDateValue("dateFromCompare", "");
    osChangeInputDateValue("dateToCompare", "");

    $("#compareCalendarAndFiltersContainer")
      .find(".removeFilterOption")
      .each(function () {
        removeFilterOption($(this));
      });

    $("#osPresetDateCompare").val("custom");
    $(".compareElement").hide("fast");
  }
}

function displayFiltersSummary(filters, filterType) {
  htmlToAdd = "";
  htmlTemplate = $("#filtersSummaryItemTemplate").html();

  dateSelectElementId = "osPresetDate" + capitalizeFirstLetter(filterType);
  filterItem = htmlTemplate;
  filterItem = filterItem.replace("%filterName%", "Date :");

  if ($("#" + dateSelectElementId + " option:selected").val() == "custom") {
    suffix = filterType == "compare" ? "Compare" : "";
    itemValues =
      $("#dateFrom" + suffix).val() + " - " + $("#dateTo" + suffix).val();
  } else itemValues = $("#" + dateSelectElementId + " option:selected").text();

  filterItem = filterItem.replace("%filterValue%", itemValues);
  htmlToAdd += filterItem;

  for (excludeInclude in filters) {
    for (filterName in filters[excludeInclude]) {
      filterItem = htmlTemplate;
      filterLabel = $(
        "#displayFilter_" + filterName + "_" + filterType + "_" + excludeInclude
      )
        .find(".filterName")
        .text();

      if (filters[excludeInclude][filterName].hasOwnProperty("useAnd")) {
        if (filters[excludeInclude][filterName]["useAnd"] == "true")
          filterLabel = "[and]" + filterLabel;
        else filterLabel = "[or]" + filterLabel;
      }

      if (excludeInclude == "exclude") filterLabel = "[-]" + filterLabel;
      else filterLabel = "[+]" + filterLabel;

      filterItem = filterItem.replace("%filterName%", filterLabel);
      itemValues = "";
      if (filters[excludeInclude][filterName]["values"].includes("%")) {
        itemValues = filters[excludeInclude][filterName]["values"];
      } else {
        var displayFilterElement = $(
          "#displayFilter_" +
            filterName +
            "_" +
            filterType +
            "_" +
            excludeInclude
        );
        isCheckbox =
          displayFilterElement.find(".checkboxFilterContainer").length > 0;
        //isCheckbox = false;
        if (isCheckbox) {
          itemValues += getFiltersNameToDisplayForCheckBox(
            displayFilterElement,
            itemValues
          );
        } else {
          itemValues += getFiltersNameToDisplayForSearchBox(
            displayFilterElement,
            itemValues
          );
        }
      }
      filterItem = filterItem.replace("%filterValue%", itemValues);
      htmlToAdd += filterItem;
    }
  }

  if (filterType == "compare" && htmlToAdd != "") {
    $("#filtersSummaryDisplayCompareTo").show();
    $(".filtersSummaryInitial").removeClass("roundedBorderEverywhere");
  } else {
    $("#filtersSummaryDisplayCompareTo").hide();
    $(".filtersSummaryInitial").addClass("roundedBorderEverywhere");
  }
  $("#filtersSummaryItems_" + filterType).html(htmlToAdd);
}

function getFiltersNameToDisplayForCheckBox(el, itemValues) {
  el.find(".checkboxFilter").each(function () {
    if ($(this).prop("checked")) {
      var nextElement = $(this).nextAll(".checkboxFilterLabel").first();
      itemValues +=
        itemValues == "" ? nextElement.text() : ", " + nextElement.text();
    }
  });
  return itemValues;
}

function getFiltersNameToDisplayForSearchBox(el, itemValues) {
  el.find(".displaySelected_" + filterName).each(function () {
    $(this)
      .find(".selectedItemLabel")
      .find("span:first")
      .each(function () {
        itemValues += itemValues == "" ? $(this).text() : ", " + $(this).text();
      });
  });
  return itemValues;
}

function capitalizeFirstLetter(string) {
  return string.charAt(0).toUpperCase() + string.slice(1);
}

function editReportName(values, reportKey) {
  console.log("edit");
  $.ajax({
    type: "POST",
    url: ajaxUrl + "&action=editReportName",
    dataType: "JSON",
    data: {
      oldName: values.oldValue.trim(),
      newName: values.newValue.trim(),
      reportKey: reportKey,
    },
    success: function (result) {
      if (result == true)
        $("#linkToReport_" + reportKey).text(values.newValue.trim());
    },
    error: function (XMLHttpRequest, textStatus, errorThrown) {
      console.log(textStatus);
      console.log(XMLHttpRequest);
      console.log(XMLHttpRequest.responseText);
      return false;
    },
  });
}

function osExportToCsv(filename, rows) {
  var processRow = function (row) {
    var finalVal = "";

    var count = 0;
    for (j in row) {
      var innerValue = row[j] === null ? "" : row[j].toString();
      if (row[j] instanceof Date) {
        innerValue = row[j].toLocaleString();
      }
      var result = innerValue.replace(/"/g, '""');
      if (result.search(/("|,|\.|\n)/g) >= 0) result = '"' + result + '"';
      if (count > 0) finalVal += ",";
      finalVal += result;
      count++;
    }
    return finalVal + "\n";
  };

  var csvFile = "\uFEFF"; //Add UTF8 BOM for excel

  var headers = Object.keys(rows[0]).join(",") + "\n";
  csvFile += headers;

  for (id in rows) csvFile += processRow(rows[id]);

  var blob = new Blob([csvFile], { type: "text/csv;charset=utf-8;" });
  if (navigator.msSaveBlob) {
    // IE 10+
    navigator.msSaveBlob(blob, filename);
  } else {
    var link = document.createElement("a");
    if (link.download !== undefined) {
      // feature detection
      // Browsers that support HTML5 download attribute
      var url = URL.createObjectURL(blob);
      link.setAttribute("href", url);
      link.setAttribute("download", filename);
      link.style.visibility = "hidden";
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }
  }
}

function executeRequiredAction(datas, metricName) {
  var target = $("#" + metricName + "Container");
  var loader = $("#" + metricName + "Loader");
  if (datas.action == "askForLoginTosaas") {
    var nodeToAdd = $("#displayAskForLoginToSaas").clone();
  }
  if (datas.action == "askForLoginToGoogleAds") {
    var nodeToAdd = $("#displayAskForLoginToGoogleAds").clone();
  }
  target.html(nodeToAdd);
  loader.fadeOut("fast", function () {
    target.fadeIn("slow");
  });
}

function formatPriceForCsv(price) {
  fomatedPrice = osDisplayPriceToHuman(price);
  csvPrice = fomatedPrice.replace(/[^0-9.,-]/g, "");

  return csvPrice;
}

function displayShareReportModal(reportName) {
  $("#modalBackground").fadeIn("fast");
  $("#shareReportModal").fadeIn("fast", function () {
    $("#shareReportModal .osLoader").fadeIn("fast");
    $.ajax({
      type: "POST",
      url: ajaxUrl + "&action=loadEmployees",
      dataType: "HTML",
      data: {
        reportName: reportName,
      },
      success: function (result) {
        $("#employeesContainer").html(result);
        $("#shareReportModal .osLoader").fadeOut("fast", function () {
          $("#employeesContainer").fadeIn("fast");
        });
        return true;
      },
      error: function (XMLHttpRequest, textStatus, errorThrown) {
        console.log(textStatus);
        console.log(XMLHttpRequest);
        console.log(XMLHttpRequest.responseText);
        return false;
      },
    });
  });
}

function hideSharedReportModal() {
  $("#shareReportModal").fadeOut("fast", function () {
    $("#modalBackground").css("display", "none");
    $("#shareReportModal .deleteMeOnClose").remove();
    $("#shareReportModal .osLoader").css("display", "block");
    $("#employeesContainer").css("display", "none");
    $("#employeesContainer").html("");
  });
}

function updateEmployeeRights(el) {
  var guestId = el.data("guestId");
  var rights = el.val();
  var reportName = $("#currentReportName").val();

  $.ajax({
    type: "POST",
    url: ajaxUrl + "&action=updateEmployeeRights",
    dataType: "JSON",
    data: {
      reportName: reportName,
      guestId: guestId,
      rights: rights,
    },
    success: function (result) {
      displaySharedReportModalMsg("success");
      return true;
    },
    error: function (XMLHttpRequest, textStatus, errorThrown) {
      displaySharedReportModalMsg("danger");
      console.log(textStatus);
      console.log(XMLHttpRequest);
      console.log(XMLHttpRequest.responseText);
      return false;
    },
  });
}

function displaySharedReportModalMsg(msgType) {
  node = $("#shareReportModal .alert-" + msgType + ".templateMsg").clone();
  node.removeClass("templateMsg");
  $("#shareReportModal").append(node);
  node.fadeIn("fast", function () {
    node.removeClass("hideMe");
    node.addClass("deleteMeOnClose");
  });
}

function displayOpartStatModal(modalId) {
  console.log("display");
  console.log(modalId);
  $("#modalBackground").fadeIn("fast");
  $("#" + modalId).fadeIn("fast");
}

function hideOpartStatModal(modalId) {
  $("#modalBackground").fadeOut("fast");
  $("#" + modalId).fadeOut("fast");
}

let pdfJpgQuality = 10.7;
async function generatePDF() {
  modifyAndSaveCssStyle(
    "#modalBackground",
    "background-color",
    "rgba(0, 0, 0, 1)"
  );
  $("#modalBackground").fadeIn("fast", function () {
    $("#exportPdfModal").fadeIn("fast");
  });

  prepareElementForPdfGeneration();
  const panels = document.querySelectorAll(".osStatPanel");
  let i = 0;
  $("#exportPdfModal #exportedPdfMetricNumber").text(i);
  $("#exportPdfModal #totalPdfMetricNumber").text(panels.length);

  if (panels.length === 0) {
    console.error("Aucun élément avec la classe osStatPanel n'a été trouvé.");
    return;
  }

  const { jsPDF } = window.jspdf;
  //const pdf = new jsPDF("p", "mm", "a4");
  const pdf = new jsPDF({
    compression: true,
    unit: "mm",
    format: "a4"
  });

  const margin = 2;
  const marginBetweenPanels = 2;
  const pageWidth = 210;
  const pageHeight = 297;
  const contentWidth = pageWidth - 2 * margin;
  const contentHeight = pageHeight - 2 * margin;

  let currentY = await addHeaderToPdfPage(pdf, margin, contentWidth);
  let currentX = margin;

  for (let panel of panels) {
    const canvas = await html2canvas(panel, {
      scale: 2,
      useCORS: true,
      logging: false,
      allowTaint: true,
      backgroundColor: null,
    });

    const columnSpan =
      parseInt($(panel).css("grid-column-end").split("span ")[1]) || 1;
    const rowSpan =
      parseInt($(panel).css("grid-row-end").split("span ")[1]) || 1;

    //const imgData = canvas.toDataURL("image/png", 1.0);
    const imgData = canvas.toDataURL("image/jpeg", pdfJpgQuality);
    const imgWidth = (contentWidth / 2) * rowSpan - marginBetweenPanels;
    const imgHeight = (canvas.height * imgWidth) / canvas.width;

    if (currentY + imgHeight > pageHeight - margin) {
      pdf.addPage();
      currentY = await addHeaderToPdfPage(pdf, margin, contentWidth);
      currentX = margin;
    }

    pdf.addImage(imgData, "PNG", currentX, currentY, imgWidth, imgHeight);
    currentX += imgWidth + marginBetweenPanels;

    if (currentX + imgWidth > pageWidth - margin) {
      currentY += imgHeight + marginBetweenPanels;
      currentX = margin;
    }

    i++;
    $("#exportPdfModal #exportedPdfMetricNumber").text(i);
  }

  addPdfPageNumber(pdf);

  var pdfFileName = document.querySelector(".page-title").textContent;
  pdfFileName = pdfFileName.trim();
  pdf.save(pdfFileName + ".pdf");

  resetElementsAfterPdfGeneration();
  $("#modalBackground").fadeOut("fast", function () {
    $("#exportPdfModal").fadeOut("fast");
    restoreCssStyle("#modalBackground", "background-color");
  });
}

async function addHeaderToPdfPage(pdf, margin, contentWidth) {
  let currentY = margin;
  const pageTitle = document.querySelector(".page-title");
  const filtersSummary = document.getElementById("filtersSummary");

  if (pageTitle) {
    const titleCanvas = await html2canvas(pageTitle, {
      scale: 2,
      useCORS: true,
      logging: false,
      allowTaint: true,
      backgroundColor: null,
    });
    //const titleImgData = titleCanvas.toDataURL("image/png", 1.0);
    const titleImgData = titleCanvas.toDataURL("image/jpeg", pdfJpgQuality);
    const titleImgHeight = 10;
    const titleImgWidth =
      (titleCanvas.width * titleImgHeight) / titleCanvas.height;
    pdf.addImage(
      titleImgData,
      "PNG",
      margin,
      currentY,
      titleImgWidth,
      titleImgHeight
    );
    currentY += titleImgHeight + 2;
  }

  if (filtersSummary) {
    const summaryCanvas = await html2canvas(filtersSummary, {
      scale: 2,
      useCORS: true,
      logging: false,
      allowTaint: true,
      backgroundColor: null,
    });
    //const summaryImgData = summaryCanvas.toDataURL("image/png", 1.0);
    const summaryImgData = summaryCanvas.toDataURL("image/jpeg", pdfJpgQuality);
    const summaryImgWidth = contentWidth;
    const summaryImgHeight =
      (summaryCanvas.height * summaryImgWidth) / summaryCanvas.width;

    pdf.addImage(
      summaryImgData,
      "PNG",
      margin,
      currentY,
      summaryImgWidth,
      summaryImgHeight
    );
    currentY += summaryImgHeight + 2;
  }

  return currentY;
}

function prepareElementForPdfGeneration() {
  const elementsToHide = [
    ".help-box",
    ".reloadBtn",
    ".savePeriodBtn",
    ".csvExportButton",
    ".osDisplayListBtn",
    ".osDisplayPieChartBtn",
  ];
  elementsToHide.forEach((selector) => {
    modifyAndSaveCssStyle(selector, "display", "none");
  });

  modifyAndSaveCssStyle(".osStatPanel", "box-shadow", "none");
  modifyAndSaveCssStyle(".osListPanel", "padding-top", "24px");
  modifyAndSaveCssStyle(".osStatPanel", "border-radius", "0");

  modifyAndSaveCssStyle("#filtersSummary", "width", "50%");

  modifyAndSaveCssStyle(".page-title", "background-color", "white");

  modifyAndSaveCssStyle(".filtersSummaryInitial", "max-height", "100%");
  modifyAndSaveCssStyle(".filtersSummaryInitial", "display", "block");
  modifyAndSaveCssStyle(".filtersSummaryInitial", "border-radius", "0");

  modifyAndSaveCssStyle(".filtersSummaryCompare", "max-height", "100%");
  modifyAndSaveCssStyle(".filtersSummaryCompare", "display", "block");
  modifyAndSaveCssStyle(".filtersSummaryCompare", "border-radius", "0");
}

function resetElementsAfterPdfGeneration() {
  const elementsToShow = [
    ".help-box",
    ".reloadBtn",
    ".savePeriodBtn",
    ".csvExportButton",
    ".osDisplayListBtn",
    ".osDisplayPieChartBtn",
  ];
  elementsToShow.forEach((selector) => {
    restoreCssStyle(selector, "display");
  });
  restoreCssStyle(".osStatPanel", "box-shadow");
  restoreCssStyle(".osListPanel", "padding-top");
  restoreCssStyle(".osStatPanel", "border-radius");

  restoreCssStyle("#filtersSummary", "width");

  restoreCssStyle(".page-title", "background-color");

  restoreCssStyle(".filtersSummaryInitial", "max-height");
  restoreCssStyle(".filtersSummaryInitial", "display");
  restoreCssStyle(".filtersSummaryInitial", "border-radius");

  restoreCssStyle(".filtersSummaryCompare", "max-height");
  restoreCssStyle(".filtersSummaryCompare", "display");
  restoreCssStyle(".filtersSummaryCompare", "border-radius");
}

function addPdfPageNumber(pdf) {
  const totalPages = pdf.internal.getNumberOfPages();
  for (let i = 1; i <= totalPages; i++) {
    pdf.setPage(i);
    pdf.setFontSize(10);
    pdf.setTextColor(150);
    pdf.text(
      `Page ${i} / ${totalPages}`,
      pdf.internal.pageSize.width / 2,
      pdf.internal.pageSize.height - 10,
      { align: "center" }
    );
  }
}

function modifyAndSaveCssStyle(selector, property, tempValue) {
  const elements = $(selector);
  elements.each(function () {
    const $el = $(this);
    const originalValue = $el.css(property);
    $el.data(`original-${property}`, originalValue);
    $el.css(property, tempValue);
  });
}

function restoreCssStyle(selector, property) {
  const elements = $(selector);
  elements.each(function () {
    const $el = $(this);
    const originalValue = $el.data(`original-${property}`);
    $el.css(property, originalValue);
  });
}


$(document).ready(function () {
  function getFieldGroup(inputName) {
    var $el = $('#' + inputName);
    if (!$el.length) return $(); // sécurité

    // selon PS / thème BO
    return $el.closest('.form-group, .form-group.row, .form-group-row, .form-group.form-group-sm, .row.form-group');
  }

  function toggleExternalDbFields() {
    var useSeparate = parseInt($('input[name="OPARTSTAT_USE_SEPARATE_DB"]:checked').val(), 10) === 1;

    var externalFields = [
      'OPARTSTAT_DB_HOST',
      'OPARTSTAT_DB_PORT',
      'OPARTSTAT_DB_NAME',
      'OPARTSTAT_DB_USER',
      'OPARTSTAT_DB_PASS',
      'OPARTSTAT_DB_PREFIX'
    ];

    // Champs connexion DB externe
    externalFields.forEach(function (name) {
      var $group = getFieldGroup(name);
      if (!$group.length) return;
      $group.toggle(useSeparate);
    });

    // Champ max visits : visible seulement si DB séparée = OFF
    var $maxVisits = getFieldGroup('OPARTSTAT_MAX_VISITS');
    if ($maxVisits.length) {
      $maxVisits.toggle(!useSeparate);
    }
  }

  // Au chargement
  toggleExternalDbFields();

  // Au changement du switch
  $(document).on('change', 'input[name="OPARTSTAT_USE_SEPARATE_DB"]', function () {
    toggleExternalDbFields();
  });
});
