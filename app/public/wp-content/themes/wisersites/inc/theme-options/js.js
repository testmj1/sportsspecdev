var $=jQuery.noConflict();

jQuery(document).ready(function( $ ){

    $('.i_wiser_section_tab a').click( open_wiser_section_tab );
    function open_wiser_section_tab(){
        var current_tab = '#'+$(this).attr('id')+'_option';
        $('.i_wiser_section_tab a.active').removeClass('active');
        $(this).addClass('active');
        $('.i_wiser_section_content.active').removeClass('active');
        $( current_tab ).addClass('active');

        return false;
    }
    $('.i_wiser_section_tab').first().children('a').click();

    $('.i_color_picker').wpColorPicker();

    /*
    * * Media / Uploading files
    */
    var file_frame, i_id, thiss, img_id = 1;

    $('.upload_image_button').on('click', function( event ){
        event.preventDefault(); i_id= $(this).attr('id');

        if ( file_frame ) { file_frame.open(); return; }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: jQuery( this ).data( 'uploader_title' ),
            button: {
                text: jQuery( this ).data( 'uploader_button_text' ),
            },
            multiple: false // Set to true to allow multiple files to be selected
        });

        // When an image is selected, run a callback.
        file_frame.on( 'select', function() {
            attachments = file_frame.state().get('selection').toJSON();
            i_add_image( attachments );
        });

        file_frame.open();
    });

    function i_add_image( image ){
        image = image[0];
        $('#'+i_id).val( image.url );
        $('.i_preview_'+i_id).attr( 'src', image.url).removeClass('i_hidden');
        return false;
    }

    $('body').on('click','.i_remove',get_open_del_window);
    function get_open_del_window() {
        var img_id = $(this).find('img').attr('id'); if(img_id==''){return false;}
        thiss = $(this);
        if (confirm("Delete this Image?")) {
            $(this).parents('.fb_image_li').remove();
        }
    }


    $('.i_add_featured_post').click( i_add_featured_post );
    function i_add_featured_post(){
        var featured_post = $(".featured_post_ex").first().clone();
        var featured_post_item = featured_post.children('select');
        featured_post_item.attr("id", featured_post_item.attr("id")+'_' + $(".featured_post_ex").length );
        featured_post_item.val('null');

        $("#featured_posts_list").append( featured_post );

        return false;
    }
    $("#featured_posts_list").sortable( {
        stop: function(event, ui) {
            var i = 0;
        }
    });

    $('body').on('click', '.i_remove_feature_post', function() {
        if( $(".featured_post_ex").length > 1 ){ console.log( $(".featured_post_ex").length );
            var remove_el = $(this).parents('.featured_post_ex');
            remove_el.hide( 500, function( ){ remove_el.remove(); } );
        } return false;
    });



});

