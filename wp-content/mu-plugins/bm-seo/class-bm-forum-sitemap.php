<?php
/**
 * Sitemap provider for the topics of Asgaros Forum (bm-seo.php).
 *
 * @package BikeMaintainer
 */

defined( 'ABSPATH' ) || exit;

/**
 * Approved forum topics in categories that guests may read, linked below the forum page of the sitemap's language.
 */
class BM_Forum_Sitemap extends WP_Sitemaps_Provider {

	/** Sets name and type of the sitemap (wp-sitemap-forum-1.xml). */
	public function __construct() {
		$this->name        = 'forum';
		$this->object_type = 'forum';
	}

	/**
	 * Entries of one sitemap page.
	 *
	 * @param int    $page_num       Page, starting at 1.
	 * @param string $object_subtype Not used.
	 * @return array[]
	 */
	public function get_url_list( $page_num, $object_subtype = '' ) {
		global $asgarosforum, $wpdb;
		$categories = $this->public_categories();
		$base       = $this->forum_page_urls();
		if ( ! is_object( $asgarosforum ) || empty( $categories ) || null === $base ) {
			return array();
		}
		$limit  = wp_sitemaps_get_max_urls( $this->object_type );
		$offset = ( max( 1, (int) $page_num ) - 1 ) * $limit;
		$in     = implode( ',', array_map( 'intval', $categories ) );
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery -- table names and integer IDs only; the sitemap has its own cache headers.
		$topics = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT t.id, MAX(p.date) AS modified FROM {$wpdb->prefix}forum_topics t
				JOIN {$wpdb->prefix}forum_forums f ON f.id = t.parent_id
				LEFT JOIN {$wpdb->prefix}forum_posts p ON p.parent_id = t.id
				WHERE t.approved = 1 AND f.parent_id IN ({$in})
				GROUP BY t.id ORDER BY t.id LIMIT %d OFFSET %d",
				$limit,
				$offset
			)
		);
		// phpcs:enable
		$list = array();
		foreach ( (array) $topics as $topic ) {
			$link = (string) $asgarosforum->get_link( 'topic', (int) $topic->id, false, '', false );
			if ( '' === $link || 0 !== strpos( $link, $base['location'] ) ) {
				continue;
			}
			$entry = array( 'loc' => $base['language'] . substr( $link, strlen( $base['location'] ) ) );
			if ( $topic->modified ) {
				$entry['lastmod'] = gmdate( 'c', (int) strtotime( get_gmt_from_date( $topic->modified ) ) );
			}
			$list[] = $entry;
		}
		return $list;
	}

	/**
	 * Number of sitemap pages.
	 *
	 * @param string $object_subtype Not used.
	 * @return int
	 */
	public function get_max_num_pages( $object_subtype = '' ) {
		global $wpdb;
		$categories = $this->public_categories();
		if ( empty( $categories ) ) {
			return 0;
		}
		$in = implode( ',', array_map( 'intval', $categories ) );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery -- table names and integer IDs only.
		$count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}forum_topics t JOIN {$wpdb->prefix}forum_forums f ON f.id = t.parent_id WHERE t.approved = 1 AND f.parent_id IN ({$in})" );
		return (int) ceil( $count / wp_sitemaps_get_max_urls( $this->object_type ) );
	}

	/**
	 * Forum categories without access restriction (Asgaros: no "category_access" or "everyone").
	 *
	 * @return int[]
	 */
	private function public_categories() {
		$terms = get_terms(
			array(
				'taxonomy'   => 'asgarosforum-category',
				'hide_empty' => false,
			)
		);
		if ( is_wp_error( $terms ) ) {
			return array();
		}
		$public = array();
		foreach ( $terms as $term ) {
			if ( ! in_array( get_term_meta( $term->term_id, 'category_access', true ), array( 'loggedin', 'moderator' ), true ) ) {
				$public[] = (int) $term->term_id;
			}
		}
		return $public;
	}

	/**
	 * Address of the forum page Asgaros builds its links from ("location") and of the forum page in the language of the
	 * sitemap; null if one of them is missing.
	 *
	 * @return array{location: string, language: string}|null
	 */
	private function forum_page_urls() {
		global $asgarosforum;
		$location = (int) ( $asgarosforum->options['location'] ?? 0 );
		if ( ! $location || 'publish' !== get_post_status( $location ) ) {
			return null;
		}
		$target = $location;
		if ( function_exists( 'pll_current_language' ) && function_exists( 'pll_get_post' ) && pll_current_language() ) {
			$target = (int) pll_get_post( $location, pll_current_language() );
		}
		if ( ! $target || 'publish' !== get_post_status( $target ) ) {
			return null;
		}
		return array(
			'location' => trailingslashit( (string) get_permalink( $location ) ),
			'language' => trailingslashit( (string) get_permalink( $target ) ),
		);
	}
}
