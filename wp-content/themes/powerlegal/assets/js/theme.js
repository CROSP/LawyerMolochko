;(function ($) {
    "use strict";
    var scroll_top;
    var window_height;
    var window_width;
    var scroll_status = '';
    var lastScrollTop = 0;
    $( document ).ready( function() {
        powerlegal_header_sticky();
        powerlegal_open_menu_toggle();
        powerlegal_panel_mobile_menu();
        powerlegal_cart_toggle();
        powerlegal_panel_anchor_toggle();
        powerlegal_sidebar_tabs_toggle();
        powerlegal_document_click();
        powerlegal_scroll_to_top();
        powerlegal_footer_fixed();
        powerlegal_magnific_popup();
        powerlegal_fancyBoxAccordion();
        powerlegal_svgDrawing();

        //* For Shop
        powerlegal_shop_view_layout();
        powerlegal_wc_single_product_gallery();
        powerlegal_quantity_plus_minus();
        powerlegal_quantity_plus_minus_action();
        powerlegal_table_cart_content();
        powerlegal_table_move_column('.woocommerce-cart-form__contents', '.woocommerce-cart-form__cart-item' ,0, 5, '', '.product-subtotal', '');
    });
    $(window).on('load', function () {
        setTimeout(function() {
            $('#pxl-loadding.default').addClass('preloaded');
        }, 800);
        $("#pxl-loadding.content-image").fadeOut("slow");
        setTimeout(function() {
            $('#pxl-loadding').remove();
        }, 3000);
        wowInit();
    });
    $(window).on('scroll', function () {
        scroll_top = $(window).scrollTop();
        window_height = $(window).height();
        window_width = $(window).width();
        if (scroll_top < lastScrollTop) {
            scroll_status = 'up';
        } else {
            scroll_status = 'down';
        }
        lastScrollTop = scroll_top;
        powerlegal_header_sticky();
        powerlegal_scroll_to_top();
    });
    jQuery( document ).on( 'updated_wc_div', function() {
        powerlegal_quantity_plus_minus();
        powerlegal_table_cart_content();
        powerlegal_table_move_column('.woocommerce-cart-form__contents', '.woocommerce-cart-form__cart-item' ,0, 5, '', '.product-subtotal', '');
    } );

    function powerlegal_header_sticky() {
        var offsetTop = $('.pxl-header-main').outerHeight();
        var h_header = $('.pxl-header-sticky').outerHeight();
        var offsetTopAnimation = offsetTop + 200;
        if (scroll_top > offsetTopAnimation) {
            $(document).find('.pxl-header-sticky').addClass('h-fixed');
        } else {
            $(document).find('.pxl-header-sticky').removeClass('h-fixed');
        }
        if (window_width > 992) {
            $('.pxl-header-sticky').css({
                'height': h_header
            });
        }
        //* Mobile Sticky update
        if ( $(document).find('.pxl-header-mobile-sticky').length > 0 && window_width < 1200  ) {
            var offset_top = $('.pxl-header-mobile').outerHeight();

            if (scroll_top > offset_top) {
                $(document).find('.pxl-header-mobile-sticky').addClass('mh-fixed');
            }else{
                $(document).find('.pxl-header-mobile-sticky').removeClass('mh-fixed');
            }
        }
    }
    function powerlegal_open_menu_toggle(){
        'use strict';
        //* Add toggle button to parent Menu
        $('.sub-menu .current-menu-item').parents('.menu-item-has-children').addClass('current-menu-ancestor');
        $('.is-arrow .pxl-primary-menu > li.menu-item-has-children').append('<span class="main-menu-toggle"></span>');
        $('.pxl-mobile-menu li.menu-item-has-children').append('<span class="main-menu-toggle"></span>');
        $('.main-menu-toggle').on('click', function () {
            $(this).toggleClass('open');
            $(this).parent().find('> .sub-menu').toggleClass('submenu-open');
            $(this).parent().find('> .sub-menu').slideToggle();
        });

        //* Menu Dropdown
        var $menu = $('.pxl-main-navigation');
        $menu.find('.pxl-primary-menu li').each(function () {
            var $submenu = $(this).find('> ul.sub-menu');
            if ($submenu.length == 1) {
                $(this).hover(function () {
                    if ($submenu.offset().left + $submenu.width() > $(window).width()) {
                        $submenu.addClass('back');
                    } else if ($submenu.offset().left < 0) {
                        $submenu.addClass('back');
                    }
                }, function () {
                    $submenu.removeClass('back');
                });
            }
        });
    }
    function powerlegal_panel_mobile_menu(){
        'use strict';
        $(document).on('click','.btn-nav-mobile',function(e){
            e.preventDefault();
            e.stopPropagation();
            var target = $(this).attr('data-target');
            $(this).toggleClass('cliked');
            $(target).toggleClass('open');
            $('body').toggleClass('side-panel-open');
        });
    }
    function powerlegal_panel_anchor_toggle(){
        'use strict';
        $(document).on('click','.pxl-anchor.side-panel',function(e){
            e.preventDefault();
            e.stopPropagation();
            var target = $(this).attr('data-target');
            $(this).toggleClass('cliked');
            $(target).toggleClass('open');
            $('body').toggleClass('side-panel-open');
            setTimeout(function(){
                $('.pxl-search-form input[name="s"]').focus();
            },1000);
        });
    }
    function powerlegal_sidebar_tabs_toggle(){
        'use strict';
        $(".anchor-inner-item").first().addClass('active');
        $(document).on('click','.pxl-sidebar-tabs .anchor-link-item',function(e){
            e.preventDefault();
            e.stopPropagation();
            var target = $(this).attr('data-target');
            $(target).addClass('active').siblings().removeClass('active');
        });
    }
    function powerlegal_cart_toggle(){
        'use strict';
        $(document).on('click','.pxl-cart.side-panel',function(e){
            e.preventDefault();
            e.stopPropagation();
            var target = $(this).attr('data-target');
            $(this).toggleClass('cliked');
            $(target).toggleClass('open');
            $('body').toggleClass('side-panel-open');
            setTimeout(function(){
                $('.pxl-search-form input[name="s"]').focus();
            },1000);
        });
    }

    function powerlegal_document_click(){
        $(document).on('click',function (e) {
            var target = $(e.target);
            var check = '.btn-nav-mobile';

            if (!(target.is(check)) && target.closest('.pxl-hidden-template').length <= 0 && $('body').hasClass('side-panel-open')) {
                $('.btn-nav-mobile').removeClass('cliked');
                //$('.pxl-cart-toggle').removeClass('cliked');
                $('.pxl-hidden-template').removeClass('open');
                $('body').removeClass('side-panel-open');
            }
        });
        $(document).on('click','.pxl-close',function(e){
            e.preventDefault();
            e.stopPropagation();
            $(this).closest('.pxl-hidden-template').toggleClass('open');
            $('.btn-nav-mobile').removeClass('cliked');
            // $('.pxl-cart-toggle').removeClass('cliked');
            $('body').toggleClass('side-panel-open');
        });
    }

    //* Scroll To Top
    function powerlegal_scroll_to_top() {
        if (scroll_top < window_height) {
            $('.pxl-scroll-top').addClass('off').removeClass('on');
        }
        if (scroll_top > window_height) {
            $('.pxl-scroll-top').addClass('on').removeClass('off');
        }
        $('.pxl-scroll-top').click(function (e) {
            e.preventDefault();
            e.stopPropagation();
            $('html, body').stop().animate({scrollTop: 0}, 800);
        });
    }

    //* Footer Fixed
    function powerlegal_footer_fixed() {
        setTimeout(function(){
            var h_footer = $('.pxl-footer-fixed .footer-type-el').outerHeight() - 1;
            $('.pxl-footer-fixed #pxl-main').css('margin-bottom', h_footer + 'px');
        }, 600);
    }


    //* Wow Animation
    function wowInit() {
        var wow = new WOW(
            {
                boxClass:     'wow',      // animated element css class (default is wow)
                animateClass: 'animated', // animation css class (default is animated)
                offset:       0,          // distance to the element when triggering the animation (default is 0)
                mobile:       true,       // trigger animations on mobile devices (default is true)
                live:         true,       // act on asynchronously loaded content (default is true)
                callback:     function(box) {
                    // the callback is fired every time an animation is started
                    // the argument that is passed in is the DOM node being animated
                },
                scrollContainer: null,    // optional scroll container selector, otherwise use window,
                resetAnimation: true,     // reset animation on end (default is true)
            }
        );
        wow.init();
    }

    //* Video Popup
    function powerlegal_magnific_popup() {
        $('a.video-play-button').magnificPopup({
            type: 'iframe',
            mainClass: 'mfp-fade',
            removalDelay: 160,
            preloader: false,
            fixedContentPos: false
        });
        /* Images Light Box - Gallery:True */
        $('.images-light-box').each(function () {
            $(this).magnificPopup({
                delegate: 'a.light-box',
                type: 'image',
                gallery: {
                    enabled: true
                },
                mainClass: 'mfp-fade',
            });
        });
    }

    // FancyBox Accordion
    function powerlegal_fancyBoxAccordion() {
        var widgetList = jQuery('.pxl-fancy-box-accordion');
        if (!widgetList.length) {
            return;
        }
        widgetList.each(function () {
            var itemClass = '.box-item';
            jQuery(this)
                .find(itemClass + ':first-child')
                .addClass('active');
            jQuery(this)
                .find(itemClass)
                .on('mouseover', function () {
                    jQuery(this).addClass('active').siblings().removeClass('active');
                });
        });
    }

    // Svg Drawing
    function powerlegal_svgDrawing() {
        $(".svg-drawing").each(function(){
            var $selector = jQuery(this);
            $(window).scroll(function() {
                var hT = $selector.offset().top,
                    hH = $selector.outerHeight(),
                    wH = $(window).height(),
                    wS = $(this).scrollTop();
                if (wS > (hT-wH)){
                    $selector.find('.drawing').each(function () {
                        let path = $(this).get(0);
                        let length = path.getTotalLength();
                        path.style.strokeDasharray = length;
                        path.style.strokeDashoffset = length;
                    });
                    $selector.addClass('dr-start');
                }
            });

        });
    }

    function powerlegal_shop_view_layout(){

        $(document).on('click','.pxl-view-layout .view-icon a', function(e){
            e.preventDefault();
            if(!$(this).parent('li').hasClass('active')){
                $('.pxl-view-layout .view-icon').removeClass('active');
                $(this).parent('li').addClass('active');
                $(this).parents('.pxl-content-area').find('ul.products').removeAttr('class').addClass($(this).attr('data-cls'));
            }
        });
    }

    function powerlegal_wc_single_product_gallery(){
        'use strict';
        if(typeof $.flexslider != 'undefined'){
            $('.wc-gallery-sync').each(function() {
                var itemW      = parseInt($(this).attr('data-thumb-w')),
                    itemH      = parseInt($(this).attr('data-thumb-h')),
                    itemN      = parseInt($(this).attr('data-thumb-n')),
                    itemMargin = parseInt($(this).attr('data-thumb-margin')),
                    window_w = $(window).outerWidth(),
                    itemSpace  = itemH - itemW + itemMargin;
                var gallery_layout = window_w > 575 ? 'vertical' : 'horizontal';

                if($(this).hasClass('thumbnail-vertical')){
                    $(this).flexslider({
                        selector       : '.wc-gallery-sync-slides > .wc-gallery-sync-slide',
                        animation      : 'slide',
                        controlNav     : false,
                        directionNav   : true,
                        prevText       : '<span class="flex-prev-icon"></span>',
                        nextText       : '<span class="flex-next-icon"></span>',
                        asNavFor       : '.woocommerce-product-gallery',
                        direction      : gallery_layout,
                        slideshow      : false,
                        animationLoop  : false,
                        itemWidth      : itemW, // add thumb image height
                        itemMargin     : itemSpace, // need it to fix transform item
                        move           : 1,
                        start: function(slider){
                            var asNavFor     = slider.vars.asNavFor,
                                height       = $(asNavFor).height(),
                                height_thumb = $(asNavFor).find('.flex-viewport').height();
                            if(window_w > 575) {
                                slider.css({'max-height' : height_thumb, 'overflow': 'hidden'});
                                slider.find('> .flex-viewport > *').css({'height': height, 'width': ''});
                            }
                        }
                    });
                }
                if($(this).hasClass('thumbnail-horizontal')){
                    $(this).flexslider({
                        selector       : '.wc-gallery-sync-slides > .wc-gallery-sync-slide',
                        animation      : 'slide',
                        controlNav     : false,
                        directionNav   : true,
                        prevText       : '<span class="flex-prev-icon"></span>',
                        nextText       : '<span class="flex-next-icon"></span>',
                        asNavFor       : '.woocommerce-product-gallery',
                        slideshow      : false,
                        animationLoop  : false, // Breaks photoswipe pagination if true.
                        itemWidth      : itemW,
                        itemMargin     : itemMargin,
                        start: function(slider){

                        }
                    });
                };
            });
        }
    }

    function powerlegal_quantity_plus_minus(){
        "use strict";
        $( ".quantity input" ).wrap( "<div class='pxl-quantity'></div>" );
        $('<span class="quantity-button quantity-down"></span>').insertBefore('.quantity input');
        $('<span class="quantity-button quantity-up"></span>').insertAfter('.quantity input');
        // contact form 7 input number
        $('<span class="pxl-input-number-spin"><span class="pxl-input-number-spin-inner pxl-input-number-spin-up"></span><span class="pxl-input-number-spin-inner pxl-input-number-spin-down"></span></span>').insertAfter('.wpcf7-form-control-wrap input[type="number"]');
    }
    function powerlegal_ajax_quantity_plus_minus(){
        "use strict";
        $('<span class="quantity-button quantity-down"></span>').insertBefore('.quantity input');
        $('<span class="quantity-button quantity-up"></span>').insertAfter('.quantity input');
    }
    function powerlegal_quantity_plus_minus_action(){
        "use strict";
        $(document).on('click','.quantity .quantity-button',function () {
            var $this = $(this),
                spinner = $this.closest('.quantity'),
                input = spinner.find('input[type="number"]'),
                step = input.attr('step'),
                min = input.attr('min'),
                max = input.attr('max'),value = parseInt(input.val());
            if(!value) value = 0;
            if(!step) step=1;
            step = parseInt(step);
            if (!min) min = 0;
            var type = $this.hasClass('quantity-up') ? 'up' : 'down' ;
            switch (type)
            {
                case 'up':
                    if(!(max && value >= max))
                        input.val(value+step).change();
                    break;
                case 'down':
                    if (value > min)
                        input.val(value-step).change();
                    break;
            }
            if(max && (parseInt(input.val()) > max))
                input.val(max).change();
            if(parseInt(input.val()) < min)
                input.val(min).change();
        });
        $(document).on('click','.pxl-input-number-spin-inner',function () {
            var $this = $(this),
                spinner = $this.parents('.wpcf7-form-control-wrap'),
                input = spinner.find('input[type="number"]'),
                step = input.attr('step'),
                min = input.attr('min'),
                max = input.attr('max'),value = parseInt(input.val());
            if(!value) value = 0;
            if(!step) step=1;
            step = parseInt(step);
            if (!min) min = 0;
            var type = $this.hasClass('pxl-input-number-spin-up') ? 'up' : 'down' ;
            switch (type)
            {
                case 'up':
                    if(!(max && value >= max))
                        input.val(value+step).change();
                    break;
                case 'down':
                    if (value > min)
                        input.val(value-step).change();
                    break;
            }
            if(max && (parseInt(input.val()) > max))
                input.val(max).change();
            if(parseInt(input.val()) < min)
                input.val(min).change();
        });
    }
    function powerlegal_table_cart_content(){
        "use strict";
        var table = jQuery('.woocommerce-cart-form__contents'),
            table_head = table.find('thead');
        table_head.find('.product-remove').remove();
        table_head.find('.product-thumbnail').remove();
        table_head.find('.product-name').attr('colspan',2);
        table_head.find('tr').append('<th class="product-remove">&nbsp;</th>');
    }

    function powerlegal_table_move_column(table, selected ,from, to, remove, colspan, colspan_value) {
        "use strict";
        var rows = jQuery(selected, table);
        var cols;
        rows.each(function() {
            cols = jQuery(this).children('th, td');
            cols.eq(from).detach().insertAfter(cols.eq(to));
        });
        var rows_remove = jQuery(remove, table);
        rows_remove.each(function(){
            jQuery(this).remove(remove);
        });
        var colspan = jQuery(colspan, table);
        colspan.each(function(){
            jQuery(this).attr('colspan',colspan_value);
        });
    }
})(jQuery);