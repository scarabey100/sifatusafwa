function displayPie(metricName) {
  container = metricName + "Container";
  $("#" + container + " > .osListExpander").hide();

  piesContainer = metricName + "PieChartContainer";
  initialPieContainer = metricName + "InititalPieContainer";
  initialPieId = metricName + "InititalPie";
  comparePieId = metricName + "ComparePie"    

  if ($("#" + piesContainer).length) {
    $("#" + piesContainer).show();
    ApexCharts.exec(initialPieId, "destroy");
    ApexCharts.exec(comparePieId, "destroy");
  }

  $("#" + container).append('<div id="' + piesContainer + '"></div>');
  $("#" + piesContainer).append('<div class ="initialPieContainer" id="' + initialPieContainer + '"></div>');

  var storedData = $("#" + metricName + "StoredData").val();
  var result = JSON.parse(storedData);

  initialSeries = createSeries(result.initial.value);
  options = createOptions(initialSeries,initialPieId);

  var chart = new ApexCharts(
    document.querySelector("#" + initialPieContainer),
    options
  );
  chart.render();

  if(result.compare.value != '') {

    comparePieContainer = metricName + "ComparePieContainer";  
    $("#" + piesContainer).append('<div class ="comparePieContainer"  id="' + comparePieContainer + '"></div>');
    compareSeries = createSeries(result.compare.value);
    options = createOptions(compareSeries,comparePieId);

    var chart = new ApexCharts(
        document.querySelector("#" + comparePieContainer),
        options
    );
    chart.render();
  }
}

function createSeries(obj) {
  array = []
  for (key in obj) {
    if(obj[key]!=null)
      array.push(obj[key]);
  }
  array.sort(dynamicSort("total", -1));
  maxSeries = 7
  series = {
    values: [],
    labels: [],
  };
  i = 0
  for (key in array) {
    if(i < maxSeries) {
        series.values.push(Number(array[key]["total"]));
        series.labels.push(array[key]["name"]);    
    }
    else {
        lastIndex = series.values.length -1
        series.values[lastIndex] = series.values[lastIndex] + Number(array[key]["total"])
        series.labels[lastIndex] = 'Others'
    }
    i++
  }
  
  for (key in series.values) 
    series.values[key] = Number(parseFloat(series.values[key]).toFixed(2))
  
  return series;
}

function createOptions(series,pieId) {
  var options = {
    series: series.values,
    chart: {
      width: 380,
      type: "pie",
      id: pieId,
    },
    labels: series.labels,
    responsive: [
      {
        breakpoint: 480,
        options: {
          chart: {
            width: 200,
          },
          legend: {
            position: "bottom",
          },
        },
      },
    ],
  };
  return options;
}
