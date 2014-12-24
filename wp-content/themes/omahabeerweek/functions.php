<?php
/**
 * Twenty Eleven functions and definitions
 *
 * Sets up the theme and provides some helper functions. Some helper functions
 * are used in the theme as custom template tags. Others are attached to action and
 * filter hooks in WordPress to change core functionality.
 *
 * The first function, twentyeleven_setup(), sets up the theme by registering support
 * for various features in WordPress, such as post thumbnails, navigation menus, and the like.
 *
 * When using a child theme (see http://codex.wordpress.org/Theme_Development and
 * http://codex.wordpress.org/Child_Themes), you can override certain functions
 * (those wrapped in a function_exists() call) by defining them first in your child theme's
 * functions.php file. The child theme's functions.php file is included before the parent
 * theme's file, so the child theme functions would be used.
 *
 * Functions that are not pluggable (not wrapped in function_exists()) are instead attached
 * to a filter or action hook. The hook can be removed by using remove_action() or
 * remove_filter() and you can attach your own function to the hook.
 *
 * We can remove the parent theme's hook only after it is attached, which means we need to
 * wait until setting up the child theme:
 *
 *
 * For more information on hooks, actions, and filters, see http://codex.wordpress.org/Plugin_API.
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */

include 'Mobile_Detect.php';
$detect = new Mobile_Detect();

/**
 * Tell WordPress to run twentyeleven_setup() when the 'after_setup_theme' hook is run.
 */
add_action('admin_init', 'obw_functions_css');

function obw_functions_css() {
	wp_enqueue_style('obw-functions-css', get_bloginfo('template_directory') . '/css/obw-functions.css');
}

add_action('init', 'create_event_postype');

function create_event_postype() {
	$labels = array(
	    'name' => _x('Events', 'post type general name'),
	    'singular_name' => _x('Event', 'post type singular name'),
	    'add_new' => _x('Add New', 'events'),
	    'add_new_item' => __('Add New Event'),
	    'edit_item' => __('Edit Event'),
	    'new_item' => __('New Event'),
	    'view_item' => __('View Event'),
	    'search_items' => __('Search Events'),
	    'not_found' =>  __('No events found'),
	    'not_found_in_trash' => __('No events found in Trash'),
	    'parent_item_colon' => ''
	);
	 
	$args = array(
	    'label' => __('Events'),
	    'labels' => $labels,
	    'public' => true,
	    'can_export' => true,
	    'show_ui' => true,
	    '_builtin' => false,
	    'map_meta_cap' => true,
	    'capability_type' => 'post',
	    'menu_icon' => get_bloginfo('template_url').'/images/wordpress.png',
	    'hierarchical' => false,
	    'rewrite' => array( "slug" => "events", "with_front" => false ),
	    'supports'=> array('title') ,
	    'show_in_nav_menus' => true,
	    'taxonomies' => array( 'obw_eventcategory', 'post_tag')
	);
	 
	register_post_type( 'obw_events', $args);
}

function create_eventcategory_taxonomy() {
 
$labels = array(
    'name' => _x( 'Categories', 'taxonomy general name' ),
    'singular_name' => _x( 'Category', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Categories' ),
    'popular_items' => __( 'Popular Categories' ),
    'all_items' => __( 'All Categories' ),
    'parent_item' => null,
    'parent_item_colon' => null,
    'edit_item' => __( 'Edit Category' ),
    'update_item' => __( 'Update Category' ),
    'add_new_item' => __( 'Add New Category' ),
    'new_item_name' => __( 'New Category Name' ),
    'separate_items_with_commas' => __( 'Separate categories with commas' ),
    'add_or_remove_items' => __( 'Add or remove categories' ),
    'choose_from_most_used' => __( 'Choose from the most used categories' ),
);
 
register_taxonomy('obw_eventcategory','obw_events', array(
    'label' => __('Event Category'),
    'labels' => $labels,
    'hierarchical' => true,
    'show_ui' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'event-category' ),
	));
}
 
add_action( 'init', 'create_eventcategory_taxonomy', 0 );

function obw_events_filter ($query) {
	//$pagenow holds the name of the current page being viewed
     global $pagenow;
 
    //$current_user uses the get_currentuserinfo() method to get the currently logged in user's data
     global $current_user;
     get_currentuserinfo();
     
    //Shouldn't happen for the admin, but for any role with the edit_posts capability and only on the posts list page, that is edit.php
    if(!current_user_can('administrator') && !current_user_can('editor') && current_user_can('edit_posts') && ('edit.php' == $pagenow)) {
	    //global $query's set() method for setting the author as the current user's id
	    $query->set('author', $current_user->ID);

	    $screen = get_current_screen();
 		add_filter('views_'.$screen->id, 'remove_post_counts');
    }
}

