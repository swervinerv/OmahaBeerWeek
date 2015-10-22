<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 */

$home_images_index = 0;

$is_mobile = $detect->isMobile() && !$detect->isTablet();
$is_tablet = $detect->isTablet();

session_start();

$_SESSION['home_images'] = null;

if ( !isset( $_SESSION['home_images'] ) ) {
	$_SESSION['home_images_index'] = -1;

	if ($detect->isMobile() && !$detect->isTablet()) {
		$_SESSION['home_images'] = array(
			'beercade480' => 'http://dgllvx19ffpgh.cloudfront.net/beercade-480.jpg',
			'borgata480' => 'http://dgllvx19ffpgh.cloudfront.net/borgata-480.jpg',
			'brix480' => 'http://dgllvx19ffpgh.cloudfront.net/brix-480.jpg',
			'huber480' => 'http://dgllvx19ffpgh.cloudfront.net/huber-480.jpg',
			'infusion480' => 'http://dgllvx19ffpgh.cloudfront.net/infusion-480.jpg',
			'jakes480' => 'http://dgllvx19ffpgh.cloudfront.net/jakes-480.jpg',
			'krug480' => 'http://dgllvx19ffpgh.cloudfront.net/krug-480.jpg',
			'nbc480' => 'http://dgllvx19ffpgh.cloudfront.net/nbc-480.jpg',
			'taps480' => 'http://dgllvx19ffpgh.cloudfront.net/taps-480.jpg',
			'upstream480' => 'http://dgllvx19ffpgh.cloudfront.net/upstream-480.jpg'
		);
	}
	else if ($detect->isTablet()) {
		$_SESSION['home_images'] = array(
			'beercade1024' => 'http://dgllvx19ffpgh.cloudfront.net/beercade-1024.jpg',
			'borgata1024' => 'http://dgllvx19ffpgh.cloudfront.net/borgata-1024.jpg',
			'brix1024' => 'http://dgllvx19ffpgh.cloudfront.net/brix-1024.jpg',
			'huber1024' => 'http://dgllvx19ffpgh.cloudfront.net/huber-1024.jpg',
			'infusion1024' => 'http://dgllvx19ffpgh.cloudfront.net/infusion-1024.jpg',
			'jakes1024' => 'http://dgllvx19ffpgh.cloudfront.net/jakes-1024.jpg',
			'krug1024' => 'http://dgllvx19ffpgh.cloudfront.net/krug-1024.jpg',
			'nbc1024' => 'http://dgllvx19ffpgh.cloudfront.net/nbc-1024.jpg',
			'taps1024' => 'http://dgllvx19ffpgh.cloudfront.net/taps-1024.jpg',
			'upstream1024' => 'http://dgllvx19ffpgh.cloudfront.net/upstream-1024.jpg'
		);
	}
	else {
		$_SESSION['home_images'] = array(
			'beercade' => 'http://dgllvx19ffpgh.cloudfront.net/beercade.jpg',
			'borgata' => 'http://dgllvx19ffpgh.cloudfront.net/borgata.jpg',
			'brix' => 'http://dgllvx19ffpgh.cloudfront.net/brix.jpg',
			'huber' => 'http://dgllvx19ffpgh.cloudfront.net/huber.jpg',
			'infusion' => 'http://dgllvx19ffpgh.cloudfront.net/infusion.jpg',
			'jakes' => 'http://dgllvx19ffpgh.cloudfront.net/jakes.jpg',
			'krug' => 'http://dgllvx19ffpgh.cloudfront.net/krug.jpg',
			'nbc' => 'http://dgllvx19ffpgh.cloudfront.net/nbc.jpg',
			'taps' => 'http://dgllvx19ffpgh.cloudfront.net/taps.jpg',
			'upstream' => 'http://dgllvx19ffpgh.cloudfront.net/upstream.jpg'
		);
	}

	shuffle($_SESSION['home_images']);
}

$home_images = $_SESSION['home_images'];
$home_images_index = $_SESSION['home_images_index'];

if ($home_images_index == ( count($home_images) - 1 )) {
	$home_images_index = 0;
}
else {
	$home_images_index++;
}

$home_image_url = $home_images[$home_images_index];

$_SESSION['home_images_index'] = $home_images_index;

get_header(); 
?>

<script>
	var IS_MOBILE = <?php echo $is_mobile ? "true" : "false" ?>;
	var IS_TABLET = <?php echo $is_tablet ? "true" : "false" ?>;
</script>
<div id="wrapper">
	<div id="home">
		<div id="celebrating">
			<h1>Celebrating Craft Beer</h1>
			<h2>February 13-22 2015</h2>
		</div>

		<?php if (!$is_mobile): ?>
		<div id="countdown"></div>
		<?php endif; ?>

		<div id="contact_links">
			<a href="//facebook.com/OmahaBeerWeek" target="_blank" class="facebook">Facebook</a>
			<a href="//twitter.com/omahabeerweek" target="_blank" class="twitter">Twitter</a>
		</div>

		<?php if (!$is_mobile): ?>
		<a href="#/events" id="view-events"></a>
		<?php endif; ?>

		<img id="home-image" src="<?php echo $home_image_url ?>" />
	</div>
</div>

<?php if ( !$detect->isMobile() || $detect->isTablet() ) { get_template_part( 'page', 'events' ); } ?>

<?php get_footer(); ?>