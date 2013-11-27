<?php
/*
Template Name: About
 */
?>

<div id="content" class="about">
	<header>
		About
	</header>
	<div class="page-content">
		<?php if ( have_posts() ) : ?>
			<?php the_post(); ?>		
				<?php the_content(); ?>
		<?php endif; ?>
	</div>
</div>