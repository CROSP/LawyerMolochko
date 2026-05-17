( function( $ ) {
    /**
     * @param $scope The Widget wrapper element as a jQuery element
     * @param $ The jQuery alias
     */
    var PXLCountdownHandler = function( $scope, $ ) {
        $scope.find(".pxl-countdown").each(function(){
            if ($('.pxl-countdown-container').length > 0) {
                $('.pxl-countdown-container').each(function () {
                    var $this = $(this);
                    var div = $this;
                    var timeout = $this.data('time');
                    var end = new Date(timeout);
                    var _second = 1000;
                    var _minute = _second * 60;
                    var _hour = _minute * 60;
                    var _day = _hour * 24;
                    var timer = setInterval(function () {
                        var now = new Date();
                        var distance = end - now;
                        if (distance > 0) {
                            var days = Math.floor(distance / _day);
                            var hours = Math.floor((distance % _day) / _hour);
                            var minutes = Math.floor((distance % _hour) / _minute);
                            var seconds = Math.floor((distance % _minute) / _second);
                            div.find('.day').html(days < 10 ? '0' + days : days);
                            div.find('.hour').html(hours < 10 ? '0' + hours : hours);
                            div.find('.minute').html(minutes < 10 ? '0' + minutes : minutes);
                            div.find('.second').html(seconds < 10 ? '0' + seconds : seconds);
                            return;
                        }
                        clearInterval(timer);
                    }, 1000);
                })
            }
        });
    };

    // Make sure you run this code under Elementor.
    $( window ).on( 'elementor/frontend/init', function() {
        elementorFrontend.hooks.addAction( 'frontend/element_ready/pxl_countdown.default', PXLCountdownHandler );
    } );
} )( jQuery );