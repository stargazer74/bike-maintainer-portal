<?php
/**
 * Plugin Name: Bike Maintainer – SEO
 * Description: Search engines and link previews: a title with tagline and a description on the start page, Open Graph and Twitter Card tags with a local preview image, structured data (JSON-LD) for the website and the app, hreflang x-default, forum topics in the sitemap and language links that stay on the same forum topic. Everything is plain markup in the page – no SEO service, no tracking, no request to a third party.
 * Author: Chris Wohlbrecht
 * License: MIT
 *
 * @package BikeMaintainer
 */

defined( 'ABSPATH' ) || exit;

/*
 * Login, registration, lost password and search results already get "noindex" from WordPress itself, forum views
 * without content (search, new topic, profiles hidden from guests, errors) from Asgaros Forum.
 */

// Links in the head that nobody needs: RSD points to XML-RPC, which is off (bm-hardening.php); the shortlink duplicates
// the address of the page.
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'template_redirect', 'wp_shortlink_header', 11 );

/**
 * Whether Asgaros Forum renders the current request (the forum pages and all their views).
 *
 * @return bool
 */
function bm_seo_is_forum() {
	global $asgarosforum;
	// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- property of Asgaros Forum.
	return is_object( $asgarosforum ) && ! empty( $asgarosforum->executePlugin );
}

/**
 * Current language: "de" or "en" (Polylang), "de" without Polylang.
 *
 * @return string
 */
function bm_seo_language() {
	$language = function_exists( 'pll_current_language' ) ? pll_current_language() : '';
	return 'en' === $language ? 'en' : 'de';
}

/**
 * Start page of the current language.
 *
 * @return string
 */
function bm_seo_home_url() {
	return trailingslashit( function_exists( 'pll_home_url' ) ? pll_home_url() : home_url( '/' ) );
}

/**
 * Short description of the website for the start page (search result snippet and link preview).
 *
 * @return string
 */
function bm_seo_site_description() {
	return __( 'Bike Maintainer reminds you of maintenance by mileage or time and keeps the service book of your motorcycle, car or bicycle. Open source, in the portal or self-hosted.', 'bike-maintainer' );
}

/**
 * Description of the current page, at most about 160 characters; "" if there is none.
 *
 * @return string
 */
function bm_seo_description() {
	if ( is_front_page() ) {
		return bm_seo_site_description();
	}
	if ( ! is_singular() ) {
		return '';
	}
	// The excerpt if one was written, otherwise the beginning of the text; tags become spaces, so the text of two
	// blocks does not run together.
	$post = get_queried_object();
	$html = has_excerpt( $post ) ? get_the_excerpt( $post ) : do_blocks( strip_shortcodes( (string) $post->post_content ) );
	$text = html_entity_decode( (string) preg_replace( '/<[^>]*>/', ' ', $html ), ENT_QUOTES, 'UTF-8' );
	$text = trim( (string) preg_replace( '/\s+/u', ' ', $text ) );
	if ( mb_strlen( $text ) <= 160 ) {
		return $text;
	}
	$cut = mb_substr( $text, 0, 157 );
	$end = mb_strrpos( $cut, ' ' );
	return rtrim( false === $end ? $cut : mb_substr( $cut, 0, $end ), ' ,.;:–-' ) . '…';
}

/*
 * Title of the start page: "Bike Maintainer – <tagline>" instead of only the name. A tagline set in the admin
 * (Settings → General) takes precedence.
 */
add_filter(
	'document_title_parts',
	/**
	 * Adds the tagline on the start page.
	 *
	 * @param array $parts Title parts (title, page, tagline, site).
	 * @return array
	 */
	static function ( $parts ) {
		if ( is_front_page() && empty( $parts['tagline'] ) ) {
			$parts['tagline'] = __( 'Maintenance planner for motorcycles, cars and bicycles', 'bike-maintainer' );
		}
		return $parts;
	}
);

/*
 * Asgaros Forum uses its forum description as description of the forum start page and the page title ("Forum") when
 * there is none. Without one set in the forum settings, describe the forum in the language of the page – the options
 * are read before Polylang knows the language, so set it once it does.
 */
