<?php
/*
*	PHP Fortschrittsbalken v1.2
*	(c) 2008 by Fabian Schlieper
*	fabian@fabi.me
*	http://fabi.me/
*/

@ini_set('max_execution_time', '120');
include('progressbar.class.php');

$myprogressbar1 = new progressbar(0, 100, 200, 20); // erste Bar initialisieren
$myprogressbar2 = new progressbar(0, 500, 300, 20, '#00f'); // und die zweite
$myprogressbar2->set_show_digits(false); // keine numerische Anzeige (### %)
?>
<html>
<head>
<title>Fortschrittsbalken Beispiel</title>
</head>
<body>
<p>Erster Fortschrittbalken:<br /><?php $myprogressbar1->print_code(); ?></p>
<p>Zweiter Fortschrittbalken:<br /><?php $myprogressbar2->print_code(); ?></p>
<?php
// hier würde der Code, dessen Fortschritt angezeigt werden soll, stehen (muss nach dem <body> und vor dem </body>-Tag sein !)
for($i = 0; $i < 200; $i++)
{
	$myprogressbar1->step();
	$myprogressbar2->step(2);
	usleep(50000);
}
$myprogressbar1->reset();
?>
</body>
</html>