function remove_post_counts($posts_count_disp) {
    //$posts_count_disp contains the 3 links, we keep 'Mine' and remove the other two.
    unset($posts_count_disp['all']);
	unset($posts_count_disp['publish']);
	unset($posts_count_disp['trash']);
     
	return $posts_count_disp;
}

add_action('pre_get_posts', 'obw_events_filter');

add_filter ('manage_edit-obw_events_columns', 'obw_events_edit_columns');
add_action ('manage_posts_custom_column', 'obw_events_custom_columns');

function obw_events_edit_columns($columns) {
	$columns = array(
	    "obw_col_ev_sponsor" => "Sponsor Name",
	    "obw_col_ev_location" => "Location",
	    "obw_col_ev_date" => "Date",
	    "obw_col_ev_times" => "Times",
	    "title" => "Event",
	    "obw_col_ev_desc" => "Description",
	    "obw_col_ev_link" => "Link"
	);

	return $columns;
}

function obw_events_custom_columns($column) {
	global $post;
	$custom = get_post_custom();

	switch ($column) {
		case "obw_col_ev_desc":
			echo $custom["obw_events_desc"][0];
			break;
		case "obw_col_ev_link":
			echo $custom["obw_col_ev_link"][0];
			break;
		case "obw_col_ev_location":
			echo $custom["obw_events_location"][0];
			break;
		case "obw_col_ev_sponsor":
			echo $custom["obw_events_sponsor"][0];
			break;
		case "obw_col_ev_date":
		    // - show dates -
		    $startd = $custom["obw_events_startdate"][0];
		    $endd = $custom["obw_events_enddate"][0];
		    $startdate = date("F j, Y", $startd);
		    $enddate = date("F j, Y", $endd);
		    echo $startdate . '<br /><em>' . $enddate . '</em>';
			break;
		case "obw_col_ev_times":
		    // - show times -
		    $startt = $custom["obw_events_startdate"][0];
		    $endt = $custom["obw_events_enddate"][0];
		    $time_format = get_option('time_format');
		    $starttime = date($time_format, $startt);
		    $endtime = date($time_format, $endt);
		    echo $starttime . ' - ' .$endtime;
			break;
		case "obw_col_ev_desc";
		    the_excerpt();
			break;
	}
}

add_action('admin_init', 'obw_events_create');
 
function obw_events_create() {
    add_meta_box('obw_events_meta', 'Event Details', 'obw_events_meta', 'obw_events');
}

