<?php
header("Access-Control-Allow-Origin: *");
date_default_timezone_set("Europe/Berlin");
header("Content-Type: text/event-stream\n\n");
ob_end_flush();

 /******************** Einstellungen ********************/
$timeout = 5 * 60; // 5 Minuten timeout
$starttime = date("U");
$mysql_connect = mysqli_connect('localhost', 'website', 'feuerwehr112'); //root passwort: feuerwehr112
if(!$mysql_connect) {
	http_response_code(500);
    exit;
}
mysqli_set_charset($mysql_connect, 'utf8');
mysqli_select_db($mysql_connect, 'feuerwehr');

 /******************** Programm ********************/
// Rety festlegen
echo "retry: 1000\n\n"; //Nach einer Sekunde soll der Browser reconnecten bei Verbindungsverlust


 /******************** Schleife ********************/
$alarmierteID = "";
while (date("U") < $starttime+$timeout) { //Browser zum reconnecten zwingen
  $query = mysqli_query($mysql_connect, "SELECT * FROM einsaetze WHERE `zeit` >= NOW() - INTERVAL 1 HOUR AND `zeit` <= NOW() ORDER BY `zeit` DESC LIMIT 1"); //nicht nach ID sortieren, da auch Einsätze für die Zukunft erstellt werden könnten
  if(mysqli_num_rows($query) == 0 && $alarmierteID != "" && mysqli_ping($mysql_connect, $mysql_connect)){ //Wenn kein Einsatz mehr in Datenbank und vorher gemeldet und Server noch online, beende Einsatz (Einsatz wurde gelöscht)
      echo "event: EinsatzEnde\n";
      echo "data: {letzterEinsatz: ".$alarmierteID."}";
      echo "\n\n";
      $alarmierteID = "";
      aktuelleEinsaetze();
  }else if (mysqli_num_rows($query) == 1){
        $einsatz = mysqli_fetch_array($query);
        if($einsatz['id'] != $alarmierteID) { //wenn noch nicht angezeigt, und nicht in der Zukunft (!!! Nur wenn man manuell einen Einsatz erstellt... sollte dadruch ein aktueller Einsatz nicht angezeigt werden, sofort entfernen!)
            echo "event: Einsatz\n";
            $einsatz['zeit'] = strtotime($einsatz['zeit']);
            $data = json_encode($einsatz);
            echo "data: ".$data;
            echo "\n\n";
            $alarmierteID = $einsatz['id'];
            aktuelleEinsaetze();
        }
  }

  flush();
  sleep(1);
}

function aktuelleEinsaetze () { // Anzahl gleichzeitig laufender Einsätze
	global $mysql_connect;
    $num = mysqli_fetch_array(mysqli_query($mysql_connect, "SELECT count(id) as 'Anzahl' FROM einsaetze WHERE `zeit` >= NOW() - INTERVAL 1 HOUR AND `zeit` <= NOW()"))["Anzahl"];
    echo "event: aktuelleEinsaetze\n";
    echo "data: ".$num;
    echo "\n\n";
}
?>
