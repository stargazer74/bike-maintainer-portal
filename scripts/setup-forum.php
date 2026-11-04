<?php
/**
 * Forum setup for bm-setup (run with `wp eval-file` after setup-languages.php): registration, Asgaros Forum with a
 * German page (/forum/) and an English one (/en/community/ – the free Polylang cannot share a slug), privacy-friendly
 * forum options and colours of the theme. Idempotent.
 *
 * @package BikeMaintainer
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'pll_set_post_language' ) || ! class_exists( 'AsgarosForum' ) ) {
	WP_CLI::error( 'Polylang and Asgaros Forum must be active.' );
}

// Open registration for forum members; the new account gets a link by mail to set its password, which also confirms
// the address. Spam protection: ALTCHA (bm-altcha.php).
update_option( 'users_can_register', 1 );
update_option( 'default_role', 'subscriber' );
// No avatars from gravatar.com (bm-privacy.php serves a local placeholder anyway).
update_option( 'show_avatars', 0 );

// Exactly one forum page per language. Asgaros creates a page "Forum" on its first run, more than one if that run is
// repeated; keep the first, delete the others.
$bm_forum_pages = get_posts(
	array(
		'post_type'      => 'page',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'orderby'        => 'ID',
		'order'          => 'ASC',
		's'              => '[forum',
		'lang'           => '',
	)
);
$bm_forum_de    = 0;
$bm_forum_en    = 0;
foreach ( $bm_forum_pages as $bm_page ) {
	if ( ! has_shortcode( $bm_page->post_content, 'forum' ) ) {
		continue;
	}
	$bm_language = pll_get_post_language( $bm_page->ID );
	if ( 'en' === $bm_language && ! $bm_forum_en ) {
		$bm_forum_en = $bm_page->ID;
	} elseif ( 'en' !== $bm_language && ! $bm_forum_de ) {
		$bm_forum_de = $bm_page->ID;
	} else {
		wp_delete_post( $bm_page->ID, true );
		WP_CLI::log( 'Deleted surplus forum page ' . $bm_page->ID );
	}
}

// Wide column (wide size of theme.json, as wide as the header): the forum needs more room than the text column.
$bm_content = '<!-- wp:group {"align":"wide","layout":{"type":"default"}} -->' . "\n"
	. '<div class="wp-block-group alignwide"><!-- wp:shortcode -->' . "\n[forum]\n" . '<!-- /wp:shortcode --></div>' . "\n"
	. '<!-- /wp:group -->';
foreach ( array(
	'de' => 'forum',
	'en' => 'community',
) as $bm_language => $bm_slug ) {
	$bm_id   = 'de' === $bm_language ? $bm_forum_de : $bm_forum_en;
	$bm_page = array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => 'Forum',
		'post_name'    => $bm_slug,
		'post_content' => $bm_content,
	);
	if ( $bm_id ) {
		$bm_page['ID'] = $bm_id;
		wp_update_post( $bm_page );
	} else {
		$bm_id = wp_insert_post( $bm_page );
		WP_CLI::log( "Created forum page ($bm_language) $bm_id" );
	}
	pll_set_post_language( $bm_id, $bm_language );
	if ( 'de' === $bm_language ) {
		$bm_forum_de = $bm_id;
	} else {
		$bm_forum_en = $bm_id;
	}
}
pll_save_post_translations(
	array(
		'de' => $bm_forum_de,
		'en' => $bm_forum_en,
	)
);

// Forum options: privacy-friendly defaults (no avatars, profiles and member list only for members, nobody's online
// status), mails from noreply@ like the rest of the website.
$bm_options = array_merge(
	(array) get_option( 'asgarosforum_options', array() ),
	array(
		'location'                         => $bm_forum_de,
		'forum_title'                      => 'Forum',
		'enable_avatars'                   => false,
		'hide_profiles_from_guests'        => true,
		'memberslist_loggedin_only'        => true,
		'show_who_is_online'               => false,
		'statistics_show_online_usernames' => false,
		'show_last_seen'                   => false,
		'show_newest_member'               => false,
		'allow_guest_postings'             => false,
		'allow_file_uploads'               => false,
		'enable_rss'                       => false,
		'reports_enabled'                  => true,
		'reports_notifications'            => true,
		'notification_sender_name'         => 'Bike Maintainer',
		'notification_sender_mail'         => getenv( 'BM_SMTP_FROM' ) ? getenv( 'BM_SMTP_FROM' ) : 'noreply@bike-maintainer.de',
	)
);
update_option( 'asgarosforum_options', $bm_options );

// Colours and font of the theme (dark "workshop" design), see theme.json.
update_option(
	'asgarosforum_appearance',
	array_merge(
		(array) get_option( 'asgarosforum_appearance', array() ),
		array(
			// Bars and buttons dark with white text (white on orange fails WCAG AA); orange for links and highlights.
			'custom_color'                  => '#1E293B',
			'custom_accent_color'           => '#F96302',
			'custom_text_color'             => '#F3F4F6',
			'custom_text_color_light'       => '#9CA3AF',
			'custom_link_color'             => '#F96302',
			'custom_background_color'       => '#121826',
			'custom_background_color_alt'   => '#0B0F19',
			'custom_border_color'           => '#1F293D',
			'custom_read_indicator_color'   => '#9CA3AF',
			'custom_unread_indicator_color' => '#F96302',
			'custom_font'                   => 'Inter, system-ui, sans-serif',
			'custom_font_size'              => '15px',
		)
	)
);

// Starting structure: one category and one forum, both languages in the name (Asgaros has no translations of its
// content). Only renames Asgaros' example on the first run; afterwards the structure belongs to the administrators.
global $wpdb;
$bm_example = get_term_by( 'name', 'Beispiel-Kategorie', 'asgarosforum-category' );
if ( ! $bm_example ) {
	$bm_example = get_term_by( 'name', 'Example Category', 'asgarosforum-category' );
}
if ( $bm_example ) {
	wp_update_term( $bm_example->term_id, 'asgarosforum-category', array( 'name' => 'Bike Maintainer' ) );
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- Asgaros keeps forums in its own table.
	$wpdb->update(
		$wpdb->prefix . 'forum_forums',
		array(
			'name'        => 'Allgemein · General',
			'slug'        => 'allgemein',
			'description' => 'Fragen, Tipps und Austausch · Questions, tips and discussion',
		),
		array( 'parent_id' => $bm_example->term_id )
	);
	WP_CLI::log( 'Renamed the example category and forum' );
}

flush_rewrite_rules();
WP_CLI::success( "Forum pages: de $bm_forum_de, en $bm_forum_en" );