function obw_events_meta () {
	// - grab data -
	global $post;

	$custom = get_post_custom($post->ID);
	$meta_sponsor = $custom["obw_events_sponsor"][0];
	$meta_location = $custom["obw_events_location"][0];
	$meta_desc = $custom["obw_events_desc"][0];
	$meta_link = $custom["obw_events_link"][0];
	$meta_sd = $custom["obw_events_startdate"][0];
	$meta_ed = $custom["obw_events_enddate"][0];
	$meta_st = $meta_sd;	
	$meta_et = $meta_ed;
	 
	// - grab wp time format -
	 
	$time_format = "H:i"; // get_option('time_format');
	$time_format_h = "H";
	$time_format_m = "i";
	// $time_format_ms = "ii";
	 
	// - populate today if empty, 00:00 for time -
	if ($meta_sd == null) {
		$meta_sd = strtotime('2015-02-13');
		$meta_ed = $meta_sd;
		$meta_st = 	0;
		$meta_et = 0;
	}
	 
	// - convert to pretty formats -
	 
	$clean_sd = date("m/d/Y", $meta_sd);
	$clean_ed = date("m/d/Y", $meta_ed);

	$start_hours = intval(date($time_format_h, $meta_st));
	$start_ampm = "am";

	$end_hours = intval(date($time_format_h, $meta_et));
	$end_ampm = "am";

	echo '<input type="hidden" name="obw-test-start" value="' . $start_hours . '" />';
	echo '<input type="hidden" name="obw-test-end" value="' . $end_hours . '" />';

	if ($start_hours >= 12) {
		$start_ampm = "pm";
		
		if ($start_hours > 12) {
			$start_hours -= 12;
		}
	}
	else if ($start_hours == 0) {
		$start_hours = 12;
	}

	if ($end_hours >= 12) {
		$end_ampm = "pm";

		if ($end_hours > 12) {
			$end_hours -= 12;
		}
	}
	else if ($end_hours == 0) {
		$end_hours = 12;
	}

	$start_minutes = date($time_format_m, $meta_st);
	$end_minutes = date($time_format_m, $meta_et);
	$clean_et = date($time_format, $meta_et);
	 
	// - security -
	echo '<input type="hidden" name="obw-events-nonce" id="obw-events-nonce" value="' . wp_create_nonce( 'obw-events-nonce' ) . '" />';
	 
	// - output -
	?>
	<div class="obw-meta">
		<ul>
			<li>
				<label>Sponsor</label>
			</li>
			<li>
				<input name="obw_events_sponsor" class="obw-input" value="<?php echo $meta_sponsor; ?>" />
			</li>
			<li>
				<label>Location</label>
			</li>
			<li>
				<input name="obw_events_location" class="obw-input" value="<?php echo $meta_location; ?>" />
			</li>
			<li>
				<label>Event Start</label>
			</li>
			<li>
				<input name="obw_events_startdate" class="obwdate" value="<?php echo $clean_sd; ?>" />
				<select id="obw_events_startdate_hour" name="obw_events_startdate_hour" class="hour">
					<option value="1">01</option>
					<option value="2">02</option>
					<option value="3">03</option>
					<option value="4">04</option>
					<option value="5">05</option>
					<option value="6">06</option>
					<option value="7">07</option>
					<option value="8">08</option>
					<option value="9">09</option>
					<option value="10">10</option>
					<option value="11">11</option>
					<option value="12">12</option>
				</select>
				<select id="obw_events_startdate_minute" name="obw_events_startdate_minute" class="minute">
					<option value="00">00</option>
					<option value="15">15</option>
					<option value="30">30</option>
					<option value="45">45</option>
				</select>
				<select id="obw_events_startdate_ampm" name="obw_events_startdate_ampm">
					<option value="am">am</option>
					<option value="pm">pm</option>
				</select>
				<input type="hidden" id="obw_events_startampm_hidden" value="<?php echo $start_ampm; ?>" />
				<input type="hidden" id="obw_events_starthour_hidden" value="<?php echo $start_hours; ?>" />
				<input type="hidden" id="obw_events_startminute_hidden" value="<?php echo $start_minutes; ?>" />
			</li>
			<li>
				<label>Event End</label>
			</li>
			<li>
				<input name="obw_events_enddate" class="obwdate" value="<?php echo $clean_ed; ?>" />
				<select id="obw_events_enddate_hour" name="obw_events_enddate_hour" class="hour">
					<option value="1">01</option>
					<option value="2">02</option>
					<option value="3">03</option>
					<option value="4">04</option>
					<option value="5">05</option>
					<option value="6">06</option>
					<option value="7">07</option>
					<option value="8">08</option>
					<option value="9">09</option>
					<option value="10">10</option>
					<option value="11">11</option>
					<option value="12">12</option>
				</select>
				<select id="obw_events_enddate_minute" name="obw_events_enddate_minute" class="minute">
					<option value="00">00</option>
					<option value="15">15</option>
					<option value="30">30</option>
					<option value="45">45</option>
				</select>
				<select id="obw_events_enddate_ampm" name="obw_events_enddate_ampm">
					<option value="am">am</option>
					<option value="pm">pm</option>
				</select>
				<input type="hidden" id="obw_events_endampm_hidden" value="<?php echo $end_ampm; ?>" />
				<input type="hidden" id="obw_events_endhour_hidden" value="<?php echo $end_hours; ?>" />
				<input type="hidden" id="obw_events_endminute_hidden" value="<?php echo $end_minutes; ?>" />
			</li>
			<li>
				<label>Link to More Information</label>
			</li>
			<li>
				<input id="obw_events_link" name="obw_events_link" type="text" value="<?php echo $meta_link ?>" />
			</li>
			<li>
				<label>Description (max 500 characters)</label>
			</li>
			<li class="textarea">
				<textarea id="obw_events_desc" name="obw_events_desc"><?php echo $meta_desc; ?></textarea>
			</li>
		</ul>
	</div>
	<?php
}

add_action('save_post', 'save_obw_events');

