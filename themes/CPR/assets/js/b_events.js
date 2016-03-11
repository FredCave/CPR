/*****************************************************************************
    
	1. GENERAL 
		1.1. WINDOW EVENTS
		1.2. RESIZE HANDLER
		1.3. HYPHENATION

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

*****************************************************************************/

$(document).ready( function(){

// 1. GENERAL

	// 1.1. WINDOW EVENTS

	$(window).on("load", function(){
		pageInit();
		pageShow();
	}).on("resize", function(){
		// oneWord();
		liCalc();
		buttonResize(); 
		secNavH();
		breakCheck();
		iframeResize(); 
	});

		// THROTTLED SCROLL DETECT
	$(window).on('scroll', _.throttle(function() {
		var scrollPos = $(window).scrollTop();
		scrollDetect();
		imagesVis ( scrollPos );
	}, 1000));

	// 1.2. RESIZE HANDLER

	$("body").addClass("first_time");
	var handleMediaChange = function (mql) {
		console.log("mql");
	    // Gives number of columns for image injection
	    if (mql.s.matches) {
	        // Less than 600px wide     
	    	imagesPrep();
	    } else if (mql.m.matches) {
	        // More than 600px wide
			imagesPrep();
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

// 2. NAV

	// 2.1. HIDE NAV COLLECTIONS DEPENDING ON CURRENT PAGE

	var currentVis = $(".page").attr("data-collection");
	$(".nav_collection_2").each( function(){
		$(this).css("cursor","text");
		// console.log( $(this).attr("id"), currentVis );
		if ( $(this).attr("id") === currentVis ) {
			$(this).removeClass("nav_hidden");
		}
	});

	// 2.2. NAV CLICK

	$("#nav_home").on( "click", function( e ){
		if ( $("#nav").hasClass("hidden") ) {
			e.preventDefault();
			navShow();
		}
	});

	$("#nav_close").on( "click", function(){
		navHide();
	});

	// 2.3. NAV LI HOVER
	
	$("#nav li.wrap").hover( function(){
		if ( $(window).width() > 600 ) {
			// CONDENSE
			$(this).addClass("li_hover");
			$(this).find(".last_word").each( function(){
				// HIDE SPACED TEXT
				$(this).children("span").hide();
				// SHOW BACKUP
				$(this).find(".rollover").show();
			});
		}
	}, function () {
		if ( $(window).width() > 600 ) {
			// STRETCH
			$(this).removeClass("li_hover");	
			$(this).find(".last_word").each( function(){
				// SHOW SPACED TEXT
				$(this).children("span").show();
				// HIDE BACKUP
				$(this).find(".rollover").hide();
			});
		}
	});

	// 2.4. HOVER OVER SOCIAL MEDIA ICONS

	$(".nav_share").hover( function(){
		if ( $(window).width() > 600 ) {
			$(this).find("a").addClass("hover");
		}
	}, function(){
		if ( $(window).width() > 600 ) {
			$(this).find("a").removeClass("hover");
		}
	});

	// 2.5. ON COLLECTIONS CLICK

	$(".nav_collection").on("click", function(e){
		e.preventDefault();
		collToggle();
	});

	// 2.6. NEWSLETTER SIGN-UP

	$("#newsletter_signup").on("click", function(e){
		e.preventDefault();
		if ( !$("#newsletter").hasClass("newsletter_visible") ) {
			newsletterShow();
		} else {
			newsletterHide();			
		}
		
	});	



// 3. COLLECTIONS

	// 3.1. COLLECTION IMAGE HOVER

	$(".collection .product").hover( function(){	
		if ( $("body").hasClass("no-touch") ) {
			$(this).find(".picturefill-background:first-child").css("opacity","0");
			$(this).find(".picturefill-background:last-child").css("opacity","1");
		}
	}, function(){
		if ( $("body").hasClass("no-touch") ) {
			$(this).find(".picturefill-background").css("opacity","");
		}
	});

	// 3.2. FILTER TOGGLE + REVEAL

	$("#filter_toggle").on("click", function(e){
		e.preventDefault();
		if ( $(this).text().toLowerCase() === "filter" ) {
			console.log("filter_toggle");
			if ( !$("#collection_filter").is(':visible') ) {
				$("#collection_filter").show();	
			} else {
				$("#collection_filter").hide();
				// CHECK IF ONE OF THE CATEGORIES HAS BEEN SELECTED
				$(".filter").each( function(){
					if ( $(this).hasClass("selected") ) {
						filterClear();	
					}
				});
			}
		}
	});

		// SHOW TOGGLE BUTTON ONCE THE COLLECTION IS VISIBLE 

	if ( $("#single_collection").length ) {	

		// get offset of collection section
		var thisTop = $("#single_collection").offset().top;	
		$(window).on("scroll", function(){
			//console.log(128);
			
			if ( $(window).scrollTop() > ( thisTop - $(window).height() ) ) {
				//console.log("SHOW");
				$("#filter_toggle").removeClass("hide_filter");
			} else {
				//console.log("HIDE");
				$("#filter_toggle").addClass("hide_filter");
			}
					
		});

		// PREP COLLECTION IMAGES
		$("#single_collection .product").removeClass("single_product");
	}

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

	$(".product_desc_toggle .wrap").on("click", function(){
		descToggle( $(this) );
	});

	// 4.3. RADIO BUTTON CLICK

	$(".variations label").not(".label label").on("click", function(){
		radioCheck( $(this) );
	});


	// 4.4. CHECK IF SIZE HAS BEEN SELECTED ON ADD TO CART

	// $(".single_add_to_cart_button").on("click", function(e) {		
	// 	// CHECK IF A SIZE HAS BEEN SELECTED
	// 	var checked = false;
	// 	$(this).parents("form").find("input[type=radio]").each( function(){
	// 		console.log("x");
	// 		if ( $(this).is(':checked') ) {
	// 			// console.log( $(this).text() );
	// 			checked = true;
	// 		}
	// 	});
	// 	if ( !checked ) {
	// 		e.preventDefault();
	// 	}  
	// });	

	// 4.5. PRODUCT INFO HOVER

	$(".single_info").hover( function(){
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

		// ON BUTTON CLICK
	$(".shipping-calculator-button").on("click", function(){
		// fix height
		var thisH = $("tr.shipping").height();
		$("tr.shipping").css("height", thisH);
		// remove text + button
		$(this).parent(".button_wrapper").hide();
		$(".shipping td p:first-child").hide();
		$(".shipping-calculator-form p").show();
		$(".woocommerce-shipping-calculator").css({
			"position": "absolute",
			"width": "50%",
			"right": "0"
		});
		
	});

	// 5.3. BUTTON HOVER
	
	$("body").on("mouseover", ".button_wrapper", function(){
		$(this).children().css("color", "#efebe8");
	}).on("mouseleave", ".button_wrapper", function(){
		$(this).children().css("color", "");
	});

	// 5.4. NEWS CAMPAIGN HOVER

	$("#campaign_list li").on("mouseover", function(){
		$(this).find("h4").css({
			"margin" : "0 auto",
			"width" : "auto"
		}).removeClass("wrap");
	}).on("mouseleave", function(){
		$(this).find("h4").css({
			"margin" : "",
			"width" : ""
		}).addClass("wrap");
	});	




	// 2.2.XX. GALLERY IMAGE HOVER

	// FALLBACK FOR TOUCH SCREEN DEVICES

	/*
	$(".position_right").hover( function(){
		if ( $(this).parents(".gallery").length ) {
			console.log("hovvver");
			$(this).parents(".row").find(".gallery_arrow").show();
		}		
	}, function(){
		$(this).parents(".row").find(".gallery_arrow").hide();
	});
	*/






	// 2.2.11. AJAX ADD TO CART

	/*
	$('.single_add_to_cart_button').click(function(e) {
	    e.preventDefault();

	    var item_id = $(this).prev("input").attr("value");

	    $.post(
	    	// URL = ADMIN-AJAX.PHP
	        cpp_ajax.ajaxurl, 
	        // DATA
	        {
	            action      : 'cpp_ajax-submit',
	            nonce       : cpp_ajax.diy_product_nonce,
	            product_id  : item_id
	        },
	        // FUNCTION
	        function(response) {
	            // UPDATE CART BUTTON
	            // $('#cart_container').html(response['a.cart-contents']);
	            // UPDATE QUANTITIES ORDERED
	            console.log(response);
	        }
	    );
	    return false;
	}); 
*/

	// EXTRA AJAX CALLS

	/*
    var data = {
        'action': 'get_post_information',
		'post_id': 237
    };

    $(window).on("click", function(){
    	alert("x");
	    $.post(ajaxurl, data, function(response) {
	        alert('Server response from the AJAX URL ' + response);
	    });    	
    });
*/


});