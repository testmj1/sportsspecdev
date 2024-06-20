// INITIALISE PAGE
$(document).ready(function () {
	mobileNavLogic();
	//stickyNav();
	waypointInit();
	owlHero();
	owlMobHero();
	owlShoveller();
	owlVideo();
	whatBP();
	megaMenu();
	sideMegaMenu();
	toggleAds();
	/*isotopeFiltersInit();*/
	hoverAni();

	/*$('a').click(function(e){
		e.preventDefault();
	})*/


});//doc.ready


$(window).load(function () {
	//advertOffset();
	/*isotopeFiltersInit();*/
	customScrollBars();



});

$(window).scroll(function() {
	//advertOffset();
});

var windowsize = $(window).width();
var isLarge = false;
var isMedium;
var isSmall;
var ismediumDown;

$(window).resize(function() {
	windowsize = $(window).width();
	whatBP();
});




function isotopeFiltersInit() {

	var container = $('.isotope');
	var layoutBtns = $('.layout-toggle');
	var layoutActive = layoutBtns.find('.active');
	var filterBtns = $('.filter-toggle');
	var filterActive = filterBtns.find('.active');
	var filterActiveValue = filterActive.attr('data-filter');

	//initialise isotope plugin
	container.isotope({
		itemSelector: '.grid-item',
		percentPosition: true,
		stagger: 20,
		transitionDuration: 0,
		filter: filterActiveValue,
		masonry: {
			columnWidth: '.grid-sizer',
			gutter: '.gutter-sizer'
		}
	});

	//layout buttons
	layoutBtns.on( 'click', 'a', function(e) {
		e.preventDefault();
		if(!$(this).hasClass('active')) {
			var layoutValue = $(this).attr('data-layout');

			$( '.isotope' ).animate({
				opacity: 0
			}, 400, function() {
				// Animation complete.
				container.removeClass('large small text-only');
				container.addClass(layoutValue).isotope('layout');

				setTimeout(function(){
					$( '.isotope' ).animate({ opacity: 1 });
				},300);
			});

			layoutBtns.find('a').removeClass('active');
			$(this).addClass('active');


		}
	});

	//filter buttons
	filterBtns.on( 'click', 'a', function(e) {
		e.preventDefault();

		if(!$(this).hasClass('active')) {

			//apply filter onclick
			var filterValue = $(this).attr('data-filter');
			container.isotope({ filter: filterValue });
			//active state conrols
			filterBtns.find('a').removeClass('active');
			$(this).addClass('active');
		}
	});

}//isotopeInit


function whatBP() {

	if (windowsize <= 667) {
		isSmall = true;
		isMedium = false;
		isLarge = false;
		ismediumDown = false;
	}

	if ((windowsize >= 667) && (windowsize<= 1024)) {
		isSmall = false;
		isMedium = true;
		isLarge = false;
		ismediumDown = false;
	}

	if (windowsize > 1400) {
		isSmall = false;
		isMedium = false;
		isLarge = true;
		ismediumDown = false;
	}

/*	if (windowsize <= 667 || windowsize <= 1024) {
		isSmall = false;
		isMedium = true;
		isLarge = false;
		ismediumDown = true;
	}*/

	return [isSmall, isMedium, isLarge, ismediumDown];

}


