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
			console.log( wrapperW - textW );
			
			var diff = ( wrapperW - textW ) / ( elemCount - 1 );
			$(this).find("span").css("margin-right", diff * 0.95);
			$(this).find("span:last-child").css({
				"margin-right" : "0px",
				"float" :  "right"
			});
		});

		/*
		wrapperW = 1637
		textW = 174 + 169 + 169 = 512
		*/

		// .each( function(){
		// 	// add span tags — for letters if 1 word, for words if more than 1 word
		// 	if ( $(this).find("span").length === 1 ) {
		// 		$(this).text( $.trim( $(this).text() ) ).lettering();

		// 		// var diff = ( wrapperW - textW ) / ( charCount - 1 );
		// 		// $(this).find("span").css("margin-right", diff * 1);
		// 		// $(this).find("span:last-child").css("margin-right", 0);
		// 	} 
		// 	var textW = 0;
		// 	var elemCount = 0;
		// 	$(this).find("span").each( function(){
		// 		textW += $(this).width();
		// 		elemCount++;
		// 	});
		// 	console.log( textW, elemCount );
		// });
	}

	// WINDOW EVENTS

	$(window).on("load", function(){
		navJustifyInit();
		navJustifyCalc();
	}).on("resize", function(){
		navJustifyCalc();
	}).on("scroll", function(){

	});
    
});