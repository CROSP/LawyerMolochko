<?php
/**
 * Include the TGM_Plugin_Activation class.
 */
get_template_part( 'inc/admin/libs/tgmpa/class-tgm-plugin-activation' );

add_action( 'tgmpa_register', 'powerlegal_register_required_plugins' );
function powerlegal_register_required_plugins() {
    $pxl_server_info = apply_filters( 'pxl_server_info', ['plugin_url' => ''] ) ;
    $default_path = $pxl_server_info['plugin_url'];  
    $images = get_template_directory_uri() . '/inc/admin/assets/img/plugins';
    $plugins = array(
        array(
            'name'               => esc_html__('Redux Framework', 'powerlegal'),
            'slug'               => 'redux-framework',
            'required'           => true,
            'logo'        => $images . '/redux.png',
            'description' => esc_html__( 'Build theme options and post, page options for WordPress Theme.', 'powerlegal' ),
        ),
        array(
            'name'               => esc_html__('Elementor', 'powerlegal'),
            'slug'               => 'elementor',
            'required'           => true,
            'logo'        => $images . '/elementor.png',
            'description' => esc_html__( 'Introducing a WordPress website builder, with no limits of design. A website builder that delivers high-end page designs and advanced capabilities', 'powerlegal' ),
        ),
        array(
            'name'               => esc_html__('Pxl Theme Core', 'powerlegal'),
            'slug'               => 'pxltheme-core',
            'source'             => 'pxltheme-core.zip',
            'required'           => true,
            'logo'        => $images . '/author-logo.jpg',
            'description' => esc_html__( 'Main process and Powerful Elements Plugin, exclusively for Powerlegal WordPress Theme.', 'powerlegal' ),
        ),
        array(
            'name'               => esc_html__('Revolution Slider', 'powerlegal'),
            'slug'               => 'revslider',
            'source'             => 'revslider.zip',
            'required'           => true,
            'logo'        => $images . '/rev-slider.png',
            'description' => esc_html__( 'Helps beginner-and mid-level designers WOW their clients with pro-level visuals.', 'powerlegal' ),
        ),
        array(
            'name'               => esc_html__('Contact Form 7', 'powerlegal'),
            'slug'               => 'contact-form-7',
            'required'           => true,
            'logo'        => $images . '/contact-f7.png',
            'description' => esc_html__( 'Contact Form 7 can manage multiple contact forms, you can customize the form and the mail contents flexibly with simple markup.', 'powerlegal' ),
        ),
        array(
            'name'               => esc_html__('WooCommerce', 'powerlegal'),
            'slug'               => "woocommerce",
            'required'           => true,
            'logo'        => $images . '/woo.png',
            'description' => esc_html__( 'WooCommerce is the world’s most popular open-source eCommerce solution.', 'powerlegal' ),
        ),
        array(
            'name'               => esc_html__('Mailchimp for WordPress', 'powerlegal'),
            'slug'               => "mailchimp-for-wp",
            'required'           => true,
            'logo'        => $images . '/mailchimp.png',
            'description' => esc_html__( 'Allowing your visitors to subscribe to your newsletter should be easy. With this plugin, it finally is.', 'powerlegal' ),
        ),
        array(
            'name'               => esc_html__('ProfilePress', 'powerlegal'),
            'slug'               => 'wp-user-avatar',
            'required'           => false,
            'logo'        => $images . '/wp-user.png',
            'description' => esc_html__( 'Allow registered users to upload & select their own avatars.', 'powerlegal' ),
        ),
        array(
            'name'               => esc_html__('Wishlist for WooCommerce', 'powerlegal'),
            'slug'               => "woo-smart-wishlist",
            'required'           => false,
            'logo'        => $images . '/woo-smart-wishlist.png',
            'description' => esc_html__( 'WPC Smart Wishlist is a simple but powerful tool that can help your customer save products for buying later.', 'powerlegal' ),
        ),
        array(
            'name'               => esc_html__('Classic Editor', 'powerlegal'),
            'slug'               => "classic-editor",
            'required'           => false,
            'logo'        => $images . '/classic-editor.png',
            'description' => esc_html__( 'Classic Editor is an official plugin maintained by the WordPress team that restores the “classic” WordPress editor and the “Edit Post” screen.', 'powerlegal' ),
        ),
    );
 

    $config = array(
        'default_path' => $default_path,           // Default absolute path to pre-packaged plugins.
        'menu'         => 'tgmpa-install-plugins', // Menu slug.
        'is_automatic' => true,
    );

    tgmpa( $plugins, $config );

}