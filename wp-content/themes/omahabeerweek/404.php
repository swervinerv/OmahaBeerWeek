<?php
/**
 * The template for displaying 404 pages (Not Found).
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */

get_header(); ?>

	<div id="wrapper">
		<div id="not-found">
			<header>
				<h2>Cheers!</h2>
			</header>
			<p>
				Either we've been drinking too much or you have because the page you're looking for doesn't exist.
			</p>
		</div>
	</div>

	<script type="text/javascript">
		$(function() {
			$('#all-events').hide();
		});
	</script>

<?php get_footer(); ?>