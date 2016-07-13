$(document).ready( function(){

	console.log("init", $("#test_button") );

	setTimeout( function(){

		console.log("click check");

		$.get( myAjax.ajaxurl, { action: "ajax_test" }, function ( data ) {
			console.log( data );
		});

	}, 8000 );
 
});

