<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Unbenanntes Dokument</title>
</head>

<body>






<?php

include("includes_classes/verbindung_mysqli.php");

exec('C:\\Programme\PostgreSQL\8.3\bin\shp2pgsql.exe D:\temp\stadtraeume stadtraeume > stadtraeume.sql');
//exec('C:\\Programme\PostgreSQL\8.3\bin\psql.exe -d dd_bezirke -f PostGIS_SQL.sql');



?>



</body>
</html>
