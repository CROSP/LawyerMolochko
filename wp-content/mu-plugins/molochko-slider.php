<?php
/**
 * Plugin Name: Molochko Slider
 * Description: Swiper-based hero slider replacing Revolution Slider.
 *              Use [molochko_slider id="1"] (Ukrainian) or [molochko_slider id="7"] (Romanian).
 *              Configure slides at Settings → Molochko Slider.
 * Version: 1.2
 */

if (!defined('ABSPATH')) exit;

class Molochko_Slider {
	private static $enqueued = false;
	const OPTION_KEY = 'molochko_slider_config';

	// -------------------------------------------------------------------------
	// Data
	// -------------------------------------------------------------------------

	private static function defaults() {
		$upload_url = wp_get_upload_dir()['baseurl'];
		return [
			'1' => [
				'label'     => 'Slider 1 — Ukrainian',
				'autoplay'  => 7000,
				'animation' => 'fade',
				'slides'    => [
					[
						'image'       => $upload_url . '/2026/02/gemini_generated_image_2gvdgb2gvdgb2gvd.png',
						'kicker'      => 'ПРОФЕСІЙНА ЮРИДИЧНА ДОПОМОГА',
						'title'       => 'Ми не зупиняємося',
						'description' => 'Досвід, прозорість, результат. Наша команда професійних юристів захищає інтереси фізичних осіб та бізнесу, надаючи комплексну юридичну підтримку від консультації до представництва в суді.',
						'buttons'     => [
							['label' => 'ПРО БЮРО',        'url' => '/about/',    'style' => 'primary'],
							['label' => 'НАПРЯМКИ РОБОТИ', 'url' => '/services/', 'style' => 'ghost'],
						],
					],
					[
						'image'       => $upload_url . '/2026/02/ource_image_use_2k_202602172119-scaled.jpeg',
						'kicker'      => 'НЕ ХВИЛЮЙТЕСЯ, МИ ПОРУЧ!',
						'title'       => 'Ми вирішуємо всі ваші питання',
						'description' => 'Досвід, прозорість, результат. Наша команда професійних юристів захищає інтереси фізичних осіб та бізнесу, надаючи комплексну юридичну підтримку від консультації до представництва в суді.',
						'buttons'     => [
							['label' => 'НАПРЯМКИ РОБОТИ', 'url' => '/services/', 'style' => 'primary'],
						],
					],
				],
			],
			'7' => [
				'label'     => 'Slider 7 — Romanian',
				'autoplay'  => 7000,
				'animation' => 'fade',
				'slides'    => [
					[
						'image'       => $upload_url . '/2026/02/gemini_generated_image_1wzzdv1wzzdv1wzz_upscayl_4x_upscayl-standard-4x-1-scaled.png',
						'kicker'      => 'ASISTENȚĂ JURIDICĂ PROFESIONALĂ',
						'title'       => 'Nu ne oprim',
						'description' => 'Experiență, transparență, rezultat. Echipa noastră de avocați vă apără interesele persoanelor fizice și ale companiilor, oferind asistență juridică de la consultație până la reprezentare în instanță.',
						'buttons'     => [
							['label' => 'DESPRE BIROU',          'url' => '/ro/despre/',   'style' => 'primary'],
							['label' => 'DOMENII DE ACTIVITATE', 'url' => '/ro/servicii/', 'style' => 'ghost'],
						],
					],
					[
						'image'       => $upload_url . '/2026/02/ource_image_use_2k_202602172119-scaled.jpeg',
						'kicker'      => 'NU-ȚI FACE GRIJĂ, SUNTEM ÎN APROPIERE!',
						'title'       => 'Vă rezolvăm toate întrebările',
						'description' => 'Experiență, transparență, rezultate. Echipa noastră de avocați profesioniști protejează interesele persoanelor fizice și juridice, oferind asistență juridică completă, de la consultanță până la reprezentare în instanță.',
						'buttons'     => [
							['label' => 'DIRECȚII DE LUCRU', 'url' => '/ro/servicii/', 'style' => 'primary'],
						],
					],
				],
			],
		];
	}

