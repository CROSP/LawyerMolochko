<?php
/**
 * Plugin Name: Molochko Slider
 * Description: Lightweight Swiper-based hero slider replacing Revolution Slider.
 *              Use [molochko_slider id="1"] (Ukrainian home) or id="7" (Romanian home).
 *              Slide config lives in the $sliders array below — edit there or wire up an admin UI later.
 * Version: 1.0
 *
 * Source images are the same ones used by the original revslider entries,
 * pulled from wp_revslider_slides on 2026-05-17.
 */

if (!defined('ABSPATH')) exit;

class Molochko_Slider {
	private static $enqueued = false;

	private static function get_sliders() {
		$upload_url = wp_get_upload_dir()['baseurl'];
		return [
			// Slider 1 — Ukrainian home (was wp_revslider_sliders.id=1)
			'1' => [
				'autoplay'  => 6000,
				'animation' => 'fade',
				'slides'    => [
					[
						'image'        => $upload_url . '/2026/02/gemini_generated_image_2gvdgb2gvdgb2gvd.png',
						'title'        => 'Адвокатське бюро Молочко',
						'subtitle'     => 'Професійний юридичний захист ваших інтересів',
						'cta_label'    => 'Безкоштовна консультація',
						'cta_url'      => '/contact/',
						'overlay'      => 0.45,
						'align'        => 'left',
					],
					[
						'image'        => $upload_url . '/2026/02/ource_image_use_2k_202602172119-scaled.jpeg',
						'title'        => 'Понад 20 років практики',
						'subtitle'     => 'Кримінальні, цивільні та господарські справи',
						'cta_label'    => 'Послуги',
						'cta_url'      => '/services/',
						'overlay'      => 0.45,
						'align'        => 'left',
					],
				],
			],
			// Slider 7 — Romanian home (was wp_revslider_sliders.id=7)
			'7' => [
				'autoplay'  => 6000,
				'animation' => 'fade',
				'slides'    => [
					[
						'image'        => $upload_url . '/2026/02/gemini_generated_image_1wzzdv1wzzdv1wzz_upscayl_4x_upscayl-standard-4x-1-scaled.png',
						'title'        => 'Cabinet de avocat Molocico',
						'subtitle'     => 'Protecție juridică profesională pentru interesele dumneavoastră',
						'cta_label'    => 'Consultație gratuită',
						'cta_url'      => '/ro/contact/',
						'overlay'      => 0.45,
						'align'        => 'left',
					],
					[
						'image'        => $upload_url . '/2026/02/ource_image_use_2k_202602172119-scaled.jpeg',
						'title'        => 'Peste 20 de ani de practică',
						'subtitle'     => 'Cauze penale, civile și comerciale',
						'cta_label'    => 'Servicii',
						'cta_url'      => '/ro/servicii/',
						'overlay'      => 0.45,
						'align'        => 'left',
					],
				],
			],
		];
	}

