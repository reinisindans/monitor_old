// JavaScript Document



//funktion für Auswahlmenü mit Indikatoren
				function MM_jumpMenu(targ,selObj,restore){ //v3.0
				  eval(targ+".location='"+selObj.options[selObj.selectedIndex].value+"'");
				  if (restore) selObj.selectedIndex=0;
				}


//Funktionen zum Verändern der Kartendarstellung				
				function markieren(AGS,Farbe)
				{
					/* Tabellenzeile <tr> und Symbol formatieren */
					document.getElementById('Marker_' + AGS).style.display = 'none';
					document.getElementById('DeMarker_' + AGS).style.display = 'inline';
					document.getElementById('Zeile_' + AGS).style.border = '2px solid #933';
					
					/* Übergabe an $_SESSION per Aufruf über Hilfsdatei (leeres Bild) */
					uebergabe = '../svg_agsname_anzeigen.php?visible=1&ags=Label_' + AGS;
					document.getElementById('verstecktegrafik').src = uebergabe;
					
					var svgdoc = document.getElementById('svgDOM').getSVGDocument();
					svgdoc.getElementById('vergleich_markierung_' + AGS).setAttributeNS(null,"stroke",Farbe);
					svgdoc.getElementById('vergleich_markierung_' + AGS).setAttributeNS(null,"stroke-width","<?php echo $StrStaerke = 3 * $_SESSION['Dokument']['Strichstaerke']; ?>");
					svgdoc.getElementById('Label_' + AGS).setAttributeNS(null,"display",'inline');

				}
				function demarkieren(AGS)
				{
					/* Tabellenzeile <tr> und Symbol formatieren */
					document.getElementById('Zeile_' + AGS).style.border = '0px solid #933';
					document.getElementById('Marker_' + AGS).style.display = 'inline';
					document.getElementById('DeMarker_' + AGS).style.display = 'none';
					
					/* Übergabe an $_SESSION per Aufruf über Hilfsdatei (leeres Bild) */
					uebergabe = '../svg_agsname_anzeigen.php?visible=0&ags=Label_' + AGS;
					document.getElementById('verstecktegrafik').src = uebergabe;
					
					var svgdoc = document.getElementById('svgDOM').getSVGDocument();
					svgdoc.getElementById('vergleich_markierung_' + AGS).setAttributeNS(null,"stroke","#000000");
					svgdoc.getElementById('vergleich_markierung_' + AGS).setAttributeNS(null,"stroke-width","<?php echo $_SESSION['Dokument']['Strichstaerke']; ?>");
					svgdoc.getElementById('Label_' + AGS).setAttributeNS(null,"display",'none');             

				}
				
				function SVG_geladen()
				{
					SVGready = '1';
					/* SVG vollständig geladen und Info in globaler Variable abgelegt */
				}
				
				function markieren_bei_reload(AGS,Farbe)
				{					
					/* var svgdoc = document.getElementById('svgDOM').getSVGDocument(); */
					if(svgdoc = document.getElementById('svgDOM').getSVGDocument())
					{
						svgdoc.getElementById('vergleich_markierung_' + AGS).setAttributeNS(null,"stroke",Farbe);
						svgdoc.getElementById('vergleich_markierung_' + AGS).setAttributeNS(null,"stroke-width","<?php echo $StrStaerke = 3 * $_SESSION['Dokument']['Strichstaerke']; ?>");
						svgdoc.getElementById('Label_' + AGS).setAttributeNS(null,"display",'inline');
					}
					else
					{
						/* setTimeout akzeptiert keine direkte Variablenübergabe, deswegenfolgendes Konstrukt */
						setTimeout(
						   function(){
							  markieren_bei_reload(AGS,Farbe);
						   },1000
						);
					}					
				}
				
				
//Funktionen zum Ein und Ausblenden des Eingabebereiches

			function toggle5(contentDivImg)
		     {
		            var ele = document.getElementById(contentDivImg);   //ID des DIV, welches ein- und ausgeklappt werden soll
		         
		            if(ele.style.display == "none" ) //  wenn div nicht angezeigt wird vor Klick
		            		 {     
		                    ele.style.display = "block";   //zeige es an bei Funktionsaufruf durch button-Klick
		                    localStorage.statusTab = "block";                
				             }
		            else {
		                    ele.style.display = "none"; //zeige es nach Klick nicht mehr an
		 										localStorage.statusTab = "none";                    
		            		}
		  	 }
			//Prüft beim Fensteraufbau/Neuladen der Seite ob DIV vorher aufgeklappt war, wenn ja klappt es erneut automatisch auf
		  	 window.onload = function()
		  	  {
					 if (localStorage.statusTab == "block")
					   {
					     $('#contentDivImg').show();
						 }		
						else
						 {
								$('#contentDivImg').hide();
						 }
					}; 



//Funktion zum Aufruf der Dialogboxen für Wahl ungültiger Differenzen und deren Aufruf
						      						            
		 				 function diff_dialog(){$(function() {
											  	$("#dialog").dialog({
										                title: 'Wichtiger Hinweis zur Bildung von Differenzwerten',
												        resizable: false,
												        modal: true,
												        width:'50%',
										                dialogClass: 'ui-dialog-osx',
										                dialogClass: 'no-close success-dialog',
										                dialogClass: "#wfs_dialog",
										               position:['middle',90],
										                iconPosition: { iconPositon: "top" },
										                open: function() {
										                    $('#menu').hide();
										                    $('.arrow').hide();
										                    $('.subMenu').hide();
										                    $( ".toggle_arrow").show();
										                    $( ".toggle_arrow" ).html('<i class="fa fa-chevron-down fa-2x"></i>');
										                    $(this).closest(".ui-dialog")
										                        .find(".ui-dialog-titlebar-close")
										                        .html("<span class='ui-button-icon-primary ui-icon ui-icon-closethick' style='margin:-4px -3px -4px -2px;' ></span>");
										                   //Dialog schließen, wenn Klick auf graue Fläche drumherum  
										                  jQuery('.ui-widget-overlay').on('click', function() {
																			jQuery('#dialog').dialog('close')});
										                }
						            			}).css("font-size", "14px");
														});
													}
													
													
		
//Funktion zum Aufruf der Dialogbox bei Download CSV
						      						            
		 				 function export_dialog(){$(function() {
											  	$("#export").dialog({
										                title: 'Download der Tabellendaten',
												        resizable: false,
												        modal: true,
												        width:'25%',
										                dialogClass: 'ui-dialog-osx',
										                dialogClass: 'no-close success-dialog',
										                dialogClass: "#wfs_dialog",
										               position:['middle',30],
										                iconPosition: { iconPositon: "top" },
										                open: function() {
										                    $('#menu').hide();
										                    $('.arrow').hide();
										                    $('.subMenu').hide();
										                    $( ".toggle_arrow").show();
										                    $( ".toggle_arrow" ).html('<i class="fa fa-chevron-down fa-2x"></i>');
										                    $(this).closest(".ui-dialog")
										                        .find(".ui-dialog-titlebar-close")
										                        .html("<span  class='ui-button-icon-primary ui-icon ui-icon-closethick' style='margin:-4px -3px -4px -2px;'  ></span>");
										                }
						            			}).css("font-size", "14px");
														});
													}		
													
													
		;				