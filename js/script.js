/****************** Hilfsfunktionen ***************** */

function fuehrendeNull (num) {
	if (num < 10) {
		return "0" + num;
	}else{
		return num;
	}
}


function uhr () {
	var date = new Date();
	hour = fuehrendeNull(date.getHours());
	minute = fuehrendeNull(date.getMinutes());
	second = fuehrendeNull(date.getSeconds());
	$(".stunde").text(hour);
	$(".minute").text(minute);
	$(".sekunde").text(second);
}

function keineDatenAusrichten () { //Vertikale Ausrichtung der Uhr
	marginTop = ($(window).height() - $("#keineDaten").height()) / 2 - $("header").height()
	$("#keineDaten").css({'margin-top': marginTop});
}

function animateFeuerwehrLogo(klein) {
	if(!klein) { //Feuerwehrlogo wieder groß anzeigen
		$("#Feuerwehrlogo").css({
			right: "auto",
			top: "auto",
			zoom: 1,
			"-moz-transform": "scale(1)"
		});
	}else{
		$("#Feuerwehrlogo").css("-moz-transform", "scale(1)"); //zurücksetzen, damit gleich richtig gerechnet werden kann
		$("#Feuerwehrlogo").css({
			right: "5px",
			top: "0px",
			zoom: $("header").height()/$("#Feuerwehrlogo").height(),
			"-moz-transform": "scale("+($("header").height()/$("#Feuerwehrlogo").height())+")"
		});
	}
}

function timestampZuText(timestamp) { // Timestamp (in Sekunden) zu lesbarer Zeit machen (z.B. "01.01.2016 - 12:30")
tmpTime = new Date (timestamp*1000);
return fuehrendeNull(tmpTime.getDate()) + "." + fuehrendeNull(tmpTime.getMonth()+1) + "." + tmpTime.getFullYear() + " " +fuehrendeNull(tmpTime.getHours()) + ":" + fuehrendeNull(tmpTime.getMinutes()) + ":" + fuehrendeNull(tmpTime.getSeconds());
}

function istEinsatz () { // Gibt Wahrheitswert, ob ein Einsatz angezeigt wird
	return ($("#einsatz").attr('data-einsatz') == "1" ? true : false);
}


/****************** Hauptprogramm ***************** */
uhr();
var Uhrinterval = setInterval(uhr, 1000); // Uhr aktivieren

var map1; //Karte definieren
$(document).ready(function () {
	map1 = L.map('map1', {zoomControl: false}); //Karte erstellen
	map1.scrollWheelZoom.disable(); //Scrollen deaktivieren

	keineDatenAusrichten(); //Uhr ausrichten
	var ausrichtenTiemout;
	$(window).resize(function (){
		clearTimeout(ausrichtenTiemout);
		ausrichtenTiemout = setTimeout(function () {
			if(!istEinsatz()){
				keineDatenAusrichten(); //Uhr neu Ausrichten
			}else{
				resizeMap(); //im Einsatz Karte neu ausrichten
			}
		}, 100); //doppelungen beim Ausrichten verhindern
	});
});

function resizeMap () {
	$("#map1").css('height', ($(window).height() - $("div[data-height='max']").position().top)); //Karten-Container an das Fenster anpassen
	map1.invalidateSize();
}

var updateSeitAlarmierung; //timer (der Zeit seit der Einsatz gemeldet wurde)
var TimeoutEinsatz; //timeout zum ausblenden
var mapRequest; //AJAX request (google/openstreetmap)
var marker = L.marker([0, 0]); //set default marker
var audio = new Audio ("audio/gong.mp3"); //load sound

