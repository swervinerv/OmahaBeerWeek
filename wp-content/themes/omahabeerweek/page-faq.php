<?php
/*
Template Name: FAQ
 */
?>

<div id="content" class="faq">
	<header>
		F.A.Q.
	</header>
	<div class="page-content">
		<?php if ( have_posts() ) : ?>
			<?php the_post(); ?>		
				<?php the_content(); ?>
		<?php endif; ?>
	</div>
</div>