var $=jQuery.noConflict();
var i_header, i_top, admin_bar_h;
$(document).ready(function() {
	$(".i_clickfalse").mousedown(function(){return false;});
    $(".i_clickfalse").bind("contextmenu",function(){return false;});
    i_header=$('#header'); admin_bar_h = Math.max(0, $('#wpadminbar').height());
    i_go_resize();

    $('.i_scroll_top').click( function(){
        $('html, body').animate({ scrollTop: 0 }, 'slow');
    } );

    $('body').on('click', '.i_scroll_menu', i_scroll_menu);
    function i_scroll_menu() {
        var scroll_to_el = $(this).attr('href');
        $('html, body').animate({ scrollTop: $( scroll_to_el ).offset().top }, 'slow');
        return false;
    }
});

////////////////////////////////////////////////////////////////////////////////
$(window).resize(i_go_resize);
function i_go_scroll() {
    if ( $(window).scrollTop() >= 300 ) {
        $('.i_scroll_top').fadeIn(500);
    } else {
        $('.i_scroll_top').fadeOut(500);
    }
}

function i_go_resize(){
    if($(window).width() < 770){
        lets_sticky(0);
    } else {
        lets_sticky(1);
    }
}
function lets_sticky( i_buld ) {
    if( i_buld ){
        if( wiser_options.fixed_header_menu == '1' ) $("#header_top_area").unstick().sticky({ topSpacing: admin_bar_h });
    } else {
        if( wiser_options.fixed_header_menu == '1' ) $("#header_top_area").unstick();//.sticky({ topSpacing: 0 });
    }
}

//////////////////////////////
$(window).load(function(){
    $(window).resize();
});

$(window).scroll(function() {

});