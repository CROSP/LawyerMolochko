/**
 * One page new version
 * @author PixelArt Team
 * @version 1.0.0
 */
(function ($) {
    "use strict";
    if (typeof(one_page_options) != "undefined") {
        one_page_options.speed = parseInt(one_page_options.speed);
        $('.is-one-page').on('click', function (e) {
            var _this = $(this);
            var _link = $(this).attr('href');
            var _id_data = e.currentTarget.hash;
            var _offset;
            var _data_offset = $(this).attr('data-onepage-offset');
            if(_data_offset) {
                _offset = _data_offset;
            } else {
                _offset = 0;
            }
            if ($(_id_data).length === 1) {
                var _target = $(_id_data);
                $('.pxl-onepage-active').closest('li.menu-item').removeClass('active');
                $('.pxl-onepage-active').removeClass('pxl-onepage-active');
                _this.closest('li.menu-item').addClass('active');
                _this.addClass('pxl-onepage-active');
                $('html, body').stop().animate({ scrollTop: _target.offset().top - _offset }, one_page_options.speed);   
                return false;
            } else {
                window.location.href = _link;
            }
            return false;
        });

        $('.is-one-page').each(function(index, item) {
            var target = $(item).attr('href');
            var el =  $(target);
            var _data_offset = (typeof $(item).attr('data-onepage-offset') !== 'undefined') ? $(item).attr('data-onepage-offset') : 0;
            var waypoint = new Waypoint({
                element: el[0],
                handler: function(direction) {
                    if(direction === 'down'){
                        $(item).closest('ul').find('li.menu-item.active').removeClass('active');
                        $(item).closest('ul').find('.pxl-onepage-active').removeClass('pxl-onepage-active');
                        $(item).closest('li.menu-item').addClass('active');
                        $(item).addClass('pxl-onepage-active');
                    }
                    else if(direction === 'up'){
                        var prev = $(item).parent().prev().find('.is-one-page');
                        $(item).closest('li.menu-item').removeClass('active');
                        $(item).removeClass('pxl-onepage-active');
                        if(prev.length > 0){
                            prev.closest('li.menu-item').addClass('active');
                            prev.addClass('pxl-onepage-active');
                        }
                    }
                    else{
                        console.log('abc');
                    }
                },
                offset: _data_offset,
            });
        });
        
        /*one_page_options.speed = parseInt(one_page_options.speed);
        $('.is-one-page').on('click', function (e) {
            var _id_data = e.currentTarget.hash;
            var _offset;
            var _data_offset = $(this).attr('data-onepage-offset');
            if(_data_offset) {
                _offset = _data_offset;
            } else {
                _offset = 0;
            }
            if ($(_id_data).length === 1) {
                var _target = $(_id_data);
                $('.pxl-onepage-active').closest('li.menu-item').removeClass('active');
                $('.pxl-onepage-active').removeClass('pxl-onepage-active');
                $(this).closest('li.menu-item').addClass('active');
                $(this).addClass('pxl-onepage-active');
                $('html, body').stop().animate({ scrollTop: _target.offset().top - _offset }, one_page_options.speed);   
                return false;
            } else {
                window.location.href = $(this).attr('href');
            }
            return false;
        });

        var pxl_section_header = '.pxl-section-header';
        $('.pxl-section-header').each(function() {
            var $header_type_onepage = $(this).find('.is-one-page');
            $($header_type_onepage).each(function(index, item) {
                var target = $(item).attr('href');
                var el =  $(target);
                var _data_offset = (typeof $(item).attr('data-onepage-offset') !== 'undefined') ? $(item).attr('data-onepage-offset') : 0;
                var waypoint = new Waypoint({
                    element: el[0],
                    handler: function(direction) {
                        if(direction === 'down'){
                            $header_type_onepage.closest('li.menu-item').removeClass('active');
                            $header_type_onepage.removeClass('pxl-onepage-active');
                            $(item).closest('li.menu-item').addClass('active');
                            $(item).addClass('pxl-onepage-active');
                        }
                        else if(direction === 'up'){
                            var prev = $(item).parent().prev().find('.is-one-page');
                            $(item).closest('li.menu-item').removeClass('active');
                            $(item).removeClass('pxl-onepage-active');
                            if(prev.length > 0){
                                prev.closest('li.menu-item').addClass('active');
                                prev.addClass('pxl-onepage-active');
                            }
                        }
                        else{
                            console.log('abc');
                        }
                    },
                    offset: _data_offset,
                });
            });
        });

        var op_menu_target_mobile = '.pxl-mobile-menu .is-one-page';
        $.each($(op_menu_target_mobile), function (index, item) {
            var target = $(item).attr('href');
            var el =  $(target);
            var _data_offset = (typeof $(item).attr('data-onepage-offset') !== 'undefined') ? parseInt($(item).attr('data-onepage-offset')) + 100 : 0;
             
            var waypoint = new Waypoint({
                element: el[0],
                handler: function(direction) {
                    if(direction === 'down'){
                        $(op_menu_target_mobile).closest('li.menu-item').removeClass('active');
                        $(op_menu_target_mobile).removeClass('pxl-onepage-active');
                        $(item).closest('li.menu-item').addClass('active');
                        $(item).addClass('pxl-onepage-active');
                    }
                    else if(direction === 'up'){
                        var prev = $(item).parent().prev().find('.is-one-page');
                        $(item).closest('li.menu-item').removeClass('active');
                        $(item).removeClass('pxl-onepage-active');
                        if(prev.length > 0){
                            prev.closest('li.menu-item').addClass('active');
                            prev.addClass('pxl-onepage-active');
                        }
                    }
                    else{
                        console.log('abc');
                    }
                },
                offset: _data_offset,
            });
        });*/
    }

})(jQuery);
