<?php
/*
Template Name: Bus Tours
 */
?>

<div id="content" class="bus-tours">
	<header id="tours">
		Tours
	</header>
	<div class="page-content">
		<?php if ( have_posts() ) : ?>
			<?php the_post(); ?>		
				<?php the_content(); ?>
		<?php endif; ?>
	</div>
	<script type="text/javascript">
		$(function(){
			$('[data-role="sub-nav"]').click(function(e) {
				e.preventDefault();

				$('html, body').animate({
					scrollTop: $('[data-role="' + $(this).attr('href') + '"]').offset().top - $('#branding').outerHeight() - $('#tours').outerHeight()
				}, 1000);

				console.log($(this).attr('href'));
			});
		});
	</script>
</div>