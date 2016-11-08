var aktuellerEinsatz = "";
if(typeof(EventSource) !== "undefined") {
	var sseEinsatz = new EventSource("sse.php");

	sseEinsatz.onopen = function () {
		$("#Verbindungsstatus").attr('data-online', true);
	}
	sseEinsatz.addEventListener("Einsatz", function (e) {
		einsatzdaten = $.parseJSON(e.data);
		if (einsatzdaten.id != aktuellerEinsatz) { //wenn Einsatz nicht angezeigt wird
			einsatz(einsatzdaten.vorfall, einsatzdaten.leistung, einsatzdaten.ort, einsatzdaten.zeit, einsatzdaten.ausrueckordnung); //aus script.js (zeigt Einsatz an)
			aktuellerEinsatz = einsatzdaten.id; //Array mit ID des Einsatzes ergänzen, damit dieser nicht doppelt angezeigt wird
		}
	}, false);
	sseEinsatz.addEventListener("aktuelleEinsaetze", function (e) {
		anzahl = e.data;
		if (anzahl != 0)$("#weitereEinsaetze").attr("data-anzahl", anzahl-1);
	});
	sseEinsatz.addEventListener("EinsatzEnde", function (e) {
		if (istEinsatz())endeEinsatz(); //könnte bereits durch den timeout beendet worden sein
	});
	sseEinsatz.onerror = function(e) {
		$("#Verbindungsstatus").attr('data-online', false); //"Keine Verbindung"-Meldung anzeigen
	}
}else{ // wenn EventSource nicht unterstützt wird
	$(document).ready(function () { //Warnung anzeigen
		$("body").append("<div id='noSupport' style='position: fixed; background-color: rgb(255,0,0); left: 0px; bottom: 0px; width: 100%; font-size: 1.2em; line-height: 1.5em; text-align: center;'>Ihr Browser unterst&uuml;tzt 'server-sent events' nicht, welche zwingend erforderlich sind.</div>");
		$("#keineDatenMeldung").html("Browser nicht unterst&uuml;tzt");
	});
}