function toggleAds() {
	var toggle = $('.toggle-box');
	var toggleText = toggle.find('span');
	var advertsLarge = $('.advert.large');
	var advertsMedium = $('.advert.medium');
	var advertsSmall = $('.advert.small');
	var advertsVisible = false;//change for page load option

	console.log(isSmall);

	$(window).resize(function() {
		if(isLarge) {
			advertsMedium.stop().slideUp();'400','easeInOutQuart'
			advertsSmall.stop().slideUp();'400','easeInOutQuart'
			if(advertsVisible) {
				advertsLarge.stop().slideDown('400','easeInOutQuart');
			} else {
				advertsLarge.stop().slideUp();'400','easeInOutQuart'
			}
		}
		if(isMedium) {
			advertsLarge.stop().slideUp();'400','easeInOutQuart'
			advertsSmall.stop().slideUp();'400','easeInOutQuart'
			if(advertsVisible) {
				advertsMedium.stop().slideDown('400','easeInOutQuart');
			} else {
				advertsMedium.stop().slideUp();'400','easeInOutQuart'
			}
		}
		if(isSmall) {
			advertsLarge.stop().slideUp();'400','easeInOutQuart'
			advertsMedium.stop().slideUp();'400','easeInOutQuart'
			if(advertsVisible) {
				advertsSmall.stop().slideDown('400','easeInOutQuart');
			} else {
				advertsSmall.stop().slideUp();'400','easeInOutQuart'
			}
		}
	});


	//on page load, set adverts display as per variable above
	if(advertsVisible) {
		toggleText.text('On');
		if(isLarge) {
			advertsLarge.stop().slideDown('400','easeInOutQuart');
		}
		if(isMedium) {
			advertsMedium.stop().slideDown('400','easeInOutQuart');
		}
		if(isSmall) {
			advertsSmall.stop().slideDown('400','easeInOutQuart');
		}
	} else {
		toggleText.text('Off');
		if(isLarge) {
			advertsLarge.stop().slideUp();'400','easeInOutQuart'
		}
		if(isMedium) {
			advertsMedium.stop().slideUp();'400','easeInOutQuart'
		}
		if(isSmall) {
			advertsSmall.stop().slideUp();'400','easeInOutQuart'
		}
	}

	//when click toggle button
	toggle.click(function(){

		if(advertsVisible) {
			advertsVisible = false;
			toggleText.text('Off');
			if(isLarge) {
				advertsLarge.stop().slideUp();'400','easeInOutQuart'
			}
			if(isMedium) {
				advertsMedium.stop().slideUp();'400','easeInOutQuart'
			}
			if(isSmall) {
				advertsSmall.stop().slideUp();'400','easeInOutQuart'
			}
		} else {
			advertsVisible = true;
			toggleText.text('On');
			if(isLarge) {
				advertsLarge.stop().slideDown('400','easeInOutQuart');
			}
			if(isMedium) {
				advertsMedium.stop().slideDown('400','easeInOutQuart');
			}
			if(isSmall) {
				advertsSmall.stop().slideDown('400','easeInOutQuart');
			}
		}

	});

}


function megaMenu() {
	var megaMenu = $('.mega-menu');
	var topLevelLink = $('ul.nav>li');
	var link = $('.mega-menu .all-links ul:first-of-type li');
	var stories = $('.mega-menu .related-stories');
	var followLink = $('ul.social>li:first-of-type');
	var followMenu = $('ul.social>li:first-of-type .follow-links-menu');
	var searchLink= $('ul.social>li.search');
	var searchMenu = $('ul.social>li.search .search-box');
	var searchMenuMob = $('.search-box-mob');

	topLevelLink.hover(function(){
		$(this).find(megaMenu).stop().fadeToggle().addClass('active');

		//add related stories first cat title to h2
		var sectionTitle = $(this).find('.mega-menu .all-links ul:first-of-type > li:first-of-type a').text();
		var destinationForTitle = stories.find($('h2 > span'));
		destinationForTitle.text(sectionTitle);
	});

	followLink.hover(function(){
		$(this).find(followMenu).stop().fadeToggle('fast').addClass('active');
	});



		searchLink.hover(function (e) {
			e.preventDefault();
			if (isLarge) {
				$(this).find(searchMenu).stop().fadeToggle('fast').addClass('active');
				$(this).find('input').focus();
			}
		});

		searchLink.click(function (e) {
			e.preventDefault();
			e.stopPropagation();
			if ((isMedium) || (isSmall)) {
				$(searchMenuMob).stop().fadeToggle('fast').toggleClass('active');
			}

		});

		searchMenuMob.click(function(e){
			e.stopPropagation();
		});

		$('body').click(function(){
				if((searchMenuMob).hasClass('active')) {
				console.log('body clicked');
				$(searchMenuMob).stop().fadeToggle('fast').toggleClass('active');
			}
		});


	//have one set of stories available on load
	stories.find('[data-index="2"]').addClass('active').fadeIn();

	link.mouseenter(function(){

		var sectionTitle = $(this).find('a').text();
		console.log(sectionTitle);
		var destinationForTitle = stories.find($('h2 > span'));
		destinationForTitle.text(sectionTitle);

		if($(this).attr('data-index')) {
			var id = $(this).data('index');
			console.log(id);
			stories.find('.active').removeClass('active').fadeOut(50, function(){
				stories.find('[data-index="'+ id +'"]').addClass('active').fadeIn(50);
			});

		}
	});

}//megaMenu

function sideMegaMenu() {
	var mobTopLevelLink = $('ul.links>li');
	var mobTopLevelLinkAnchor = $('ul.links>li>a');
	var mobMegaMenu = mobTopLevelLink.find('.mega-menu');

//	mobTopLevelLinkAnchor.click(function(e){
//		e.preventDefault();
//	});

	mobTopLevelLink.click(function(){
		$(this).find(mobMegaMenu).stop().slideToggle('400','easeInOutQuart').toggleClass('active');
		$(this).toggleClass('active');
	});

}//sideMegaMenu

