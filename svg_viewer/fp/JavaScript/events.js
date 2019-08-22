/*********Veranstaltungen*****/
// Abrufen des Json der geparsten Veranstaltungen von Monitor/Fp-API und speichern in lokaler Datei auf Server
$(function(){
	//heutiges Datum erfassen
	var today = new Date();
	var dd = today.getDate();
	console.log(dd);

	//Item anlegen, dass Nutzer schonmal da war, vergleichen mit Tag letzten Updates
	var check = localStorage.getItem("zugriff");
	console.log('last check on' + check);
	if (check !=  dd ) {
		$(function(){
			//Wenn Update Tag ist: Abrufen der neuen Veranstaltungen von API
			console.log('Heute Update der Veranstaltungen!');
			localStorage.setItem('zugriff', dd);
			$.get("http://monitor.ioer.de:5000/fp/",function(obj){
				//Senden an php zum Speichern der neuen JSON auf Server	
				var data = JSON.stringify(obj);		
				$.ajax({
				    type: "POST",
					url: './update_events.php',
					 data:  {myData:data},				
				});
			});
		});
	}
	else{
		console.log("Heute kein Update.");
	}
});

//Abrufen der Veranstaltungen aus lokalem json (für kurze Ladezeiten), parsen, ausgabe in div mit id=doc-body (veranstaltungen.php)
$(function(){
	$content = $("#doc-body");
	$start1 = $("#event1");
	$start2 = $("#event2");
	$start3 = $("#event3");
	//Aufruf und parsen der Event-JSON
		$.get("./data/events.json",function(data2){
		$content.empty();  	$start1.empty();  	$start2.empty(); 	$start3.empty(); 
		$.each(data2,function(key,value){
			$.each(value,function(_key,_value){
				$.each(_value.events,function(k,v){
					//Anfügen der jeweiligen Veranstaltung in div #doc-body (in veranstaltungen.php)
					let name = v.name;			
					let url = v.url.replace("veranstaltungen//", "veranstaltungen/"); // eigentlich let url = v.url; hier nun replace, da Fehler in Parser immer // statt / in URL beim bmu ausgibt
					let date = v.date;			
					let div = "<p><a target='_blank' href='" +  url + "'><h4>" + name + "</h4></a></p><p class='last-p'><span class='bold'>Datum: </span> <span>" + date + "</span>	</p>";
					$content.append(div);			
				});
			});	
		});
		
		//Anfügen der ersten 3 Events auf Startseite
			let name1 = data2[0].arl.events[0].name;
				let url1 =  data2[0].arl.events[0].url;
				let date1 = data2[0].arl.events[0].date;
				let div1 = "<p class='small'>" + date1 + "</p><a target='_blank' href='" + url1 + "'>" + name1 + "</a>";
				$start1.append(div1);
			
				let name2 = data2[0].arl.events[1].name;
				let url2 = data2[0].arl.events[1].url;
				let date2 = data2[0].arl.events[1].date;
				let div2 =  "<p class='small'>" + date2 + "</p><a target='_blank' href='" + url2 + "'>" + name2 + "</a>";
				$start2.append(div2);
				
				let name3 = data2[0].arl.events[2].name;
				let url3 = data2[0].arl.events[2].url;
				let date3 = data2[0].arl.events[2].date;
				let div3 =  "<p class='small'>" + date3 + "</p><a target='_blank' href='" + url3 + "'>" + name3 + "</a>";
				$start3.append(div3);		
	});
});