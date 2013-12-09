<?php
/*
Template Name: Members
 */

$wp_user_search = new WP_User_Query( array( 
	'role' => 'contributor')
);
$members = $wp_user_search->get_results();
?>

<div id="content" class="bars">
	<header>
		Members
	</header>
	<div class="page-content">
		<div class="descriptions">
		<?php foreach ( $members as $member ) : ?>
			<div id="<?php echo $member->ID; ?>" class="bar-info">
				<h3><?php echo get_user_meta( $member->ID, 'nickname', true ); ?></h3>
				<div class="links">
					<?php if (strlen(trim($member->user_url)) > 0) {
						echo '<a href="'.$member->user_url.'" target="_blank">Site</a>';
					} ?>
					<?php if (strlen(trim(get_user_meta( $member->ID, 'contact', true ))) > 0) {
						echo ' + <a href="mailto:'.get_user_meta( $member->ID, 'contact', true ).'" target="_blank">Contact</a>';
					} ?>
					<?php if (strlen(trim(get_user_meta( $member->ID, 'facebook', true ))) > 0) {
						echo ' + <a href="'.get_user_meta( $member->ID, 'facebook', true ).'" target="_blank">Facebook</a>';
					} ?>
					<?php if (strlen(trim(get_user_meta( $member->ID, 'twitter', true ))) > 0) {
						echo ' + <a href="http://twitter.com/'.get_user_meta( $member->ID, 'twitter', true ).'" target="_blank">Twitter</a>';
					} ?>
				</div>
				<p>
					<?php echo get_user_meta( $member->ID, 'about', true ); ?>
				</p>
				<!-- <div>
					<a href="#" class="show-all" data-id="<?php echo $member->ID; ?>" data-action="show">SHOW ALL OF THIS MEMBER’S EVENTS</a>
				</div> -->
			</div>
		<?php endforeach; ?>
		</div>
		<div class="list">
			<span>All Members</span>
			<?php foreach ( $members as $member ) : ?>
			    <a href="#" data-id="<?php echo $member->ID; ?>"><?php echo get_user_meta( $member->ID, 'nickname', true ); ?></a>
			<?php endforeach; ?>
		</div>

		<div class="clear"></div>
	</div>

	<script type="text/javascript">
		$(function () {
			$('div.list', 'div.bars').css({
				'left': ($('div.descriptions', 'div.bars').width()).toString() + 'px'
			});

			if ($('div.bar-info', 'div.descriptions').last().height() < ($(window).innerHeight() - $('#branding').outerHeight() - $('div.bars header').outerHeight())) {
				$('div.bar-info', 'div.descriptions').last().css({
					'min-height': ($(window).innerHeight() - $('#branding').outerHeight() - $('div.bars header').outerHeight()).toString() + 'px'
				});
			}

			$(document).bind('page_animating', function (e) {
				$('div.list', 'div.bars').fadeOut('fast');

				$(document).unbind('page_animating');
			});

			$('a.show-all', 'div.bar-info').unbind('click');
			$('a.show-all', 'div.bar-info').click(function(e) {
				e.preventDefault();

				if ($(this).data('action') === 'show') {
					var barId = $(this).data('id');

					$(this).data('action', 'hide');
					$(this).html('HIDE MEMBER’S EVENTS');

					getMembersEvents(barId);
				}
				else {
					$(this).data('action', 'show');
					$(this).html('SHOW ALL OF THIS MEMBER’S EVENTS');

					$('#events', '#' + $(this).data('id')).slideUp(function() {
						$(this).remove();
					});
				}
			});

			$('a', 'div.list').unbind('click');
			$('a', 'div.list').click(function(e) {
				e.preventDefault();

				$('html, body').animate({
	        		'scrollTop': $('#' + $(this).data('id'), 'div.bars').offset().top - $('#branding').outerHeight() - $('div.bars header').outerHeight()
				}, 750);
			});
			
			if (!IS_MOBILE && $(window).innerWidth() > 1024) {
				$('div.list', 'div.bars').delay(750).fadeIn();
			};

			if (showEventsAuthorId !== 0) {
				$('div.bar-info').each(function() {
					if (parseInt($(this).attr('id')) === showEventsAuthorId) {
						setTimeout(function() {
							$('html, body').animate({
				        		'scrollTop': $('#' + showEventsAuthorId.toString(), 'div.bars').offset().top - $('#branding').outerHeight() - $('div.bars header').outerHeight()
							}, 750, function() {
								$('#' + showEventsAuthorId.toString() + ' a.show-all', 'div.bars').data('action', 'hide');
								$('#' + showEventsAuthorId.toString() + ' a.show-all', 'div.bars').html('HIDE MEMBER’S EVENTS');

								getMembersEvents(showEventsAuthorId);

								showEventsAuthorId = 0;
							});
						}, 750);

						return false;
					}
				});
			};

			function getMembersEvents(barId) {
				$.get(ROOT + 'events/?author_id=' + barId, function(result) {
					var $events = $(result);

					if ($events.children().length === 0) {
						$events = $('<div id="events">There are no events for this member</div>');
					}
					$events.hide();

					$('#' + barId).append($events);

					$events.slideDown(1000);
				}, 'html');
			}

			$(window).scroll(function () {
				if ($(window).scrollTop() > 115) {
					$('div.list', 'div.bars').css({
						'position': 'fixed',
						'top': ($('header', 'div.bars').outerHeight() - $('div.list span', 'div.bars').outerHeight()).toString() + 'px',
						'left': ($('div.page-content', 'div.bars').offset().left + $('div.descriptions', 'div.bars').width()).toString() + 'px'
					})
				}
				else {
					$('div.list', 'div.bars').css({
						'position': 'absolute',
						'top': '300px',
						'left': ($('div.descriptions', 'div.bars').width()).toString() + 'px'
					})
				}
			});
		});
	</script>
</div>