add_action(
	'template_redirect',
	static function () {
		global $asgarosforum;
		if ( bm_seo_is_forum() && isset( $asgarosforum->options ) && '' === trim( (string) ( $asgarosforum->options['forum_description'] ?? '' ) ) ) {
			$asgarosforum->options['forum_description'] = __( 'Questions, tips and discussion about maintaining your vehicles – the community of Bike Maintainer.', 'bike-maintainer' );
		}
	}
);

/*
 * The start page "/" sends visitors whose browser prefers English to "/en/" (bm-polylang.php), so it serves as the
 * page for all other languages too. Polylang leaves x-default out when the default language has no prefix in the URL.
 */
add_filter(
	'pll_rel_hreflang_attributes',
	/**
	 * Adds x-default with the German address.
	 *
	 * @param array $hreflangs Language code => URL.
	 * @return array
	 */
	static function ( $hreflangs ) {
		if ( isset( $hreflangs['de'] ) && ! isset( $hreflangs['x-default'] ) ) {
			$hreflangs['x-default'] = $hreflangs['de'];
		}
		return $hreflangs;
	}
);

/*
 * The forum has one page per language (/forum/, /en/community/) with the same topics below it. Polylang only knows the
 * two pages, so hreflang and the language switcher would point from every topic to the start of the other forum. Keep
 * the rest of the address instead: /forum/topic/x/ <-> /en/community/topic/x/.
 */
add_filter(
	'pll_translation_url',
	/**
	 * Same forum view on the forum page of the other language.
	 *
	 * @param string $url  Address of the translation (here: the other forum page).
	 * @param string $lang Language slug.
	 * @return string
	 */
	static function ( $url, $lang ) {
		global $asgarosforum;
		if ( ! $url || ! bm_seo_is_forum() || 'overview' === $asgarosforum->current_view || ! is_page() ) {
			return $url;
		}
		$current = (string) $asgarosforum->get_link( 'current', false, false, '', false );
		$page    = (string) get_permalink( get_queried_object_id() );
		if ( '' === $page || 0 !== strpos( $current, $page ) || ! function_exists( 'pll_get_post' ) ) {
			return $url;
		}
		$target = pll_get_post( get_queried_object_id(), $lang );
		return $target ? trailingslashit( (string) get_permalink( $target ) ) . ltrim( substr( $current, strlen( $page ) ), '/' ) : $url;
	},
	10,
	2
);

/**
 * Preview image for link previews (Open Graph), 1200 × 630, from the theme; "" without our theme.
 *
 * @return string
 */
function bm_seo_image_url() {
	$file = get_theme_file_path( 'assets/images/social-preview.png' );
	return file_exists( $file ) ? get_theme_file_uri( 'assets/images/social-preview.png' ) : '';
}

/**
 * Prints one meta tag.
 *
 * @param string $attribute "property" (Open Graph) or "name".
 * @param string $key       E.g. "og:title".
 * @param string $value     Content; nothing is printed if empty.
 */
function bm_seo_meta( $attribute, $key, $value ) {
	if ( '' !== (string) $value ) {
		printf( '<meta %1$s="%2$s" content="%3$s" />' . "\n", esc_attr( $attribute ), esc_attr( $key ), esc_attr( $value ) );
	}
}