function einsatz (vorfall, leistung, ort, zeit, ausrueckordnung) { //Ton fehlt
	clearInterval(Uhrinterval); // Uhr des Startbildschirmes deaktivieren
	clearTimeout(TimeoutEinsatz); // Timeout eines vorherigen Einsatzes deaktivieren
	clearInterval(updateSeitAlarmierung); // Timer eines vorherigen Einsatzes beenden

	if(vorfall == null)vorfall = "-- Kein Vorfall angegeben --";
	if(leistung == null)leistung = "-- Keine Leistung angegeben --";
	if(ort == null)ort = "-- Kein Ort angegeben --";
	if(zeit == null)zeit = Date.now()/1000 - (60*60);
	if(ausrueckordnung == null)ausrueckordnung = "-- Keine Ausrückordnung angegeben --";

	if((Date.now() / 1000)-300 < zeit) audio.play(); // Spiele den Sound ab, wenn der Einsatz nicht älter als 5 Minuten ist
	if(typeof(mapRequest) !== "undefined")mapRequest.abort(); //falls mehrere Einsatze schnell aufeinander folgen, brich den AJAX request ab
	$("#einsatz").attr({'data-einsatz': 1}); //Einsatz-container bereit machen
	animateFeuerwehrLogo(true);

	// Einsatzdaten ausfüllen
	$("#vorfall").text(vorfall);
	$("#leistung").text(leistung);
	$("#ort").text(ort);
	$("#ausrueckordnung").text(ausrueckordnung);
	$("#zeit").text(timestampZuText(zeit));

	//Maps
	mapRequest = $.ajax({
		method: "GET",
		// url: "https://maps.googleapis.com/maps/api/geocode/json?address="+encodeURI(ort)+"&key=AIzaSyA3owCuLfV9Bg8FFEibbpJ3FabzQy2mnEI", //openstreetmap url: "http://nominatim.openstreetmap.org/search?format=json&q="+ort, //data = data[0];Lng = data.lon;Lat = data.lat;
		url: "http://nominatim.openstreetmap.org/search?format=json&q="+ort,
		success: function (data) {
			//if(data.status=="ZERO_RESULTS"){$("#maps").hide();} //Wenn Standort nicht gefunden
			//else{
				$("#maps").show();
				data = data[0];Lng = data.lon;Lat = data.lat;
				//Lng = data["results"][0]["geometry"]["location"]["lng"];
				//Lat = data["results"][0]["geometry"]["location"]["lat"];
				map1.setView([Lat, Lng], 18); // Zur Position springen
				L.tileLayer("https://api.mapbox.com/styles/v1/mapbox/streets-v9/tiles/256/{z}/{x}/{y}?access_token=pk.eyJ1IjoibWljaGVsYmxrIiwiYSI6ImNpcmdmaHpzYzAwMW5pN21oaXFodmZpc3UifQ.EbQK1NWVRFK6OIehvPvVNQ",{id:"mapbox.streets-v9"}).addTo(map1);
				map1.removeLayer(marker); //Alte Marker vorheriger Einsätze entfernen
				marker = L.marker([Lat, Lng]).addTo(map1); //Marker setzen
			//}
		},
		error: function () {
			console.log("Geocode abfrage wurde abgebrochen oder konnte nicht geladen werden");
			$("#maps").hide();
		}
	});

	// Einsatz einblenden
	$("#keineDaten").fadeOut(400, function () { //erst Uhr ausblenden
		$("#einsatz").fadeIn(400, function () { //dann Einsatz einblenden
			resizeMap(); //Karte zum aktualisieren der Größe zwingen
		});
	});

	// Timer starten
	$("#zeitSeitAlarmierung").removeClass('rot'); // Rote Markierung des Timers entfernen
	updateSeitAlarmierung = setInterval(function () {
		SeitAlarmierunginS = (Date.now() / 1000) - zeit; // Zeit seit Alarmierung ausrechnen
		minutes = Math.floor(SeitAlarmierunginS / 60); // Minuten ausrechen
		seconds = Math.floor(SeitAlarmierunginS - (minutes * 60)); // Sekunden ausrechnen

		$("#zeitSeitAlarmierung").text(fuehrendeNull(minutes)+":"+fuehrendeNull(seconds)); // Zeit ausgeben
		if (minutes >= 5 && !$("#zeitSeitAlarmierung").is(".rot")) { // Nach 5 Minuten rot darstellen
			$("#zeitSeitAlarmierung").addClass('rot');
		}
	}, 1000);

	// Einsatz Timeout einstellen (mindest Timeout 30 Sekunden, ansonsten eine Stunde nach Einsatzbeginn)
	TimeoutEinsatz = setTimeout(function (){
		endeEinsatz();
	}, ((3600+zeit)*1000-Date.now() > 30000 ? (3600+zeit)*1000-Date.now():30000)); //Eine Stunde Einsatz anzeigen (mindestens jedoch 30 Sekunden)
}

function endeEinsatz () {
	clearInterval(updateSeitAlarmierung); // Einsatz Timeout deaktivieren
	$("#weitereEinsaetze").attr("data-anzahl", "0"); // "weitere Einsätze" ausblenden
	$("#einsatz").fadeOut(500, function () { // Einsatz ausblenden
		$("#keineDaten").fadeIn(500); // Uhr einbelnden
		Uhrinterval = setInterval(uhr, 1000); // Uhr starten
		keineDatenAusrichten();
	});
	animateFeuerwehrLogo(false);
	$("#einsatz").attr({'data-einsatz': 0}); // Einsatz-container wieder deaktivieren
	$("#zeitSeitAlarmierung").removeClass('rot'); // Rote Markierung der Zeit entfernen
}
