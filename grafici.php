<?php

	$txt="";
	if(isset($_POST["data"]) && isset($_POST["grafico"]))
	{
		$txt=$_POST["data"];
	}
	else
		die("Dati non passati!");
	
?>

<html>
	<head>
    	<title>Valenza Emotiva</title>
		<script src="js/jquery.min.js"></script>
		<script src="js/d3.min.js" charset="utf-8"></script>
		<script src="js/radarChart.js"></script>	
		<!-- For plotting: Chart.js -->
		<script src="js/lib/Chart.js"></script>
		<!-- Functions from lodash -->
		<script src="js/lib/lodash.js"></script>
		<!-- Custom plotting -->
		<script src="js/plots.js"></script>
		<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
		<script type="text/javascript">
		  google.charts.load('current', {'packages':['corechart']});
		  google.charts.setOnLoadCallback(Inizia);
			window.chartColors = {
				red: 'rgb(255, 99, 132)',
				orange: 'rgb(255, 159, 64)',
				yellow: 'rgb(255, 205, 86)',
				green: 'rgb(75, 192, 192)',
				blue: 'rgb(54, 162, 235)',
				purple: 'rgb(153, 102, 255)',
				grey: 'rgb(201, 203, 207)'
			};
		
			var tipoDiGrafico='<?php  echo $_POST["grafico"];?>';
			var JsonTxt='<?php  echo $txt;?>';
			var JsonArray={};
			var FullData={};
			
			var EmotionList=null;
			
			//
			var sum_ok = [];
			//
			var ColoriGrafici=["#FF0000","#0700EF","#E4F400","#38d835","#E600F2","#00EDE9","#0C0014"];
			
			var Tempo_tipoVisualizzazione=0;
			var DataArmoniche=null;
			
			
			function Inizia()
			{
				$(document).ready(function() //aspetta che tutto il documento sia caricato
				{  
					$("#buttonShowFiltriContainer").click(function(){
						$("#FiltriContainer").slideToggle(500);
					});
					$("#TipoVisualizzazione").change(function(){
						Tempo_tipoVisualizzazione=$(this).val();
						if($(this).val()==1)//armoniche
						{					
							$("#div_emo_armoniche").slideDown(300);
							DrawGraphic(FullData);
						}
						else
						{
							$("#div_emo_armoniche").slideUp(300);
							DrawGraphic(FullData);
						}
					});
					
					Tempo_tipoVisualizzazione=0;
					$("#TipoVisualizzazione").val(Tempo_tipoVisualizzazione);
					$("#div_emo_armoniche").slideUp(300);
					
					
					ParseJsonData(JsonTxt);
					
					
					if( tipoDiGrafico=="g3")
					{
						$("#td_g1").css("display", "");
						$("#td_g2").css("display", "");
						$("#buttonShowFiltriContainer").css("display", "none");
						
						//nascondi g2
						PrintCheckBox();
						PrintCheckBoxFiles();
						RisettaColoriLegenda();
					}
					else if( tipoDiGrafico=="g4")
					{
						$("#td_g1").css("display", "none");
						$("#td_g2").css("display", "none");
						$("#buttonShowFiltriContainer").css("display", "");
						PrintSelectFile();
						PrintTempoEmoSelect();
					}
					
					DrawGraphic(FullData); 
				});
			}

			function ParseJsonData(json_response)
			{
				JsonArray=JSON.parse(json_response);
			
				//controllo se esiste json_response[error] -> torno all'index

				for (var FileName in JsonArray) {
					currentValue=JsonArray[FileName];
					var ToSetEmoName=false;
					if(EmotionList==null)
					{
						EmotionList={};
						ToSetEmoName=true;
					}
					
					var Totale=0;
					if (FullData[FileName] == null)
					{
							FullData[FileName]={};
							FullData[FileName]["aggregate"]={};
					}
						
					currentValue["result"]["aggregate"].forEach(function(currentValue2, index, arr){
						FullData[FileName]["aggregate"][currentValue["emotion_names"][index]]=currentValue2;
						Totale+=currentValue2;
						if(ToSetEmoName)
							EmotionList[index]=currentValue["emotion_names"][index];
					});
					FullData[FileName]["emotionsTemporale"]=currentValue["result"]["emotions"];
					FullData[FileName]["harmonics"]=currentValue["result"]["harmonics"];
					
					//TODO: scorrere tutto l'array per ciascuna emozione e troncare i valori a X cifre decimali
					
					//calcolo la percentuale
					for(var key in FullData[FileName]["aggregate"])
					{
						//x:100=parte:tot
						FullData[FileName]["aggregate"][key]=FullData[FileName]["aggregate"][key]/Totale;
					}
					FullData[FileName]["text_id"]=currentValue["text_id"];
					
					//CurrentFileName=FileName;
				}
				
			}
			
			function DrawTot(DataToPrint)
			{
			    //Grafico 1 Totale
			    var DataToPrint_1 = sum_ok;
				var emo = [];
				var val = [];
			    
				 // estraggo la stringa contenente il nome dell'emozione e i valori
                        for (var i=0; i<DataToPrint_1.length; i++){
                            emo[i] = DataToPrint_1[i][0];
                        }	
                        
                        var i=0;
                        for(var key in DataToPrint_1){
    	                    val[i]={axis:emo[i],value:DataToPrint_1[i][1]/100};
    	                    i++;
                        }
			        val.shift();
			        var prova =[];
			        prova[0]=val;
					
					var margin = {top: 100, right: 100, bottom: 100, left: 100},
						width = Math.min(700, window.innerWidth - 10) - margin.left - margin.right,
						height = Math.min(width, window.innerHeight - margin.top - margin.bottom - 20);

						var color = d3.scale.ordinal()
							.range(ColoriGrafici);
															 
						var radarChartOptions = {
						  w: width,
						  h: height,
						  margin: margin,
						  maxValue: 0.5,
						  levels: 5,
						  roundStrokes: true,
						  color: color
						};
						
						//Call function to draw the Radar chart
						RadarChart(".radarChart", prova, radarChartOptions);	
					
				
				//Grafico 2 Totale
				DataToPrint = sum_ok;
				
				DataToPrint.shift();
				DataToPrint.unshift(["Emozioni","Totale"]);
				

				var data = google.visualization.arrayToDataTable(DataToPrint);
						
						
						var options = {
							legend: {position: 'none'},
							bar: {groupWidth: "60%"},
							colors: ColoriGrafici,
							'chartArea': {
								'backgroundColor': {
									'fill': '#000000'
								 },
							 }
						};
						
						var chart = new google.visualization.ColumnChart(document.getElementById('curve_chart'));

						chart.draw(data, options);
						$('#curve_chart').find('svg rect:eq( 1 )').attr('fill-opacity','0');
						
						$('#curve_chart svg text').css({"font-size": "15px","font-family":"'Raleway', sans-serif"});
						$('#curve_chart svg text').attr("stroke", "#000000");
						$('#curve_chart svg text').attr("stroke-width", "1");
						$('#curve_chart').find('svg g:eq( 0 ) rect').attr('fill-opacity','0.7');
			
			}
			
			function DrawGraphic(DataToPrint)
			{
				
				if(tipoDiGrafico=="g1" || tipoDiGrafico=="g3" )
				{
					// GRAFICO 1
					var margin = {top: 100, right: 100, bottom: 100, left: 100},
						width = Math.min(700, window.innerWidth - 10) - margin.left - margin.right,
						height = Math.min(width, window.innerHeight - margin.top - margin.bottom - 20);
						
					
					var d=[];
					
					var i=0;
					for (var keyFile in DataToPrint) {
						d[i]=[];
						for (var key in DataToPrint[keyFile]["aggregate"]) {
							d[i].push({axis:key,value:DataToPrint[keyFile]["aggregate"][key]});
						}
						i++;
					}
					console.log(d);
					if( d.length==0 || d[0].length==0)
					{
						$(".divError1").css("display","");
						$(".radarChart").css("display","none");
					}
					else
					{
						$(".divError1").css("display","none");
						$(".radarChart").css("display","");
						
						
						var color = d3.scale.ordinal()
							.range(ColoriGrafici);
															 
						var radarChartOptions = {
						  w: width,
						  h: height,
						  margin: margin,
						  maxValue: 0.5,
						  levels: 5,
						  roundStrokes: true,
						  color: color
						};
						
						//Call function to draw the Radar chart
						RadarChart(".radarChart", d, radarChartOptions);	
					}

				}
				
				if(tipoDiGrafico=="g2" || tipoDiGrafico=="g3" )
				{
					// GRAFICO 2
				
					var d_google=[];
					i=0;
					var primaRiga=true;
					
					
					for (var keyFile in DataToPrint) {
						if(primaRiga)
						{
							d_google[0]=[];
							d_google[0].push("Emozioni");			
							for (var key in DataToPrint[keyFile]["aggregate"]) {
								d_google[0].push(key);
							}
							primaRiga=false;
							i++;
						}
						d_google[i]=[];
						d_google[i].push(keyFile);
						for (var key in DataToPrint[keyFile]["aggregate"]) {
							d_google[i].push(DataToPrint[keyFile]["aggregate"][key]*100);
						}
						i++;
					}
				
					
					if( d_google.length<=1 || d_google[0].length<=1)
					{
						$(".divError2").css("display","");
						$("#curve_chart").css("display","none");
					}
					else
					{
						$(".divError2").css("display","none");
						$("#curve_chart").css("display","");
						
						d_google=transpose(d_google);
						
					
					/*
						emo,		"nome primo file",		"nome secondo file"
						"rabbia",	10,						15							Nfile+1???
						"paura",	10,						15
							
					
					
						emo,		"Totale",		
						"rabbia",	25,											Nfile+1???
						"paura",	25,						
						
					
					*/
					
					/*
					Ordinamento
					*/
					var sortable = [];
				
					if(d_google[0].length==2){
					    
					    for (var i=0; i<d_google.length; i++) {
                            sortable.push(d_google[i]);
                            
                        }
                        sortable.shift();
                        
                        for(var j=0; j<sortable.length; j++){
                            sortable.sort(function(a, b) {
                                return a[1] - b[1];
                            });
    					}
    					
    					sortable.unshift(d_google[0]);
					    
					}
					else{
    					 
                        for (var key in d_google) {
                            sortable.push(d_google[key]);
                        }
                        
                        sortable.shift();
                        
                        sortable.sort();
                        
                        for(var j=0; j<sortable.length; j++){
                            sortable[j].sort(function(a, b) {
                                return a - b;
                            });
    					}
    					
                        var sum = []; 
                        for(var x=0; x<sortable.length; x++){
                            
                            for(var y=1; y<sortable[x].length; y++){
                                sum[x] = sortable[x][y];
                                
                                if(sum[x]<sortable[x][y])
                                sum[x] = sortable[x][y];
                            }
                        }
					
                    
                        var emozioni = [];
                        
                        // pulisco il vettore sum_ok
                        sum_ok.length=0;
                        
                        // estraggo la stringa contenente il nome dell'emozione
                        for (var i=0; i<sortable.length; i++){
                            emozioni[i] = sortable[i][0];
                        }
                        
                        
                        // vettore contenente le somme delle singole emozioni
                        for(var i=0; i< sortable.length; ++i){
    	                    sum_ok[i] = [emozioni[i],sum[i]];
                        }
                        
                        //ordinamento del vettore delle somme delle emozioni
                        sum_ok.sort(function(a, b) {
                            return a[1] - b[1];
                        });
					
                        sortable.unshift(d_google[0]);
                        sum_ok.unshift(d_google[0]);
					}
				
						var data = google.visualization.arrayToDataTable(sortable);
						
						
						var options = {
							legend: {position: 'none'},
							bar: {groupWidth: "60%"},
							colors: ColoriGrafici,
							'chartArea': {
								'backgroundColor': {
									'fill': '#000000'
								 },
							 }
						};
						
						var chart = new google.visualization.ColumnChart(document.getElementById('curve_chart'));

						chart.draw(data, options);
						$('#curve_chart').find('svg rect:eq( 1 )').attr('fill-opacity','0');
						
						$('#curve_chart svg text').css({"font-size": "15px","font-family":"'Raleway', sans-serif"});
						$('#curve_chart svg text').attr("stroke", "#000000");
						$('#curve_chart svg text').attr("stroke-width", "1");
						$('#curve_chart').find('svg g:eq( 0 ) rect').attr('fill-opacity','0.7');
					}
				}
			
				if(tipoDiGrafico=="g4")
				{
					if( Tempo_tipoVisualizzazione==1) //Armoniche 
					{					
						var harmonics=FullData[$("#SelectFiles").val()]["harmonics"];
						var Emotion= $("#emo_armoniche").val();
						
						var canv=document.createElement('CANVAS');
						$(canv).attr('id','grafico-temporale');
						$("#grafico-temporale-container").html(canv);
						
											
						//10
						var ctxTemporale = document.getElementById('grafico-temporale').getContext('2d');
						var _10 = harmonics[10][Emotion];
						var xLabels = _.range(_10.length);
						var _10Color = {"r": 255, "g": 255, "b": 131, "a": 1};
						var temporalePlot = makeLinePlot(ctxTemporale, _10, "10", xLabels, _10Color);
						
						
						//20
						var _20Color = {"r": 160, "g": 194, "b": 242, "a": 1};
						addNewDatasetToPlot(temporalePlot, {
							label: "20",
							data: harmonics[20][Emotion],
							borderColor: convertColorToString(_20Color, false),
							backgroundColor: convertColorToString(_20Color, true)
						});
					
						//100
						var _100Color = {"r": 255, "g": 180, "b": 180, "a": 1};
						addNewDatasetToPlot(temporalePlot, {
							label: "100",
							data: harmonics[100][Emotion],
							borderColor: convertColorToString(_100Color, false),
							backgroundColor: convertColorToString(_100Color, true)
						});
						
					
						return;
					}				
					
					var canv=document.createElement('CANVAS');
					$(canv).attr('id','grafico-temporale');
				
					$("#grafico-temporale-container").html(canv);
					
					var emoValues=DataToPrint[$("#SelectFiles").val()]["emotionsTemporale"];
					
					// Emozione per emozione
                    var ctxTemporale = document.getElementById('grafico-temporale').getContext('2d');
                    var gioia = emoValues['Gioia'];
                    var xLabels = _.range(gioia.length);
                    var gioiaColor = {"r": 255, "g": 255, "b": 131, "a": 1};
                    var temporalePlot = makeLinePlot(ctxTemporale, gioia, "Gioia", xLabels, gioiaColor);
					
                    var tristezzaColor = {"r": 160, "g": 194, "b": 242, "a": 1};
                    addNewDatasetToPlot(temporalePlot, {
                        label: "Tristezza",
                        data: emoValues['Tristezza'],
                        borderColor: convertColorToString(tristezzaColor, false),
						backgroundColor: convertColorToString(tristezzaColor, true)
                    });
					
					var rabbiaColor = {"r": 255, "g": 180, "b": 180, "a": 1};
					addNewDatasetToPlot(temporalePlot, {
                        label: "Rabbia",
                        data: emoValues['Rabbia'],
                        borderColor: convertColorToString(rabbiaColor, false),
                        backgroundColor: convertColorToString(rabbiaColor, true)
                    });
					
					var pauraColor = {"r": 75, "g": 174, "b": 136, "a": 1};
                    addNewDatasetToPlot(temporalePlot, {
                        label: "Paura",
                        data: emoValues['Paura'],
                        borderColor: convertColorToString(pauraColor, false),
                        backgroundColor: convertColorToString(pauraColor, true)
                    });
					
					var anticipazioneColor = {"r": 255, "g": 211, "b": 167, "a": 1};
                    addNewDatasetToPlot(temporalePlot, {
                        label: "Anticipazione",
                        data: emoValues['Anticipazione'],
                        borderColor: convertColorToString(anticipazioneColor, false),
                        backgroundColor: convertColorToString(anticipazioneColor, true)
                    });
					
					var disgustoColor = {"r": 228, "g": 161, "b": 255, "a": 1};
                    addNewDatasetToPlot(temporalePlot, {
                        label: "Disgusto",
                        data: emoValues['Disgusto'],
                        borderColor: convertColorToString(disgustoColor, false),
                        backgroundColor: convertColorToString(disgustoColor, true)
                    });
					
					var sorpresaColor = {"r": 190, "g": 230, "b": 255, "a": 1};
                    addNewDatasetToPlot(temporalePlot, {
                        label: "Sorpresa",
                        data: emoValues['Sorpresa'],
                        borderColor: convertColorToString(sorpresaColor, false),
                        backgroundColor: convertColorToString(sorpresaColor, true)
                    });
					
					
					var fiduciaColor = {"r": 149, "g": 229, "b": 151, "a": 1};
                    addNewDatasetToPlot(temporalePlot, {
                        label: "Fiducia",
                        data: emoValues['Fiducia'],
                        borderColor: convertColorToString(fiduciaColor, false),
                        backgroundColor: convertColorToString(fiduciaColor, true)
                    });
				}
			
			}
			
			function PrintCheckBox()
			{
				var ul=document.createElement('UL');
				$(ul).attr('class','EmotionList');

				var obj=FirstInOBJ(FullData);
				for (var key in obj["aggregate"]) {
					var li=document.createElement('LI');
					$(ul).append(li);
					var v1=document.createElement('LABEL');
					var v2=document.createElement('INPUT');
					$(v2).attr('type','checkbox');
					$(v2).attr('checked','');
					$(v2).attr('class','EmotionCheckbox');
					$(v2).attr('emotion',key);
					$(v1).append(v2);
					$(v1).append(key);
					$(li).append(v1);
					$(ul).append(li);
					
					 $(v2).change(function() {
						 ArrayEmotion=ScanCheckbox();
						 ArrayCheckFiles=ScanCheckboxFiles();
						 newData=CreateData(ArrayEmotion,ArrayCheckFiles);
						 RisettaColoriLegenda();
						 DrawGraphic(newData);
					 });
	
				}
				
				//CheckBox Emozioni Prevalenti
				var li=document.createElement('LI');
					$(ul).append(li);
					var v1=document.createElement('LABEL');
					
					var vcolor=document.createElement('DIV');
					$(vcolor).attr('color','');
					$(vcolor).attr('class','ColorFile');
					$(vcolor).attr('fileName',key);
					$(vcolor).attr('style',"width:13px;height:13px;display: inline-block;");
					
					var v2=document.createElement('INPUT');
					$(v2).attr('type','checkbox');
					$(v2).attr('class','SumCheckbox');
					$(v2).attr('fileName',key);
					
					$(v1).append(v2);
					$(v1).append('Emozioni Prevalenti');
					$(li).append(v1);
					$(ul).append(li);
					
			$("#CheckboxContainer").append(ul);
				
			}
			
			function ScanCheckbox()
			{
				//scansiona tutte le checkbox e ritorna la lista di checkbox attive
				var ListEmotion=[];
				
				$(".EmotionCheckbox").each(function( index ) {
					if( $(this).is(":checked"))
						ListEmotion.push($( this ).attr("emotion"));
				});
				
				return ListEmotion;
			}
			
			
			function PrintCheckBoxFiles()
			{
				var ul=document.createElement('UL');
				$(ul).attr('class','EmotionList');

				for (var key in FullData) {
					var li=document.createElement('LI');
					$(ul).append(li);
					var v1=document.createElement('LABEL');
					
					var vcolor=document.createElement('DIV');
					$(vcolor).attr('color','');
					$(vcolor).attr('class','ColorFile');
					$(vcolor).attr('fileName',key);
					$(vcolor).attr('style',"width:13px;height:13px;display: inline-block;");
					
					var v2=document.createElement('INPUT');
					$(v2).attr('type','checkbox');
					$(v2).attr('checked','');
					$(v2).attr('class','FilesCheckbox');
					$(v2).attr('fileName',key);
					
					$(v1).append(vcolor);
					$(v1).append(v2);
					$(v1).append(key);
					$(li).append(v1);
					$(ul).append(li);
					
					 $(v2).change(function() {
						 ArrayEmotion=ScanCheckbox();
						 ArrayCheckFiles=ScanCheckboxFiles();
						 newData=CreateData(ArrayEmotion,ArrayCheckFiles);
						 RisettaColoriLegenda();
						 DrawGraphic(newData);
						 
					 });	
	
				}
				
				
				$("#CheckboxFilesContainer").append(ul);
				
				// codice che deseleziona tutte le checkbox dei files e delle emo
				// se la checkbox Emozioni Prevalenti è selezionata
			
				if(ScanCheckboxFiles().length == 1)
				     $(".SumCheckbox").attr("disabled", true);
				
				    $(".SumCheckbox").change(function () {
                        if ($(".SumCheckbox").is(':checked')) {
                          $(".FilesCheckbox").prop("checked", false);
                          $(".FilesCheckbox").attr("disabled", true);
                          $(".EmotionCheckbox").prop("checked",false);
                          $(".EmotionCheckbox").attr("disabled",true);
                          RisettaColoriLegenda();
                          DrawTot();
                        
                    } 
                
                else 
                    {
                        $(".FilesCheckbox").prop("checked", true);
                        $(".FilesCheckbox").attr("disabled", false);
                        $(".EmotionCheckbox").prop("checked",true);
                        $(".EmotionCheckbox").attr("disabled",false);
                        ArrayEmotion=ScanCheckbox();
                        ArrayCheckFiles=ScanCheckboxFiles();
                        newData=CreateData(ArrayEmotion,ArrayCheckFiles);
                        RisettaColoriLegenda();
						DrawGraphic(newData);
                    }
				
				});
				
			}
			
			function PrintSelectFile()
			{
				var sel=document.createElement('SELECT');
				$(sel).attr("id","SelectFiles");
				for (var key in FullData) {
					var opt=document.createElement('OPTION');
					$(opt).attr("value",key);
					$(opt).text(key);
					$(sel).append(opt);	
				}
				
				$(sel).change(function() {
					if(Tempo_tipoVisualizzazione==1) //armoniche
					{					
						DrawGraphic(FullData);
					}
					else
						DrawGraphic(FullData);
				 });
				
				$("#SelectFilesContainer").append("<b> File sorgente: </b> &nbsp;");
				$("#SelectFilesContainer").append(sel);

			}
			
			function PrintTempoEmoSelect()
			{
				var obj=FirstInOBJ(FullData);
				for (var key in obj["aggregate"]) {
					var o=document.createElement('OPTION');
					$(o).val(key);
					$(o).append(key);
					$("#emo_armoniche").append(o);
				}
				$("#emo_armoniche").change(function(){
					DrawGraphic(FullData);
				});

			}
			
			
			function ScanCheckboxFiles()
			{
				//scansiona tutte le checkbox e ritorna la lista dei file
				var ListFiles=[];
				
				$(".FilesCheckbox").each(function( index ) {
					if( $(this).is(":checked"))
						ListFiles.push($( this ).attr("fileName"));
				});
				
				return ListFiles;
			}
			
			
			function CreateData(ArrayEmotion,ArrayCheckFiles)
			{
				//Crea il Data da passare poi alla DrawGraphic in base all'ArrayEmotion passato ( base di dati -> FullData)
				var CheckFile=false;
				
				if(ArrayCheckFiles!=null)
					CheckFile=true;
				
				var newData={};
				for(var keyFile in FullData)
				{
					if(!CheckFile || $.inArray(keyFile, ArrayCheckFiles)!=-1)
					{
						if(newData[keyFile]==null)
						newData[keyFile]={};
						newData[keyFile]["aggregate"]={};
						
						ArrayEmotion.forEach(function(currentValue, index, arr){
							newData[keyFile]["aggregate"][currentValue]= FullData[keyFile]["aggregate"][currentValue];
						});
					
					}
				}	
				
				return newData;
				
			}
			
			function FirstInOBJ(obj)
			{
				for (var x in obj)
					return obj[x];
			}
						
			
			function RisettaColoriLegenda()
			{
				var i=0;
				$(".ColorFile").each(function( index ) {
					if( $(this).parent().find(".FilesCheckbox").first().is(":checked"))
					{
						$(this).attr("color",ColoriGrafici[i%ColoriGrafici.length]);
						$(this).css("background-color",$(this).attr("color"));
						$(this).css("border","1px solid black"); 
						i++;
					}
					else
					{
						$(this).css({ 'background-color' : ''});
						$(this).css({ 'border' : ''});
						$(this).attr("color","");
					}
						
				});
				
			}
	
			function transpose(a) 
			{

				  // Calculate the width and height of the Array
				  var w = a.length || 0;
				  var h = a[0] instanceof Array ? a[0].length : 0;

				  // In case it is a zero matrix, no transpose routine needed.
				  if(h === 0 || w === 0) { return []; }

				  /**
				   * @var {Number} i Counter
				   * @var {Number} j Counter
				   * @var {Array} t Transposed data is stored in this array.
				   */
				  var i, j, t = [];

				  // Loop through every item in the outer array (height)
				  for(i=0; i<h; i++) {

					// Insert a new row (array)
					t[i] = [];

					// Loop through every item per item in outer array (width)
					for(j=0; j<w; j++) {

					  // Save transposed data.
					  t[i][j] = a[j][i];
					}
				  }

				  return t;
				}
        </script>
		
	
		<!-- Google fonts -->
		<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300' rel='stylesheet' type='text/css'>
		<link href='https://fonts.googleapis.com/css?family=Raleway' rel='stylesheet' type='text/css'>
		<!-- Import Boostrap and JQuery--> 
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
		<!-- -->

		<style>
			body 
			{
                 background: url('../Resources/Sfondo.png') no-repeat;
				 background-attachment: fixed;
				   background-size: cover;
				font-family: 'Open Sans', sans-serif;
				font-size: 11px;
				font-weight: 300;
				fill: #242424;
				text-align: center;
				text-shadow: 0 1px 0 #fff, 1px 0 0 #fff, -1px 0 0 #fff, 0 -1px 0 #fff;
				cursor: default;
			}
			
			.legend {
				font-family: 'Raleway', sans-serif;
				fill: #333333;
			}
			
			.tooltip {
				fill: #333333;
			}
			
			
			.EmotionList
			{
				list-style-type: none;
			}
			.divError1,.divError2{
				background-color: red;
				width: 200px;
				text-align: center;
				font-size: 30px;
				margin: auto;
				}
				
				#grafico-temporale{
					
					max-width:1200px;
					margin: auto;
				}
				
				#buttonShowFiltriContainer{
					margin:auto;
					
				}
		</style>
  
	</head>

	<body>
	
    <a href="http://stage2017.000webhostapp.com/index.php">
			<img src="./Resources/Home.png" style="float: left"/></a>
			
	<a href="javascript:history.go(-1);"><img src="./Resources/Freccia_SX.png" style="float: left"/></a>
	
	<table style="width:80%;">
		<tr>
		    
			<td>  
				<h1 style="text-align: center;"><font face="Georgia"><br>
					Legenda
				</h1></font>
				
				<div style="text-align:center;" >
					<br>
					<button id="buttonShowFiltriContainer" class="btn btn-primary">Filtri</button><br>
					<div id="FiltriContainer" style="display:none">
						<table style="margin:auto;">
							<tr>
								<td>
									<div><br>
										<table style="margin:auto;">
											<tr>
												<td><font face="Georgia">
													Tipo di visualizzazione
												</td></font>
												<td> &nbsp;
													<select id="TipoVisualizzazione">
														<option value="0"><font face="Georgia">Normale</option>
														<option value="1"><font face="Georgia">Armoniche</option>
													</select>
												</td>
											</tr>
										</table>
									</div>
								</td>
							</tr>
							<tr >
								<td><br>
									<div id="div_emo_armoniche" >
										<table style="margin:auto;">
											<tr>
												<td><font face="Georgia">
													Tipo di emozione
												</font></td>
												<td> &nbsp;
													<select id="emo_armoniche">
														
													</select>
												</td>
											</tr>
										</table>
									</div>
								</td>
							</tr>
						</table>
					</div>
					<br>
				</div>
				
				<table style="margin:auto;">
					<tr>
						<td>
							<div id="CheckboxContainer"></div>
						</td>
						<td>
							<div id="CheckboxFilesContainer"></div>
							<div id="SelectFilesContainer"></div>
						</td>
					</tr>
				
				</table>
			</td>
		</tr>
		<tr>
			<table style="width:100%;">
				<tr>
					<td id="td_g1" style="width:50%;    vertical-align: top; display:none;">
						<font face="Georgia"><h1 style="text-align: center;">
							Radar
						</h1></font>
						<div class="divError1" style="display:none;"><font face="Georgia">
							Errore!
						</font></div>
						<div class="radarChart" style="    text-align: center;"></div>
						 
					</td>
					
					<td id="td_g2" style="width:50%;    vertical-align: top;  display:none;">
						<font face="Georgia"><h1 style="text-align: center;">
							Bar Chart
						</h1></font>
						<div class="divError2" style="display:none;"><font face="Georgia">
							Errore!
						</font></div>
						<br>
						<div id="curve_chart" style="width: 1100px; height: 500px;    margin: auto;"></div>

						 
					</td>
				</tr>
				<tr>
					<td>
						<div id="grafico-temporale-container" width="400px" height="400px"></div>
					</td>
				</tr>
			</table>
		
		</tr>
	
	</table>
		
    </body>
</html>