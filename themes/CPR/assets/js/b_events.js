/*****************************************************************************
    
	1. GENERAL 
		1.1. WINDOW EVENTS
		1.2. RESIZE HANDLER
		1.3. HYPHENATION
		1.4. DETECT TOUCH SCREEN DEVICE
		1.5. LANDING PAGE CLICK DOWN
		1.6. LANDING PAGE SCROLL DETECT

	2. NAV
		2.1. HIDE NAV COLLECTIONS DEPENDING ON CURRENT PAGE
		2.2. NAV CLICK
		2.3. NAV LI HOVER
		2.4. HOVER OVER SOCIAL MEDIA ICONS
		2.5. ON COLLECTIONS CLICK
		2.6. NEWSLETTER SIGN-UP

	3. COLLECTION
		3.1. COLLECTION IMAGE HOVER
		3.2. FILTER TOGGLE + REVEAL
		3.3. FILTER ON CLICK

	4. SINGLE
		4.1. IMAGE SLIDESHOW
		4.2. PRODUCT DESCRIPTION TOGGLE
		4.3. RADIO BUTTON CLICK
		4.4. CHECK IF SIZE HAS BEEN SELECTED ON ADD TO CART
		4.5. PRODUCT INFO HOVER

	5. OTHER PAGE EVENTS
		5.1. TOGGLE QUANTITY INPUT
		5.2. TOGGLE SHIPPING FORM
		5.3. BUTTON HOVER

	6. WHOLESALE
		6.1. CLICK ON OTHER COLOURS


*****************************************************************************/

