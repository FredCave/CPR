$( document ).ready(function() {

	// GLOBAL WRAP FUNCTION

	function textWrapInit () {
		// Apply lettering to words if string
		$(".wrap").each( function(){
			var target;
			if ( $(this).has("a").length ) {
				// target is child
				target = $(this).find("a");
			} else {
				target = $(this);
			}
			target.lettering("words");
			$(this).addClass("lettering-words");
			// if only one word
			if ( target.find("span").length === 1 ) {
				target.text( $.trim( target.text() ) ).lettering().removeClass("lettering-words");
			}
		});
	}	

	function textWrapCalc () {
		
		/* 

		THIS NEEDS FIXING 

		*/

		$(".wrap").each( function(){
			var target;
			if ( $(this).has("a").length ) {
				// target is child
				target = $(this).find("a");
			} else {
				target = $(this);
			}

			var wrapperW = target.parent().width();
			var textW = 0;
			var elemCount = 0;
			target.find("span").each( function(){
				textW += target.width();
				elemCount++;
			});
			// console.log( wrapperW - textW );
			
			// How to take into account letter-spacing??

			var diff = ( wrapperW - textW ) / ( elemCount - 1 );
			//$(this).find("span").css("margin-right", diff * 1.02);
			target.find("span").css("margin-right", diff * 0.95);
			$(".lettering-words").find("span:last-child").css({
				"margin-right" : "0px"
				// "float" :  "right"
			});
		});
	}


	
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

	function liHCalc () {
		return $("#nav_home").outerHeight() + 8;		
	}

	var liH = liHCalc();

	// NAV DROPDOWN 

	function navHide () {
		$("#nav").addClass("hidden");
		$("#nav_dropdown").css("height", "0px");
		// hide collections
		$(".nav_hidden").each( function(){
			var thisHref = $(this).find("a").data("href");
			$(this).css("height","").find("a").attr("href", "").css("cursor","text");	
		});
	}

	$("#nav_home").on("click", function(e){
		e.preventDefault();
		if ( $("#nav").hasClass("hidden") ) {
			$("#nav").removeClass("hidden");
			$("#nav_dropdown").css({
				"height" : liH * 4
			});
		} else {
			navHide();
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
			console.log(number);
			total += number; 
		}
		
	}

	// if on collection page, run function
	if ( $(".page_collection").length ) {
		imagesPrep();
	} 

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

	// NEWS

	$(".news_text").each( function(){
		$(this).find("img").appendTo( $(this).next(".news_images") )
	});

	// WINDOW EVENTS

	$(window).on("load", function(){
		textWrapInit();
		//textWrapCalc();
		liHCalc();
		buttonResize(); 
	}).on("resize", function(){
		//textWrapCalc();
		liHCalc();
		buttonResize(); 
	}).on("scroll", function(){
		navHide();
	});
    
});