function save_obw_events() {
	global $post;

	// - still require nonce
	if ( !wp_verify_nonce( $_POST['obw-events-nonce'], 'obw-events-nonce' )) {
	    return $post->ID;
	}
 
	if ( !current_user_can( 'edit_posts', $post->ID ))
	    return $post->ID;

	$obw_event_desc = $_POST["obw_events_desc"];
	if ( strlen( trim( $obw_event_desc ) ) > 500) {
		$obw_event_desc = substr($obw_event_desc, 0, 500);
	}

	update_post_meta($post->ID, "obw_events_desc", $obw_event_desc);
	update_post_meta($post->ID, "obw_events_link",  $_POST["obw_events_link"]);
	update_post_meta($post->ID, "obw_events_location", $_POST["obw_events_location"]);
	update_post_meta($post->ID, "obw_events_sponsor", $_POST["obw_events_sponsor"]);

	if(!isset($_POST["obw_events_startdate"])):
		return $post->ID;
	endif;

	$newStartTime = intval($_POST["obw_events_startdate_hour"]);
	$newStartTimeMin = strval($_POST["obw_events_startdate_minute"]);

	if (isset($_POST["obw_events_startdate_ampm"]) && $_POST["obw_events_startdate_ampm"] == "pm" && $newStartTime != 12) {
		$newStartTime += 12;
	}
	else if (isset($_POST["obw_events_startdate_ampm"]) && $_POST["obw_events_startdate_ampm"] == "am" && $newStartTime == 12) {
		$newStartTime = 0;
	}

	$updatestartd = strtotime( $_POST["obw_events_startdate"] . " " . strval($newStartTime) . ":" . $newStartTimeMin);
	update_post_meta($post->ID, "obw_events_startdate", $updatestartd );
	update_post_meta($post->ID, "obw_events_startdate_day", date( "d", strtotime( $_POST["obw_events_startdate"] ) ) );
 
	if(!isset($_POST["obw_events_enddate"])):
		return $post;
	endif;

	$newEndTime = $_POST["obw_events_enddate_hour"];
	$newEndTimeMin = strval($_POST["obw_events_enddate_minute"]);

	if (isset($_POST["obw_events_enddate_ampm"]) && $_POST["obw_events_enddate_ampm"] == "pm" && $newEndTime != 12) {
		$newEndTime += 12;
	}
	else if (isset($_POST["obw_events_enddate_ampm"]) && $_POST["obw_events_enddate_ampm"] == "am" && $newEndTime == 12) {
		$newEndTime = 0;
	}

	$updateendd = strtotime( $_POST["obw_events_enddate"] . " " . strval($newEndTime) . ":" . $newEndTimeMin );
	update_post_meta($post->ID, "obw_events_enddate", $updateendd );
}

// JS Datepicker UI

function events_styles() {
    global $post_type;
    
    if( 'obw_events' != $post_type )
        return;
    
    wp_enqueue_style('ui-datepicker', get_bloginfo('template_url') . '/css/jquery-ui-1.8.9.custom.css');
}

function events_scripts() {
    global $post_type;
    
    if( 'obw_events' != $post_type )
    	return;
	
    wp_enqueue_script('jquery-ui', get_bloginfo('template_url') . '/js/jquery-ui-1.8.9.custom.min.js', array('jquery'));
    wp_enqueue_script('ui-datepicker', get_bloginfo('template_url') . '/js/jquery.ui.datepicker.js');
    wp_enqueue_script('custom_script', get_bloginfo('template_url').'/js/pubforce-admin.js', array('jquery'));
}

add_action( 'admin_print_styles-post.php', 'events_styles', 1000 );	
add_action( 'admin_print_styles-post-new.php', 'events_styles', 1000 );

add_action( 'admin_print_scripts-post.php', 'events_scripts', 1000 );
add_action( 'admin_print_scripts-post-new.php', 'events_scripts', 1000 );

/**** custom profile sections ****/

add_action( 'show_user_profile', 'my_show_extra_profile_fields' );
add_action( 'edit_user_profile', 'my_show_extra_profile_fields' );

