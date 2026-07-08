/**
 * Prestashop module : OpartStat
 *
 * @author Olivier CLEMENCE <contact@store-opart.fr>
 * @copyright  Op'art
 * @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 */
 /**
 * Attente du chargement de la locale ApexCharts avant de créer le graphique
 */
async function initApexChartWhenReady(callback) {
  // On attend que la variable globale soit prête
  if (typeof window.apexLocaleReady === 'undefined' || window.apexLocaleReady === false) {
    return new Promise((resolve) => {
      const timer = setInterval(() => {
        if (window.apexLocaleReady === true) {
          clearInterval(timer);
          resolve();
        }
      }, 50);
    }).then(callback);
  } else {
    // Déjà prêt → on exécute directement
    return callback();
  }
}


async function displayTrend(
  result,
  metricName,
  displayLoading = "false"
) {
  // On attend la locale avant d'exécuter le reste
  await initApexChartWhenReady(async function() {
    var period = $('#' + metricName + 'SelectPeriod').val();
    var initialValue = result.initial["value"];
    var globalValueContainer = metricName + "GlobalValue";
    var globalValue = displayDataToHuman(initialValue["globalValue"], result.initial["conf"]['globalValueFormat']);

    $("#" + globalValueContainer).fadeOut("fast", function () {
      $("#" + globalValueContainer).text(globalValue);
      $("#" + globalValueContainer).fadeIn("slow");
    });

    var container = metricName + "Container";
    var loader = metricName + "Loader";

    if (displayLoading == true) {
      $("#" + container).hide();
      $("#" + loader).fadeIn("slow");
      ApexCharts.exec(container + "Chart", "destroy");
    }

    $("#" + container + "Data").val(JSON.stringify(result));
    var initialSerie = createSerie(initialValue, period);

    if (typeof result.compare !== "undefined") {
      var compareValue = result.compare["value"];
      var compareSerie = createSerie(compareValue, period);

      let initialLength = initialSerie['data'].length;
      let compareLength = compareSerie['data'].length;
      if (initialLength > compareLength) {
        let diff = initialLength - compareLength;
        for (let i = 0; i < diff; i++) {
          compareSerie['data'].push(null);
          compareSerie['times'].push(addPeriodToTimeStamp(compareSerie['times'][compareSerie['times'].length - 1], period));
        }
      } else if (compareLength > initialLength) {
        let diff = compareLength - initialLength;
        for (let i = 0; i < diff; i++) {
          initialSerie['data'].push(null);
          initialSerie['times'].push(addPeriodToTimeStamp(initialSerie['times'][initialSerie['times'].length - 1], period));
        }
      }
    }

    var options = {
      colors: ['#329eef', '#ff8100'],
      series: [],
      chart: {
        id: container + "Chart",
        type: "line",
        height: 250,
        toolbar: { show: false },
      },
      yaxis: {
        labels: {
          formatter: function (value) {
            return displayDataToHuman(value, result["conf"]['dataFormat']);
          },
        },
      },
      xaxis: {
        type: "datetime",
        categories: initialSerie["times"],
        labels: {
          datetimeFormatter: {
            year: "yyyy",
            month: "MMM 'yy",
            day: "dd MMM",
            hour: "HH:mm",
          },
        },
      },
      tooltip: {
        enabled: true,
        x: {
          show: true,
          formatter: function (value, { series, seriesIndex, dataPointIndex, w }) {
            let date = new Date(value);
            let toolTipLabel = date.toLocaleString(Apex.chart.defaultLocale, { year: "numeric" });
            if (period == 'perDay') {
              toolTipLabel = date.toLocaleString(Apex.chart.defaultLocale, { dateStyle: "short" });
            } else if (period == 'perWeek') {
              var year = new Date(date.getFullYear(), 0, 1);
              var days = Math.floor((date - year) / (24 * 60 * 60 * 1000));
              toolTipLabel = Math.ceil((date.getDay() + 1 + days) / 7);
            } else if (period == 'perMonth') {
              toolTipLabel = date.toLocaleString(Apex.chart.defaultLocale, { month: "long" });
            }
            if (typeof series !== "undefined") {
              if (series[0] && series[1]) {
                var variationPercent = "";
                if (series[0][dataPointIndex] && series[1][dataPointIndex]) {
                  variationPercent = calcPercentVariation(series[0][dataPointIndex], series[1][dataPointIndex]);
                }
                return toolTipLabel + " (" + variationPercent + "%)";
              }
              return toolTipLabel;
            }
          },
        },
      },
    };

    var chart = new ApexCharts(document.querySelector("#" + container), options);

    var series = [];
    if (initialSerie["dateStart"] != 0) {
      series = [{
        name:
          initialSerie["dateStart"].toLocaleDateString(Apex.chart.defaultLocale) +
          " - " +
          initialSerie["dateEnd"].toLocaleDateString(Apex.chart.defaultLocale),
        data: initialSerie["data"],
      }];
    }

    if (typeof result.compare !== "undefined") {
      var compareGlobalValueContainer = metricName + "CompareGlobalValue";
      var globalPercentVariationContainer = metricName + "GlobalPercentVariationContainer";
      var compareGlobalValue = displayDataToHuman(compareValue["globalValue"], result.compare["conf"]['globalValueFormat']);
      var percentGlobal = calcPercentVariation(initialValue["globalValue"], compareValue["globalValue"]);

      $("#" + compareGlobalValueContainer).fadeOut("fast", function () {
        $("#" + compareGlobalValueContainer).text(" | " + compareGlobalValue);
        $("#" + compareGlobalValueContainer).fadeIn("slow");
      });

      $("#" + globalPercentVariationContainer).fadeOut("fast", function () {
        $("#" + globalPercentVariationContainer).removeClass('osUpColor osDownColor osFlatColor');
        let trendIco;
        if (percentGlobal == 0) {
          $("#" + globalPercentVariationContainer).addClass('osFlatColor');
          trendIco = "trending_flat";
        } else if (percentGlobal > 0 && result.conf.superiorIsBetter == true) {
          $("#" + globalPercentVariationContainer).addClass('osUpColor');
          trendIco = "trending_up";
        } else if (percentGlobal < 0 && result.conf.superiorIsBetter == false) {
          $("#" + globalPercentVariationContainer).addClass('osUpColor');
          trendIco = "trending_up";
        } else {
          $("#" + globalPercentVariationContainer).addClass('osDownColor');
          trendIco = "trending_down";
        }
        $("#" + globalPercentVariationContainer).html(
          " (<i class='material-icons " + trendIco + "'><span>" + trendIco + "</span></i>" + percentGlobal + "%)"
        ).fadeIn("slow");
      });

      if (compareSerie["dateStart"] != 0) {
        series.push({
          name:
            compareSerie["dateStart"].toLocaleDateString(Apex.chart.defaultLocale) +
            " - " +
            compareSerie["dateEnd"].toLocaleDateString(Apex.chart.defaultLocale),
          data: compareSerie["data"],
        });
      }
    }

    $("#" + loader).fadeOut("fast", function () {
      $("#" + container).fadeIn("fast", async function () {
        $("#" + metricName + "SelectPeriod").val(period);
        $("#" + metricName + "SelectPeriod").parent().fadeIn("fast");
        chart.render().then(() => {
          chart.updateSeries(series);
        });
      });
    });
  });
}


