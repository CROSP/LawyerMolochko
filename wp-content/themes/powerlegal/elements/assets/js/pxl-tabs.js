( function( $ ) {
    var PXLTabsHandler = function( $scope, $ ) {
        $scope.find(".pxl-tabs .tabs-title .tab-title").on("click", function(e){
            e.preventDefault();
            var target = $(this).data("target");
            $(this).addClass('active').siblings().removeClass('active');
            $(target).addClass('active').siblings().removeClass('active'); 
            $(target).siblings().find('.pxl-animate').each(function(){
                var data = $(this).data('settings');
                $(this).removeClass('animated '+data['animation']).addClass('pxl-invisible');
            });
            $(target).find('.pxl-animate').each(function(){
                var data = $(this).data('settings');
                var cur_anm = $(this);
                setTimeout(function () {  
                    $(cur_anm).removeClass('pxl-invisible').addClass('animated ' + data['animation']);
                }, data['animation_delay']);

            });
        });
    };

    var Pxl_Tablist_Animation_Handler = function( $scope, $ ) {

        $scope.find('.tl-item').on({
            mouseenter: function mouseenter() {
                $(this).addClass('active');
                $(this).siblings('.tl-item').removeClass('active');
            },
            /*mouseleave: function mouseleave() {
                this.swiper.autoplay.start();
            }*/
        });
    };
    // Make sure you run this code under Elementor.
    $( window ).on( 'elementor/frontend/init', function() {
        elementorFrontend.hooks.addAction( 'frontend/element_ready/pxl_tabs.default', PXLTabsHandler );
    } );
} )( jQuery );