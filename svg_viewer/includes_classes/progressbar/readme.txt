PHP Fortschrittsbalken v1.2

##############################
## Benutzung
##########

Die Datei progressbar.class.php in den PHP-Code per include einbinden:
<?php
include('progressbar.class.php');
?>

Um einen Fortschrittsbalken zu erstellen, eine Instanz der Klasse "progressbar" erstellen, z.B:
<?php
$myprogressbar = new progressbar(0, 100, 200, 20);
?>

Es können bis zu 8 Parameter übergeben werden:
progressbar( $value, $steps, $width, $height, $color, $bgcolor, $inner_styleclass, $outer_styleclass)
$value = Anfangswert des Fortschritts (Standard: 0)
$steps = Anzahl der Schritte, mit denen der Fortschritt angegeben werden kann (Standard: 100)
$width/$height = Höhe und Breite des Balkens (Standard: 200/20)
$color/$bgcolor = Farbe und Hintergrundfarbe des Balkens (Standard: green/white)
$inner_styleclass/$outer_styleclass = Die CSS-Klasse, die der innere Balken und der Hintergrund haben soll (Standard: keine)

Ab Version 1.2 kann mit der Funktion set_show_digits(true/false) die numerische Fortschrittsanzeige ein bzw. ausgeschaltet werden (standard ist ein).

An die Stelle, wo der Fortschrittsbalken im HTML-Code erscheinen soll, einfach
<? $myprogressbar->print_code() ?> schreiben.

Jetzt kann mit der step-Funktion der Fortschritt verändert werden:
<? $myprogressbar->step(); ?>
Optional kann ein Parameter angegeben werden, der den Fortschritt um mehrere Schritte erhöht, also z.B:
<? $myprogressbar->step(5); ?>

Desweiteren stehen die folgenden Funktionen zur Verfügung:
reset() setzt den Fortschritt auf null zurück
complete() setzt den Fortschritt auf 100%


##############################
## Copyright
##########

(c) 2008 by Fabian Schlieper
fabian@fabi.me
http://fabi.me/
