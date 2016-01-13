$( document ).ready(function() {

	// GLOBAL WRAP FUNCTION

	function hasWhiteSpace(s) {
		return s.indexOf(' ') >= 0;
	}

	function textWrapInit () {	
		$(".wrap").each( function(){
			var elmt;
			if ( $(this).has("a").length ) {
				// target is child
				elmt = $(this).find("a");
			} else {
				elmt = $(this);
			}		
			var txt = elmt.text().trim();
			if ( hasWhiteSpace( txt ) ) {
				// STRING
				elmt.wrapInner("<span class='string_wrap'></span>").append("<span class='break'></span>");
			} else {
				elmt.wrapInner("<span class='word_wrap'></span>");
			}
		});
	}

	$.fn.stretch_text = function(){

		var elmt;
		if ( $(this).has("a").length ) {
			// target is child
			elmt = $(this).find("a");
		} else {
			elmt = $(this);
		}	    
	    var cont_width;
	    
    	if ( $(this).parents("#nav").length ) {
			cont_width = $("#nav").width();	
    	} else {
    		cont_width = elmt.parent(".wrap").width();
    		console.log( $(this).text(), cont_width );
    	} 
	    var txt           = elmt.text(),
	        one_line      = $('<span class="stretch_it">' + txt + '</span>'),
	        nb_char       = elmt.text().length,
	        spacing       = cont_width/nb_char,
	        txt_width;
   	    
    	elmt.html(one_line).css({'letter-spacing': "0"});
   		txt_width = one_line.width();
   		// console.log(txt, cont_width, nb_char, txt_width);
	
	    if (txt_width < cont_width){
	        var char_width = txt_width/nb_char,
	            ltr_spacing = spacing - char_width + (spacing - char_width)/nb_char;   
	        one_line.css({'letter-spacing': ltr_spacing});
	    } else {
	        one_line.contents().unwrap();
	        // elmt.addClass('justify');
	    }

	};

	var liH;

	function textWrapCalc () {
		
		// JUST FOR SINGLE WORDS
		
		$(".word_wrap").each( function(){			
			var elmt;
			if ( $(this).has("a").length ) {
				// target is child
				elmt = $(this).find("a");
			} else {
				elmt = $(this);
			}		
			// console.log( $(this).text() );
			$(this).css("text-align","center").stretch_text()				
		});

		// CALCULATE LI HEIGHT HERE

		liH = $("#nav_home .string_wrap").height() - 12;
		console.log( liH );
		$("#nav li").css("height", liH);

		$(".string_wrap").each( function(){
			if ( $(this).parent("h2").length ) {
				$("h2").css( "height", $(this).height() );
			} else if ( $(this).parent("h3").length ) {
				$("h3").css( "height", $(this).height() );
			} else {
				$(this).parent(".wrap").css( "height", $(this).height() );
			}

		});

	}


	// NAV DROPDOWN 

	// $("#nav_dropdown").css("height", 5 * liH);

	function navHide () {
		$("#nav").addClass("hidden");
		$("#nav_dropdown").css("height", "0px");
		// hide collections
		$(".nav_hidden").each( function(){
			var thisHref = $(this).find("a").data("href");
			$(this).css("height","").find("a").attr("href", "").css("cursor","text");	
		});
	}

	$("#nav").hover( function(){
		$("#nav").removeClass("hidden");
		$("#nav_dropdown").css({
			"height" : liH * 4
		});
	}, function () {
		navHide();
	});


		// ON SCROLL

	var lastScrollTop = 0;
	function scrollDetect () {
		var current = $(this).scrollTop();
		// console.log(current, lastScrollTop);
	   	if (current > (lastScrollTop + 100) ){
	       navHide();
	   	} 
	   	lastScrollTop = current;
	}

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
	 
	function imagesPrep () {
		var noImages = $(".page_collection li").length;
		var total = 0;
		// while loop corresponds to each row
		while ( total < noImages ) {
			var number;
			// If less than 6 until end
			if ( ( noImages - total ) <= 6 ) {
				var max = noImages - total - 2;
				number = parseInt( Math.random() * max ) + 2;
				if ( max <= 0 ) {
					number = noImages-total;
				}
			} else {
				// random number between 2 and 6
				number = parseInt( Math.random() * 4 ) + 2;
			}
			
			$(".page_collection li").slice( total, total+number ).wrapAll("<div class='row'></div>").addClass("child-" + number);
			// console.log(number);
			total += number; 
		}
		
	}

	// if on collection page, run function
	if ( $(".page_collection").length ) {
		imagesPrep();
	} 

	// COLLECTION FILTER

		// TOGGLE

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

		// FILTER REVEAL ON SINGLE PAGE

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

	/* 

	SINGLE

	*/

	$(".price").wrap("<div class='wrap'></div>");


	/* 

	CART

	*/

	// QUANTITY TOGGLE

	$(".product-quantity-default").on("click", function(){
		$(this).hide().next(".product-quantity-input").show();
	});

	// BUTTON STYLING

	function buttonResize () {
		$("a.button, a.shipping-calculator-button").each( function(){
	 		var thisW = $(this).width();
			$(this).parent().addClass("button_wrapper").css(
				"max-width", thisW
			);
		});		
	}

	$(".button_wrapper").hover( function(){
		console.log("hover");
		//$(this).children().css("color", "#efebe8");
	}, function(){
		//$(this).children().css("color", "");
	}); //// ?????


	// NEWS

	$(".news_text").each( function(){
		$(this).find("img").appendTo( $(this).next(".news_images") )
	});

	// WINDOW EVENTS

	$(window).on("load", function(){
		textWrapInit();
		textWrapCalc();
		buttonResize(); 
	}).on("resize", function(){
		textWrapCalc();
		buttonResize(); 
	});

		// THROTTLED SCROLL DETECT
	$(window).on('scroll', _.throttle(function() {
		scrollDetect();
	}, 1000));
    
});