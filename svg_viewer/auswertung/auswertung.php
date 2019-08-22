<?php
session_start(); // Sitzung starten/ wieder aufnehmen



include("../includes_classes/verbindung_mysqli.php");
$Verbindung->set_charset("utf8");  //um string/text ausgeben zukönnen

//Gesammtzahl aller INDI aufrufe
    $myquery2 = "SELECT SUM(ZAEHLER) FROM v_auswertung";
   $query2 = mysqli_query($Verbindung, $myquery2);
   
   
//Aufrufzahl pro Indi
    $myquery = "SELECT v_auswertung.ID_INDIKATOR, m_indikatoren.INDIKATOR_NAME, SUM(v_auswertung.ZAEHLER) as ZAHL
    FROM v_auswertung, m_indikatoren
    WHERE v_auswertung.ID_INDIKATOR = m_indikatoren.ID_INDIKATOR
    group by v_auswertung.ID_INDIKATOR ORDER BY ZAHL DESC";
   $query = mysqli_query($Verbindung, $myquery);


    if (!$query) {
        echo mysqli_error();
        die;
       
    }
    
     $data = array();
    //Gesammtzahl aller INDI aufrufe
     for ($x = 0; $x < mysqli_num_rows($query2); $x++) {
        $data[] = mysqli_fetch_assoc($query2);
    }
 

    //Aufrufzahl pro Indi
     for ($x = 0; $x < mysqli_num_rows($query); $x++) {
        $data[] = mysqli_fetch_assoc($query);
      
        
    }
    header('Content-Type: application/json'); //Browser auf json hinweisen für übersichtliche Ausgabe
    echo json_encode($data,JSON_UNESCAPED_UNICODE); //Array als Json ausgeben & Umlaute korrekt darstellen    
    
    
     
    
     
    mysqli_close($Verbindung);
   
   
   
   /*    $myquery = "SELECT ID_AUSWERTUNG, ID_INDIKATOR, JAHR, RAUMEBENE, RAUMGLIEDERUNG, ZAEHLER FROM v_auswertung";
*/
?>