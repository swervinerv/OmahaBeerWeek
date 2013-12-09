<?php
/*
Template Name: Events
 */

$detect = new Mobile_Detect();
$start_date = null;
$query_author_id = null;
$query_date = null;
$is_mobile_device = $detect->isMobile() && !$detect->isTablet();

if ( isset($_GET['author_id']) ) {
	$query_author_id = $_GET['author_id'];
}

if ( isset($_GET['date']) ) {
	$query_date = $_GET['date'];
	// $query_date = '02/'.$query_date.'/2013';
}

$args = array(
	'post_type' => 'obw_events',
	'orderby_meta_key' => 'obw_events_startdate',
	'orderby_order' => 'ASC',
	'posts_per_page' => -1
);

if (!is_null( $query_author_id ) ) {
	$args['author'] = $query_author_id;
}

if ( !is_null( $query_date ) ) {
	$args['meta_query'] = array(
		'key' => 'obw_events_startdate_day',
		'value' => $query_date,
		'compare' => '=='
	);
}

// Query the posts:
$events_query = new PostsOrderedByMetaQuery($args);

?>

<?php if ( is_null( $query_author_id ) ): ?>
<div id="content" class="events">
<?php endif; ?>
	
	<div id="events">
		<?php if ( $is_mobile_device && is_null( $query_date ) ): ?>
		<div id="events-nav">
			<a href="#/15" data-id="15" class="event-date">FRIDAY 02.15.13</a>
			<a href="#/16" data-id="16" class="event-date">SATURDAY 02.16.13</a>
			<a href="#/17" data-id="17" class="event-date">SUNDAY 02.17.13</a>
			<a href="#/18" data-id="18" class="event-date">MONDAY 02.18.13</a>
			<a href="#/19" data-id="19" class="event-date">TUESDAY 02.19.13</a>
			<a href="#/20" data-id="20" class="event-date">WEDNESDAY 02.20.13</a>
			<a href="#/21" data-id="21" class="event-date">THURSDAY 02.21.13</a>
			<a href="#/22" data-id="22" class="event-date">FRIDAY 02.22.13</a>
			<a href="#/23" data-id="23" class="event-date">SATURDAY 02.23.13</a>
			<a href="#/24" data-id="24" class="event-date">SUNDAY 02.24.13</a>
		</div>
		<?php else: ?>
			<?php while ($events_query->have_posts()) : $events_query->the_post(); ?>
				<?php if ($start_date != strtotime(date("m/d/Y", get_post_meta($post->ID, 'obw_events_startdate', true)))) {
					if ( is_null( $query_date ) || $query_date == get_post_meta($post->ID, 'obw_events_startdate_day', true) ) {
						echo '</section>';
					}

					if ( is_null( $query_date ) || $query_date == get_post_meta($post->ID, 'obw_events_startdate_day', true) ) {
						echo '<section id="'.date("d", get_post_meta($post->ID, 'obw_events_startdate', true)).'" data-day="'.date("l", get_post_meta($post->ID, 'obw_events_startdate', true)).'">';
					}

					if ( is_null( $query_author_id ) && ( is_null( $query_date ) || $query_date == get_post_meta($post->ID, 'obw_events_startdate_day', true) ) ) {
						echo '<header>';
						if ( !$is_mobile_device ) {
							echo '<h3>'.date("l", get_post_meta($post->ID, 'obw_events_startdate', true)).'</h3>';
							echo '<h2>'.date('m.d.y', get_post_meta($post->ID, 'obw_events_startdate', true)).'</h2>';
						}
						else if ( get_post_meta( $post->ID, 'obw_events_startdate_day', true ) == $query_date ) {
							echo '<h3>'.strtoupper( date( "l", get_post_meta( $post->ID, 'obw_events_startdate', true ) ) ).' '.date('m.d.y', get_post_meta($post->ID, 'obw_events_startdate', true)).'</h3>';
							echo '<a href="#" class="events-back">BACK</a>';
						}
						echo '</header>';
					}

					$start_date = is_null($start_date) ? strtotime('02/15/2013') : strtotime(date("m/d/Y", get_post_meta($post->ID, 'obw_events_startdate', true)));
				} ?>

				<?php if ( is_null( $query_date ) || get_post_meta( $post->ID, 'obw_events_startdate_day', true ) == $query_date ): ?>
				<div class="event-detail">
					<h3>
						<?php echo strtoupper( get_post_meta( $post->ID, 'obw_events_location', true ) ); ?>
						<?php if ( is_null( $query_date ) ) { echo ' - '; } else { echo '<br />'; } ?>
						<?php echo strtoupper( $post->post_title ); ?>
						<?php if ( !is_null( $query_author_id ) ) { echo date ( 'm/d/Y', get_post_meta( $post->ID, 'obw_events_startdate', true ) ); } ?>
						<?php
							$start_time = date ( 'H', get_post_meta( $post->ID, 'obw_events_startdate', true ) );

							if (intval( $start_time ) < 12) {
								echo ' - '.$start_time.'AM';
							}
							else if (intval( $start_time ) == 12){
								echo ' - '.$start_time.'PM';
							}
							else {
								echo ' - '.(string)(intval( $start_time ) - 12).'PM';
							}
						?>
					</h3>
					<?php if ( is_null( $query_author_id ) ): ?>
					<div class="links">
						<!-- <a href="#" class="member-events" data-author="<?php echo $post->post_author ?>" data-user-type="<?php echo get_user_meta( $post->post_author, 'user_type', true ) ?>">All Events By This Member</a> -->
						<?php if (strlen(trim(get_userdata( $post->post_author )->user_url)) > 0) {
							//echo '&nbsp;&nbsp;+&nbsp;&nbsp;<a href="'.get_userdata( $post->post_author )->user_url.'" target="_blank">Site</a>';
							echo '<a href="'.get_userdata( $post->post_author )->user_url.'" target="_blank">Site</a>';
						} ?>
						<?php if (strlen(trim(get_user_meta( $post->post_author, 'contact', true ))) > 0) {
							echo '&nbsp;&nbsp;+&nbsp;&nbsp;<a href="mailto:'.get_user_meta( $post->post_author, 'contact', true ).'">Contact</a>';
						} ?>
					</div>
					<?php endif; ?>
					<p>
						<?php echo get_post_meta($post->ID, 'obw_events_desc', true); ?>
						<?php if (strlen(trim(get_post_meta($post->ID, 'obw_events_link', true))) > 0) {
							echo '<br /><a href="'.get_post_meta($post->ID, 'obw_events_link', true).'" target="_blank">Click here for more information</a>';
						} ?>
					</p>
				</div>
				<?php endif; ?>
			<?php endwhile; ?>
		<?php endif; ?>
	</div>

