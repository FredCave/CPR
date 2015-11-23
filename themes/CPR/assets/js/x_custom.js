$( document ).ready(function() {

	var winH = $(window).height(),
	winW = $(window).width();

	// Check if browser supports CSS VH units

	function vhTest() {
		if( !Modernizr.cssvhunit ) {
			$(".section").css("height", winH);
		}
	}

	// NAVIGATE LEFT & RIGHT

	

	// $(document).on("click", function(e){
	// 	// check 3 cases here : col_left, centre, col_right
	// 	var dataPos = $("#wrapper").attr("data-position");
	// 	// sort by desired result
	// 	if ( e.pageX < winW * 0.5 && dataPos === "centre" ) {
	// 		// slide left
	// 		//console.log("slide left");
	// 		$("#wrapper").attr("data-position","left");
	// 		$(".col").removeClass("slideright").addClass("slideleft");
	// 	} else if ( e.pageX > winW * 0.5 && dataPos === "centre" ) {
	// 		// slide right
	// 		//console.log("slide right");
	// 		$("#wrapper").attr("data-position","right");
	// 		$(".col").removeClass("slideleft").addClass("slideright");
	// 	} else if ( e.pageX < winW * 0.5 && dataPos === "right" || e.pageX > winW * 0.5 && dataPos === "left" ) {
	// 		// slide centre
	// 		//console.log("slide centre");
	// 		$("#wrapper").attr("data-position","centre");
	// 		$(".col").removeClass("slideleft slideright")
	// 	} else {
	// 		// null
	// 		//console.log("null");
	// 	}
	// });

	function slideLeft () {
		var dataPos = $("#wrapper").attr("data-position");
		if ( dataPos === "centre" ) {
			$("#wrapper").attr("data-position","left");
			$(".col").removeClass("slideright").addClass("slideleft");
		}	
	}

	function slideCentre () {
		var dataPos = $("#wrapper").attr("data-position");
		if ( dataPos !== "centre" ) {
			$("#wrapper").attr("data-position","centre");
			$(".col").removeClass("slideright slideleft");
		}	
	}

	function slideRight () {
		var dataPos = $("#wrapper").attr("data-position");
		if ( dataPos !== "right" ) {
			$("#wrapper").attr("data-position","right");
			$(".col").removeClass("slideleft").addClass("slideright");
		}	
	}

	// MAIN MENU NAVIGATION

		// HOME

	$(".nav_home").on("click", function(e){
		e.preventDefault();
		slideCentre();
	});

		// INFO COLUMN

		// CREATE INFO NAV POINTS

		function infoPoints() {
			$("#info_wrapper section").each( function(){
				var offsetPoint = $(this).offset().top;
				$(this).attr("data-offset", offsetPoint);
			});
		}

	$(".nav_info").on("click", function(e){
		e.preventDefault();
		var dest = $(this).attr("data-info");
		slideLeft();
		// scroll to selected section
		var scrollPoint = $("#" + dest).attr("data-offset");
		$("#info_wrapper").animate({
                scrollTop: scrollPoint
        }, 1000);
	});

		// COLLECTIONS

	$(".nav_coll, .to_grid").on("click", function(e){
		console.log("slide right");
		e.preventDefault();
		slideRight();
	});

	// WINDOW EVENTS

	$(window).on("load", function(){
		vhTest();
		infoPoints()	
	}).on("resize", function(){
		vhTest();
		infoPoints()
	}).on("scroll", function(){
		// 
	});
    
});