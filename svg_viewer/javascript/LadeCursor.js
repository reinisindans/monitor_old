// JavaScript Document

//Lässt Cursor mit Ladeanzeige beim Laden von allen Seitenelementen erscheinen

$(window).on('beforeunload', function(){
   $('*').css("cursor", "progress");
});



