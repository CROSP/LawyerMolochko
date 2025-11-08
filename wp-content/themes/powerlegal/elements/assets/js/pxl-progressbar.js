( function( $ ) {
    /**
     * @param $scope The Widget wrapper element as a jQuery element
     * @param $ The jQuery alias
     */
    var PXLProgressBarHandler = function( $scope, $ ) {
        elementorFrontend.waypoint($scope.find('.pxl-progress-bar'), function () {
            $(this).progressbar();
        });

    };

    // Make sure you run this code under Elementor.
    $( window ).on( 'elementor/frontend/init', function() {
        elementorFrontend.hooks.addAction( 'frontend/element_ready/pxl_progressbar.default', PXLProgressBarHandler );
    } );
} )( jQuery );