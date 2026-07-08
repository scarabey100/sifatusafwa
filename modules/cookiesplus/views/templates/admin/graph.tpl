{**
 * ISC License
 *
 * Copyright (c) 2025 idnovate.com
 * idnovate is a Registered Trademark & Property of idnovate.com, innovación y desarrollo SCP
 *
 * Permission to use, copy, modify, and/or distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
 * REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
 * INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
 * LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
 * OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
 * PERFORMANCE OF THIS SOFTWARE.
 *
 * @author    idnovate
 * @copyright 2025 idnovate.com
 * @license   https://www.isc.org/licenses/ https://opensource.org/licenses/ISC ISC License
 *}

<style>
    #chartContainer svg {
        width: 100%;
        height: 500px;
    }

    #content.bootstrap .nvtooltip h3 {
        margin: 0;
        padding: 4px 14px;
        line-height: 18px;
        font-weight: normal;
        background-color: rgba(247, 247, 247, 0.75);
        text-align: center;
        border-bottom: 1px solid #ebebeb;
        -webkit-border-radius: 5px 5px 0 0;
        -moz-border-radius: 5px 5px 0 0;
        border-radius: 5px 5px 0 0;
    }
</style>

<div>
    <div class="btn-group">
        <label class="btn btn-default">
            <input type="radio" id="refreshConsentsHits" name="options" onchange="refreshConsents('hits');"> {l s='Hits' mod='cookiesplus'}
        </label>
        <label class="btn btn-default">
            <input type="radio" id="refreshConsentsPercentage" name="options" onchange="refreshConsents('percentage');">
            {l s='Percentage' mod='cookiesplus'}
        </label>
    </div>

    <div id="chartContainer">
        <svg></svg>
    </div>

    <button class="btn btn-default row-margin-top" name="submitCookiesPlusRevokeCookies" onclick="resetStats();">
        <i class="icon-refresh"></i>
        {l s='Reset stats' mod='cookiesplus'}
    </button>
</div>