add_action(
	'wp_head',
	/**
	 * Description, Open Graph and Twitter Card. On forum pages Asgaros Forum prints description, canonical, title and
	 * URL itself; only the rest is added there.
	 */
	static function () {
		if ( is_404() || ( ! is_singular() && ! is_front_page() ) ) {
			return;
		}
		$locales = array(
			'de' => 'de_DE',
			'en' => 'en_US',
		);
		$current = bm_seo_language();
		$image   = bm_seo_image_url();

		if ( ! bm_seo_is_forum() ) {
			$description = bm_seo_description();
			$title       = is_front_page() ? wp_get_document_title() : single_post_title( '', false );
			$url         = is_front_page() ? bm_seo_home_url() : wp_get_canonical_url();
			bm_seo_meta( 'name', 'description', $description );
			bm_seo_meta( 'property', 'og:title', html_entity_decode( $title, ENT_QUOTES, 'UTF-8' ) );
			bm_seo_meta( 'property', 'og:description', $description );
			bm_seo_meta( 'property', 'og:url', (string) $url );
			bm_seo_meta( 'property', 'og:site_name', get_bloginfo( 'name' ) );
		}
		bm_seo_meta( 'property', 'og:type', is_singular( 'post' ) ? 'article' : 'website' );
		bm_seo_meta( 'property', 'og:locale', $locales[ $current ] );
		foreach ( $locales as $language => $locale ) {
			if ( $language !== $current ) {
				bm_seo_meta( 'property', 'og:locale:alternate', $locale );
			}
		}
		if ( $image ) {
			bm_seo_meta( 'property', 'og:image', $image );
			bm_seo_meta( 'property', 'og:image:width', '1200' );
			bm_seo_meta( 'property', 'og:image:height', '630' );
			bm_seo_meta( 'property', 'og:image:alt', __( 'Bike Maintainer – maintenance planner for motorcycles, cars and bicycles', 'bike-maintainer' ) );
		}
		bm_seo_meta( 'name', 'twitter:card', $image ? 'summary_large_image' : 'summary' );
	},
	5
);

add_action(
	'wp_head',
	/** Structured data on the start page: the website and the app (schema.org, JSON-LD). */
	static function () {
		if ( ! is_front_page() ) {
			return;
		}
		$home     = bm_seo_home_url();
		$language = 'en' === bm_seo_language() ? 'en-US' : 'de-DE';
		$app      = array(
			'@type'               => 'WebApplication',
			'@id'                 => $home . '#app',
			'name'                => 'Bike Maintainer',
			'description'         => bm_seo_site_description(),
			'applicationCategory' => 'UtilitiesApplication',
			'operatingSystem'     => __( 'Web browser; self-hosted with Docker on Linux, also on a Raspberry Pi', 'bike-maintainer' ),
			'inLanguage'          => array( 'de', 'en' ),
			'isAccessibleForFree' => true,
			'license'             => 'https://opensource.org/licenses/MIT',
			'offers'              => array(
				'@type'         => 'Offer',
				'price'         => '0',
				'priceCurrency' => 'EUR',
			),
			'screenshot'          => get_theme_file_uri( 'assets/images/app-preview.webp' ),
			'sameAs'              => array( 'https://github.com/stargazer74/bike-maintainer-backend' ),
		);
		if ( function_exists( 'bike_maintainer_app_url' ) ) {
			$app['url'] = bike_maintainer_app_url();
		}
		if ( bm_seo_image_url() ) {
			$app['image'] = bm_seo_image_url();
		}
		$data = array(
			'@context' => 'https://schema.org',
			'@graph'   => array(
				array(
					'@type'       => 'WebSite',
					'@id'         => $home . '#website',
					'url'         => $home,
					'name'        => get_bloginfo( 'name' ),
					'description' => bm_seo_site_description(),
					'inLanguage'  => $language,
					'about'       => array( '@id' => $home . '#app' ),
				),
				$app,
			),
		);
		// wp_json_encode() escapes "/" and "<", so the content cannot end the script element.
		printf( '<script type="application/ld+json">%s</script>' . "\n", wp_json_encode( $data, JSON_UNESCAPED_UNICODE ) );
	},
	6
);

/*
 * Forum topics in the sitemap (wp-sitemap.xml), per language – Polylang wraps every sitemap provider and calls it once
 * for each language. Only approved topics in categories that guests may read.
 */
add_action(
	'init',
	static function () {
		if ( ! class_exists( 'WP_Sitemaps_Provider' ) || ! class_exists( 'AsgarosForum' ) || ! wp_sitemaps_get_server()->sitemaps_enabled() ) {
			return;
		}
		require_once __DIR__ . '/bm-seo/class-bm-forum-sitemap.php';
		wp_register_sitemap_provider( 'forum', new BM_Forum_Sitemap() );
	}
);
