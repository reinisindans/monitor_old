// JavaScript Default Document

$(document).ready(function() {

	// mmenu - Sliding submenu
	$('#navbar').mmenu({
		// options
		extensions: [
			'border-full',
			'shadow-page',
			'shadow-panels',
			"theme-dark"
		]
		}, {
		// configuration
		clone: true,
		classNames: {
			divider: 'divider'
		}
	});

	// Headroom.js - Header slides out of view when scrolling down and slides back in when scrolling up
	$('nav').headroom({
		offset: 0,
		tolerance: 5
	});

	//Ridiculously simple accordion
	$('.accordion').find('.accordion-toggle').click(function(){

		//Expand or collapse this panel
		$(this).toggleClass('active');
		$(this).next().slideToggle('fast');
		
		//Hide the other panels
		$(".accordion-toggle").not($(this)).removeClass('active');
		$(".accordion-content").not($(this).next()).slideUp('fast');

	});

	//Opens per default an accordion element, which is set by #hash
	if ($(location).attr('hash') && $('.accordion-content'+$(location).attr('hash')).length) {

		element = $('.accordion-content'+$(location).attr('hash'));

		//Hide all panels
		$('.accordion-toggle').removeClass('active');
		$('.accordion-content').slideUp('fast');

		//Expand the #hash panel
		element.prev().addClass('active');
		element.slideToggle('fast');

		//Scroll page to given anchor
		$(document.body).animate({'scrollTop': element.offset().top}, 800);
	}

	//Sets the label of the language menu button to the active language
	if($(".language-menu")) {

		activeLang = $(".language-menu .dropdown-menu li.active a");
		dropdownBtn = $(".language-menu .dropdown-toggle");

		if($(".language-menu").hasClass("dropdown")){
			dropdownBtn.html(activeLang.text());
		} else {
			dropdownBtn.html(activeLang.text());
		}
		
		dropdownBtn.addClass(activeLang.attr('class'));
	}

/*
	setTimeout(function(){
		stickyFooter();
	}, 100);
*/

});

/*
$(window).resize(function() {
	stickyFooter()
});
*/

//Sticky Bottom-Footer
function stickyFooter() {
	var bodyHeight = $("body").height();
	var vwptHeight = $(window).height();
	if (bodyHeight < vwptHeight) {
		$("footer").css("position","absolute").css("bottom",0).css("width","100%");
	} else {
		$("footer").css("position","relative").css("bottom", "inherit").css("width","auto");
	}
}