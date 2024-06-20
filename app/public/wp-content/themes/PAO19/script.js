jQuery(document).ready(function ($) {


    var a_stick_height = $('.left_and_sidebar').outerHeight()-$('#primary_sidebar').outerHeight();
    $('#a_sticky_sidebar_parent').css('min-height', a_stick_height);
//    var el_width = '';
//    var el_height = '';
//    $('#owl-home .owl-item').each(function (index) {
//        el_width = $(this).find('.slid_image').width();
//        el_height = (el_width / 16) * 9;
//        console.log(el_width);
//        console.log(el_height);
//        $(this).find('.slid_image').css('max-height', el_height);
//        $(this).find('.slid_image').css('min-height', el_height);
//    });
    setTimeout(function () {
        $(window).resize(function () {
            var el_res_width = '';
            var el_res_height = '';
            console.log($('#owl-home .owl-item').length);
            $('#owl-home .owl-item').each(function (index) {
                el_res_width = $(this).find('.slid_image').width();
                el_res_height = (el_res_width / 16) * 9;
                $(this).find('.slid_image').css('max-height', el_res_height);
                $(this).find('.slid_image').css('min-height', el_res_height);
            });
        }).resize();

        $(window).resize(function () {
            var post_el_res_width = '';
            var post_el_res_height = '';
            post_el_res_width = $('.a_post_img_class').width();
            post_el_res_height = (post_el_res_width / 16) * 9;
            $('.a_post_img_class').css('max-height', post_el_res_height);
            $('.a_post_img_class').css('min-height', post_el_res_height);
        }).resize();


        $(window).resize(function () {
            var header_height = '';
            header_height = $('#header').outerHeight();
            $('#content_parent.site-content').css('margin-top', header_height + 20);
            setTimeout(function () {
                a_stick_height = $('.left_and_sidebar').outerHeight()-$('#primary_sidebar').outerHeight();
                $('#a_sticky_sidebar_parent').css('min-height', a_stick_height);
            }, 500 );

        }).resize();
    }, 1000);


    $(window).scroll(function () {
        var sticky = $('#header'),
            scroll = $(window).scrollTop();

        if (scroll >= 130) sticky.addClass('fixed');
        else sticky.removeClass('fixed');
    });


    var a_global_post_count = 5;
    var a_global_appended_post_count = 0;
    $('#category_main_menu_ul li.menu-item-has-children>a').click(function (event) {
        event.preventDefault();
    })


    $(".hamburger_icon").click(function () {
        if ($(".header_bottom_left_and_right").hasClass("show_top_menu")) {
            $(".header_bottom_left_and_right").removeClass("show_top_menu");
        } else {
            $(".header_bottom_left_and_right").addClass("show_top_menu");

        }
    })
    $('.search_div .fa.fa-check').css('display', 'none');
    $('.search_div i.fa-check').click(function () {
        var field_value = $('.search_div .type_your_text').val();
        if ($('.search_div .serch_part').hasClass('show_search')) {
            $(this).parent().find('.serch_part').removeClass('show_search');
            $(this).css('display', 'none');
            $('.search_div .fa.fa-search').css('display', 'block');
//            $(this).removeClass('fa fa-check');
//            $(this).addClass('fa fa-search');
        }
        if (field_value !== "") {
            $('.search_div .search_form').submit();
        }
    });
    $('.search_div i.fa-search').click(function () {
//            $(this).removeClass('fa fa-search');
//            $(this).addClass('fa fa-check');
        $(this).css('display', 'none');
        $('.search_div .fa.fa-check').css('display', 'block');
        $(this).parent().find('.serch_part').addClass('show_search');
    });

    $('.owl-carousel').owlCarousel({
        loop: true,
        nav: true,
        items: 1,
        navText: ['<i class="fas fa-chevron-left"></i>', '<i class="fas fa-chevron-right"></i>'],
        autoplay: true,
        autoplayTimeout: 4000,
        autoplayHoverPause: true
    });


//    $('#primary_sidebar_inner .subscribe_ad').parents('aside').addClass('stiky_sidebar');


    $(".fancybox").fancybox({
        type: "iframe",
        // other API options
    })
    $('#videocategories ul .selected_category').click(function () {
        var a_data_cat_id = $(this).attr('data-cat-id');
        var tax = $(this).attr('data-tax');
        $('#videocategories ul .selected_category').removeClass('active');
        $(this).addClass('active');
        var category_id = $('#videocategories ul .all').attr('data-cat-id');
//        console.log(product_id);
        $.ajax({
            type: "POST",
            url: i_infos.ajax_url,
            data: {
//                    checked_checkboxes: checked_checkboxes,
                action: 'load_filtered_vidoes',
                a_data_cat_id: a_data_cat_id,
                tax: tax,
                category_id: category_id,
            },
            success: function (response) {
                console.log(response);
                $('.archive_videos_and_content .archive_post_preview').remove();
                $('.archive_videos_and_content').prepend(response);
                if (tax == 'category') {
                    $('.pagenav').show();
                } else {
                    $('.pagenav').hide();
                }
                $(".fancybox").fancybox({
                    type: "iframe",
                    // other API options
                })
            }
        });
    });


    var load_more_el = $(".load_more_posts").first();
    var scroll_wait = false;
    var lazy_load_count = 0;
    if ($(".load_more_posts").length)
        $(window).scroll(function () {
            if (!$(".load_more_posts").length || !$(".load_more_posts").is(":visible") || $(window).width() <= 667)
                return false;
            var load_more_el = $(".load_more_posts").first();
            if (($(window).scrollTop() + $(window).height()) > (load_more_el.offset().top + 2 * load_more_el.height())) {
                if (!scroll_wait && !load_more_el.hasClass('finished')) {
                    scroll_wait = true;
                    i_load_more_posts(load_more_el);
                    if (lazy_load_count == 0) {
                        $('#i_footer').addClass('stickItLoadMore');
                    }
                    lazy_load_count++;
                }
            }


            if ($('.stickItLoadMore').length)
                if (($(window).scrollTop() + $(window).height() + ($('.stickItLoadMore').outerHeight() + $(window).height() / 3)) < $(document).height()) {

                    $('.stickItLoadMore').addClass('stickIt');
                } else {
                    $('.stickItLoadMore').removeClass('stickIt');
                }
        });
    $('.load_more_posts_btn').click(function (e) {
//        if($('body').hasClass('home') && $('ul.filter-toggle li a.active').parent().attr('data-filter') == 'all'){
//            var all_loaded_post_count = Math.floor($('.home_page_posts').length/20);
//            $('ul.filter-toggle li a.active').attr('data-paged',all_loaded_post_count);
//            $(this).attr('data-paged',all_loaded_post_count);
//            console.log('paged for all tab'+all_loaded_post_count);
//        }
        load_more_el = $(this);
        if (!scroll_wait && !load_more_el.hasClass('finished')) {
            scroll_wait = true;

            i_load_more_posts(load_more_el);
        }
        e.preventDefault();
        e.stopPropagation();
        return false;
    });

    var $masonry_container = $(".i_posts_container.isotope");
    $masonry_container.find('.grid-item').addClass('i_show');
    var msnry;
    var masonry_actived = false;
//    function call_masonry(){
//        $masonry_container.masonry({
//            itemSelector: '.grid-item.i_show',
//            transitionDuration: 0,
//        });
//        masonry_actived = true;
//    }

    var filterBtns = $('.filter-toggle');
    var current_filter_el = '';
    filterBtns.on('click', 'a.i_filters', do_filtering);

    function do_filtering(e) {
        e.preventDefault();
        var category_title = $(this).text();
        console.log(category_title);
        $('.isotope.i_posts_container').removeClass('a_choosed_all_filter');
        if (category_title.toLowerCase() == 'all') {
            category_title = "All Articles";
        }
        $('.sport-category-part h2').html(category_title);
        if ($(this).attr('data-term_id') == 'all') {
            $('.isotope.i_posts_container').addClass('a_choosed_all_filter');
            $(this).attr('data-term_id', load_more_el.attr('data-all_term_ids'));
        }

        if (!$(this).hasClass('active')) {

            var filterValue = $(this).attr('data-filter');
            var filterText = $(this).text();

            if ($('body').hasClass('category')) {
                var currentUrl = window.location.href;
                var url = currentUrl;
                var parts = url.split("/");
                var last_part = parts[parts.length - 1];
                if (filterValue.replace('.', '') == 'start') {
                    history.pushState({}, null, '/sport/');
                } else {
                    history.pushState({}, null, '/sport/' + filterValue.replace('.', '') + '/');

                }
            }

            load_more_el.hide();
            current_filter_el = $(this);

            load_more_el.attr('data-term_id', $(this).attr('data-term_id'));
            load_more_el.attr('data-paged', $(this).attr('data-paged'));

            var filter_container = $(this).parents('#filter-toggle-block').find('.i_posts_container');


            if (filterValue == '.start') {
                filter_container.find('.grid-item').addClass('i_show').removeClass('masonry_hidden');
            } else {
                filter_container.find('.grid-item').each(function () {
                    if ($(this).hasClass(filterValue.replace('.', ''))) {
                        $(this).addClass('i_show').removeClass('masonry_hidden');
                        $(this).find(".tag.hand").removeAttr('class').addClass('tag hand ' + filterValue.replace('.', '')).text(filterText);
                    } else {
                        console.log('a_masonry_hidden');
                        $(this).removeClass('i_show').addClass('masonry_hidden');
                    }
                });
            }
//            $('.i_show').show();

            if (masonry_actived)
                $masonry_container.masonry('destroy');
//            call_masonry();
            filterBtns.find('a.active').removeClass('active');
            $(this).addClass('active');
            load_more_el.show();
            $(window).scroll();
            setTimeout(function () {
                $(window).scroll();
            }, 500);
//            $grid.isotope('layout');
            if ($('body').hasClass('home')) {
                console.log('home masonry 1111');
            }
            if ($('.isotop_elements.i_show').length < 5) {
                $('.load_more_posts_btn').trigger("click");
            }
        }
        return false;
    }

    function set_maxHeight() {
        var maxHeight = Math.max.apply(null, $(".i_posts_container div.grid-item.i_show").map(function () {
            return $(this).height();
        }).get());
    }

    var load_more_info = {
        'term_id': $('.load_more_posts').attr('data-all_term_ids'),
        'paged': '1'
    };

    function i_load_more_posts(load_more_el) {
        var posts_style = load_more_el.attr('data-style');
        if ($('.i_filters.active').attr('data-term_id') == 'all') {
            load_more_el.attr('data-term_id', load_more_el.attr('data-all_term_ids'));
        } else {
            load_more_el.attr('data-term_id', $('.i_filters.active').attr('data-term_id'));
        }

        var request_info = {
            'action': 'i_load_more_posts',
            'post_type': load_more_el.attr('data-post_type'),
            'taxonomy': load_more_el.attr('data-taxonomy'),
            'term_id': load_more_el.attr('data-term_id'),
            'posts_per_page': load_more_el.attr('data-posts_per_page'),
            'paged': load_more_el.attr('data-paged'),
            'offset': load_more_el.attr('data-offset'),
            'posts_item_view_btn_text': load_more_el.attr('data-posts_item_view_btn_text'),
            'search': load_more_el.attr('data-search'),
            'style': load_more_el.attr('data-style'),
        };
        if (load_more_el.hasClass('load_more_posts_btn')) {
            load_more_el.text('Loading...');
        }
        $.post(i_infos.ajax_url, request_info).done(function (data) {
            data = JSON.parse(data);
            console.log(data);
            var html_array = data.html_array;
            var html_second_array = data.html_second_array;

            var $items = '';
            var a_counter = a_global_appended_post_count;
            if (Object.keys(html_array).length) {
                $.each(html_array, function (key, value) {
                    if (a_counter < a_global_post_count) {
                        if (!$('#i_' + key).length || ($('#i_' + key).hasClass('hide_for_all') && $('[data-filter=all]>a').hasClass('active'))) {
                            $items += value;
                            a_counter = a_counter + 1;
                            a_global_appended_post_count = a_global_appended_post_count + 1;
                        }
                    }
                });
            }
            var diff_count = 0;
            if (a_counter > 0) {
                if (Object.keys(html_second_array).length) {
                    $.each(html_second_array, function (key, value) {
                        if (diff_count < a_global_post_count - a_counter) {
                            if (!$('#i_' + key).length || ($('#i_' + key).hasClass('hide_for_all') && $('[data-filter=all]>a').hasClass('active'))) {
                                $items += value;
                                diff_count = diff_count + 1;
                                a_global_appended_post_count = a_global_appended_post_count + 1;
                            }
                        } else {
                            return false;
                        }
                    });
                }

            }
            console.log($items);
            $items = $($items);

            load_more_el.attr('data-paged', Number(load_more_el.attr('data-paged')) + 1);
            $('.i_filters.active').attr('data-paged', Number($('.i_filters.active').attr('data-paged')) + 1);
            load_more_info.paged += 1;
            console.log($items);
            $('.isotope.i_posts_container').append($items);
//                $grid.append( $items ).isotope( 'appended', $items ).isotope( 'layout' );;
//                $(".isotope .grid-item .image img").one("load", function() {
//                    $grid.isotope('layout');
////                    $grid.isotope({ 
////                        sortBy : 'timestamp',
////                        resizesContainer: true,
////                        sortAscending : false
////                    }); 
//                    console.log('sfsdfsfsfadsdffgdfgdgssdf')
//                }).each(function() {
//                    if(this.complete) $(this).load();
//                });

            if (data.finished == '1') {
                load_more_el.hide();
            }

            if (load_more_el.hasClass('load_more_posts_btn')) {
                load_more_el.text('Load More');
            }

            $('.i_animate_appear').each(function (index) {
                var li = $(this);

                setTimeout(function () {
                    li.fadeTo(300, 1, function () {
                        $(this).removeClass('i_animate_appear');
                    });
                }, 300 + index);
            });
            $('.i_visibility_appear').each(function (index) {
                var li = $(this);

                setTimeout(function () {
                    li.removeClass('i_visibility_appear');
                }, 1300 + index);
            });
            scroll_wait = false;
            if (a_global_appended_post_count < a_global_post_count) {
                console.log(a_global_appended_post_count);
                console.log(11212121);
                $(".load_more_posts_btn").click();
            } else {
                console.log(34343443);
                a_global_appended_post_count = 0;
            }
        });
    }


    $('.i_filters').click(function () {

        var filters_offset_top = $(this).parents('#filter-toggle-block').offset().top;
        var main_header_height = $('.filter-toggle').outerHeight() + 20;
        $('html, body').animate({
            scrollTop: parseInt(filters_offset_top - main_header_height)
        }, 0);
    });
//    $('.filter-toggle li:not(.filter_hamburger_button)').click(function () {
//        $('.filter-toggle').removeClass('filter_opened');
//    })


    $('body:not(.logged-in) #header_advertisement a').click(function (event) {
//        console.log('iframe');
        ad_clicks_count_request(1);
    });
//    $('body:not(.logged-in) .stiky_sidebar .advertisement_sidebar .textwidget iframe').iframeTracker(function(event) {
//        console.log('iframe');
//        ad_clicks_count_request(1);
//    });
    $('body:not(.logged-in) #sidebar .subscribe_ad a').click(function (event) {
//        event.preventDefault();
        ad_clicks_count_request(2);
    });


    function ad_clicks_count_request(ad_id) {
        $.ajax({
            type: "POST",
            url: i_infos.ajax_url,
            data: {
//                    checked_checkboxes: checked_checkboxes,
                action: 'ad_click_count',
                ad_id: ad_id
            },
            success: function (response) {
                console.log(response);
//                window.open(redirect_url, '_blank');
            }
        });
    }


    /*$('#sidebar').stickySidebar({
        topSpacing: $('#header').outerHeight(),
        innerWrapperSelector: '#primary_sidebar',
        containerSelector: '#content',
        bottomSpacing: $('#footer').outerHeight(),
    });*/ 
    a_stick_height = $('.left_and_sidebar').outerHeight()-$('#primary_sidebar').outerHeight()-250;
    console.log(a_stick_height);
    $('#a_sticky_sidebar_parent').css('min-height', a_stick_height);
    $('#a_sticky_sidebar').stickySidebar({
        topSpacing: $('#header').outerHeight(),
        innerWrapperSelector: '#sticky_sidebar_inner',
        containerSelector: '#a_sticky_sidebar_parent',
        bottomSpacing: $('#footer').outerHeight()+20,
    });
    /*$( ".stiky_sidebar" ).each(function( index ) {
                                    $( this ).clone().appendTo( "#stiky_sidebar_block" );
    //                        $(this).css('top',(20 + prev_el_ehight));
    //                        prev_el_ehight = $(this).height();
                      });

        /*$(window).scroll(function () {
            var prev_el_ehight = 0;
            var fixSidebar = $('#header').innerHeight();
            var hero = $('.hero');
            var contentHeight = $('.content_left_part').innerHeight();
            var main_left_height = $('.content_left_part').height();
            var sidebarHeight = $('.stiky_sidebar').height();
            var sidebar_height = $('#sidebar').height();
            var footer_height = $('#footer').innerHeight();
            if (hero.length > 0) {
                var hero_inner_height = $('.hero').innerHeight();
                fixSidebar = fixSidebar + hero_inner_height;
            }
            var sidebarBottomPos = contentHeight - sidebarHeight;
            var trigger = $(window).scrollTop() - fixSidebar;
            if ($('#sidebar').length) {
                var page_sidebar_offset_top = $('#sidebar').offset().top;
                var page_sidebar_outer_height = $('#sidebar').outerHeight();
                var page_sidebr_offset_bootom = parseInt(page_sidebar_offset_top + page_sidebar_outer_height);
                if (main_left_height > sidebar_height) {
                    if ($(window).scrollTop() >= page_sidebr_offset_bootom) {
                        $('#stiky_sidebar_block').addClass('fixed');
                        $('#stiky_sidebar_block').css('max-height',($(window).height() - 2*footer_height - 168));

                    } else {
                        $('#stiky_sidebar_block').removeClass('fixed');
                    }

    //                if (trigger >= sidebarBottomPos) {
    //                    $('#stiky_sidebar_block').addClass('bottom');
    //                } else {
    //                    $('#stiky_sidebar_block').removeClass('bottom');
    //                }
                }
            }

        }).scroll();
        $(window).resize(function () {
            var sidebar_width = $('#sidebar').width();
            $('.stiky_sidebar').css("width", sidebar_width);
        }).resize();*/
    
    var a_blog_posts_page = 2;
    $('.loadmore').click(function () {
        $.ajax({
            type: "POST",
            url: i_infos.ajax_url,
            data: {
                action: 'load_posts_by_ajax_callback',
                a_blog_posts_page : a_blog_posts_page
            },
            success: function (response) {
                console.log(response);
                $('.all_posts .row').append(response);
            a_blog_posts_page++;
            }
        });
    });

});


jQuery( window ).load(function($) {
  
            header_height = jQuery('#header').outerHeight();
            jQuery('#content_parent.site-content').css('margin-top', header_height + 20);
});