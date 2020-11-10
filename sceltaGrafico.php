<?php
	$txt="";
	if(isset($_POST["data"]))
	{
		$txt=$_POST["data"];
	}
	else
		header("location:index.php");
	
?>

<html>
	<head>
    	<title>Scegli grafico</title>
		<script src="js/jquery.min.js"></script>

        <script>
		
			var JsonTxt='<?php  echo $txt;?>';
			
			function InviaArrayPaginaSuccessiva(tipoGrafico)
			{
				
				var form = document.createElement("form");
				form.method = 'post';
				form.action = "grafici.php";
				
				var input = document.createElement('input');
				input.type = "textarea";
				input.name = "data";
				input.value = JsonTxt;
				form.appendChild(input);
				
				var input2 = document.createElement('input');
				input2.name = "grafico";
				input2.value = tipoGrafico;
				form.appendChild(input2);
				
				
				$(form).css("display","none");
				
				$("body").append(form);
				
				form.submit();
				
			}
			
			$(document).ready(function(){
				$("#bottoneVisualizza").click(function(){
					InviaArrayPaginaSuccessiva($("#grafico").val());
				});
				
			});
			
			
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
			body {
			    
                background: url('../Resources/Sfondo.png') no-repeat;
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

		</style>
		
	</head>

	<body>

		<table style="margin:auto;height: 100%;	">
			<tr>
				<td style="text-align: center;"><font face=Gerogia size="5"><b>

					Scegli il tipo di grafico:
				</font></b>	
					<select id="grafico">
					
						<option value="g3">Visualizzazione Di Comparazione</option>		
						<option value="g4">Visualizzazione Contenutistica</option>	
						
					</select>
					
					<br>
					<br>
					
					<button id="bottoneVisualizza" class="btn btn-primary btn-lg">Visualizza</button>
					<br><br><br><br><br><br><br><br>
					<a href="http://stage2017.000webhostapp.com/index.php">
					    <img src="./Resources/Home.png"/></a>
				</td>

			</tr>
		
		</table>
			
    </body>
</html>