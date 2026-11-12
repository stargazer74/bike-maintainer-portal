<?php
/**
 * Title: Feature tour
 * Slug: bike-maintainer/feature-tour
 * Categories: bike-maintainer
 * Description: Feature page: every feature with a short text and a screenshot, text and picture alternating sides.
 *
 * @package BikeMaintainer
 */

/*
 * One section per feature: screenshot file (assets/screenshots/de|en/, made by tools/screenshots), heading, text,
 * alternative text. The screenshot is on the right in the first section and changes sides from section to section.
 */
$bm_sections = array(
	array(
		'dashboard',
		__( 'Everything due at a glance', 'bike-maintainer' ),
		__( 'The overview shows each vehicle with its mileage, the last maintenance and what is due next. Red means overdue, yellow due soon – by kilometres or by date, whichever comes first.', 'bike-maintainer' ),
		__( 'Overview with three vehicles and their due maintenance', 'bike-maintainer' ),
	),
	array(
		'status',
		__( 'Every vehicle in detail', 'bike-maintainer' ),
		__( 'For each job you see when it was last done and how much is left until the next time. The costs of the whole history and of the current year are added up for you.', 'bike-maintainer' ),
		__( 'Vehicle page with costs and the status of each maintenance job', 'bike-maintainer' ),
	),
	array(
		'tasks',
		__( 'Your maintenance plan', 'bike-maintainer' ),
		__( 'Set for each job after how many kilometres and/or months it is due, and when it is due for the first time. One-off jobs, such as tightening the mirror mount, are possible too.', 'bike-maintainer' ),
		__( 'Form of a maintenance job with intervals in kilometres and months', 'bike-maintainer' ),
	),
	array(
		'log',
		__( 'Record maintenance', 'bike-maintainer' ),
		__( 'Tick what you did, enter the mileage and add parts with part number, quantity and price, plus labour costs. Work you only notice in the workshop is added right away as further work.', 'bike-maintainer' ),
		__( 'Maintenance entry with tasks, tyre values and a part', 'bike-maintainer' ),
	),
	array(
		'history',
		__( 'The complete history', 'bike-maintainer' ),
		__( 'Every entry with its tasks, notes, measured values and costs. Values outside the target are highlighted.', 'bike-maintainer' ),
		__( 'Maintenance entry with measured values of brakes and chain and its costs', 'bike-maintainer' ),
	),
	array(
		'measurements',
		__( 'Measured values and shim suggestion', 'bike-maintainer' ),
		__( 'Store the target values from the workshop manual – valve clearance, brakes, chain and tyres. For valves with shims, the app suggests the shim that brings the clearance back to the middle of the target range.', 'bike-maintainer' ),
		__( 'Valve clearance of a two-cylinder engine with target values and a shim suggestion', 'bike-maintainer' ),
	),
	array(
		'worksheet',
		__( 'Worksheets for the workshop', 'bike-maintainer' ),
		__( 'Print a worksheet for valve clearance, brakes, chain or tyres, with the target values and the last shims of your vehicle already filled in. Fill it in by hand at the bike and photograph it with the Android app: your phone reads the values, the photo never leaves it.', 'bike-maintainer' ),
		__( 'Printable valve clearance worksheet with boxes for the digits', 'bike-maintainer' ),
	),
	array(
		'report',
		__( 'Maintenance report as PDF', 'bike-maintainer' ),
		__( 'Download the history as a PDF – for your records or when you sell the vehicle. A checklist of the jobs that are due is available as a PDF too.', 'bike-maintainer' ),
		__( 'First page of a maintenance report with parts and costs', 'bike-maintainer' ),
	),
	array(
		'import',
		__( 'Take your data with you', 'bike-maintainer' ),
		__( 'Export a vehicle with all its tasks, entries and target values as a file and import it elsewhere – from the portal to your own server or the other way round. Before the import you see what the file contains.', 'bike-maintainer' ),
		__( 'Import dialog with a preview of the vehicle in the file', 'bike-maintainer' ),
	),
	array(
		'app',
		__( 'The Android app', 'bike-maintainer' ),
		__( 'Reminders of due maintenance as notifications, recording maintenance right at the vehicle and photographing worksheets. Without Google services, for the portal and for your own server.', 'bike-maintainer' ),
		__( 'Android app with the vehicle list and the due jobs of a motorcycle', 'bike-maintainer' ),
	),
	array(
		'household',
		__( 'Household mode: no login needed', 'bike-maintainer' ),
		__( 'At home, Bike Maintainer runs on a PC, a server or a Raspberry Pi. Everyone picks their profile – no password, no account – and the data stays in your home network.', 'bike-maintainer' ),
		__( 'Profile selection of household mode with three people', 'bike-maintainer' ),
	),
);
?>
<!-- wp:group {"metadata":{"name":"Feature tour"},"align":"full","style":{"spacing":{"padding":{"top":"clamp(3rem, 8vw, 5rem)","bottom":"clamp(3rem, 8vw, 5rem)","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"backgroundColor":"base","layout":{"type":"constrained","wideSize":"1100px"}} -->
<div class="wp-block-group alignfull has-base-background-color has-background" style="padding-top:clamp(3rem, 8vw, 5rem);padding-right:var(--wp--preset--spacing--50);padding-bottom:clamp(3rem, 8vw, 5rem);padding-left:var(--wp--preset--spacing--50)">
	<!-- wp:group {"align":"wide","style":{"spacing":{"blockGap":"0.75rem"}},"layout":{"type":"constrained","contentSize":"720px"}} -->
	<div class="wp-block-group alignwide">
		<!-- wp:paragraph {"align":"center","className":"is-style-eyebrow"} -->
		<p class="has-text-align-center is-style-eyebrow"><?php esc_html_e( 'Features', 'bike-maintainer' ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"textAlign":"center","level":1,"style":{"typography":{"fontSize":"clamp(2rem, 5vw, 3rem)","fontWeight":"900"}}} -->
		<h1 class="wp-block-heading has-text-align-center" style="font-size:clamp(2rem, 5vw, 3rem);font-weight:900"><?php esc_html_e( 'What Bike Maintainer can do', 'bike-maintainer' ); ?></h1>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"align":"center","textColor":"accent-4"} -->
		<p class="has-text-align-center has-accent-4-color has-text-color"><?php esc_html_e( 'A tour of the app with example vehicles – the same in the portal and on your own server.', 'bike-maintainer' ); ?></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
<?php
foreach ( $bm_sections as $bm_index => $bm_section ) :
	list( $bm_file, $bm_heading, $bm_text, $bm_alt ) = $bm_section;

	$bm_right = 0 === $bm_index % 2;
	// The app picture shows two phones on a transparent background: no frame around it.
	$bm_class = 'app' === $bm_file ? 'is-style-screenshot bm-phones' : 'is-style-screenshot';
	$bm_image = '<figure class="wp-block-media-text__media"><img src="' . esc_url( bike_maintainer_screenshot_url( $bm_file ) ) . '" alt="' . esc_attr( $bm_alt ) . '"/></figure>';
	?>

	<!-- wp:media-text {"align":"wide",<?php echo $bm_right ? '"mediaPosition":"right",' : ''; ?>"mediaType":"image","mediaWidth":58,"verticalAlignment":"center","className":"<?php echo esc_attr( $bm_class ); ?>","style":{"spacing":{"margin":{"top":"clamp(4rem, 10vw, 7rem)"}}}} -->
	<div class="wp-block-media-text alignwide <?php echo $bm_right ? 'has-media-on-the-right ' : ''; ?>is-stacked-on-mobile is-vertically-aligned-center <?php echo esc_attr( $bm_class ); ?>" style="margin-top:clamp(4rem, 10vw, 7rem);grid-template-columns:<?php echo $bm_right ? 'auto 58%' : '58% auto'; ?>">
	<?php
	if ( ! $bm_right ) {
		echo $bm_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- URL and alt text escaped above.
	}
	?>
<div class="wp-block-media-text__content">
		<!-- wp:heading {"style":{"typography":{"fontSize":"clamp(1.5rem, 3vw, 2rem)"}}} -->
		<h2 class="wp-block-heading" style="font-size:clamp(1.5rem, 3vw, 2rem)"><?php echo esc_html( $bm_heading ); ?></h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"textColor":"accent-4"} -->
		<p class="has-accent-4-color has-text-color"><?php echo esc_html( $bm_text ); ?></p>
		<!-- /wp:paragraph -->
	<?php if ( 'app' === $bm_file ) : ?>

		<!-- wp:buttons -->
		<div class="wp-block-buttons">
			<!-- wp:button {"className":"is-style-secondary"} -->
			<div class="wp-block-button is-style-secondary"><a class="wp-block-button__link wp-element-button" href="https://github.com/stargazer74/bike-maintainer-app/releases"><?php esc_html_e( 'Download on GitHub', 'bike-maintainer' ); ?></a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->
	<?php elseif ( 'household' === $bm_file ) : ?>

		<!-- wp:buttons -->
		<div class="wp-block-buttons">
			<!-- wp:button {"className":"is-style-secondary"} -->
			<div class="wp-block-button is-style-secondary"><a class="wp-block-button__link wp-element-button" href="https://github.com/stargazer74/bike-maintainer-deploy#selbst-hosten-im-heimnetz"><?php esc_html_e( 'Guide on GitHub', 'bike-maintainer' ); ?></a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->
	<?php endif; ?>
</div>
	<?php
	if ( $bm_right ) {
		echo $bm_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- URL and alt text escaped above.
	}
	?>
</div>
	<!-- /wp:media-text -->
<?php endforeach; ?>

	<!-- wp:group {"align":"wide","style":{"spacing":{"blockGap":"1.5rem","margin":{"top":"clamp(4rem, 10vw, 7rem)"}}},"layout":{"type":"constrained","contentSize":"720px"}} -->
	<div class="wp-block-group alignwide" style="margin-top:clamp(4rem, 10vw, 7rem)">
		<!-- wp:heading {"textAlign":"center","style":{"typography":{"fontSize":"clamp(1.5rem, 3vw, 2rem)"}}} -->
		<h2 class="wp-block-heading has-text-align-center" style="font-size:clamp(1.5rem, 3vw, 2rem)"><?php esc_html_e( 'Try it yourself', 'bike-maintainer' ); ?></h2>
		<!-- /wp:heading -->

		<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
		<div class="wp-block-buttons">
			<!-- wp:button -->
			<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="#bm-app"><?php esc_html_e( 'Open the app', 'bike-maintainer' ); ?></a></div>
			<!-- /wp:button -->

			<!-- wp:button {"className":"is-style-secondary"} -->
			<div class="wp-block-button is-style-secondary"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( bike_maintainer_home_url( 'betrieb' ) ); ?>"><?php esc_html_e( 'Host it yourself', 'bike-maintainer' ); ?></a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
