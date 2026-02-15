<?php
/**
 * Law Talk section: blog/social about law — idea, Instagram/TikTok links, carousel of reels/tiktoks.
 * Data: ACF on front page (law_talk_*) + contact options (instagram_url, tiktok_url).
 *
 * @package Molochko
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fid = (int) get_option( 'page_on_front' );
if ( $fid && function_exists( 'pll_get_post' ) && function_exists( 'pll_current_language' ) ) {
	$lang = pll_current_language( 'slug' );
	if ( $lang ) {
		$tr_id = pll_get_post( $fid, $lang );
		if ( $tr_id ) {
			$fid = (int) $tr_id;
		}
	}
}

$subtitle = $fid && function_exists( 'get_field' ) ? get_field( 'law_talk_subtitle', $fid ) : '';
$title    = $fid && function_exists( 'get_field' ) ? get_field( 'law_talk_title', $fid ) : '';
$desc     = $fid && function_exists( 'get_field' ) ? get_field( 'law_talk_description', $fid ) : '';
$embeds   = $fid && function_exists( 'get_field' ) ? get_field( 'law_talk_embeds', $fid ) : array();

if ( empty( $subtitle ) ) {
	$subtitle = molochko_pll__( 'Блог про право' );
}
if ( empty( $title ) ) {
	$title = molochko_pll__( 'Law Talk' );
}
if ( empty( $desc ) ) {
	$desc = molochko_pll__( 'Короткі відео про право українською: що варто знати про закони, ваші права та типові ситуації. Дивіться наші Reels в Instagram та відео в TikTok — просто та зрозуміло.' );
}

$instagram_url = molochko_get_contact_option( 'instagram_url' ) ?: 'https://www.instagram.com/law.talk.taras.m/';
$tiktok_url   = molochko_get_contact_option( 'tiktok_url' ) ?: 'https://www.tiktok.com/@law.talk.taras.m';

// Carousel: latest Instagram Reels (if configured) or manual embed URLs.
$embed_items = array();
$fetch_ig    = $fid && function_exists( 'get_field' ) ? get_field( 'law_talk_fetch_instagram', $fid ) : false;
$ig_user_id  = $fid && function_exists( 'get_field' ) ? get_field( 'law_talk_instagram_user_id', $fid ) : '';
$ig_token    = $fid && function_exists( 'get_field' ) ? get_field( 'law_talk_instagram_access_token', $fid ) : '';

if ( $fetch_ig && $ig_user_id !== '' && $ig_token !== '' && function_exists( 'molochko_get_latest_instagram_reels' ) ) {
	$embed_items = molochko_get_latest_instagram_reels( $ig_user_id, $ig_token, 12 );
}
if ( empty( $embed_items ) && ! empty( $embeds ) && is_array( $embeds ) ) {
	foreach ( $embeds as $row ) {
		$url = isset( $row['embed_url'] ) ? trim( (string) $row['embed_url'] ) : '';
		if ( $url !== '' ) {
			$embed_items[] = $url;
		}
	}
}
?>
<section class="molochko-law-talk" id="law-talk">
	<div class="lt-bg-wrap">
		<div class="lt-bg-pattern" aria-hidden="true"></div>
		<div class="lt-bg-overlay"></div>
	</div>

	<div class="lt-inner">
		<header class="lt-header">
			<p class="lt-subtitle"><?php echo esc_html( $subtitle ); ?></p>
			<h2 class="lt-title"><?php echo esc_html( $title ); ?></h2>
			<p class="lt-desc"><?php echo wp_kses_post( nl2br( $desc ) ); ?></p>
			<div class="lt-social-buttons">
				<a href="<?php echo esc_url( $instagram_url ); ?>" target="_blank" rel="noopener noreferrer" class="lt-btn lt-btn-instagram" aria-label="<?php echo molochko_pll_esc_attr__( 'Instagram Law Talk' ); ?>">
					<svg class="lt-btn-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
					<span><?php molochko_pll_esc_html_e( 'Instagram' ); ?></span>
				</a>
				<a href="<?php echo esc_url( $tiktok_url ); ?>" target="_blank" rel="noopener noreferrer" class="lt-btn lt-btn-tiktok" aria-label="<?php echo molochko_pll_esc_attr__( 'TikTok Law Talk' ); ?>">
					<svg class="lt-btn-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/></svg>
					<span><?php molochko_pll_esc_html_e( 'TikTok' ); ?></span>
				</a>
			</div>
		</header>

		<?php if ( ! empty( $embed_items ) ) : ?>
		<div class="lt-carousel-wrap">
			<div class="lt-carousel">
				<div class="lt-carousel-nav" aria-hidden="true"></div>
				<div class="lt-track">
					<?php
					foreach ( $embed_items as $embed_url ) {
						$cache_key = 'law_talk_embed_' . md5( $embed_url );
						$html      = get_transient( $cache_key );
						if ( false === $html ) {
							$html = wp_oembed_get( $embed_url, array( 'width' => 400, 'height' => 600 ) );
							if ( $html ) {
								set_transient( $cache_key, $html, 12 * HOUR_IN_SECONDS );
							}
						}
						?>
						<div class="lt-slide">
							<?php if ( $html ) : ?>
								<div class="lt-embed-responsive">
									<?php echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — oEmbed HTML ?>
								</div>
							<?php else : ?>
								<a href="<?php echo esc_url( $embed_url ); ?>" target="_blank" rel="noopener noreferrer" class="lt-embed-fallback">
									<span class="lt-embed-fallback-icon" aria-hidden="true">▶</span>
									<span class="lt-embed-fallback-text"><?php molochko_pll_esc_html_e( 'Дивитися відео' ); ?></span>
								</a>
							<?php endif; ?>
						</div>
						<?php
					}
					?>
				</div>
			</div>
		</div>
		<?php else : ?>
		<p class="lt-cta-text"><?php molochko_pll_esc_html_e( 'Дивіться наші відео про право в Instagram та TikTok — підписуйтесь на канали вище.' ); ?></p>
		<?php endif; ?>
	</div>
</section>
