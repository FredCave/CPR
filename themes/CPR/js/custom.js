$( document ).ready(function() {

	var winH = $(window).height();

	// Check if browser supports CSS VH units

	function vhTest() {
		if( !Modernizr.cssvhunit ) {
			$(".section").css("height", winH);
		}
	}

	// Scrollify init (called only on front page)

	if ( $("#front_collections").length ) {
		console.log("innit");
		$.scrollify({
			section : "section",
			sectionName : "section-name",
			easing: "easeOutExpo",
			scrollSpeed: 1100,
			offset : 0,
			scrollbars: true
		});
	}

	// WINDOW EVENTS

	$(window).on("load", function(){
		vhTest();	
	}).on("resize", function(){
		vhTest();
	}).on("scroll", function(){

	});
    
});