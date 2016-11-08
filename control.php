<?php
date_default_timezone_set("Europe/Berlin");
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_GET["act"]) && $_GET['act'] == "neuerEinsatz") {
	$zeit = $_POST["zeit"];
	$vorfall = $_POST["vorfall"];
	$leistung = $_POST["leistung"];
	$ausrueckordnung = $_POST["ausrueckordnung"];
	$ort = $_POST["ort"];

	$mysql_connect = mysqli_connect('localhost', 'website', 'feuerwehr112');
	mysqli_set_charset($mysql_connect, 'utf8');
	mysqli_select_db($mysql_connect, 'feuerwehr');

	mysqli_query($mysql_connect, "INSERT INTO `einsaetze`(`zeit`, `vorfall`, `leistung`, `ort`, `ausrueckordnung`) VALUES ('$zeit', '$vorfall', '$leistung', '$ort', '$ausrueckordnung')");
	if(mysqli_errno($mysql_connect)){http_response_code(406);}
	exit;
}else
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["act"]) && $_GET['act'] == "zeigeEinsaetze"){
	$mysql_connect = mysqli_connect('localhost', 'website', 'feuerwehr112');
	mysqli_set_charset($mysql_connect, 'utf8');
	mysqli_select_db($mysql_connect, 'feuerwehr');

	$query = mysqli_query($mysql_connect, "SELECT * FROM `einsaetze` ORDER BY `id` DESC LIMIT 50");
	$ergebnis = [];
	while($einsatz = mysqli_fetch_array($query)) {
		$einsatz['zeit'] = strtotime($einsatz['zeit']);
		$ergebnis[] = $einsatz;
	}
	echo json_encode($ergebnis);
	exit;
}else
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_GET["act"]) && $_GET['act'] == "loescheEinsatz"){
	$mysql_connect = mysqli_connect('localhost', 'website', 'feuerwehr112');
	mysqli_set_charset($mysql_connect, 'utf8');
	mysqli_select_db($mysql_connect, 'feuerwehr');

	if (isset($_GET['id']) && isset($_POST['id']) && $_POST["id"] == $_GET["id"]) {
		mysqli_query($mysql_connect, "DELETE FROM `einsaetze` WHERE `id` = '".$_POST['id']."'");
		if(!mysqli_errno($mysql_connect)){http_response_code(204);}else{http_response_code(400);}
	}else{
		http_response_code(428);
	}
	exit;
}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Entwicklereinstellungen - Feuerwehr Einsatzmonitor Steinbach (Taunus)</title>
		<meta charset="UTF-8">
		<meta name="author" content="Michel Blank" />
		<meta name="robots" content="noindex,nofollow" />
		<link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
		<link rel='stylesheet' type='text/css' href="css/roboto.css" />
		<link rel='stylesheet' type='text/css' href="css/main.css" />
		<script src="js/jQuery.js"></script>
		<style>
			html, body {
				overflow-y: auto;
			}
			.box {
				border: 1px solid;
				width: calc(50% - 10px);
				float: left;
				margin-left: 3px;
			}
			.box-header {
				line-height: 1.5em;
				background: rgba(255,255,255,1);
				color: #28292B;
			}
			.box-content {
				min-height: 50px;
			}
			#neuerEinsatz input {
				width: calc(100% - 28px);
				line-height: 1.5em;
				padding: 3px;
				margin: 3px 10px;
				border: 1px solid #FFFFFF;
			}
			#neuerEinsatz-submitStatus div {
				display: none;
				margin: 2px 10px;
				border-radius: 5px;
				padding: 1em;
			}
			#einsatzdatenbank table td[data-name="vorfall"], #einsatzdatenbank table td[data-name="leistung"]  {
				word-break: break-all;
			}
			#einsatzdatenbank table {
				border: 0px;
				width: 100%;
				border-collapse: collapse;
			}
			#einsatzdatenbank table tr {
				border-bottom: 1px solid white;
			}
			li {
				list-style-type: none;
				text-indent: -1em;
			}
			li:before {
				content: "- ";
			}
		</style>
		<script>
			function fuehrendeNull (num) {
				if (num < 10) {
					return "0" + num;
				}else{
					return num;
				}
			}
			$(document).ready(function () {
				$("#neuerEinsatz").submit(function (e){
					e.preventDefault();
					$("#neuerEinsatz div").hide();
					$.ajax({
						url: "?act=neuerEinsatz",
						method: "POST",
						data: $("#neuerEinsatz").serialize(),
						success: function () {
							$("#neuerEinsatz-submitStatus div[data-success='true']").show();
							$("#neuerEinsatz")[0].reset();
							date = new Date();
							$("#neuerEinsatz input[name='zeit']").val(date.getFullYear()+"-"+fuehrendeNull(date.getMonth()+1)+"-"+fuehrendeNull(date.getDate())+"T"+fuehrendeNull(date.getHours())+":"+fuehrendeNull(date.getMinutes())+":"+fuehrendeNull(date.getSeconds()));
							aktualisiereEinsaetze();
						},
						error: function () {
							$("#neuerEinsatz-submitStatus div[data-success='false']").show();
						}
					}); // Ende AJAX
				}); //Ende Submit

				$("#einsatzdatenbank").on("contextmenu", "tr:not('.persistent')", function (e) {
					e.stopPropagation();
					e.preventDefault();
					id = $(this).children("[data-name='id']").text();
					tr = $(this);
					if(!id || id == "")return;
					if (confirm("Wollen Sie diesen Einsatz (ID "+id+") wirklich löschen?")){
						if(confirm("Sicher?")){
							console.log("Einsatz ("+id+") wird auf Nutzeranweisung gelöscht!");
							$.ajax({
								url: '?act=loescheEinsatz&id='+id,
								method: "POST",
								data: {"id": id},
								success: function (data) {
									tr.html("<td colspan='5' style='text-align: center;'>Einsatz (ID "+id+") gelöscht.</td>");
								},
								error: function () {
									alert("Einsatz konnte nicht gelöscht werden :/");
								}
							});
						}
					}
				});
				aktualisiereEinsaetze();
			});
			function aktualisiereEinsaetze () {
				$.ajax({
					url: '?act=zeigeEinsaetze',
					method: "GET",
					success: function (data) {
						$("#einsatzdatenbank table tr:not('.persistent')").remove();
						data = $.parseJSON(data);
						$.each(data, function (index, value){
							zeit = new Date (value['zeit']*1000);
							zeittext = fuehrendeNull(zeit.getDate()) + "." + fuehrendeNull(zeit.getMonth()+1) + "." + zeit.getFullYear() + " " +fuehrendeNull(zeit.getHours()) + ":" + fuehrendeNull(zeit.getMinutes()) + ":" + fuehrendeNull(zeit.getSeconds());
							$("#einsatzdatenbank table").append("<tr><td data-name='id'>"+value['id']+"</td><td data-name='zeit'>"+zeittext+"</td><td data-name='vorfall'>"+value['vorfall']+"</td><td data-name='leistung'>"+value['leistung']+"</td><td data-name='ort'>"+value['ort']+"</td></tr>");
						});
					},
					error: function () {
						alert("Einsaetze konnten nicht aktualisiert werden");
					}
				});
			}
			if(typeof(EventSource) !== "undefined") {
				var sseEinsatz = new EventSource("sse.php");
				sseEinsatz.addEventListener("Einsatz", function (e) {
					aktualisiereEinsaetze();

				}, false);
			}
		</script>
	</head>
	<body>
		<header>
			<div id="Softwarelogo">Feuerwehr Einsatzmonitor</div>
		</header>
		<div id="Feuerwehrlogo">
			<div id="Stadtlogo"></div>
			<div id="Feuerwehrname">
				<span style="font-size: 0.9em;">Freiw. Feuerwehr</span><br/>
				<span>Steinbach/Ts.</span>
			</div>
		</div>
		<main style="margin: 80px 10px 10px 10px;">
			<div class="box">
				<div class="box-header">Neuen Einsatz erstellen</div>
				<div class="box-content">
					<div id="neuerEinsatz-submitStatus">
						<div data-success="true" style="color: #3c763d; background-color: #dff0d8;">Daten erfolgreich eingetragen!</div>
						<div data-success="false" style="color: #a94442; background-color: #f2dede;">Daten konnten nicht eingetragen werden!</div>
					</div>
					<form action="#" method="POST" id="neuerEinsatz">
						<input type="datetime-local" name="zeit" placeholder="15.05.2016, 12:00" value="<?php echo date('Y-m-d')."T".date('H:i:s'); ?>" step="1" required />
						<input type="text" name="vorfall" placeholder="Vorfall" required />
						<input type="text" name="leistung" placeholder="Leistung" required />
						<input type="text" name="ausrueckordnung" placeholder="Ausrückordnung" />
						<input type="text" name="ort" placeholder="Adresse" required />
						<input type="submit" value="Absenden" style="background: #FFFFFF; color: #28292B;" />
					</form>

					<div class="box-header">Hinweise</div>
					<ul>
						<li>Ein Einsatz wird angezeigt, bis dieser 60 Minuten alt ist</li>
						<li>Einsätze, die älter als 5 Minuten sind, werden nicht akustisch alarmiert</li>
						<li>Einsätze können hier für die Zukunft erstellt werden, um zu einer bestimmten Uhrzeit einen Einsatz zu simulieren</li>
						<li>Es wird jeweils der aktuellste Einsatz auf dem Einsatzmonitor angezeigt</li>
						<li>Werden mehrere Einsätze innerhalb einer Stunde gemeldet, wird ein Hinweis angezeigt</li>
						<li>Einsätze können hier mit einem Rechtsklick auf einen Einsatz gelöscht werden</li>
					</ul>
				</div>

			</div>
			<div class="box">
				<div class="box-header">Einsatzdatenbank</div>
				<div class="box-content" id="einsatzdatenbank">
					<table>
						<tr class="persistent"><th>ID</th><th>Zeit</th><th>Vorfall</th><th>Leistung</th><th>Ort</th></tr>
					</table>
				</div>
			</div>
		</main>
	</body>
</html>
