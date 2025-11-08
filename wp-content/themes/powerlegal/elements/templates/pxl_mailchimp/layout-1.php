<?php if(class_exists('MC4WP_Container')) : ?>
	<div class="pxl-mailchimp d-flex layout-1 <?php echo esc_attr($settings['style']); ?>">
		<div class="col">
		<?php echo do_shortcode('[mc4wp_form]'); ?>
		</div>
	</div>
<?php endif; ?>
