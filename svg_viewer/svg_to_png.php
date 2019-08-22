<?php session_start(); 
/* 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Unbenanntes Dokument</title>
</head>

<body>
<?php 

 */
ob_start(); 

//include('svg_svg.php');
// $inhalte = ob_get_contents();

ob_end_clean();

$inhalte = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ev="http://www.w3.org/2001/xml-events" version="1.1" baseProfile="full" viewBox="0 0 300 300" width="300px" height="300px">
<text x="20" y="20" style="font-size: 6px; font-family: Arial;">Der angeforderte Indikator: </text>
<text x="20" y="30" style="font-size: 7px; font-family: Arial;"/>
<text x="20" y="40" style="font-size: 6px; font-family: Arial;">ist für diesen Zeitschnitt leider noch nicht verfügbar.</text>
</svg>';


//echo '<img>'.rasterize($inhalte).'</img>';
echo rasterize($inhalte);

/* 
function rasterize($svg) {
    @header("Content-type: image/png");
    $descriptorspec = array(0 => array("pipe", "r"), 1 => array("pipe", "w"));
    $convert = proc_open("c:\programme\imagemagick\convert.exe -density 144 svg:- png:-", $descriptorspec, $pipes);
    fwrite($pipes[0], $svg);
    fclose($pipes[0]);
    while(!feof($pipes[1])) {
        $png .= fread($pipes[1], 1024);
    }
    fclose($pipes[1]);
    proc_close($convert);
    return(stripslashes($png));
} */

function rasterize($svg) {
    @header("Content-type: image/jpeg");
    $descriptorspec = array(0 => array("pipe", "r"), 1 => array("pipe", "w"));
	//$convert = proc_open("c:\programme\imagemagick\convert.exe -quality 100 svg:- jpg:-", $descriptorspec, $pipes);
    $convert = proc_open("/usr/local/ImageMagick-6.4.5/utilities/convert -quality 100 svg:- jpg:-", $descriptorspec, $pipes);
    fwrite($pipes[0], $svg);
    fclose($pipes[0]);
    while(!feof($pipes[1])) {
        $jpg .= fread($pipes[1], 1024);
    }
    fclose($pipes[1]);
    proc_close($convert);
    return(stripslashes($jpg));
}


/* 
?>




</body>
</html>
<?php  */?>