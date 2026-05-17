( function( $ ) {
    /**
     * @param $scope The Widget wrapper element as a jQuery element
     * @param $ The jQuery alias
     */
    var PXLCounterHandler = function( $scope, $ ) {
        setTimeout(function(){
            elementorFrontend.waypoint($scope.find('.pxl-counter-number-value'), function () {
                var $number = $(this),
                    data = $number.data();
                var el = $number[0];
                var startNumber = data['startnumber'], endNumber = data['endnumber'], separator = data['delimiter'], duration = data['duration'] ;
                od = new Odometer({
                    el: el,
                    value: startNumber,
                    format: separator,
                    theme: 'default'
                });
                od.update(endNumber);
            });
        }, 150);
    };

    // Make sure you run this code under Elementor.
    $( window ).on( 'elementor/frontend/init', function() {
        elementorFrontend.hooks.addAction( 'frontend/element_ready/pxl_counter.default', PXLCounterHandler );
    } );
} )( jQuery );