function my_show_extra_profile_fields( $user ) {
	if ( current_user_can('create_roles') ) {
	?>
		<table class="form-table">
			<tr>
				<th>
					<label for="user_type">User Type</label>
				</th>
				<td>
					<select id="user_type" name="user_type">
					<?php
					if ( esc_attr(get_the_author_meta( 'user_type', $user->ID )) == 'brewery') {
					?>
						<option value="bar">Bar</option>
						<option value="brewery" selected="selected">Brewery</option>
					<?php
					}
					else {
					?>
						<option value="bar" selected="selected">Bar</option>
						<option value="brewery">Brewery</option>
					<?php
					}
					?>
					</select>
				</td>
			</tr>
		</table>
	<?php
	}
	else {
		echo "<input id='user_type' name='user_type' type='hidden' value='".esc_attr(get_the_author_meta( "user_type", $user->ID ))."' />";
	}
?>
	<h3>About</h3>

	<table class="form-table">
		<tr>
			<td>
				<textarea type="text" name="about" id="about" style="width:520px; height: 200px; resize:none;"><?php echo esc_attr( str_replace("<br />", "\r\n", get_the_author_meta( 'about', $user->ID ) ) ); ?></textarea>
				<br />
				<span class="description">Tell us about yourself.</span>
			</td>
		</tr>

	</table>
<?php
} 

add_action( 'personal_options_update', 'my_save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'my_save_extra_profile_fields' );

function my_save_extra_profile_fields( $user_id ) {
	if ( !current_user_can( 'edit_user', $user_id ) )
		return false;

	/* Copy and paste this line for additional fields. Make sure to change 'twitter' to the field ID. */
	update_usermeta( $user_id, 'about', str_replace("\r\n", "<br />", $_POST['about']) );
	update_usermeta( $user_id, 'user_type', $_POST['user_type'] );
	// update_usermeta( $user_id, 'facebook', $_POST['facebook'] );
	// update_usermeta( $user_id, 'twitter', $_POST['twitter'] );
}

function extended_contact_info($user_contactmethods) {  
	$user_contactmethods = array(
		'contact' => 'Contact (Email Address)',
		'twitter' => 'Twitter (Username without the @)',
		'facebook' => 'Facebook'
	);

	return $user_contactmethods;
}  
add_filter('user_contactmethods', 'extended_contact_info');

function admin_del_options() {
   global $_wp_admin_css_colors;
   $_wp_admin_css_colors = 0;
}

function hide_personal_options() {
?>
<script type="text/javascript">
  jQuery(document).ready(function(){
    // jQuery("#your-profile .form-table:first, #your-profile h3:first, #your-profile label[for='first_name'], #your-profile #first_name, #your-profile label[for='last_name'], #your-profile #last_name").remove();
    jQuery("#your-profile .form-table:first, #your-profile h3:first").remove();
    jQuery("#your-profile #display_name, #your-profile #first_name, #your-profile #last_name").parents('tr').remove();
  });
</script>
<?php
}

add_action('admin_head', 'admin_del_options');
add_action('admin_head', 'hide_personal_options');

class PostsOrderedByMetaQuery extends WP_Query {
  var $posts_ordered_by_meta = true;
  var $orderby_order = 'ASC';
  var $orderby_meta_key;

  function __construct($args=array()) {
    add_filter('posts_join',array(&$this,'posts_join'),10,2);
    add_filter('posts_orderby',array(&$this,'posts_orderby'),10,2);
    
    $this->posts_ordered_by_meta = true;
    $this->orderby_meta_key = $args['orderby_meta_key'];
    
    unset($args['orderby_meta_key']);
    
    if (!empty($args['orderby_order'])) {
      $this->orderby_order = $args['orderby_order'];
      unset($args['orderby_order']);
    }

    parent::query($args);
  }

  function posts_join($join,$query) {
    if (isset($query->posts_ordered_by_meta)) {
      global $wpdb;
      $join .=<<<SQL
INNER JOIN {$wpdb->postmeta} postmeta_price ON postmeta_price.post_id={$wpdb->posts}.ID
       AND postmeta_price.meta_key='{$this->orderby_meta_key}'
SQL;
    }

    return $join;
  }

  function posts_orderby($orderby,$query) {
    if (isset($query->posts_ordered_by_meta)) {
      global $wpdb;
      $orderby = "postmeta_price.meta_value {$this->orderby_order}";
    }
    
    return $orderby;
  }
}

function get_is_mobile() {
	if($_SESSION['is_mobile_device'] == null)
		return null;
	else if($_SESSION['is_mobile_device'] == true)
		return true;
	else
		return false;
}

function get_is_tablet() {
	if($_SESSION['is_tablet'] == null)
		return null;
	else if($_SESSION['is_tablet'] == true)
		return true;
	else
		return false;
}