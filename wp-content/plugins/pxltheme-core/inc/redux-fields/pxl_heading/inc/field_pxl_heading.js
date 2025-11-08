/* global redux_change */

/*global redux_change, redux*/

(
	function( $ ) {
		"use strict";

		redux.field_objects = redux.field_objects || {};

		$( document ).ready( function() {
			for( var prop in redux.field_objects ) {
				redux.field_objects[prop].init();
			}
			var $sectionWrapper = $( '.redux-section-collapse-wrapper' );

			if ( $sectionWrapper.length > 0 ) {
				$sectionWrapper.each( function() {
					var $el = $( this ),
						id = $el.data( 'id' ),
						$selector = $( '#redux-section-collapse-wrapper-' + id ),
						$table = $( '#section-table-' + id );

					$selector.on( 'click', function(e) {
						e.preventDefault();
	
						$( this ).toggleClass( 'active' );

						$table.slideToggle(
							0,
							'swing',
							function() {
								for( var prop in redux.field_objects ) {
									redux.field_objects[prop].init();
								}
							}
						);
					});
				});
			}

			$( 'body' ).on(
				'check_dependencies',
				function( e, variable ) {
					e = null;
					var current;
					var id;
					var container;
					var isHidden;
					 
					if ( null === redux.optName.required ) {
						return;
					}
					current = $( variable );
					id      = current.parents( '.redux-field:first' ).data( 'id' );

					if ( ! redux.optName.required.hasOwnProperty( id ) ) {
						return;
					}

					container = current.parents( '.redux-field-container:first' );
					isHidden  = container.parents( 'tr:first' ).hasClass( 'hide' );

					if ( ! container.parents( 'tr:first' ).length ) {
						isHidden = container.parents( '.customize-control:first' ).hasClass( 'hide' );
					}

					$.each(
						redux.optName.required[id],
						function( child ) {
							var pxl_heading_div;
							var show          = false;
							var childFieldset = $( '#' + redux.optName.args.opt_name + '-' + child );

							if( childFieldset.closest('.redux-container-pxl_heading').length > 0 ){
								pxl_heading_div = childFieldset.closest('.form-table').next('.indent-section-container');
								if(pxl_heading_div.length <= 0){
									pxl_heading_div = childFieldset.closest('.form-table').next('.redux-section-collapse-wrapper');
								}
							}
							 
							if ( ! isHidden ) {
								show = $.redux.check_parents_dependencies( child );
							}
							if ( true === show ) {
								if (typeof pxl_heading_div !== 'undefined') {
									pxl_heading_div.fadeIn(
										300,
										function() {
											$( this ).removeClass( 'hide' );
										}
									);
								}
							} else if ( false === show ) {
								if (typeof pxl_heading_div !== 'undefined') {
									pxl_heading_div.fadeOut(
										100,
										function() {
											$( this ).addClass( 'hide' );
										}
									);
								}
							}
						}
					); 
				}
			);
		});
	}
)( jQuery );
