<html>
	<head>
		<title>Emotions Draws</title>
		<h1 align="center"><b><i><font face="Algerian" size="50">Emotions Draws</font></i></b></h1>
		<!-- Import Boostrap and JQuery--> 
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
		<!-- -->
		
		<script src="js/jquery.min.js" charset="utf-8"></script>
		<script src="js/zip/zip.js" ></script>
		<script src="js/zip/zip-ext.js" ></script>
		<script>
		
			zip.workerScriptsPath = "/js/zip/";
			
			var File_Unico="";
			
			var ArrayFileJSON={};
			
			$(document).ready(function(){
				$("#SendTextArea").click(function(){
					RichiediJson($("#TextAreaText").val(),function(json){
						ArrayFileJSON["Testo inserito"]=json;
						InviaArrayPaginaSuccessiva();
					});
	
				});
				
				
				$("#testIn").change(function(){
					var file=this.files[0];
					// use a BlobReader to read the zip from a Blob object
					var ext=GetExtension(file.name).toLowerCase();
					
					if( ext=="zip")
					{
						LeggiZip(file);
						
					}
					else if( ext=="txt")
					{
						LeggiTxt(file);
						
					}
					else
					{
						alert("Tipo di file non riconosciuto!");
					}
					
				});
			
			});
		
		
			function GetExtension(filename)
			{
				return filename.split('.').pop();
			}
			
			
			function LeggiZip(fileZip)
			{
				
				var sommaTesti="";
				
				
				$("#divCaricamento").css("display","");
				$("#spazio").css("display","none");
				
				var FinitoDiLeggere=function(){
					//ho finito di leggere i testi
					//richiedo il json del testo unito
					
					
					$("#NumEl").text("Elaborazione dell'inviluppo...");	
					//richiede il json del testo totale
					RichiediJson(sommaTesti,function(json){ //"sommaTesti" etichetta che si visualizza
						ArrayFileJSON["Inviluppo"]=json;	
						
						//invio i dati alla pagina successiva
						console.log(ArrayFileJSON);
						InviaArrayPaginaSuccessiva();
					});
					
					
				};
				$("#NumEl").text("Lettura file in corso...");
				
				
				zip.createReader(new zip.BlobReader(fileZip), function(reader) {

					  // get all entries from the zip
					  reader.getEntries(function(entries) {
						if (entries.length) {
							
							var tot=0;
							var c=0;
							var c1=0;
							
							
							var LeggiElemento=function(elemento){
								elemento.getData(new zip.TextWriter(), function(text) {
									
									//text è già il testo
									//lo appendo ad una variabile temporanea
									sommaTesti+=text;
									RichiediJson(text,function(json){
										ArrayFileJSON[elemento["filename"]]=json;
										c1++;
										$("#NumEl").text(c1+"/"+tot);
										$("#progressBar").css("width",(100/tot*c1)+"%");
										
										if(c1==tot)
										{
											$("#progressBar").css("width","100%");
											FinitoDiLeggere();
										}
									});
									
										c++;									
										if( c==tot)
										{
											// close the zip reader
											reader.close(function() {});
										}
										
								  }, function(current, total) {
										console.log("current "+current+" - total "+total); 
			
								  });
							}
								
							var ArTmp=[];
							
							
							for(var i=0;i<entries.length;i++)
							{
								if( GetExtension(entries[i]["filename"]).toLowerCase()=="txt")
								{
									ArTmp.push(entries[i]);
								}
							}
							
							tot=ArTmp.length;
							for(var i=0;i<ArTmp.length;i++)
							{
								LeggiElemento(ArTmp[i]);
							}
					
						}
						
						
					  });
					}, function(error) {
						console.log(error); 
						
					});
				
			}
			
		
			function VediTesto(fileTxt)
			{
			    var reader = new FileReader();
			     
                    reader.onload = function(event) {
     
                    File_Unico = event.target.result;
                    
                    };
                    
                File_Unico=File_Unico+"\n\n"+fileTxt;
                
			}
			
			function LeggiTxt(fileTxt)
			{
				//leggi il contenuto del file ed invia
				if (fileTxt) {
					var reader = new FileReader();
					reader.readAsText(fileTxt, "UTF-8");
					reader.onload = function (evt) {
					    
						RichiediJson(evt.target.result,function(json){
							ArrayFileJSON[fileTxt["name"]]=json;
							InviaArrayPaginaSuccessiva();
						});
						
						
					}
					reader.onerror = function (evt) {
						console.log(evt);
					}
				}
				
			}
			
			//Richiedere il json al servizio e richiama la callback alla fine della chiamata 
			function RichiediJson(content,callback)
			{
				data_to_send = JSON.stringify({"content":content, "postprocessing":true, "number_of_harmonics": [10, 20, 100]})

				// use jQuery $.ajax to send and receive async requests
				$.ajax({
					url: 'https://syuzhet-web.herokuapp.com/analyze',
					cache: false,
					type: 'POST', 
					data : data_to_send,
					contentType: 'application/json; charset=utf-8',
					dataType: 'json',
					success: function(json_response) {
						callback(json_response);
					},
					error: function(request, textStatus, errorThrown) {
						// client code to execute in case of error
					}
				});
			}
			
									
			function InviaArrayPaginaSuccessiva(){
				
				var form = document.createElement("form");
				form.method = 'post';
				form.action = 'sceltaGrafico.php';
				var input = document.createElement('input');
				input.type = "textarea";
				input.name = "data";
				input.value = JSON.stringify(ArrayFileJSON);
				form.appendChild(input);
				$(form).css("display","none");
				
				$("body").append(form);
				
				form.submit();
				
			}
			
			
			var b=false;
			function Notify()
			{
				b=true;
			}
			
			function Wait(callback)
			{
				if(b)
				{
					b=false;
					callback();
				}
				else
					setTimeout(function(){ Wait(callback);},100);
			}

		</script>
		<style>
		
			span
			{
				font-size: 19px;
                font-weight: bold;
			}
			
		</style>
		
	</head>
	<body background="./Resources/Sfondo.png">
		<table style="margin:auto;    text-align: center;">
                <br>
                <h1 align="center"><b><i><font face="Georgia">Visualizzazione della valenza emotiva di testi narrativi</font></i></b></h1>
			<tr>
				<td>
					<span><br><font face="Georgia">Selezionare un file di testo (.txt) o uno zip (contenente più file .txt)</font></span><br><br><br><br>
					
			<div class="input-group">
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;
                <label class="input-group-btn">
                    <span class="btn btn-primary btn-lg">
                        <input type="file" id="testIn" multiple="">
                    </span>
                </label>
            </div>
            
			</td>
			</tr>
			<tr>
				<td>
					<br>
					<div id="divCaricamento" style="height:150px;display:none;">
						<span id="NumEl">
							
						</span>
						<div id="progressBarContainer" style="width:400px; border:1px solid #2a2a2a; height:40px; margin:auto;">
							<div id="progressBar" style="width:0%; background-color:#35d435;height: 100%;">
								
							</div>
						</div>
					
					</div>
					
					<div id="spazio" style="height:150px;" >
						
					</div>
					<br><br>
				</td>
			</tr>
			<tr>
				<td>
					<span><font face="Georgia">Oppure scrivi un testo da analizzare (min 25 parole)</font></span><br>
					<textarea id="TextAreaText" style="width:700px;height:300px;"></textarea><br>
					<br><br>
					<button id="SendTextArea" class="btn btn-primary btn-lg">Analizza</button>
				</td>	
			</tr>
		</table>
	
	</body>

</html>