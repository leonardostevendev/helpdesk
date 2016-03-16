//	//	//	//	//	//	//	//	//	//	//	//	//	//
//													//
//	Hider Plugin by Reuben @dalegroup	          	//
//													//
//	//	//	//	//	//	//	//	//	//	//	//	//	//



$(document).ready(function() {
    //Show-Hide script  
    $('.show-more').attr('title', 'Press to view more');    // Add this to the show button/link
    $('a.show-more').attr('href', '#'); // Clears anchors from the show/hide button
    $('.show-more').on( "click", function() { // Button click function
		$(this).closest('div').find('.hide-this').slideToggle();  //Use .hide-this on a div adjacent to the .show-more button to slideToggle it on click
        $(this).closest('div').find('.hide-this-notoggle').slideDown(); // Only appears once, clicking again doesnt hide
        $(this).closest('div').find('.hide-this-mobile').slideDown(); //This element is only hidden on mobile devices
        $(this).closest('div').find('.hide-arrow').toggleClass('down'); //If .hide-arrow is used, rotates it 180 degrees
        $(this).closest('div').find('.more-text').html($('.more-text').text() == 'Show less options' ? 'Show more options' : 'Show less options');  //Changes hide/show button text
    });
});