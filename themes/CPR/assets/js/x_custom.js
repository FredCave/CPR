$( document ).ready(function() {

	// NAV ONE LINE JUSTIFY

	function navJustifyInit() {
		$("#nav a").lettering('words').each( function(){
			if ( $(this).find("span").length === 1 ) {
				$(this).text( $.trim( $(this).text() ) ).lettering();
			}
		});
	}

	function navJustifyCalc() {
		var wrapperW = $("#nav").width();
		// set height - necessary??
		//var liH = $("#nav li:first-child").height();	
		//$("#nav li").css("height", liH);

		// set spacing

		$("#nav a").each( function(){
			var textW = 0;
			var elemCount = 0;
			$(this).find("span").each( function(){
				textW += $(this).width();
				elemCount++;
			});
			// console.log( wrapperW - textW );
			
			var diff = ( wrapperW - textW ) / ( elemCount - 1 );
			$(this).find("span").css("margin-right", diff * 0.95);
			$(this).find("span:last-child").css({
				"margin-right" : "0px",
				"float" :  "right"
			});
		});
	}

	// NAV HEIGHTS — CALCULATE ON RESIZE

	var liH = $("#nav_home").outerHeight() + 8;
	console.log(liH);


	// NAV DROPDOWN 

	$("#nav_home").on("click", function(e){
		e.preventDefault();
		if ( !$(this).hasClass("clicked") ) {
			$(this).addClass("clicked");
			$("#nav_dropdown").css({
				"height" : liH * 4
			});
		} else {
			$(this).removeClass("clicked");
			$("#nav_dropdown").css("height", "0px");
			// hide collections
			$(".nav_hidden").each( function(){
				var thisHref = $(this).find("a").data("href");
				$(this).css("height","").find("a").attr("href", "").css("cursor","text");	
			});
		}
	});

	// NAV COLLECTION DROPDOWN + VISIBILITY

	// init - on each page — check data-collection attribute
	var currentVis = $(".page").attr("data-collection");
	$(".nav_collection_2").each( function(){
		$(this).css("cursor","text");
		if ( $(this).attr("id") === currentVis ) {
			$(this).removeClass("nav_hidden");
		}
	});

	function collToggle ( main ) {
		var colls;
		$("#nav_dropdown").css("height", "auto");
		if ( !$(".nav_collection").hasClass("clicked") && main !== "main" ) {
			// get number of collections
			var colls = parseInt( $(".nav_collection").attr("data-length") );
			$(".nav_collection").addClass("clicked");
			// make visible +
			// activate links for .nav_hidden, not for current
			$(".nav_hidden").each( function(){
				var thisHref = $(this).find("a").data("href");
				$(this).css("height", liH).find("a").attr("href", thisHref).css("cursor","");	
			});
		} else {
			colls = 1;
			$(".nav_collection").removeClass("clicked");
			// hide hidden
			// deactivate links
			$(".nav_hidden").each( function(){
				var thisHref = $(this).find("a").data("href");
				$(this).css("height","").find("a").attr("href", "").css("cursor","text");	
			});
		}	
		setTimeout( function(){
			$("#nav_dropdown").css("height", liH * ( colls + 3 ) );
		}, 1000);
	}

	// On .nav_collection click
		// toggle visibility
	$(".nav_collection").on("click", function(e){
		e.preventDefault();
		collToggle();
	});

	// COLLECTION IMAGES

	// 1. Get number of images
	// 2. Create row loop
		// 2B. Append random number of images between 2 and 6
		// Until all images are placed
	 



	// WINDOW EVENTS

	$(window).on("load", function(){
		navJustifyInit();
		navJustifyCalc();
	}).on("resize", function(){
		navJustifyCalc();
	}).on("scroll", function(){

	});
    
});