	public static function shortcode($atts) {
		$atts = shortcode_atts(['id' => '1'], $atts, 'molochko_slider');
		$sliders = self::get_sliders();
		$slider_id = (string) $atts['id'];
		if (!isset($sliders[$slider_id])) return '';
		self::enqueue_assets();
		$slider = $sliders[$slider_id];
		$unique = 'molochko-slider-' . esc_attr($slider_id);

		ob_start(); ?>
		<div class="molochko-slider swiper" id="<?php echo $unique; ?>"
		     data-autoplay="<?php echo (int) $slider['autoplay']; ?>"
		     data-animation="<?php echo esc_attr($slider['animation']); ?>">
			<div class="swiper-wrapper">
				<?php foreach ($slider['slides'] as $slide): ?>
					<div class="swiper-slide molochko-slide molochko-slide--<?php echo esc_attr($slide['align'] ?? 'left'); ?>">
						<div class="molochko-slide__bg" style="background-image:url('<?php echo esc_url($slide['image']); ?>');"></div>
						<div class="molochko-slide__overlay" style="opacity:<?php echo esc_attr($slide['overlay'] ?? 0.4); ?>"></div>
						<div class="molochko-slide__content">
							<?php if (!empty($slide['title'])): ?>
								<h2 class="molochko-slide__title"><?php echo esc_html($slide['title']); ?></h2>
							<?php endif; ?>
							<?php if (!empty($slide['subtitle'])): ?>
								<p class="molochko-slide__subtitle"><?php echo esc_html($slide['subtitle']); ?></p>
							<?php endif; ?>
							<?php if (!empty($slide['cta_label']) && !empty($slide['cta_url'])): ?>
								<a class="molochko-slide__cta" href="<?php echo esc_url($slide['cta_url']); ?>"><?php echo esc_html($slide['cta_label']); ?></a>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="swiper-pagination"></div>
			<div class="swiper-button-prev"></div>
			<div class="swiper-button-next"></div>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function enqueue_assets() {
		if (self::$enqueued) return;
		self::$enqueued = true;

		// Swiper 11 (latest stable); SRI for integrity.
		wp_enqueue_style(
			'swiper-css',
			'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
			[],
			'11'
		);
		wp_enqueue_script(
			'swiper-js',
			'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
			[],
			'11',
			true
		);
		add_action('wp_footer', [__CLASS__, 'inline_init'], 99);
		add_action('wp_head', [__CLASS__, 'inline_css'], 99);
	}

	public static function inline_css() { ?>
		<style id="molochko-slider-css">
		.molochko-slider{position:relative;width:100%;height:75vh;min-height:480px;max-height:780px;overflow:hidden;background:#0a1a2f}
		.molochko-slider .swiper-slide{position:relative;overflow:hidden}
		.molochko-slide__bg{position:absolute;inset:0;background-size:cover;background-position:center;transform:scale(1.08);transition:transform 8s ease-out}
		.swiper-slide-active .molochko-slide__bg{transform:scale(1)}
		.molochko-slide__overlay{position:absolute;inset:0;background:linear-gradient(90deg,rgba(10,26,47,.7) 0%,rgba(10,26,47,.35) 60%,rgba(10,26,47,.15) 100%);}
		.molochko-slide__content{position:absolute;inset:0;display:flex;flex-direction:column;justify-content:center;padding:0 8vw;max-width:60%;color:#fff}
		.molochko-slide--center .molochko-slide__content{align-items:center;text-align:center;max-width:100%;padding:0 12vw}
		.molochko-slide--right .molochko-slide__content{align-items:flex-end;text-align:right;left:auto;right:0;max-width:60%}
		.molochko-slide__title{font-size:clamp(1.8rem,4.2vw,3.6rem);font-weight:700;line-height:1.1;margin:0 0 .8rem;text-shadow:0 2px 12px rgba(0,0,0,.35)}
		.molochko-slide__subtitle{font-size:clamp(1rem,1.6vw,1.35rem);font-weight:400;line-height:1.4;margin:0 0 2rem;max-width:38rem;opacity:.95}
		.molochko-slide__cta{display:inline-block;padding:.95rem 2.2rem;font-size:1rem;font-weight:600;letter-spacing:.04em;text-transform:uppercase;background:#c9a268;color:#0a1a2f;border-radius:2px;text-decoration:none;transition:background .2s,transform .2s}
		.molochko-slide__cta:hover{background:#b58c4d;transform:translateY(-1px)}
		.molochko-slider .swiper-pagination-bullet{background:#fff;opacity:.6}
		.molochko-slider .swiper-pagination-bullet-active{background:#c9a268;opacity:1}
		.molochko-slider .swiper-button-prev,.molochko-slider .swiper-button-next{color:#fff;width:48px;height:48px}
		.molochko-slider .swiper-button-prev::after,.molochko-slider .swiper-button-next::after{font-size:22px;font-weight:700}
		@media (max-width:768px){
			.molochko-slider{height:60vh;min-height:380px}
			.molochko-slide__content{max-width:90%;padding:0 6vw}
		}
		</style>
	<?php }

	public static function inline_init() { ?>
		<script id="molochko-slider-init">
		(function(){
			function init(){
				if (typeof Swiper === 'undefined') { return setTimeout(init, 100); }
				document.querySelectorAll('.molochko-slider').forEach(function(el){
					var autoplay = parseInt(el.dataset.autoplay || '6000', 10);
					var animation = el.dataset.animation || 'fade';
					new Swiper(el, {
						loop: true,
						speed: 900,
						effect: animation === 'fade' ? 'fade' : 'slide',
						fadeEffect: { crossFade: true },
						autoplay: autoplay > 0 ? { delay: autoplay, disableOnInteraction: false } : false,
						pagination: { el: el.querySelector('.swiper-pagination'), clickable: true },
						navigation: { nextEl: el.querySelector('.swiper-button-next'), prevEl: el.querySelector('.swiper-button-prev') },
						keyboard: { enabled: true },
					});
				});
			}
			document.addEventListener('DOMContentLoaded', init);
		})();
		</script>
	<?php }
}

add_shortcode('molochko_slider', ['Molochko_Slider', 'shortcode']);
