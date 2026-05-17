<?php
/**
 * Template part: Footer (no Elementor)
 */
?>
<footer id="pxl-footer" class="pxl-footer footer-type-df footer-layout-0">
	<div class="pxl-footer-bottom">
		<div class="container">
			<div class="row justify-content-center align-items-center">
				<div class="col-12 col-md-auto text-center">
					<div class="pxl-copyright-text pxl-footer-copyright">
						<?php
						printf(
							/* translators: 1: year, 2: site name */
							esc_html__( 'Copyright © %1$s %2$s. All Rights Reserved.', 'molochko' ),
							date( 'Y' ),
							'<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html( get_bloginfo( 'name' ) ) . '</a>'
						);
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
</footer>
