( function( $ ) {
    /**
     * @param $scope The Widget wrapper element as a jQuery element
     * @param $ The jQuery alias
     */
    function sep_grid_refresh($scope){
        $scope.find('.pxl-grid-masonry').each(function () {
            var iso = new Isotope(this, {
                itemSelector: '.grid-item',
                layoutMode: $(this).closest('.pxl-grid').attr('data-layout-mode'),
                fitRows: {
                    gutter: 0
                },
                percentPosition: true,
                masonry: {
                    columnWidth: '.grid-sizer',
                },
                containerStyle: null,
                stagger: 30,
                sortBy : 'name',
            });

            var filtersElem = $(this).closest('.pxl-grid').find('.grid-filter-wrap');
            filtersElem.on('click', function (event) {
                var filterValue = event.target.getAttribute('data-filter');
                iso.arrange({filter: filterValue});
            });

            var filterItem = $(this).closest('.pxl-grid').find('.filter-item');
            filterItem.on('click', function (e) {
                filterItem.removeClass('active');
                $(this).addClass('active');
                $(this).closest('.pxl-grid').find('.grid-item').removeClass('animated');
            });

            var filtersSelect = $(this).closest('.pxl-grid').find('.select-filter-wrap');
            filtersSelect.change(function() {
                var filters = $(this).val();
                iso.arrange({filter: filters});
            });

        });
    }
    
    
    var widget_post_masonry_handler = function( $scope, $ ) {
        $scope.find('.pxl-post-grid .pxl-grid-masonry').imagesLoaded(function(){
            if($(document).find('.elementor-editor-active').length > 0){
                let oldHTMLElement = HTMLElement;
                window.HTMLElement = window.parent.HTMLElement;
                sep_grid_refresh($scope);
                window.HTMLElement = oldHTMLElement;
            }else{
                sep_grid_refresh($scope);
            }
        });
    };

    $(document).on('click', '.btn-grid-loadmore', function(){
        var loadmore      = $(this).parents('.pxl-load-more').data('loadmore');
        var perpage       = loadmore.perpage;
        var _this         = $(this).parents(".pxl-grid");
        var loading_text  = $(this).parents('.pxl-load-more').data('loading-text');
        loadmore.paged    = parseInt(_this.data('start-page')) +1;
        $(this).addClass('loading');
        if(loadmore.filter == 'true'){
            $.ajax({
                url: main_data.ajax_url,
                type: 'POST',
                beforeSend: function () {

                },
                data: {
                    action: 'powerlegal_get_filter_html',
                    settings: loadmore,
                    loadmore_filter: 1
                }
            }).done(function (res) {
                if(res.status == true){
                    _this.find(".grid-filter-wrap").html(res.data.html);

                }
                else if(res.status == false){
                }
            }).fail(function (res) {
                return false;
            }).always(function () {
                return false;
            });
        }
        $.ajax({
            url: main_data.ajax_url,
            type: 'POST',
            beforeSend: function () {

            },
            data: {
                action: 'powerlegal_load_more_post_grid',
                settings: loadmore
            }
        })
        .done(function (res) {   
            if(res.status == true) {
                _this.find('.btn-grid-loadmore').removeClass('loading');
                _this.find('.pxl-grid-inner').append(res.data.html);
                _this.data('start-page', res.data.paged);
                elementorFrontend.waypoint(_this.find('.pxl-animate'), function () {
                    var $animate_el = $(this),
                        data = $animate_el.data('settings');
                    if(typeof data['animation'] != 'undefined'){
                        setTimeout(function () {
                            $animate_el.removeClass('pxl-invisible').addClass('animated ' + data['animation']);
                        }, data['animation_delay']);
                    }
                });
                sep_grid_refresh(_this);
                if(res.data.paged >= res.data.max){
                    _this.find('.pxl-load-more').hide();
                } 
            }  
            
        })
        .fail(function (res) {
            _this.find('.pxl-load-more').hide();
            return false;
        })
        .always(function () {
            return false;
        });
    });

    $(document).on('click', '.pxl-grid-pagination .ajax a.page-numbers', function(){
        var _this = $(this).parents(".pxl-grid");
        var loadmore = _this.find(".pxl-grid-pagination").data('loadmore');
        var query_vars = _this.find(".pxl-grid-pagination").data('query');

        var paged = $(this).attr('href');
        paged = paged.replace('#', '');
        loadmore.paged = parseInt(paged);
        query_vars.paged = parseInt(paged); 

        var _class = loadmore.pagin_align_cls;

        _this.find('.pxl-grid-overlay').addClass('loader');
        $('html,body').animate({scrollTop: _this.offset().top - 100}, 750);

        // reload pagination
        $.ajax({
            url: main_data.ajax_url,
            type: 'POST',
            beforeSend: function () {

            },
            data: {
                action: 'powerlegal_get_pagination_html',
                query_vars: query_vars,
                cls: _class 
            }
        }).done(function (res) { //console.log(res); return false;
            if(res.status == true){
                _this.find(".pxl-grid-pagination").html(res.data.html);
                _this.find('.pxl-grid-overlay').removeClass('loader');
            }
            else if(res.status == false){
            }
        }).fail(function (res) {
            return false;
        }).always(function () {
            return false;
        });

        // load post
        $.ajax({
            url: main_data.ajax_url,
            type: 'POST',
            beforeSend: function () {

            },
            data: {
                action: 'powerlegal_load_more_post_grid',
                settings: loadmore
            }
        }).done(function (res) { //console.log(res); return false;
            if(res.status == true){
                _this.find('.pxl-grid-inner .grid-item').remove();
                _this.find('.pxl-grid-inner').append(res.data.html);
                _this.data('start-page', res.data.paged);

                sep_grid_refresh(_this);
                
                elementorFrontend.waypoint(_this.find('.pxl-animate'), function () {
                    var $animate_el = $(this),
                        data = $animate_el.data('settings');
                    if(typeof data['animation'] != 'undefined'){
                        setTimeout(function () {
                            $animate_el.removeClass('pxl-invisible').addClass('animated ' + data['animation']);
                        }, data['animation_delay']);
                    }
                });  
            }
            else if(res.status == false){
            }
        }).fail(function (res) {
            return false;
        }).always(function () {
            return false;
        });
        return false;
    });

    // Make sure you run this code under Elementor.
    $( window ).on( 'elementor/frontend/init', function() {
        elementorFrontend.hooks.addAction( 'frontend/element_ready/pxl_post_grid.default', widget_post_masonry_handler );
        elementorFrontend.hooks.addAction( 'frontend/element_ready/pxl_post_list.default', widget_post_masonry_handler );
        elementorFrontend.hooks.addAction( 'frontend/element_ready/pxl_team_grid.default', widget_post_masonry_handler );
        elementorFrontend.hooks.addAction( 'frontend/element_ready/pxl_image_gallery.default', widget_post_masonry_handler );
    } );
     
} )( jQuery );