var advertOffset = function() {
	var ad = $('.js-follow-ad');
	var adInner = $('.js-follow-ad > div')
	var footer = $('footer');
	var adFromTop = $(ad).offset().top - $(window).scrollTop();
	var bottomOfAd = ad.position().top + adInner.outerHeight(true);
	var footerFromTop = $(footer).offset().top - $(window).scrollTop();
	var adHeight = adInner.outerHeight(true);
	var newtopVal = $('.main-left').outerHeight(true) - adInner.outerHeight(true);

	/*	console.log('footer is' + footerFromTop);
	 console.log('bottom Of Ad is' + bottomOfAd);
	 console.log('adFromTop is' + adFromTop);
	 console.log('adHeight is' + adHeight);*/

	if(adFromTop <= 110) {
		$(adInner).css('top','110px');
		$(adInner).css('position','fixed');
	} else {
		$(adInner).css('top','auto');
		$(adInner).css('position','relative');
	}


	if(adHeight >= footerFromTop - 110) {
		$(adInner).css('position','absolute');
		$(adInner).css('top',newtopVal);
	}
};



function owlHero() {

	$('.owl-hero').owlCarousel({
		slideSpeed : 2000,
		paginationSpeed : 2000,
                smartSpeed:2000,
		pagination :false,
		addClassActive : true,
		loop:true,
		autoplay:true,
		autoplayTimeout:6000,
		autoplayHoverPause:true,
		slideBy:1,
		navRewind:true,
		navigation:false,
		responsive:{
			0:{
				items:1
			},
			668:{
				items:1
			},
			1024:{
				items:1
			}
		}

	});

	$('.hero-control-next').click(function() {
		$('.owl-hero').trigger('next.owl.carousel');
	});

	$('.hero-control-prev').click(function() {
		$('.owl-hero').trigger('prev.owl.carousel');
	})
}



function owlMobHero() {

	$('.owl-mob-hero').owlCarousel({
		slideSpeed : 2000,
		paginationSpeed : 2000,
                smartSpeed:2000,
		pagination :false,
		addClassActive : true,
		loop:true,
		autoplay:true,
		autoplayTimeout:6000,
		autoplayHoverPause:true,
		slideBy:1,
		navRewind:true,
		navigation:false,
		responsive:{
			0:{
				items:1
			},
			668:{
				items:1
			},
			1024:{
				items:1
			}
		}

	});

	$('.hero-control-next').click(function() {
		$('.owl-hero').trigger('next.owl.carousel');
	});

	$('.hero-control-prev').click(function() {
		$('.owl-hero').trigger('prev.owl.carousel');
	})
}




function owlShoveller() {

	$('.owl-shoveller').owlCarousel({
		slideSpeed : 2000,
		paginationSpeed : 2000,
                smartSpeed:2000,
		pagination :true,
		items : 6,
		addClassActive : true,
		loop:true,
		autoplay:false,
		autoplayTimeout:6000,
		autoplayHoverPause:true,
		dots:false,
		responsive:{
			0:{
				items:1
			},
			668:{
				items:2
			},
			1024:{
				items:3
			},
			1025:{
				items:4
			}
		}
	});
	$('.shoveller-control-next').click(function() {
		$('.owl-shoveller').trigger('next.owl.carousel');
	});

	$('.shoveller-control-prev').click(function() {
		$('.owl-shoveller').trigger('prev.owl.carousel');
	})
}

function owlVideo() {

	$('.owl-video').owlCarousel({
		slideSpeed : 2000,
		paginationSpeed : 2000,
                smartSpeed:2000,
		pagination :true,
		items : 4,
		addClassActive : true,
		loop:true,
		autoplay:false,
		autoplayTimeout:6000,
		autoplayHoverPause:true,
		dots:false,
		responsive:{
			0:{
				items:1
			},
			600:{
				items:3
			},
			1000:{
				items:4
			}
		}
	})
	$('.video-control-next').click(function() {
		$('.owl-video').trigger('next.owl.carousel');
	});

	$('.video-control-prev').click(function() {
		$('.owl-video').trigger('prev.owl.carousel');
	})
}






function stickyNav() {
	var sticky = new Waypoint.Sticky({
		element: $('header.main-header')[0]
	})
}

var waypointInit = function() {
	$('.hero').waypoint(function(direction) {
		if (direction === 'down') {
			$('header.main-header').addClass('shrink');
		}
		else {
			$('header.main-header').removeClass('shrink');
		}
	}, { offset: '-300px' });
}