	private static function get_sliders() {
		$saved = get_option( self::OPTION_KEY );
		if ( $saved && is_array( $saved ) && ! empty( $saved ) ) {
			return $saved;
		}
		return self::defaults();
	}

	// -------------------------------------------------------------------------
	// Front-end shortcode
	// -------------------------------------------------------------------------

	public static function shortcode( $atts ) {
		$atts    = shortcode_atts( ['id' => '1'], $atts, 'molochko_slider' );
		$sliders = self::get_sliders();
		$id      = (string) $atts['id'];
		if ( ! isset( $sliders[ $id ] ) ) return '';
		self::enqueue();
		$slider  = $sliders[ $id ];
		$wrap_id = 'molochko-slider-' . esc_attr( $id );

		ob_start(); ?>
		<section class="molochko-slider-wrap">
		<div class="molochko-slider swiper" id="<?php echo $wrap_id; ?>"
			 data-autoplay="<?php echo (int) $slider['autoplay']; ?>"
			 data-animation="<?php echo esc_attr( $slider['animation'] ); ?>">
			<div class="swiper-wrapper">
				<?php foreach ( $slider['slides'] as $slide ) : ?>
					<div class="swiper-slide molochko-slide">
						<div class="molochko-slide__bg" style="background-image:url('<?php echo esc_url( $slide['image'] ); ?>');" aria-hidden="true"></div>
						<div class="molochko-slide__veil" aria-hidden="true"></div>
						<div class="molochko-slide__inner">
							<div class="molochko-slide__content">
								<?php if ( ! empty( $slide['kicker'] ) ) : ?>
									<span class="molochko-slide__kicker"><?php echo esc_html( $slide['kicker'] ); ?></span>
								<?php endif; ?>
								<?php if ( ! empty( $slide['title'] ) ) : ?>
									<h1 class="molochko-slide__title"><?php echo esc_html( $slide['title'] ); ?></h1>
								<?php endif; ?>
								<?php if ( ! empty( $slide['description'] ) ) : ?>
									<p class="molochko-slide__desc"><?php echo esc_html( $slide['description'] ); ?></p>
								<?php endif; ?>
								<?php if ( ! empty( $slide['buttons'] ) ) : ?>
									<div class="molochko-slide__cta">
										<?php foreach ( $slide['buttons'] as $btn ) :
											$cls = ( ( $btn['style'] ?? 'primary' ) === 'ghost' ) ? 'molochko-btn molochko-btn--ghost' : 'molochko-btn molochko-btn--primary';
										?>
											<a class="<?php echo esc_attr( $cls ); ?>" href="<?php echo esc_url( $btn['url'] ); ?>"><?php echo esc_html( $btn['label'] ); ?></a>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="swiper-pagination molochko-slider__pagination"></div>
			<button class="molochko-slider__nav molochko-slider__nav--prev swiper-button-prev" aria-label="Previous"></button>
			<button class="molochko-slider__nav molochko-slider__nav--next swiper-button-next" aria-label="Next"></button>
		</div>
		</section>
		<?php
		return ob_get_clean();
	}

	// -------------------------------------------------------------------------
	// Asset enqueueing
	// -------------------------------------------------------------------------

	public static function enqueue() {
		if ( self::$enqueued ) return;
		self::$enqueued = true;

		wp_enqueue_style( 'swiper-css',
			'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', [], '11' );
		wp_enqueue_script( 'swiper-js',
			'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', [], '11', true );
		add_action( 'wp_head',   [__CLASS__, 'css'],  99 );
		add_action( 'wp_footer', [__CLASS__, 'init'], 99 );
	}

