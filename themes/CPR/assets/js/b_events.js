/*****************************************************************************
    
	2. EVENTS
		2.1. VARIOUS INIT
			2.1.1. PRICE WRAP ON SINGLE PAGES
			2.1.2. HIDE NAV COLLECTIONS DEPENDING ON CURRENT PAGE
			2.1.3. IF ON COLLECTION PAGE, POSITION IMAGES
		2.2. EVENTS
			2.2.1. NAV HOVER
			2.2.2. NAV LI HOVER / CENTER TEXT
			2.2.3. HOVER OVER SOCIAL MEDIA ICONS
			2.2.4. TOGGLE COLLECTIONS
			2.2.5. IMAGE HOVER
			2.2.6. PRODUCT FILTER TOGGLE ON COLLECTION PAGES + REVEAL ON SINGLE PAGES
			2.2.7. FILTER ON CLICK
			2.2.8. TOGGLE QUANTITY INPUT
			2.2.9. TOGGLE SHIPPING FORM
			2.2.10. IMAGE SLIDESHOW
			2.2.11. AJAX ADD TO CART
			2.2.12. WINDOW EVENTS

*****************************************************************************/

$(document).ready( function(){

// 2.1. VARIOUS INIT

	// 2.1.1. VAR. ELEMENTS WRAP ON SINGLE PAGES
	
	$(".price").wrap("<div class='wrap'></div>");
	// $(".single_add_to_cart_button").innerHTML().wrap("<div class='wrap'></div>");

	// 2.1.2. HIDE NAV COLLECTIONS DEPENDING ON CURRENT PAGE

	var currentVis = $(".page").attr("data-collection");
	$(".nav_collection_2").each( function(){
		$(this).css("cursor","text");
		// console.log( $(this).attr("id"), currentVis );
		if ( $(this).attr("id") === currentVis ) {
			$(this).removeClass("nav_hidden");
		}
	});

	// 2.1.3. IF ON COLLECTION PAGE, POSITION IMAGES
	
	if ( $(".page_collection").length ) {
		imagesPrep();
	} 

// 2.2. EVENTS

	// 2.2.1. NAV CLICK

	$("#nav_home").on( "click", function( e ){
		if ( $("#nav").hasClass("hidden") ) {
			e.preventDefault();
			navShow();
		}
	});

	$("#nav_close").on( "click", function(){
		navHide();
	});

	// 2.2.2. NAV LI HOVER / CENTER TEXT 
	
	var spacing;
	$("#nav li.wrap").hover( function(){
		// CONDENSE
		$(this).addClass("li_hover");
	// 	$(this).css("text-align","center");
		// record calculated letter-spacing
		spacing = parseInt( $(this).find(".stretch_it").css("letter-spacing") );
		// $(this).find(".stretch_it").css("letter-spacing","0.2em").attr("data-spacing", spacing);
	}, function () {
		$(this).removeClass("li_hover");
		// STRETCH
	// 	$(this).css("text-align","");	
		// $(this).find(".stretch_it").css("letter-spacing", spacing);
	});

	// 2.2.3. HOVER OVER SOCIAL MEDIA ICONS

	$(".nav_share").hover( function(){
		$(this).find("a").addClass("hover");
	}, function(){
		$(this).find("a").removeClass("hover");
	});

	// 2.2.4. TOGGLE COLLECTIONS	

	$(".nav_collection").on("click", function(e){
		e.preventDefault();
		collToggle();
	});

	// 2.2.5. IMAGE HOVER

	$("#home .product").hover( function(){	
		$(this).find(".picturefill-background:first-child").css("opacity","0");
		$(this).find(".picturefill-background:last-child").css("opacity","1");
	}, function(){
		$(this).find(".picturefill-background").css("opacity","");
	});

	// 2.2.6. PRODUCT FILTER TOGGLE ON COLLECTION PAGES + REVEAL ON SINGLE PAGES

	var filterVis = false;
	$("#filter_toggle").on("click", function(e){
		e.preventDefault();
		if ( !filterVis ) {
			$("#collection_filter").show();
			filterVis = true;	
		} else {
			$("#collection_filter").hide();
			filterVis = false;
		}
	});

		// SHOW TOGGLE BUTTON ONCE THE COLLECTION IS VISIBLE 

	if ( $("#single_collection").length ) {	
		// get offset of collection section
		var thisTop = $("#single_collection").offset().top;	
		$(window).on("scroll", function(){
			if ( $(window).scrollTop() > thisTop ) {
				$("#filter_toggle").show();
			} else {
				$("#filter_toggle").hide();
			}			
		});
	}

	// 2.2.7. FILTER ON CLICK

	$(".filter").on("click", function(e) {
		e.preventDefault();
		filterProducts( $(this) );
	});

		// CLEAR FILTER

	$(".clear_filter").on("click", function(){
		filterClear( $(this) );
	});

	// 2.2.8. TOGGLE QUANTITY INPUT

	$(".product-quantity-default").on("click", function(){
		$(this).hide().next(".product-quantity-input").show();
	});

	// 2.2.9. TOGGLE SHIPPING FORM

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

	// 2.2.10. IMAGE SLIDESHOW

	$(".single_additional_images").on("click", ".gallery", function() {
		slideShowGo( $(this) );
	});

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

	// 2.2.XX. BUTTON HOVER
	
	$("body").on("mouseover", ".button_wrapper", function(){
		$(this).children().css("color", "#efebe8");
	}).on("mouseleave", ".button_wrapper", function(){
		$(this).children().css("color", "");
	});

	// 2.2.12. WINDOW EVENTS

	$(window).on("load", function(){
		pageInit();
	}).on("resize", function(){
		oneWord();
		buttonResize(); 
	});

		// THROTTLED SCROLL DETECT
	$(window).on('scroll', _.throttle(function() {
		scrollDetect();
	}, 1000));




});