<script>
    var chart;
    let usersConsentControllerLink = "{$usersConsentControllerLink nofilter}";

    function refreshConsents(data) {
        $.ajax({
            url: usersConsentControllerLink,
            data: {
                ajax: true,
                action: 'refreshConsents',
                type: data
            },
            success: function (result) {
                result = JSON.parse(result);

                // Clear chart
                $('#chartContainer').html('').html('<svg></svg>');
                chart = nv.models.lineChart();
                d3.select('#chartContainer svg')
                    .attr('width', 960) // Set SVG width to match chart width
                    // .attr('height', 500)
                    .datum([])
                    .call(chart);

                if (result.chart === 'line') {
                    nv.addGraph(function () {
                        chart = nv.models.lineChart()
                            .margin({ left: 100, right: 100, top: 0, bottom: 100 })
                            .useInteractiveGuideline(true);

                        chart.xAxis
                            .axisLabel("{l s='Date' mod='cookiesplus'}")
                            .tickFormat(function (d) {
                                return d3.time.format('%Y-%m-%d')(new Date(d));
                            });

                        chart.yAxis
                            .axisLabel("{l s='Hits' mod='cookiesplus'}")
                            .tickFormat(d3.format('d'));

                        let accept = [],
                            refuse = [],
                            configure = [];

                        let parseDate = d3.time.format("%Y-%m-%d").parse;
                        let formatDate = d3.time.format("%Y-%m-%d");

                        // Collect all unique dates and action data
                        let dates = {};

                        result.stats.forEach(function (item) {
                            let date = parseDate(item.day);
                            if (!dates[date]) {
                                dates[date] = { date: date, accept: 0, refuse: 0, configure: 0 };
                            }
                            if (item.action == 1) {
                                dates[date].accept = parseInt(item.total_records);
                            } else if (item.action == 2) {
                                dates[date].refuse = parseInt(item.total_records);
                            } else {
                                dates[date].configure = parseInt(item.total_records);
                            }
                        });

                        // Get the complete range of dates
                        let dateKeys = Object.keys(dates).sort(function (a, b) {
                            return new Date(a) - new Date(b);
                        });
                        let startDate = new Date(dateKeys[0]);
                        let endDate = new Date(dateKeys[dateKeys.length - 1]);

                        // Populate missing dates
                        for (let d = new Date(startDate); d <= endDate; d.setDate(d.getDate() + 1)) {
                            let dateStr = formatDate(d);
                            if (!dates[d]) {
                                dates[d] = { date: new Date(d), accept: 0, refuse: 0, configure: 0 };
                            }
                        }

                        // Sort the dates before populating the arrays for the chart
                        let sortedDates = Object.keys(dates).sort(function (a, b) {
                            return new Date(a) - new Date(b);
                        });

                        // Populate the arrays for the chart
                        sortedDates.forEach(function (date) {
                            let entry = dates[date];

                            let total = entry.accept + entry.refuse + entry.configure;
                            let acceptPct = total > 0 ? entry.accept / total : 0;
                            let refusePct = total > 0 ? entry.refuse / total : 0;
                            let configurePct = total > 0 ? entry.configure / total : 0;

                            accept.push({ x: entry.date, y: entry.accept, z: acceptPct });
                            refuse.push({ x: entry.date, y: entry.refuse, z: refusePct });
                            configure.push({ x: entry.date, y: entry.configure, z: configurePct });
                        });

                        myData = [
                            {
                                key: "{l s='Accepts Hits' mod='cookiesplus'}",
                                values: accept,
                                color: '#2ca02c'
                            },
                            {
                                key: "{l s='Configure Hits' mod='cookiesplus'}",
                                values: configure,
                                color: '#ff7f0e'
                            },
                            {
                                key: "{l s='Refuse Hits' mod='cookiesplus'}",
                                values: refuse,
                                color: '#d62728'
                            }
                        ];

                        d3.select('#chartContainer svg')
                            .attr('width', 960) // Set SVG width to match chart width
                            // .attr('height', 500)
                            .datum(myData)
                            .call(chart);

                        nv.utils.windowResize(chart.update);

                        return chart;
                    });
                } else {
                    nv.addGraph(function () {
                        chart = nv.models.multiBarChart()
                            .margin({ left: 100, right: 100, top: 100, bottom: 100 })
                            .stacked(true) // Enable stacking
                            .reduceXTicks(true)   //If 'false', every single x-axis tick label will be rendered.
                            .rotateLabels(0)      //Angle to rotate x-axis labels.
                            .showControls(false)   //Allow user to switch between 'Grouped' and 'Stacked' mode.
                            .groupSpacing(0.1) //Distance between each group of bars.
                        ;

                        chart.xAxis
                            .axisLabel("{l s='Date' mod='cookiesplus'}")
                            .tickFormat(function (d) {
                                return d3.time.format('%Y-%m-%d')(new Date(d));
                            });

                        chart.yAxis
                            .axisLabel("{l s='Percentage' mod='cookiesplus'}")
                            .tickFormat(d3.format('.0%'));

                        let acceptPercentage = [],
                            refusePercentage = [],
                            configurePercentage = [];

                        let parseDate = d3.time.format("%Y-%m-%d").parse;
                        let formatDate = d3.time.format("%Y-%m-%d");

                        // Collect all unique dates and action data
                        let dates = {};

                        result.stats.forEach(function (item) {
                            let date = parseDate(item.day);
                            if (!dates[date]) {
                                dates[date] = { date: date, accept: 0, refuse: 0, configure: 0 };
                            }
                            if (item.action == 1) {
                                dates[date].accept = parseInt(item.total_records);
                            } else if (item.action == 2) {
                                dates[date].refuse = parseInt(item.total_records);
                            } else {
                                dates[date].configure = parseInt(item.total_records);
                            }
                        });

                        // Get the complete range of dates
                        let dateKeys = Object.keys(dates).sort(function (a, b) {
                            return new Date(a) - new Date(b);
                        });
                        let startDate = new Date(dateKeys[0]);
                        let endDate = new Date(dateKeys[dateKeys.length - 1]);

                        // Populate missing dates
                        for (let d = new Date(startDate); d <= endDate; d.setDate(d.getDate() + 1)) {
                            let dateStr = formatDate(d);
                            if (!dates[d]) {
                                dates[d] = { date: new Date(d), accept: 0, refuse: 0, configure: 0 };
                            }
                        }

                        // Sort the dates before populating the arrays for the chart
                        let sortedDates = Object.keys(dates).sort(function (a, b) {
                            return new Date(a) - new Date(b);
                        });

                        // Populate the arrays for the chart
                        sortedDates.forEach(function (date) {
                            let entry = dates[date];

                            let total = entry.accept + entry.refuse + entry.configure;
                            let acceptPct = total > 0 ? entry.accept / total : 0;
                            let refusePct = total > 0 ? entry.refuse / total : 0;
                            let configurePct = total > 0 ? entry.configure / total : 0;

                            acceptPercentage.push({ x: entry.date, y: acceptPct, z: entry.accept });
                            refusePercentage.push({ x: entry.date, y: refusePct, z: entry.refuse });
                            configurePercentage.push({ x: entry.date, y: configurePct, z: entry.configure });
                        });

                        myData = [
                            {
                                key: "{l s='Accept %' mod='cookiesplus'}",
                                type: 'bar',
                                values: acceptPercentage,
                                color: '#2ca02c'
                            },
                            {
                                key: "{l s='Configure %' mod='cookiesplus'}",
                                type: 'bar',
                                values: configurePercentage,
                                color: '#ff7f0e'
                            },
                            {
                                key: "{l s='Refuse %' mod='cookiesplus'}",
                                type: 'bar',
                                values: refusePercentage,
                                color: '#d62728'
                            }
                        ];

                        d3.select('#chartContainer svg')
                            .attr('width', 960) // Set SVG width to match chart width
                            // .attr('height', 500)
                            .datum(myData)
                            .call(chart);

                        nv.utils.windowResize(chart.update);

                        return chart;
                    });
                }

                /*let chart = nv.models.multiChart()
                    .margin({ left: 100, right: 100, top: 0, bottom: 100 })
                    .tooltipContent(function (key, y, e, graph) {
                        return '<h3 class="modal-title" style="font-size: 16px; font-weight: strong">' + graph.series.originalKey + '</h3>' +
                            '<p>' + graph.series.values[graph.pointIndex].z + ' (' + e + ')</p>' +
                            '<p>' + y + '</p>';
                    });*/
            }
        });
    }

    function resetStats() {
        if (confirm("{l s='Are you sure you want to reset the stats?' mod='cookiesplus'}")) {
            $.ajax({
                url: usersConsentControllerLink,
                data: {
                    ajax: true,
                    action: 'resetStats'
                },
                success: function (result) {
                    result = JSON.parse(result);

                    if (result.result === true) {
                        alert('{l s='Stats reset successfully' mod='cookiesplus'}');
                    }

                    document.getElementById("refreshConsentsHits").click();
                }
            });
        }
    }


    {literal}
    /*nv.addGraph(function() {
        let chart = nv.models.multiBarChart()
            .margin({left: 100, right: 100, top: 0, bottom: 100}) // Adjust margins as needed
            .stacked(true) // Enable stacking
            .showControls(false) // Show controls to switch between stacked and grouped
            .reduceXTicks(true) // Reduce x-axis ticks for better readability
            //.rotateLabels(45) // Rotate x-axis labels if they're too long
            .showYAxis(true)
            .showXAxis(true)
            .tooltipContent(function(key, y, e, graph) {
                return '<h3 class="modal-title" style="font-size: 16px; font-weight: strong">' + key + '</h3>' +
                       '<p>' + graph.series.values[graph.pointIndex].z + ' (' + e + ')</p>' +
                       '<p>' + y + '</p>';
            });

        */
    {/literal}

    // On load, by default
    document.getElementById("refreshConsentsHits").click();
</script>

<div class="clearfix"></div>