//off-canvas-nav BOOM
var mobBtnOpen = $('header .open-nav-btn'); // class of the open button
var mobBtnClose = $('.menu div.close-nav-btn'); // class of the open button
var header = $('header.main-header');
var menu = $('.menu');
var cover = $('.blanket-cover');


function mobileNavLogic() {
	$(mobBtnOpen).stop().click(function(){
		if(!$('html').hasClass('js-nav-active')){
			openMobileNav();
		} else {
            closeMobileNav();
        }
		return false;
	});

	$(mobBtnClose).add(cover).stop().click(function(){
		if($('html').hasClass('js-nav-active')){
			closeMobileNav();
		}
		return false;
	});
}

function openMobileNav() {
	$('html').addClass('js-nav-active');
	cover.addClass('active');

	menu.addClass('opening');

}//openMobileNav

function closeMobileNav() {
	$('html').removeClass('js-nav-active');
	cover.removeClass('active');
	menu.addClass('closing');



	setTimeout(function() {
		menu.removeClass('closing opening');
	}, 600);

}//closeMobileNav



function customScrollBars() {

	$(".menu .scroll-content").mCustomScrollbar({
		axis:"y",
		theme:"light",
		scrollbarPosition:"inside",
		autoHideScrollbar:true
	});

}//customScrollBars






function hoverAni() {

	if (!Modernizr.touchevents) {

		var gridItem = $('.grid-item:not(.promo)');
		var heroImageItem = $('section.hero .item');

		//grid-item-hover
		gridItem.each(function(index, element){
			var tag = $(this).find('span.tag');
			var letters = new SplitText(tag, {type: 'words,chars'});
			var svg = $(this).find('.image svg');
			var img = $(this).find('.image img');
			var arrows = svg.find('#p1,#p2,#p3,#p4,#p5,#p6,#p7,#p8,#p9,#p10');
			var arrowsRed = svg.find('#p1,#p3,#p5,#p7,#p9');
			var arrowsWhite = svg.find('#p2,#p4,#p6,#p8,#p10');

			var tl = new TimelineMax({paused:true});


			tl.set(arrows,{transformOrigin:'center center'});
			tl.set(arrowsRed,{transformOrigin:'center center', fill:'#e32'});
			tl.set(arrowsWhite,{transformOrigin:'center center', fill:'#fff'});

			tl.staggerTo(letters.chars, 0.3, {scale: 1.5, yoyo: true, repeat: 1, ease: Power2.easeOut}, 0.05, "sameTime")
					.to(tag, 0.3, {scale: 2, rotation: 12, yoyo: true, repeat: 1, ease: Power2.easeOut}, "sameTime")
					.to(tag, 0.3, {rotation: 12, x:'50%',y:'50%', yoyo: true, repeat: 1, ease: Back.easeOut.config(4)}, "sameTime")
					.staggerTo(arrows, .1, {opacity:0.8, yoyo: true, repeat: 1, ease: Power2.easeOut},-0.04, "sameTime");


			element.animation = tl;


			return tl;


		});

		gridItem.mouseenter(over);

		function over() {
			if(!this.animation.isActive()){
				this.animation.play(0);
			}
		}

		//hero-carousel-hover
		heroImageItem.each(function(index, element){
			var svg = $(this).find('.image svg');
			var arrows = svg.find('#p1,#p2,#p3,#p4,#p5,#p6,#p7,#p8,#p9,#p10');
			var arrowsRed = svg.find('#p1,#p3,#p5,#p7,#p9');
			var arrowsWhite = svg.find('#p2,#p4,#p6,#p8,#p10');

			var tl = new TimelineMax({paused:true});


			tl.set(arrows,{transformOrigin:'center center'});
			tl.set(arrowsRed,{transformOrigin:'center center', fill:'#e32'});
			tl.set(arrowsWhite,{transformOrigin:'center center', fill:'#fff'});

			tl.staggerTo(arrows, .1, {opacity:0.8, yoyo: true, repeat: 1, ease: Power2.easeOut},-0.04, "sameTime");


			element.animation = tl;


			return tl;


		});

		heroImageItem.mouseenter(over2);

		function over2() {
			if(!this.animation.isActive()){
				this.animation.play(0);
			}
		}


	}//modernizr touch events false
}//hoverAni






function disable_scroll() {
	window.onwheel = function(){ return false; }
	window.onmousewheel = document.onmousewheel = wheel;
	document.onkeydown = keydown;
	document.body.ontouchmove = touchmove;
}

function enable_scroll() {
	window.onmousewheel = window.onwheel = document.onmousewheel = document.onkeydown = document.body.ontouchmove = null;
}