<?php if ( is_null( $query_author_id ) ): ?>
	<script type="text/javascript">
		$(function () {
			$('.member-events', '#events').unbind('click');
			$('.member-events', '#events').click(function(e) {
				e.preventDefault();

				showEventsAuthorId = parseInt($(this).data('author'));
				var userType = $(this).data('user-type');

				switch (userType.toLowerCase()) {
					case 'bar':
						$.address.value('/bars');

						break;
					case 'brewery':
						$.address.value('/breweries');

						break;
				}
			});

			$('a', '#show-all').unbind('click');
			$('a', '#show-all').click(function(e) {
				e.preventDefault();

				$('#show-all').css({
					'z-index': '2'
				})

				$('#show-all').animate({
					'top': '-=32'
				}, 'fast', function() {
					$('#show-all').hide();
				});

				$('div.event-detail', '#events').each(function() {
					if ($(this).css('display') === 'none') {
						$(this).slideDown();
					}
				});

				$('div.links', '#events').each(function() {
					if ($(this).css('display') === 'none') {
						$(this).slideDown();
					}
				});
			});

			<?php if ( $is_mobile_device ): ?>
			$('a.event-date', '#events').unbind('click');
			$('a.event-date', '#events').click(function(e) {
				e.preventDefault();

				var eventDate = $(this).text();

				$.get(ROOT + '/events/?date=' + $(this).data('id'), function (result) {
					var $events = $(result);

					$events.css({
						'position': 'fixed',
						'top': $('#branding').outerHeight().toString() + 'px',
						'left': $(window).width().toString() + 'px'
					});
					// $events.addClass('mobile off-screen');

					$('a.events-back', $events).data('events', $events);

					$('a.events-back', $events).click(function(e) {
						e.preventDefault();

						var $events = $(this).data('events');

						if ($events !== null) {
							$events.css({
								'position': 'absolute',
								'left': 0
							});

							$('#events-nav').parents('div.events').css({
								'position': 'fixed',
								'left': 0,
								'top': $('#branding').outerHeight()
							});

							$events.animate({
								'left': $(window).outerWidth()
							}, 'slow', function() {
								$('#events-nav').parents('div.events').css({
									'position': 'relative',
									'top': 0
								});

								$events.remove();
							});
						}
					});

					$('#wrapper').append($events);

					$events.animate({
						'left': '0'
					}, 'slow', function() {
						$('#events-nav').parents('div.events').css({
							'position': 'fixed',
							'top': 0,
							'left': -$(window).outerWidth()
						});

						$events.css({
							'position': 'relative',
							'top': 0
						});
					});
				}, 'html');
			});
			<?php endif; ?>
		});
	</script>
</div>
<?php endif; ?>