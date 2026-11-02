<?php
/**
 * Title: Footer
 * Slug: bike-maintainer/footer
 * Categories: footer
 * Block Types: core/template-part/footer
 * Inserter: no
 *
 * @package BikeMaintainer
 */

?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"2.5rem","bottom":"2.5rem","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}},"border":{"top":{"color":"var:preset|color|accent-6","width":"1px","style":"solid"}}},"backgroundColor":"base","layout":{"type":"constrained","wideSize":"1280px"}} -->
<div class="wp-block-group alignfull has-base-background-color has-background" style="border-top-color:var(--wp--preset--color--accent-6);border-top-style:solid;border-top-width:1px;padding-top:2.5rem;padding-right:var(--wp--preset--spacing--50);padding-bottom:2.5rem;padding-left:var(--wp--preset--spacing--50)">
	<!-- wp:group {"align":"wide","style":{"typography":{"fontSize":"0.8rem"}},"textColor":"accent-4","layout":{"type":"flex","justifyContent":"space-between"}} -->
	<div class="wp-block-group alignwide has-accent-4-color has-text-color" style="font-size:0.8rem">
		<!-- wp:paragraph -->
		<p><strong class="has-inline-color has-white-color">bike-maintainer<mark style="background-color:rgba(0, 0, 0, 0)" class="has-inline-color has-accent-1-color">.de</mark></strong> · © <?php echo esc_html( gmdate( 'Y' ) ); ?> · <?php esc_html_e( 'Built by home mechanics for home mechanics', 'bike-maintainer' ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:group {"style":{"spacing":{"blockGap":"1.5rem"},"elements":{"link":{"color":{"text":"var:preset|color|accent-4"},":hover":{"color":{"text":"var:preset|color|white"}}}}},"layout":{"type":"flex"}} -->
		<div class="wp-block-group has-link-color">
			<!-- wp:paragraph -->
			<p><a href="<?php echo esc_url( bike_maintainer_legal_url( 'imprint' ) ); ?>"><?php esc_html_e( 'Legal notice', 'bike-maintainer' ); ?></a></p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph -->
			<p><a href="<?php echo esc_url( bike_maintainer_legal_url( 'privacy' ) ); ?>"><?php esc_html_e( 'Privacy', 'bike-maintainer' ); ?></a></p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph -->
			<p><a href="https://github.com/stargazer74">GitHub</a></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
