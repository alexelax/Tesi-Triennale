function makeEmoIndexMapping(emoNames) {
    var emoIndexMapping = {};

    for (j = 0; j < emoNames.length; j++) {
        emoIndexMapping[emoNames[j]] = j;
    }

    return emoIndexMapping;
}

function convertColorToString(color, isBackground) {
    var red = color["r"].toString();
    var green = color["g"].toString();
    var blue = color["b"].toString();
    var alpha = color["a"];

    if (isBackground == true) {
        alpha = 0.6		* alpha;
    }

    alpha = alpha.toString();

    var colorString = "rgba(".concat(red);
    colorString = colorString.concat(", ").concat(green).concat(", ");
    colorString = colorString.concat(blue).concat(", ").concat(alpha);
	colorString = colorString.concat(")"); 
	
	/*var colorString = "rgb(".concat(red);
    colorString = colorString.concat(", ").concat(green).concat(", ");
    colorString = colorString.concat(blue);*/
	
    return colorString;
}

function makeRadarPlot(context, emoNames, emoValues) {
    emoIndexMapping = makeEmoIndexMapping(emoNames);

    var emoLabels = ["Gioia", "Fiducia", "Paura", "Sorpresa",
                     "Tristezza", "Disgusto", "Rabbia", "Anticipazione"];

    var dataToPlot = [];
    for (i = 0; i < emoLabels.length; i++) {
        key = emoLabels[i];
        value = emoValues[emoIndexMapping[key]];
        dataToPlot.push(value);
    }

    // radarBorderColor = "rgba(0, 157, 255, 1.0)";
    // radarBgColor = "rgba(0, 157, 255, 0.2)";

    radarBorderColor = "rgba(162, 210, 255, 1.0)";
    radarBgColor = "rgba(162, 210, 255, 0.4)";

    var myChart = new Chart(context, {
        type: 'radar',
        data: {
            labels: emoLabels,
            datasets: [{
                label: "Intensità",
                backgroundColor: radarBgColor,
                borderColor: radarBorderColor,
                pointBackgroundColor: radarBorderColor,
                data: dataToPlot
            }]
        },
        options: {
            onresize: function(instance, newSize) {
                console.log(instance);
                console.log(newSize);
            },
            maintainaspectratio: true,
            legend: {
                labels: {
                    fontSize: 16
                }
            },
            scale: {
                // display: false,
                pointLabels: {
                    fontSize: 18
                }
            }
        }
    });

    return myChart;
}

function makeLinePlot(context, dataToPlot, dataName, xLabels, color) {
    var lineColor = convertColorToString(color, false);
    var fillColor = convertColorToString(color, true);

    var myData = {
        labels: xLabels,
        datasets: [{
            label: dataName,
            data: dataToPlot,
            borderColor: lineColor,
            backgroundColor: fillColor
        }]
    }

    if (xLabels.length > 30) {
        myData['datasets'][0]['pointRadius'] = 0
        myData['datasets'][0]['pointHitRadius'] = 5
    }

    var myChart = new Chart(context, {
        type: 'line',
        data: myData,
        options: {
            onresize: function(instance, newSize) {
                console.log(instance);
                console.log(newSize);
            },
            maintainaspectratio: true,
			tooltips: {
				/*mode: 'index',			//modificato per tipo di visualizzazione TOOLTIPS
				intersect: false,*/
				mode: 'index',
				position: 'nearest',
			},
			hover: {
				mode: 'nearest',
				intersect: true
			},
            scales: {
                xAxes:[{
					 display: true,
					scaleLabel: {
						display: true,
						labelString: 'Tempo'
					}
                }],
                yAxes:[{
					display: true,
					scaleLabel: {
						display: true,
						labelString: 'Valori'
					}
                }]
            }
        }
    });

    return myChart;
}

function addNewDatasetToPlot(plot, newDataset) {
    if (newDataset['data'].length > 20) {
        newDataset['pointRadius'] = 0
        newDataset['pointHitRadius'] = 5
    }

    plot.data.datasets.push(newDataset)
    plot.update()
}
