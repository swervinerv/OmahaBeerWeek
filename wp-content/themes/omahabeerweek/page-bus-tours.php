<?php
/*
Template Name: Bus Tours
 */
?>

<div id="content" class="bus-tours">
	<header>
		Bus
	</header>
	<div class="page-content">
		<?php if ( have_posts() ) : ?>
			<?php the_post(); ?>		
				<?php the_content(); ?>
		<?php endif; ?>
		<!-- <p style="text-align: center;">
			<img src="http://dgllvx19ffpgh.cloudfront.net/craft-beer-bus-tour.png" alt="Craft Beer Bus Tour" />
		</p> -->
	</div>
</div>