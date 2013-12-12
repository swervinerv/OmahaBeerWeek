<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */

$template_directory_uri = get_template_directory_uri();
$detect = new Mobile_Detect();

?><!DOCTYPE html>
<!--[if IE 6]>
<html id="ie6" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 7]>
<html id="ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html id="ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 6) | !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<meta name="format-detection" content="telephone=no" />
<title>
	<?php
	wp_title( '|', true, 'right' );

	// Add the blog name.
	bloginfo( 'name' );

	?>
</title>
<link rel="shortcut icon" href="<?php bloginfo('url'); ?>/favicon.png">
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link href='http://fonts.googleapis.com/css?family=Karla:400,700,700italic' rel='stylesheet' type='text/css'>
<!--[if lt IE 9]>
<script src="<?php echo $template_directory_uri ?>/js/html5.js" type="text/javascript"></script>
<![endif]-->
<?php wp_head(); ?>

<script type="text/javascript">
	var ROOT = '<?php bloginfo('url'); ?>/';
</script>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script src="<?php echo $template_directory_uri ?>/js/lib/modernizr-2.6.2.min.js"></script>
<script src="<?php echo $template_directory_uri ?>/js/lib/jquery.countdown.js" type="text/javascript"></script>
<script src="<?php echo $template_directory_uri ?>/js/lib/jquery.address-1.5.min.js" type="text/javascript"></script>
<script src="<?php echo $template_directory_uri ?>/js/lib/jquery.imagesloaded.min.js" type="text/javascript"></script>
<link href="<?php echo $template_directory_uri ?>/style.css?v=1.5" rel='stylesheet' />

</head>

<body>

<div id="page">
	<header id="branding" role="banner">
		<nav>
			<div class="logo">
				<a href="<?php bloginfo('url'); ?>/"></a>
			</div>

			<?php if ($detect->isMobile() && !$detect->isTablet()): ?>
			<div id="mobile-nav">
				<div class="arrow"></div>
				<a id="selected-page" href="#" class="">HOME</a>
				<a href="<?php bloginfo('url'); ?>/">HOME</a>
				<!-- <a href="<?php bloginfo('url'); ?>/#/events" data-path="/events">PAST EVENTS</a> -->
				<a href="<?php bloginfo('url'); ?>/#/about" data-path="/about">ABOUT</a>
				<a href="<?php bloginfo('url'); ?>/#/tours" data-path="/tours">BUS</a>
				<a href="<?php bloginfo('url'); ?>/#/members" data-path="/members">MEMBERS</a>
				<a href="<?php bloginfo('url'); ?>/#/faq" data-path="/faq">FAQ</a>
				<a href="<?php bloginfo('url'); ?>/#/sponsors" data-path="/sponsors">SPONSORS</a>
			</div>
			<?php else: ?>
			<a href="<?php bloginfo('url'); ?>/#/about">About</a>
			<!-- <a href="<?php bloginfo('url'); ?>/#/events">Past Events</a> -->
			<a href="<?php bloginfo('url'); ?>/#/tours">Bus</a>
			<a href="<?php bloginfo('url'); ?>/#/members">Members</a>
			<a href="<?php bloginfo('url'); ?>/#/faq">FAQ</a>
			<a href="<?php bloginfo('url'); ?>/#/sponsors">Sponsors</a>
			<?php endif; ?>

			<?php if (!$detect->isMobile() && !$detect->isTablet()): ?>
			<div id="all-events">
				<span></span>
				<a href="#" class="title">All Events</a>
				<a href="#/15" data-id="15">FRIDAY 02.15.13</a>
				<a href="#/16" data-id="16">SATURDAY 02.16.13</a>
				<a href="#/17" data-id="17">SUNDAY 02.17.13</a>
				<a href="#/18" data-id="18">MONDAY 02.18.13</a>
				<a href="#/19" data-id="19">TUESDAY 02.19.13</a>
				<a href="#/20" data-id="20">WEDNESDAY 02.20.13</a>
				<a href="#/21" data-id="21">THURSDAY 02.21.13</a>
				<a href="#/22" data-id="22">FRIDAY 02.22.13</a>
				<a href="#/23" data-id="23">SATURDAY 02.23.13</a>
				<a href="#/24" data-id="24">SUNDAY 02.24.13</a>
			</div>
			<?php endif; ?>
		</nav>
	</header>

	<div id="main">
