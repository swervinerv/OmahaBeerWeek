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
			'krug480' => 'http://dgllvx19ffpgh.cloudfront.net/krug-480.jpg'
		);
	}
	else if ($detect->isTablet()) {
		$_SESSION['home_images'] = array(
			'krug1024' => 'http://dgllvx19ffpgh.cloudfront.net/krug-1024.jpg'
		);
	}
	else {
		$_SESSION['home_images'] = array(
			'krug' => 'http://dgllvx19ffpgh.cloudfront.net/krug.jpg'
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
		<!-- <a href="#/events" id="view-events"></a> -->
		<?php endif; ?>

		<img id="home-image" src="<?php echo $home_image_url ?>" />
	</div>
</div>

<?php //if ( !$detect->isMobile() || $detect->isTablet() ) { get_template_part( 'page', 'events' ); } ?>

<?php get_footer(); ?>