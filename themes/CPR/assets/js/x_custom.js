$( document ).ready(function() {

	var winH = $(window).height(),
	winW = $(window).width();

	// CHECK IF BROWSER SUPPORTS CSS VH UNITS

	function vhTest() {
		if( !Modernizr.cssvhunit ) {
			$(".section").css("height", winH);
		}
	}

	// NAVIGATE LEFT & RIGHT

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

	$("#back a").on("click", function(e){
		e.preventDefault();
		slideCentre();
		$(this).parent().hide();
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
		e.preventDefault();
		slideRight();
	});

	// COLLECTIONS — LOAD AJAX CONTENT

		// loading bar
		setTimeout( function(){
			$("#loading_bar .loading").css({
				"transition" : "",
				"-webkit-transition" : "",
				"width" : "100%"
			});
		}, 250 );

	$location = $("#coll_ajax_wrapper");

	$("a.to_grid").on("click", function(e){
		e.preventDefault();
		// empty ajax wrapper
		$location.empty();
		$("#loading_bar .loading").css({
			"transition" : "width 0s",
			"-webkit-transition" : "width 0s",
			"width" : "0%"
		}); 
		$location.css("opacity","0"); 
		// get section name
		var sectionName = $(this).parent("section").attr("data-section-name");
		
		// ajax call
        $.get("collection/" + sectionName, function (response) {
            $location.html(response);                   
        }).done(function () {           
	        // update url  
	        window.history.pushState("", "", sectionName);  

	        $("#loading_bar .loading").css({
				"transition" : "",
				"-webkit-transition" : "",
				"width" : "100%"
			});  

	        $location.animate({
	        	"opacity" : "1"
	        }, 500);

        });
	});

	// SINGLE — LOAD AJAX CONTENT

	$single_location = $("#single_ajax_wrapper");

	$("#col_4").on("click", ".open_single", function(e){
		e.preventDefault();
		
		// hide existing content
		$("#collections").hide();

		// empty ajax wrapper
		$single_location.empty().show().css("opacity","0"); 

		// get section name
		var thisHref = $(this).attr("href").split("/item")[1];
		
		// ajax call
        $.get("item/" + thisHref, function (response) {
            $single_location.html(response);                   
        }).done(function () {           
	        
        	console.log("checkkk");

        	/*
	        // update url  
	        window.history.pushState("", "", sectionName);  
			*/

	        $single_location.animate({
	        	"opacity" : "1"
	        }, 500);

	        $("#back").show();
	        
        });

	});


	// WINDOW EVENTS

	$(window).on("load", function(){
		vhTest();
		infoPoints()	
	}).on("resize", function(){
		vhTest();
		infoPoints()
	}).on("scroll", function(){

	});
    
});