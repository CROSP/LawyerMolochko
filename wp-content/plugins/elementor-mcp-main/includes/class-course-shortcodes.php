<?php
/**
 * Course Shortcodes Class
 *
 * Handles custom shortcodes for displaying WooCommerce course products.
 *
 * @package ElementorMCP
 * @since 1.0.0
 */

namespace ElementorMCP;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Course Shortcodes class.
 *
 * @since 1.0.0
 */
class Course_Shortcodes {

	/**
	 * Instance.
	 *
	 * @since 1.0.0
	 * @access private
	 * @static
	 *
	 * @var Course_Shortcodes
	 */
	private static $_instance = null;

	/**
	 * Instance.
	 *
	 * Ensures only one instance of the class is loaded.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @return Course_Shortcodes An instance of the class.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		$this->register_shortcodes();
	}

	/**
	 * Register shortcodes.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function register_shortcodes() {
		add_shortcode( 'display_course_products', [ $this, 'render_course_products' ] );
	}

	/**
	 * Render course products shortcode.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_course_products( $atts ) {
		// Parse shortcode attributes
		$atts = shortcode_atts(
			[
				'category_id'    => 69, // Default to Courses category ID
				'columns'        => 3,
				'rows'           => 2,
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
				'show_price'     => 'yes',
				'show_button'    => 'yes',
				'button_text'    => 'View Course',
			],
			$atts,
			'display_course_products'
		);

		// Calculate posts per page
		$posts_per_page = intval( $atts['columns'] ) * intval( $atts['rows'] );

		// Query arguments for WooCommerce products
		$args = [
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => $posts_per_page,
			'orderby'        => $atts['orderby'],
			'order'          => $atts['order'],
			'tax_query'      => [
				[
					'taxonomy' => 'product_cat',
					'field'    => 'term_id',
					'terms'    => intval( $atts['category_id'] ),
				],
			],
		];

		// Get products
		$products = new \WP_Query( $args );

		// Start output buffering
		ob_start();

		if ( $products->have_posts() ) : ?>
			<style>
				.elementor-mcp-courses-grid {
					display: grid;
					grid-template-columns: repeat(<?php echo esc_attr( $atts['columns'] ); ?>, 1fr);
					gap: 30px;
					padding: 20px 0;
				}
				
				@media (max-width: 768px) {
					.elementor-mcp-courses-grid {
						grid-template-columns: repeat(2, 1fr);
						gap: 20px;
					}
				}
				
				@media (max-width: 480px) {
					.elementor-mcp-courses-grid {
						grid-template-columns: 1fr;
					}
				}
				
				.elementor-mcp-course-card {
					background: #fff;
					border-radius: 8px;
					box-shadow: 0 2px 10px rgba(0,0,0,0.08);
					overflow: hidden;
					transition: transform 0.3s ease, box-shadow 0.3s ease;
					display: flex;
					flex-direction: column;
				}
				
				.elementor-mcp-course-card:hover {
					transform: translateY(-5px);
					box-shadow: 0 5px 20px rgba(0,0,0,0.15);
				}
				
				.elementor-mcp-course-image {
					width: 100%;
					height: 200px;
					background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
					display: flex;
					align-items: center;
					justify-content: center;
				}
				
				.elementor-mcp-course-image-placeholder {
					color: white;
					font-size: 48px;
					opacity: 0.8;
				}
				
				.elementor-mcp-course-content {
					padding: 20px;
					flex-grow: 1;
					display: flex;
					flex-direction: column;
				}
				
				.elementor-mcp-course-title {
					margin: 0 0 10px 0;
					font-size: 20px;
					font-weight: 600;
					color: #1a1a1a;
					line-height: 1.3;
				}
				
				.elementor-mcp-course-title a {
					color: inherit;
					text-decoration: none;
				}
				
				.elementor-mcp-course-title a:hover {
					color: #0066cc;
				}
				
				.elementor-mcp-course-description {
					color: #666;
					font-size: 14px;
					line-height: 1.6;
					margin-bottom: 15px;
					flex-grow: 1;
				}
				
				.elementor-mcp-course-meta {
					display: flex;
					justify-content: space-between;
					align-items: center;
					margin-top: auto;
					padding-top: 15px;
					border-top: 1px solid #eee;
				}
				
				.elementor-mcp-course-price {
					font-size: 22px;
					font-weight: 700;
					color: #0066cc;
				}
				
				.elementor-mcp-course-price .sale-price {
					color: #e74c3c;
					margin-right: 8px;
				}
				
				.elementor-mcp-course-price .regular-price {
					text-decoration: line-through;
					color: #999;
					font-size: 18px;
					font-weight: 400;
				}
				
				.elementor-mcp-course-button {
					background: #0066cc;
					color: white;
					padding: 8px 20px;
					border-radius: 5px;
					text-decoration: none;
					font-weight: 500;
					transition: background 0.3s ease;
					display: inline-block;
				}
				
				.elementor-mcp-course-button:hover {
					background: #0052a3;
					color: white;
				}
				
				.elementor-mcp-course-badge {
					position: absolute;
					top: 15px;
					right: 15px;
					background: #e74c3c;
					color: white;
					padding: 5px 12px;
					border-radius: 20px;
					font-size: 12px;
					font-weight: 600;
					text-transform: uppercase;
				}
			</style>
			
			<div class="elementor-mcp-courses-grid">
				<?php while ( $products->have_posts() ) : $products->the_post();
					global $product;
					
					// Get product details
					$product_id = get_the_ID();
					$product_obj = wc_get_product( $product_id );
					$regular_price = $product_obj->get_regular_price();
					$sale_price = $product_obj->get_sale_price();
					$is_on_sale = $product_obj->is_on_sale();
					?>
					
					<div class="elementor-mcp-course-card">
						<?php if ( $is_on_sale ) : ?>
							<span class="elementor-mcp-course-badge">Sale</span>
						<?php endif; ?>
						
						<div class="elementor-mcp-course-image">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'medium' ); ?>
							<?php else : ?>
								<span class="elementor-mcp-course-image-placeholder">📚</span>
							<?php endif; ?>
						</div>
						
						<div class="elementor-mcp-course-content">
							<h3 class="elementor-mcp-course-title">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h3>
							
							<div class="elementor-mcp-course-description">
								<?php 
								$short_desc = $product_obj->get_short_description();
								if ( $short_desc ) {
									echo wp_trim_words( $short_desc, 20, '...' );
								} else {
									echo wp_trim_words( get_the_excerpt(), 20, '...' );
								}
								?>
							</div>
							
							<div class="elementor-mcp-course-meta">
								<?php if ( $atts['show_price'] === 'yes' ) : ?>
									<div class="elementor-mcp-course-price">
										<?php if ( $is_on_sale && $sale_price ) : ?>
											<span class="sale-price">$<?php echo esc_html( $sale_price ); ?></span>
											<span class="regular-price">$<?php echo esc_html( $regular_price ); ?></span>
										<?php else : ?>
											<span>$<?php echo esc_html( $regular_price ); ?></span>
										<?php endif; ?>
									</div>
								<?php endif; ?>
								
								<?php if ( $atts['show_button'] === 'yes' ) : ?>
									<a href="<?php the_permalink(); ?>" class="elementor-mcp-course-button">
										<?php echo esc_html( $atts['button_text'] ); ?>
									</a>
								<?php endif; ?>
							</div>
						</div>
					</div>
					
				<?php endwhile; ?>
			</div>
			
		<?php else : ?>
			<p>No courses found.</p>
		<?php endif;

		wp_reset_postdata();

		return ob_get_clean();
	}
}

// Initialize the class
Course_Shortcodes::instance();