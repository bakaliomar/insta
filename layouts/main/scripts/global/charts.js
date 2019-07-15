var Charts = function ()
{
    return {
        createChart: function (id, data, colors,lastIndex)
        {
            var chart = $('#' + id);
            
            if (chart.size() != 0)
            {
                $('#' + id + '-loading').hide();
                $('#' + id + '-content').show();

                var chartData = [];

                for (var index in data)
                {
                    var tmpData = [];
                    
                    
                    for (var i = 1;i <= lastIndex;i++)
                    {
                        var found = false;

                        for (var j = 0; j < data[index][1].length;j++)
                        {
                            if(data[index][1][j][0] == i)
                            {
                                tmpData.push([i,data[index][1][j][1]]);
                                found = true;
                            }
                        }

                        if(found == false)
                        {
                            tmpData.push([i,0]);
                        }
                    }
                    
                    chartData.push({
                        label: data[index][0],
                        data: tmpData,
                        lines: {lineWidth: 1},
                        shadowSize: 0
                    });
                }

                $.plot(chart, chartData ,{
                    series: {
                        lines: {
                            show: true,
                            lineWidth: 2,
                            fill: true,
                            fillColor: {
                                colors: [{
                                    opacity: 0.05
                                }, {
                                    opacity: 0.01
                                }]
                            }
                        },
                        points: {
                            show: true,
                            radius: 3,
                            lineWidth: 1
                        },
                        shadowSize: 2
                    },
                    grid: {
                        hoverable: true,
                        clickable: true,
                        tickColor: "#eee",
                        borderColor: "#eee",
                        borderWidth: 1
                    },
                    colors: colors,
                    xaxis: {
                        ticks: 11,
                        tickDecimals: 0,
                        tickColor: "#eee",
                    },
                    yaxis: {
                        ticks: 11,
                        tickDecimals: 0,
                        tickColor: "#eee",
                    }
                });

                var previousPoint = null;

                chart.bind("plothover", function (event, pos, item)
                {
                    event.preventDefault();
                    
                    $("#x").text(pos.x.toFixed(2));
                    $("#y").text(pos.y.toFixed(2));

                    if (item)
                    {
                        if (previousPoint != item.dataIndex)
                        {
                            previousPoint = item.dataIndex;
                            $("#tooltip").remove();
                            Charts.showChartTooltip(item.pageX, item.pageY, item.datapoint[1] + " " + item.series.label);
                        }
                    } else
                    {
                        $("#tooltip").remove();
                        previousPoint = null;
                    }
                });
            }
        },
        showChartTooltip : function(x, y,yValue) 
        {
            $('<div id="tooltip" class="chart-tooltip">' + yValue + '<\/div>').css({
                position: 'absolute',
                display: 'none',
                top: y - 40,
                left: x - 40,
                border: '0px solid #ccc',
                padding: '2px 6px',
                'background-color': '#fff'
            }).appendTo("body").fadeIn(200);
        }
    };
}();