	public static function css() { ?>
		<style id="molochko-slider-css">
		.molochko-slider-wrap{position:relative;width:100%;margin:0;padding:0;background:#0a1a2f}
		.molochko-slider{position:relative;width:100%;height:78vh;min-height:560px;max-height:820px;overflow:hidden}
		.molochko-slider .swiper-wrapper{height:100%}
		.molochko-slider .swiper-slide{position:relative;overflow:hidden;display:flex;align-items:stretch}
		.molochko-slide__bg{position:absolute;inset:0;background-size:cover;background-position:center 15%;transform:scale(1.06);transition:transform 9s ease-out;will-change:transform}
		.swiper-slide-active .molochko-slide__bg{transform:scale(1)}
		/* Veil: strong dark on left (text side), fades right so portrait is visible */
		.molochko-slide__veil{position:absolute;inset:0;background:linear-gradient(105deg,rgba(7,18,33,.88) 0%,rgba(7,18,33,.72) 35%,rgba(7,18,33,.30) 62%,rgba(7,18,33,.08) 100%)}
		.molochko-slide__inner{position:relative;z-index:2;width:100%;max-width:1320px;margin:0 auto;padding:6vh clamp(20px,5vw,80px);display:flex;align-items:center}
		.molochko-slide__content{max-width:640px;color:#fff;display:grid;grid-row-gap:22px}
		.molochko-slide__kicker{display:inline-block;font-size:.82rem;font-weight:700;letter-spacing:.22em;text-transform:uppercase;color:#d9b682;padding:.5em .9em;border:1px solid rgba(217,182,130,.55);border-radius:2px;justify-self:start;line-height:1}
		.molochko-slide__title{font-family:inherit;font-size:clamp(2rem,4.6vw,4rem);font-weight:700;line-height:1.05;margin:0;letter-spacing:-.01em;text-shadow:0 2px 16px rgba(0,0,0,.35);color:#fff}
		.molochko-slider-wrap h1,.molochko-slider-wrap h2{color:#fff}
		.molochko-slide__desc{font-size:clamp(.98rem,1.25vw,1.18rem);line-height:1.55;margin:0;color:rgba(255,255,255,.88);max-width:38em}
		.molochko-slide__cta{display:flex;flex-wrap:wrap;gap:14px;margin-top:6px}
		.molochko-btn{display:inline-flex;align-items:center;justify-content:center;padding:14px 28px;font-size:.84rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;text-decoration:none;border-radius:2px;border:1px solid transparent;transition:background .2s,color .2s,border-color .2s,transform .15s;cursor:pointer;line-height:1}
		.molochko-btn--primary{background:#c9a268;color:#0a1a2f;border-color:#c9a268}
		.molochko-btn--primary:hover{background:#b58c4d;border-color:#b58c4d;color:#0a1a2f}
		.molochko-btn--ghost{background:transparent;color:#fff;border-color:rgba(255,255,255,.55)}
		.molochko-btn--ghost:hover{background:rgba(255,255,255,.1);border-color:#fff}
		.molochko-btn:hover{transform:translateY(-1px)}
		.molochko-slider .swiper-pagination{bottom:24px}
		.molochko-slider .swiper-pagination-bullet{width:10px;height:10px;background:#fff;opacity:.45;margin:0 6px;transition:opacity .2s,background .2s,width .2s}
		.molochko-slider .swiper-pagination-bullet-active{background:#d9b682;opacity:1;width:32px;border-radius:5px}
		.molochko-slider__nav{width:54px;height:54px;background:rgba(7,18,33,.45);border:1px solid rgba(255,255,255,.18);border-radius:50%;color:#fff;transition:background .2s,border-color .2s;margin-top:-27px}
		.molochko-slider__nav:hover{background:rgba(201,162,104,.7);border-color:#c9a268}
		.molochko-slider__nav.swiper-button-prev{left:max(20px,4vw)}
		.molochko-slider__nav.swiper-button-next{right:max(20px,4vw)}
		.molochko-slider__nav::after{font-size:18px;font-weight:800}
		@media(max-width:1024px){.molochko-slider{height:70vh;min-height:480px}.molochko-slide__content{max-width:560px;grid-row-gap:18px}}
		@media(max-width:768px){
			.molochko-slider{height:auto;min-height:0;max-height:none}
			.molochko-slider .swiper-slide{min-height:80vh}
			.molochko-slide__veil{background:linear-gradient(180deg,rgba(7,18,33,.4) 0%,rgba(7,18,33,.55) 40%,rgba(7,18,33,.85) 100%)}
			.molochko-slide__inner{padding:8vh 22px 12vh;align-items:flex-end}
			.molochko-slide__content{max-width:100%;grid-row-gap:16px}
			.molochko-slide__title{font-size:clamp(1.7rem,7vw,2.4rem)}
			.molochko-slide__desc{font-size:.97rem}
			.molochko-slider__nav{display:none}
			.molochko-slider .swiper-pagination{bottom:20px}
			.molochko-slide__cta{gap:10px}
			.molochko-btn{padding:12px 20px;font-size:.78rem;flex:1 1 auto;text-align:center;min-width:140px;justify-content:center}
		}
		</style>
	<?php }

	public static function init() { ?>
		<script id="molochko-slider-init">
		(function(){
			function boot(){
				if(typeof Swiper==='undefined'){return setTimeout(boot,100);}
				document.querySelectorAll('.molochko-slider').forEach(function(el){
					if(el.dataset.molochkoBooted==='1')return;
					el.dataset.molochkoBooted='1';
					var autoplay=parseInt(el.dataset.autoplay||'7000',10);
					var animation=el.dataset.animation||'fade';
					new Swiper(el,{
						loop:true,speed:1000,
						effect:animation==='fade'?'fade':'slide',
						fadeEffect:{crossFade:true},
						autoplay:autoplay>0?{delay:autoplay,disableOnInteraction:false,pauseOnMouseEnter:true}:false,
						pagination:{el:el.querySelector('.swiper-pagination'),clickable:true},
						navigation:{nextEl:el.querySelector('.swiper-button-next'),prevEl:el.querySelector('.swiper-button-prev')},
						keyboard:{enabled:true},a11y:{enabled:true},
					});
				});
			}
			if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',boot);}else{boot();}
		})();
		</script>
	<?php }

	// -------------------------------------------------------------------------
	// Admin — menu + page
	// -------------------------------------------------------------------------

	public static function admin_menu() {
		add_options_page(
			'Molochko Slider',
			'Molochko Slider',
			'manage_options',
			'molochko-slider',
			[__CLASS__, 'admin_page']
		);
	}

	public static function admin_enqueue( $hook ) {
		if ( $hook !== 'settings_page_molochko-slider' ) return;
		wp_enqueue_media();
		wp_enqueue_style( 'wp-color-picker' );
	}

	public static function admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) return;

		// Handle save.
		if (
			isset( $_POST['molochko_slider_nonce'] ) &&
			wp_verify_nonce( $_POST['molochko_slider_nonce'], 'molochko_slider_save' )
		) {
			$config = self::sanitize_post( $_POST );
			update_option( self::OPTION_KEY, $config );
			echo '<div class="notice notice-success is-dismissible"><p>Slider settings saved.</p></div>';
		}

		$sliders     = self::get_sliders();
		$slider_ids  = array_keys( $sliders );
		$active_tab  = isset( $_GET['tab'] ) && in_array( $_GET['tab'], $slider_ids, true ) ? $_GET['tab'] : $slider_ids[0];
		$page_url    = admin_url( 'options-general.php?page=molochko-slider' );
		?>
		<div class="wrap">
			<h1>Molochko Slider</h1>

			<nav class="nav-tab-wrapper" style="margin-bottom:20px">
				<?php foreach ( $sliders as $sid => $sdata ) : ?>
					<a href="<?php echo esc_url( $page_url . '&tab=' . $sid ); ?>"
					   class="nav-tab <?php echo $active_tab === $sid ? 'nav-tab-active' : ''; ?>">
						<?php echo esc_html( $sdata['label'] ?? 'Slider ' . $sid ); ?>
					</a>
				<?php endforeach; ?>
			</nav>

			<form method="post" action="<?php echo esc_url( $page_url . '&tab=' . $active_tab ); ?>">
				<?php wp_nonce_field( 'molochko_slider_save', 'molochko_slider_nonce' ); ?>

				<?php foreach ( $sliders as $sid => $sdata ) : ?>
					<div class="mslider-tab-panel" id="mslider-tab-<?php echo esc_attr( $sid ); ?>"
						 style="<?php echo $active_tab === $sid ? '' : 'display:none'; ?>">

						<input type="hidden" name="sliders[<?php echo esc_attr( $sid ); ?>][label]"
							   value="<?php echo esc_attr( $sdata['label'] ?? '' ); ?>">

						<table class="form-table" style="max-width:700px">
							<tr>
								<th>Autoplay (ms)</th>
								<td>
									<input type="number" min="0" step="500"
										   name="sliders[<?php echo esc_attr( $sid ); ?>][autoplay]"
										   value="<?php echo (int) ( $sdata['autoplay'] ?? 7000 ); ?>"
										   class="small-text">
									<p class="description">Set 0 to disable autoplay.</p>
								</td>
							</tr>
							<tr>
								<th>Animation</th>
								<td>
									<select name="sliders[<?php echo esc_attr( $sid ); ?>][animation]">
										<option value="fade" <?php selected( $sdata['animation'] ?? 'fade', 'fade' ); ?>>Fade</option>
										<option value="slide" <?php selected( $sdata['animation'] ?? 'fade', 'slide' ); ?>>Slide</option>
									</select>
								</td>
							</tr>
						</table>

						<h2 style="margin-top:28px">Slides</h2>
						<div class="mslider-slides" id="mslider-slides-<?php echo esc_attr( $sid ); ?>"
							 data-sid="<?php echo esc_attr( $sid ); ?>">
							<?php foreach ( $sdata['slides'] as $si => $slide ) :
								self::render_slide_row( $sid, $si, $slide );
							endforeach; ?>
						</div>
						<p>
							<button type="button" class="button mslider-add-slide"
									data-sid="<?php echo esc_attr( $sid ); ?>"
									data-template="<?php echo esc_attr( self::slide_template_json( $sid ) ); ?>">
								+ Add slide
							</button>
						</p>

					</div>
				<?php endforeach; ?>

				<?php submit_button( 'Save slider settings' ); ?>
			</form>
		</div>

		<style>
		.mslider-slide-row{background:#fff;border:1px solid #ccd0d4;border-radius:4px;padding:18px 20px;margin-bottom:14px}
		.mslider-slide-row .slide-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;cursor:pointer}
		.mslider-slide-row .slide-header h3{margin:0;font-size:14px}
		.mslider-slide-row table{width:100%;max-width:660px}
		.mslider-slide-row table th{width:130px;vertical-align:top;padding-top:8px;text-align:left;font-weight:600}
		.mslider-slide-row table td{padding:4px 0 10px}
		.mslider-slide-row input[type=text],.mslider-slide-row textarea{width:100%}
		.mslider-image-preview{display:block;max-height:90px;max-width:200px;margin-top:6px;border-radius:3px;object-fit:cover;border:1px solid #ddd}
		.mslider-image-preview[src=""]{display:none}
		.mslider-btn-row{display:flex;gap:8px;align-items:center;margin-bottom:6px;flex-wrap:wrap}
		.mslider-btn-row input{flex:1;min-width:120px}
		.mslider-btn-row select{width:90px}
		.mslider-slide-body{display:block}
		</style>

		<script>
		(function($){
			// Tab active panel already handled via PHP display:none — nothing needed.

			// Toggle slide body
			$(document).on('click', '.slide-header', function(){
				$(this).closest('.mslider-slide-row').find('.mslider-slide-body').toggle();
			});

			// Media picker
			$(document).on('click', '.mslider-pick-image', function(e){
				e.preventDefault();
				var btn = $(this);
				var frame = wp.media({ title: 'Select slide image', button: { text: 'Use this image' }, multiple: false });
				frame.on('select', function(){
					var att = frame.state().get('selection').first().toJSON();
					btn.siblings('input[type=hidden]').val(att.url);
					var prev = btn.siblings('.mslider-image-preview');
					if(!prev.length){ prev = $('<img class="mslider-image-preview">').insertAfter(btn); }
					prev.attr('src', att.url).show();
				});
				frame.open();
			});

			// Remove image
			$(document).on('click', '.mslider-remove-image', function(e){
				e.preventDefault();
				var row = $(this).closest('td');
				row.find('input[type=hidden]').val('');
				row.find('.mslider-image-preview').attr('src','').hide();
			});

			// Add button row inside a slide
			$(document).on('click', '.mslider-add-btn', function(){
				var container = $(this).closest('.mslider-slide-row').find('.mslider-btn-rows');
				var sid  = container.data('sid');
				var si   = container.data('si');
				var bi   = container.find('.mslider-btn-row').length;
				container.append(buildBtnRow(sid, si, bi, '', '', 'primary'));
			});

			// Remove button row
			$(document).on('click', '.mslider-remove-btn', function(){
				$(this).closest('.mslider-btn-row').remove();
				// Re-index button field names so they're sequential
				$(this).closest('.mslider-btn-rows').find('.mslider-btn-row').each(function(i){
					$(this).find('input, select').each(function(){
						$(this).attr('name', $(this).attr('name').replace(/\[buttons\]\[\d+\]/, '[buttons]['+i+']'));
					});
				});
			});

			// Remove entire slide
			$(document).on('click', '.mslider-remove-slide', function(e){
				e.stopPropagation();
				if(!confirm('Remove this slide?')) return;
				var wrap = $(this).closest('.mslider-slides');
				$(this).closest('.mslider-slide-row').remove();
				reindexSlides(wrap);
			});

			// Add new slide
			$(document).on('click', '.mslider-add-slide', function(){
				var sid = $(this).data('sid');
				var wrap = $('#mslider-slides-' + sid);
				var si  = wrap.find('.mslider-slide-row').length;
				wrap.append(buildSlideRow(sid, si, { image:'', kicker:'', title:'', description:'', buttons:[] }));
			});

			function reindexSlides(wrap){
				wrap.find('.mslider-slide-row').each(function(i){
					$(this).find('input, select, textarea').each(function(){
						$(this).attr('name', $(this).attr('name').replace(/\[slides\]\[\d+\]/, '[slides]['+i+']'));
					});
					$(this).find('.mslider-slide-row-num').text('Slide ' + (i+1));
				});
			}

			function f(sid, si, field){ return 'sliders['+sid+'][slides]['+si+']['+field+']'; }
			function fb(sid, si, bi, field){ return 'sliders['+sid+'][slides]['+si+'][buttons]['+bi+']['+field+']'; }

			function buildBtnRow(sid, si, bi, label, url, style){
				var opts = ['primary','ghost'].map(function(v){
					return '<option value="'+v+'"'+(v===style?' selected':'')+'>'+v+'</option>';
				}).join('');
				return '<div class="mslider-btn-row">'
					+ '<input type="text" name="'+fb(sid,si,bi,'label')+'" value="'+esc(label)+'" placeholder="Button label">'
					+ '<input type="text" name="'+fb(sid,si,bi,'url')+'" value="'+esc(url)+'" placeholder="/url/">'
					+ '<select name="'+fb(sid,si,bi,'style')+'">'+opts+'</select>'
					+ '<button type="button" class="button-link mslider-remove-btn" style="color:#a00">✕</button>'
					+ '</div>';
			}

			function buildSlideRow(sid, si, slide){
				var btns = (slide.buttons||[]).map(function(b,bi){
					return buildBtnRow(sid, si, bi, b.label||'', b.url||'', b.style||'primary');
				}).join('');
				var img = slide.image||'';
				var imgHtml = img ? '<img class="mslider-image-preview" src="'+esc(img)+'" style="display:block;max-height:90px;max-width:200px;margin-top:6px;border-radius:3px;object-fit:cover;border:1px solid #ddd">' : '<img class="mslider-image-preview" src="" style="display:none">';
				return '<div class="mslider-slide-row">'
					+ '<div class="slide-header"><h3 class="mslider-slide-row-num">Slide '+(si+1)+'</h3>'
					+ '<button type="button" class="button-link mslider-remove-slide" style="color:#a00">Remove slide</button></div>'
					+ '<div class="mslider-slide-body"><table>'
					+ '<tr><th>Image</th><td>'
					+ '<input type="hidden" name="'+f(sid,si,'image')+'" value="'+esc(img)+'">'
					+ '<button type="button" class="button mslider-pick-image">Select image</button>'
					+ ' <button type="button" class="button-link mslider-remove-image" style="color:#a00">Remove</button>'
					+ imgHtml + '</td></tr>'
					+ '<tr><th>Kicker</th><td><input type="text" name="'+f(sid,si,'kicker')+'" value="'+esc(slide.kicker||'')+'"></td></tr>'
					+ '<tr><th>Title</th><td><input type="text" name="'+f(sid,si,'title')+'" value="'+esc(slide.title||'')+'"></td></tr>'
					+ '<tr><th>Description</th><td><textarea name="'+f(sid,si,'description')+'" rows="3">'+esc(slide.description||'')+'</textarea></td></tr>'
					+ '<tr><th>Buttons</th><td>'
					+ '<div class="mslider-btn-rows" data-sid="'+sid+'" data-si="'+si+'">' + btns + '</div>'
					+ '<button type="button" class="button mslider-add-btn" style="margin-top:6px">+ Add button</button>'
					+ '</td></tr>'
					+ '</table></div>'
					+ '</div>';
			}

			function esc(s){ return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

		})(jQuery);
		</script>
		<?php
	}

	private static function render_slide_row( $sid, $si, $slide ) {
		$img = $slide['image'] ?? '';
		?>
		<div class="mslider-slide-row">
			<div class="slide-header">
				<h3 class="mslider-slide-row-num">Slide <?php echo $si + 1; ?></h3>
				<button type="button" class="button-link mslider-remove-slide" style="color:#a00">Remove slide</button>
			</div>
			<div class="mslider-slide-body">
			<table>
				<tr>
					<th>Image</th>
					<td>
						<input type="hidden"
							   name="sliders[<?php echo esc_attr($sid); ?>][slides][<?php echo $si; ?>][image]"
							   value="<?php echo esc_attr( $img ); ?>">
						<button type="button" class="button mslider-pick-image">Select image</button>
						<button type="button" class="button-link mslider-remove-image" style="color:#a00">Remove</button>
						<img class="mslider-image-preview"
							 src="<?php echo esc_url( $img ); ?>"
							 <?php echo $img ? '' : 'style="display:none"'; ?>>
					</td>
				</tr>
				<tr>
					<th>Kicker</th>
					<td><input type="text"
							   name="sliders[<?php echo esc_attr($sid); ?>][slides][<?php echo $si; ?>][kicker]"
							   value="<?php echo esc_attr( $slide['kicker'] ?? '' ); ?>"></td>
				</tr>
				<tr>
					<th>Title</th>
					<td><input type="text"
							   name="sliders[<?php echo esc_attr($sid); ?>][slides][<?php echo $si; ?>][title]"
							   value="<?php echo esc_attr( $slide['title'] ?? '' ); ?>"></td>
				</tr>
				<tr>
					<th>Description</th>
					<td><textarea name="sliders[<?php echo esc_attr($sid); ?>][slides][<?php echo $si; ?>][description]"
								  rows="3"><?php echo esc_textarea( $slide['description'] ?? '' ); ?></textarea></td>
				</tr>
				<tr>
					<th>Buttons</th>
					<td>
						<div class="mslider-btn-rows"
							 data-sid="<?php echo esc_attr($sid); ?>"
							 data-si="<?php echo $si; ?>">
							<?php foreach ( ( $slide['buttons'] ?? [] ) as $bi => $btn ) : ?>
								<div class="mslider-btn-row">
									<input type="text"
										   name="sliders[<?php echo esc_attr($sid); ?>][slides][<?php echo $si; ?>][buttons][<?php echo $bi; ?>][label]"
										   value="<?php echo esc_attr( $btn['label'] ?? '' ); ?>"
										   placeholder="Button label">
									<input type="text"
										   name="sliders[<?php echo esc_attr($sid); ?>][slides][<?php echo $si; ?>][buttons][<?php echo $bi; ?>][url]"
										   value="<?php echo esc_attr( $btn['url'] ?? '' ); ?>"
										   placeholder="/url/">
									<select name="sliders[<?php echo esc_attr($sid); ?>][slides][<?php echo $si; ?>][buttons][<?php echo $bi; ?>][style]">
										<option value="primary" <?php selected( $btn['style'] ?? 'primary', 'primary' ); ?>>primary</option>
										<option value="ghost"   <?php selected( $btn['style'] ?? 'primary', 'ghost' ); ?>>ghost</option>
									</select>
									<button type="button" class="button-link mslider-remove-btn" style="color:#a00">✕</button>
								</div>
							<?php endforeach; ?>
						</div>
						<button type="button" class="button mslider-add-btn" style="margin-top:6px">+ Add button</button>
					</td>
				</tr>
			</table>
			</div>
		</div>
		<?php
	}

	private static function slide_template_json( $sid ) {
		return wp_json_encode( [ 'image' => '', 'kicker' => '', 'title' => '', 'description' => '', 'buttons' => [] ] );
	}

	// -------------------------------------------------------------------------
	// Sanitize POST data
	// -------------------------------------------------------------------------

	private static function sanitize_post( $post ) {
		$existing = self::get_sliders();
		$out      = [];

		$raw_sliders = isset( $post['sliders'] ) && is_array( $post['sliders'] ) ? $post['sliders'] : [];

		foreach ( $raw_sliders as $sid => $sdata ) {
			$sid = sanitize_key( $sid );
			if ( ! isset( $existing[ $sid ] ) ) continue;

			$out[ $sid ] = [
				'label'     => sanitize_text_field( $sdata['label'] ?? $existing[ $sid ]['label'] ?? '' ),
				'autoplay'  => max( 0, (int) ( $sdata['autoplay'] ?? 7000 ) ),
				'animation' => in_array( $sdata['animation'] ?? 'fade', ['fade', 'slide'], true ) ? $sdata['animation'] : 'fade',
				'slides'    => [],
			];

			$raw_slides = isset( $sdata['slides'] ) && is_array( $sdata['slides'] ) ? $sdata['slides'] : [];
			foreach ( $raw_slides as $slide ) {
				$buttons = [];
				foreach ( ( $slide['buttons'] ?? [] ) as $btn ) {
					$label = sanitize_text_field( $btn['label'] ?? '' );
					$url   = esc_url_raw( $btn['url'] ?? '' );
					$style = in_array( $btn['style'] ?? '', ['primary', 'ghost'], true ) ? $btn['style'] : 'primary';
					if ( $label !== '' || $url !== '' ) {
						$buttons[] = [ 'label' => $label, 'url' => $url, 'style' => $style ];
					}
				}
				$out[ $sid ]['slides'][] = [
					'image'       => esc_url_raw( $slide['image'] ?? '' ),
					'kicker'      => sanitize_text_field( $slide['kicker'] ?? '' ),
					'title'       => sanitize_text_field( $slide['title'] ?? '' ),
					'description' => sanitize_textarea_field( $slide['description'] ?? '' ),
					'buttons'     => $buttons,
				];
			}
		}

		// Preserve sliders not present in POST (e.g. inactive tab).
		foreach ( $existing as $sid => $sdata ) {
			if ( ! isset( $out[ $sid ] ) ) {
				$out[ $sid ] = $sdata;
			}
		}

		return $out;
	}
}

// Front-end.
add_shortcode( 'molochko_slider', ['Molochko_Slider', 'shortcode'] );

// Enqueue assets early (before wp_head fires) so the inline CSS hook lands in time.
// The shortcode runs after wp_head, so add_action('wp_head','css') registered there is too late.
add_action( 'wp_enqueue_scripts', function () {
	global $post;
	$is_front  = is_front_page();
	$has_short = $post && has_shortcode( $post->post_content, 'molochko_slider' );
	if ( $is_front || $has_short ) {
		Molochko_Slider::enqueue();
	}
}, 1 );

// Admin.
add_action( 'admin_menu',             ['Molochko_Slider', 'admin_menu'] );
add_action( 'admin_enqueue_scripts',  ['Molochko_Slider', 'admin_enqueue'] );
