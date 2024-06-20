jQuery( document ).ready(function($) {
    $( ".sortable_custom" ).sortable({
        revert: true
    });
   
    $( ".a_admin_part ul, .a_admin_part li" ).disableSelection();
       
    
    
    
    
    var a_select_posts_view_select_val = $('.a_select_posts_view_select').val();
        console.log(a_select_posts_view_select_val);
        $('.field_cat_checkbox_div').css('display','none');
        if(a_select_posts_view_select_val == 'Category Filter'){
            $('.field_cat_checkbox_div').css('display','block');
        }
        
    $(".a_select_posts_view_select").change(function() {
        var a_select_posts_view_select_val = $(this).val();
        console.log(a_select_posts_view_select_val);
        $(this).parents('#i_home_page_option').find('.field_cat_checkbox_div').css('display','none');
        if(a_select_posts_view_select_val == 'Category Filter'){
            $(this).parents('#i_home_page_option').find('.field_cat_checkbox_div').css('display','block');
        }
    });
})