$(document).ready( function(){

// 1. GENERAL

	// 1.1. WINDOW EVENTS

	$(window).on("load", function(){
		pageInit();
	}).on("resize", function(){
		// RESIZE IMAGES FOR LAZYSIZES
		imgWidth();
		// RESIZE IFRAMES
		if ( $("#news").length || $("#campaign").length ) {
			iframeResize();
		}
	});

		// THROTTLED SCROLL DETECT
	$(window).on('scroll', _.throttle(function() {
		var scrollPos = $(window).scrollTop();
		singleCollCheck( scrollPos );
	}, 500));

	// 1.2. RESIZE HANDLER

	$("body").addClass("first_time");
	var handleMediaChange = function (mql) {
		console.log("mql");
	    // Gives number of columns for image injection
	    if (mql.s.matches) {
	        // Less than 600px wide     
	    	imagesPrep();
	    	// ALL SINGLE INFO CENTERED
	    	// console.log(75);
	    	// $(".single_info").each( function(){
	    	// 	console.log(77);
	    	// 	singleInfoOn( $(this), true );
	    	// });
	    } else if (mql.m.matches) {
	        // More than 600px wide
			imagesPrep();
	    	// ALL SINGLE INFO CENTERED
	    	// console.log(83);
	    	// $(".single_info").each( function(){
	    	// 	singleInfoOn( $(this), true );
	    	// });
	    } else {
	    	// More than 780px wide
			imagesPrep();
	    }
	}

	var mql = {};
	mql.s = window.matchMedia("(max-width: 600px)");
	mql.m = window.matchMedia("(max-width: 780px)");
	mql.s.addListener(function(){
		handleMediaChange(mql);
	});
	mql.m.addListener(function(){
		handleMediaChange(mql);
	});
	handleMediaChange(mql);


	// 1.3. HYPHENATION

	$("p, .s1").hyphenate('en-us');

	// 1.4 DETECT TOUCH SCREEN DEVICE

	if (Modernizr.touch) { 
	    // TOUCH SCREEN
	    $("body").addClass("touch");
	} else { 
	    // NO TOUCH SCREEN
	    $("body").addClass("no-touch");
	}

	// 1.5. LANDING PAGE CLICK DOWN

	$("#landing_page_access a").on( "click", function(e) {
		e.preventDefault();
		landingDown();
		collectionInit();
	});

	// 1.6. LANDING PAGE SCROLL DETECT

	$('#landing_page').bind( "mousewheel DOMMouseScroll touchmove", _.throttle(function (e) {
	    // CHECK IF IN SLIDER MODE
	    var delta = 0, 
	    	element = $(this), 
	    	value, 
	    	result, 
	    	oe;
	    oe = e.originalEvent; // for jQuery >=1.7
	    if (oe.wheelDelta) {
	        delta = -oe.wheelDelta;
	    }
	    if (oe.detail) {
	        delta = oe.detail * 40;
	    }
	    console.log( 127, delta );
	    if ( delta > 0 ) {
	    	// LANDING PAGE SLIDER FORWARD
	    	landingForward();
	    } else if ( delta < -1 ) {
	    	// LANDING PAGE SLIDER BACK
	    	landingBack();
	    }
	    return false;
	}, 1000) );

	// 1.7. SAFARI DETECT

	if (navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Chrome') == -1) {
		console.log('safari check');
		$(".info_justified").find(".wrap").removeClass("wrap");
		$(".info_justified").removeClass("info_justified");
	}

// 2. NAV

	// 2.2. NAV CLICK

	$("#nav_home").on( "click", function( e ){
		if ( $("#nav_dropdown").hasClass("hidden") ) {
			e.preventDefault();
			navShow();
		}
	});

	$("#nav_close").on( "click", function(){
		navHide();
	});

	// 2.3. NAV LI HOVER
	
	$("#nav li").not("#nav_share, #nav_home").hover( function(){
		navLiCompress( $(this) );
	}, function () {
		navLiReset( $(this) );
	});

	// 2.4. HOVER OVER SOCIAL MEDIA ICONS

	$(".nav_share").hover( function(){

	}, function(){

	});

	// 2.5. ON COLLECTIONS CLICK

	$(".nav_collection").on("click", function(e){
		e.preventDefault();
		collToggle();
	});

// 3. COLLECTIONS

	// 3.1. COLLECTION IMAGE HOVER

	$(".collection .product").hover( function(){	
		if ( $("body").hasClass("no-touch") ) {
			$(this).find(".product_image:first-child").css("opacity","0");
			$(this).find(".product_image:last-child").css("opacity","1");
		}
	}, function(){
		if ( $("body").hasClass("no-touch") ) {
			$(this).find(".product_image").css("opacity","");
		}
	});

	// 3.2. FILTER TOGGLE + REVEAL

	$("#filter_toggle").on("click", function(e){
		e.preventDefault();
		filterToggle();		
	});

	// 3.3. FILTER ON CLICK

	$(".filter").on("click", function(e) {
		e.preventDefault();
		filterProducts( $(this) );
	});

		// CLEAR FILTER

	$(".clear_filter").on("click", function(){
		filterClear( $(this) );
	});

// 4. SINGLE

	// 4.1. IMAGE SLIDESHOW

	$("body").on("click", ".gallery", function() {
		slideShowGo( $(this) );
	});

	// 4.2. PRODUCT DESCRIPTION TOGGLE

	$(".product_desc_click").on("click", function(){
		descToggle( $(this) );
	});

	// 4.3. RADIO BUTTON CLICK

	$(".variations label").not(".label label").on("click", function(){
		radioCheck( $(this) );
	});

	// 4.4. PRODUCT INFO HOVER

	$(".info_justified").hover( function(){
		singleInfoOn( $(this) );
	}, function(){
		singleInfoOff( $(this) );
	});

// 5. OTHER PAGE EVENTS

	// 5.1. TOGGLE QUANTITY INPUT

	$(".product-quantity-default").on("click", function(){
		$(this).hide().next(".product-quantity-input").show();
	});

	// 5.2. TOGGLE SHIPPING FORM



	// 5.3. BUTTON HOVER
	
	$("body").on("mouseover", ".button_wrapper", function(){

	}).on("mouseleave", ".button_wrapper", function(){

	});

	// 5.4. NEWS CAMPAIGN HOVER

	$("#campaign_list li").hover( function(){
		$(this).find(".campaign_title").css("width","auto");
	}, function(){
		$(this).find(".campaign_title").css("width","");
	});	

// 	6. WHOLESALE
		
	// 6.1. CLICK ON OTHER COLOURS

	$("#wwof_product_listing_ajax_content").on( "click", ".wholesale_other_colours", function(){
		wsaleOtherColours( $(this) );
	});

	// 6.1. FILTER TOGGLE

	$("#ws_filter_toggle").on( "click", function (e) {
		e.preventDefault();
		wsaleFilterToggle( $(this) );
	});

		// CLICK OUTSIDE TO CLOSE
	$(document).on( "click", function (e) {
    	var container = $("#search_wrapper");
	    if (!container.is(e.target) && container.has(e.target).length === 0) {
	        container.css({
	        	"height" : ""
	        }).addClass("hidden");
	    }
	});

	// 6.2. FILTER TERMS CLICK

	$(".wsale_term").on( "click", function (e) {
		e.preventDefault();
		wsaleFilter( $(this) );
	});


});