function createSerie(dataArray, period) {
  var data = [];
  var times = [];
  minTime = 0;
  var dateStart = 0;
  var dateEnd = 0;
  for (year in dataArray[period]) {
    for (x in dataArray[period][year]) {
      array2 = dataArray[period][year][x];

      //calc utc timestamp
      var myDate = array2["date"].split("-");
      var newDate = new Date(myDate[0], myDate[1] - 1, myDate[2]);
      var time = Date.UTC(
        newDate.getFullYear(),
        newDate.getMonth(),
        newDate.getDate(),
        newDate.getHours(),
        newDate.getMinutes(),
        newDate.getSeconds(),
        newDate.getMilliseconds()
      );
      data.push(array2["value"]);
      times.push(time);

      minTime = minTime != 0 ? minTime : time;

      dateStart = dateStart != 0 ? dateStart : newDate;
      dateEnd = newDate;
    }
  }
  var result = {
    data: data,
    times: times,
    minTime: minTime,
    dateStart: dateStart,
    dateEnd: dateEnd,
  };
  return result;
}

function addPeriodToTimeStamp(timeStamp,period) {
  let lastTime = new Date(timeStamp || 0);
  if (period === 'perDay') lastTime.setDate(lastTime.getDate() + 1);
  else if (period === 'perWeek') lastTime.setDate(lastTime.getDate() + 7);
  else if (period === 'perMonth') lastTime.setMonth(lastTime.getMonth() + 1);
  else if (period === 'perYear') lastTime.setFullYear(lastTime.getFullYear() + 1);
  return lastTime.getTime();
}
