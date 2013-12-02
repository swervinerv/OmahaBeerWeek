<?php
/*
Template Name: Events
 */

header('Content-type: application/json');

$detect = new Mobile_Detect();
$start_date = null;
$query_author_id = null;
$query_date = null;
$is_mobile_device = $detect->isMobile() && !$detect->isTablet();
$content = array();

if (isset($_GET['author_id'])) {
	$query_author_id = $_GET['author_id'];
}

if (isset($_GET['date'])) {
	$query_date = $_GET['date'];
}

$args = array(
	'post_type' => 'obw_events',
	'orderby_meta_key' => 'obw_events_startdate',
	'orderby_order' => 'ASC',
	'posts_per_page' => -1
);

if (!is_null($query_author_id)) {
	$args['author'] = $query_author_id;
}

if (!is_null($query_date)) {
	// $args['meta_query'] = array(array(
	// 	'key' => 'obw_events_startdate_day',
	// 	'value' => $query_date
	// ));
	$args['meta_key'] = 'obw_events_startdate_day';
	$args['meta_value'] = $query_date;
}

// Query the posts:
$events_query = new PostsOrderedByMetaQuery($args);
$events_list = $events_query->get_posts();
$event_date = trim(get_post_meta(current($events_list)->ID, 'obw_events_startdate_day', true));
$event_date_display = date('m/d/Y', get_post_meta(current($events_list)->ID, 'obw_events_startdate', true));
$event_date_array = array();

function addToContentArray() {
	array_push($content, array(
		'id' => $event_date,
		'date' => $event_date_display,
		'events' => $event_date_array
	));

	$event_date = $post_date;
	$event_date_display = date('m/d/Y', get_post_meta($post->ID, 'obw_events_startdate', true));
	
	unset($event_date_array);
	$event_date_array = array();
}

foreach ($events_list as $post) {
	$post_date = get_post_meta($post->ID, 'obw_events_startdate_day', true);

	$new_event = array(
		'id' => $post->ID,
		'title' => $post->post_title,
		'location' => get_post_meta($post->ID, 'obw_events_location', true),
		'startDate' => get_post_meta($post->ID, 'obw_events_startdate', true),
		'startTime' => date('H', get_post_meta($post->ID, 'obw_events_startdate', true)),
		'description' => get_post_meta($post->ID, 'obw_events_desc', true),
		'moreInformation' => get_post_meta($post->ID, 'obw_events_link', true)
	);

	if (is_null($query_author_id)) {
		$new_event['links'] = array(
			'site' => get_userdata($post->post_author)->user_url,
			'contact' => get_user_meta($post->post_author, 'contact', true)
		);
	}

	array_push($event_date_array, $new_event);

	if ($event_date != $post_date) {
		array_push($content, array(
			'id' => $event_date,
			'date' => $event_date_display,
			'events' => $event_date_array
		));

		$event_date = $post_date;
		$event_date_display = date('m/d/Y', get_post_meta($post->ID, 'obw_events_startdate', true));
		
		unset($event_date_array);
		$event_date_array = array();
	}
}

if (!is_null($query_date)) {
	array_push($content, array(
		'id' => $event_date,
		'date' => $event_date_display,
		'events' => $event_date_array
	));

	$event_date = $post_date;
	$event_date_display = date('m/d/Y', get_post_meta($post->ID, 'obw_events_startdate', true));
	
	unset($event_date_array);
	$event_date_array = array();
}

echo json_encode($content);

?>