<?php
/*
Plugin Name: Peter's Post Notes
Plugin URI: http://www.theblog.ca/wordpress-post-notes
Description: Add notes on the "edit post" and "edit page" screens' sidebars, as well as general notes on the dashboard, in WordPress 2.8 and up. When used with Peter's Collaboration E-mails 1.2 and up, the notes are sent along with the e-mails in the collaboration workflow.
Author: Peter Keung
Version: 1.3.1
Change Log:
2011-08-13  1.3.1: Minor code cleanup to remove unnecessary error notices.
2011-07-03  1.3.0: Added "Latest note" column to the manage posts view.
2010-08-02  1.2.0: Added a couple of settings so that you can grant specific roles and/or capabilities the ability to edit and delete any note. Also added a setting to allow basic HTML in notes.
2010-04-24  1.1.0: Added option to move "add note" box for posts to the notes window. Added a couple of settings so that you grant only specific roles and/or capabilities the ability to view all collaboration notes. Added support for custom post types. Also fixed a couple of bugs with line breaks and pagination on general notes.
2010-04-11  1.0.8: Fixed bug where line breaks weren't preserved when first adding a note. (Thanks SNURK!)
2010-04-02  1.0.7: Added a check in the "save note" function to prevent the same note from being posted twice in a row.
2010-01-11  1.0.6: Plugin now removes its database tables when it is uninstalled, instead of when it is deactivated. This prevents the notes from being deleted when upgrading WordPress automatically.
2009-11-24  1.0.5: More efficient loading of notes if there are no relevant posts for the current user.
2009-09-20  1.0.4: Fixed a bug in date translations. (Thanks Denis!)
2009-09-19  1.0.3: Fixed a bug in the query to show other users' posts on the dashboard. (Thanks martijn!)  Also added proper code call to support translations. (Thanks dreb!)
2009-06-27  1.0.2: Fixed a display compatibility issue within the WordPress post form.
2009-06-23  1.0.1: Fixed minor issue where general notes database table wasn't being created on some installs.
2009-04-08  1.0.0: Added general and private notes system on the dashboard. Fixed UTF-8 encoding and line breaks in notes.
2009-01-17  0.3: Added "Notes" window to pages.  Also added an option (in this plugin file) for the Dashboard "Notes" window: show either all notes by everybody, notes by everybody on relevant posts / pages, and notes by other people on relevant posts / pages.
2008-12-28  0.2: Added ability for users to edit and delete their own notes. Uses Ajax, so JavaScript must be enabled in your browser.
2008-11-10  0.1: First release.
Author URI: http://www.theblog.ca/
*/

if( is_admin() ) { // This line makes sure that all of this code only runs if someone is logged in

/* --------------------------------------------
Configurable setting section start
---------------------------------------------*/

// Which post notes to show on the Dashboard
// 'others' means notes by people other than the current user on posts that were written by or previously commented on by the current user
// 'related' means notes by all people on posts that were written by or previously commented on by the current user
// 'all' means all notes by all people
$ppn_show_which_notes = 'related';

// How many recent notes to display on the Dashboard
// A value of 0 means unlimited
$ppn_num_notes_limit = 5;

// Character set
$ppn_charset = 'UTF-8';

// Set this to false to show the "add note" text area for posts at the bottom of the notes panel
$ppn_add_post_note_in_publish_panel = true;

// Roles that are allowed to view all notes
// Used when $ppn_show_which_notes is set to "related" or "others" but you don't want to give everybody super access
// Defaults to no "super" roles but you can add any of the default WordPress roles
// Default WordPress roles are: administrator, editor, author, contributor, subscriber
// You could also add custom roles if your site has any
$ppn_super_roles = array();
// To add roles, add a line such as this:
// $ppn_super_roles[] = 'administrator';

// Capabilities that are allowed to view all notes
// Used when $ppn_show_which_notes is set to "related" or "others" but you don't want to give everybody super access
// Defaults to no "super" capabilities but you can add any of the default WordPress capabilities
// Default WordPress capabilities are documented here: http://codex.wordpress.org/Roles_and_Capabilities
// You could also add custom capabilities if your site has any
$ppn_super_caps = array();
// To add capabilities, add a line such as this:
// $ppn_super_caps[] = 'edit_others_posts';

// Roles that are allowed to edit all notes
// They need to be allowed to view the notes in question, via the $ppn_show_which_notes and/or $ppn_super_roles and $ppn_super_caps settings
// You could also add custom roles if your site has any
$ppn_superedit_roles = array();
// To add roles, add a line such as this:
// $ppn_superedit_roles[] = 'administrator';

// Capabilities that are allowed to edit all notes
// They need to be allowed to view the notes in question, via the $ppn_show_which_notes and/or $ppn_super_roles and $ppn_super_caps settings
// You could also add custom capabilities if your site has any
$ppn_superedit_caps = array();
// To add capabilities, add a line such as this:
// $ppn_superedit_caps[] = 'edit_others_posts';

// List of post types on which the post notes should be enabled
$ppn_post_types = array();
$ppn_post_types[] = 'post';
$ppn_post_types[] = 'page';
$ppn_post_types[] = 'obw_events';

// Set this to true to allow the same set of HTML tags in posts as are allowed in WordPress comments
// This uses the kses functionality that comes with WordPress; for more information, see http://ottopress.com/2010/wp-quickie-kses/
$ppn_allow_html = false;

// Set this to false if you don't want to show the latest note in the manage posts page
$ppn_show_latest_notes_column = true;

/* --------------------------------------------
Configurable setting section end
---------------------------------------------*/

// Enable translations
add_action('init', 'ppn_textdomain');
function ppn_textdomain() {
	load_plugin_textdomain('peters_post_notes', PLUGINDIR . '/' . dirname(plugin_basename(__FILE__)), dirname(plugin_basename(__FILE__)));
}

global $wpdb;
global $ppn_db_notes;
global $ppn_db_generalnotes;
global $ppn_version;
// Name of the database table that will hold the post-specific notes
$ppn_db_notes = $wpdb->prefix . 'collabnotes';
$ppn_db_generalnotes = $wpdb->prefix . 'generalnotes';
$ppn_version = '1.3.1';

/* --------------------------------------------
Helper functions
-----------------------------------------------*/
class ppnFunctionCollection
{
    function is_super( $user, $super_roles, $super_caps )
    {
        $is_super = false;
        foreach( $super_roles as $super_role ) {
            if( in_array( $super_role, $user->roles ) )
            {
                $is_super = true;
                break;
            }
        }
        if( !$is_super )
        {
            foreach( $super_caps as $super_cap ) {
                if( in_array( $super_cap, $user->allcaps ) )
                {
                    $is_super = true;
                    break;
                }
            }
        }
        return $is_super;
    }
    function note_scrub( $text )
    {
        global $ppn_charset, $ppn_allow_html;
        if( $ppn_allow_html )
        {
            return str_replace( "\n", '<br />', ppnFunctionCollection::kses_data( $text ) );
        }
        else
        {
            return str_replace( "\n", '<br />', htmlentities( $text, ENT_QUOTES, $ppn_charset ) );
        }
    }
    function preserve_code( $text )
    {
        global $ppn_allow_html, $ppn_charset;
        
        $text = str_replace( '<br />', "\n", stripslashes( $text ) );
        if( $ppn_allow_html )
        {
            // Double-encode anything within <code> so that it is preserved
            preg_match_all( "/<code>(.*?)\<\/code>/is", $text, $out );

            foreach( $out[1] as $instance => $inner_match )
            {
                $target_string = htmlentities( $out[1][ $instance ], ENT_QUOTES, $ppn_charset );
                $text = str_replace( $inner_match, $target_string, $text );
            }
        }
        return $text;
    }
    function kses_data( $text )
    {
        // This function is a copy of wp_kses_data that was introduced in WordPress 2.9
        // It's copied in this plugin to retain compatibility with WordPress 2.8
        global $allowedtags;
        return wp_kses( $text, $allowedtags );
    }
    function prepare_js_from_kses( $text )
    {
        return preg_replace( '/([^\\\\])"/', '$1\\"', $text );
    }
    // Return the author's display name if the author exists; otherwise, default to a generic name
    function get_author_name( $author_id )
    {
        $author = get_userdata( $author_id );
        // If the user does not exist (if they've been removed, for example, show a default name)
        if( !$author )
        {
            $author_name = __( 'User', 'peters_post_notes' );
        }
        else
        {
            $author_name = $author->display_name;
        }
        return $author_name;
    }
    // Defines the header for the "Latest note" column on the manage posts page
    function notes_column_header( $columns )
    {
        $columns['ppn_notes'] = __( 'Latest note' );
        return $columns;
    }
    // Defines the actual "Latest note" column content on the manage posts page
    function notes_column_content( $name )
    {
        global $post, $ppn_db_notes, $wpdb;
        switch( $name )
        {
            case 'ppn_notes':
                $latest_note = $wpdb->get_row( 'SELECT noteid, notecontent, author, notetime FROM ' . $ppn_db_notes . ' WHERE postid = ' . $post->ID . ' ORDER BY notetime DESC LIMIT 1', OBJECT );
                if( $latest_note )
                {
                    $author_name = ppnFunctionCollection::get_author_name( $latest_note->author );
                    print '[<em>' . date( __( 'M j, Y \a\t G:i', 'peters_post_notes' ), strtotime( $latest_note->notetime ) ) . ', ' . $author_name . '</em>]<br />' . $latest_note->notecontent;
                }
                else
                {
                    print '-';
                }
        }
    }
}
/* --------------------------------------------
Add meta box
---------------------------------------------*/

function ppn_add_meta_box() {
    global $ppn_post_types;
    foreach( $ppn_post_types as $ppn_post_type ) {
        add_meta_box('collaboration', __('Notes', 'peters_post_notes'), 'ppn_meta_contents', $ppn_post_type, 'side', 'high');
    }
}

/* --------------------------------------------
Output within meta box
---------------------------------------------*/

function ppn_meta_contents($post) {
    global $wpdb, $ppn_db_notes, $ppn_add_post_note_in_publish_panel, $ppn_superedit_roles, $ppn_superedit_caps;

    // Get information about the currently logged in user
    $current_user = wp_get_current_user();

    // Show all notes for this post
    $ppn_notes = $wpdb->get_results('SELECT noteid, notecontent, author, notetime FROM ' . $ppn_db_notes . ' WHERE postid = ' . $post->ID . ' ORDER BY notetime DESC', OBJECT);
        
    // If they are part of groups, get the moderator info for each group
    if( $ppn_notes ) {
        $ppn_num_notes = count($ppn_notes);
        $ppn_this_note = 1;
        $is_supereditor = ppnFunctionCollection::is_super( $current_user, $ppn_superedit_roles, $ppn_superedit_caps );
        foreach ($ppn_notes as $ppn_note) {
            $author_name = ppnFunctionCollection::get_author_name( $ppn_note->author );
            print '<div id="ppn_entire_note_' . $ppn_note->noteid . '">' . "\n";
            print '<p><strong>' . $author_name . '</strong></p>' . "\n";
            print '<p><em>' . date(__('M j, Y \a\t G:i', 'peters_post_notes'), strtotime($ppn_note->notetime)) . '</em></p>' . "\n";
            print '<br />' . "\n";
            print '<div id="ppn_noteerror_' . $ppn_note->noteid . '"></div>' . "\n";
            print '<div id="ppn_notecontent_' . $ppn_note->noteid . '">';
            print '<p id="ppn_notecontent_p_' . $ppn_note->noteid . '">' . stripslashes($ppn_note->notecontent) . '</p>' . "\n";
            if( $current_user->ID == $ppn_note->author || $is_supereditor ) {
                print '<p><a onclick="ppn_ajax_edit_form(' . $ppn_note->noteid . '); return false;" href="#">' . __('Edit', 'peters_post_notes') . '</a></p>' . "\n";
            }
            print '</div>';
            if( $current_user->ID == $ppn_note->author || $is_supereditor ) {
                print '<div id="ppn_noteform_' . $ppn_note->noteid . '" style="display: none;" >' . "\n";
                print '<p style="float:right;"><a onclick="ppn_ajax_delete_note(' . $ppn_note->noteid . ', 1); return false;" href="#">' . __('Delete', 'peters_post_notes') . '</a></p>' . "\n";
                print '<p><textarea name="ppn_note_text_' . $ppn_note->noteid . '" id="ppn_note_' . $ppn_note->noteid . '" cols="30" style="width: 99%;">' . ppnFunctionCollection::preserve_code( $ppn_note->notecontent ) . '</textarea></p>' . "\n";
                print '<p><input type="hidden" name="note_id" value="' . $ppn_note->noteid . '" /><input type="button" class="button button-highlighted" value="' . __('Save', 'peters_post_notes') . '" onclick="ppn_ajax_edit_note(' . $ppn_note->noteid . ',this.form.ppn_note_text_' . $ppn_note->noteid . ', 1);" />';
                print ' <a onclick="ppn_ajax_edit_form_cancel(' . $ppn_note->noteid . '); return false;" href="#">' . __('Cancel', 'peters_post_notes') . '</a></p>' . "\n";
                print '</div>' . "\n";
            }
            // Show a divider if this isn't the last note
            if( $ppn_this_note != $ppn_num_notes ) {
                print '<br /><hr /><br />' . "\n";
            }
            print '</div>' . "\n";
            ++$ppn_this_note;
        }
    }
    else {
        print '<p>' . __('No notes for this post.', 'peters_post_notes') . '</p>';
    }
    if ( $ppn_add_post_note_in_publish_panel ) {
        print '<script type="text/javascript" charset="utf-8">
                  jQuery(document).ready(function(){
                      jQuery("#major-publishing-actions").prepend( jQuery("#ppn_add_post_note" ) );
                  });
               </script>';
    }
    else {
        print '<br /><hr />';
    }
    print '<div id="ppn_add_post_note" style="margin: 10px 0 10px 0;"><label for="ppn_post_note">' . __('Add note:', 'peters_post_notes') . '</label><br /><textarea rows="3" cols="30" name="ppn_post_note" id="ppn_post_note" style="width: 99%"></textarea></div>';
}

/* --------------------------------------------
Add a note during any post save or update action
---------------------------------------------*/

function ppn_save_note($post_id, $post) {
    global $wpdb, $ppn_db_notes, $_POST;

    // Get information about the currently logged in user, as the person submitting the post for review or approving it
    $current_user = wp_get_current_user();

    
    // Check to see whether anybody wrote anything
    if( isset($_POST['ppn_post_note']) && $_POST['ppn_post_note'] != '' ) {
        // Post note
        $ppn_post_note = ppnFunctionCollection::note_scrub( $_POST['ppn_post_note'] );
        
        // Check whether this exact note already exists, and whether it was the last note written on this post
        // This is to allow people to write things like "Thanks!" multiple times in a note as long as someone else has posted in between
        $latest_note = $wpdb->get_var( $wpdb->prepare( "SELECT `notecontent` FROM $ppn_db_notes 
                                                        WHERE `postid` = $post_id
                                                        ORDER BY `notetime` DESC
                                                        LIMIT 1;" ) );
        if( $ppn_post_note != $latest_note )
        {
            // Insert the note into the database if they haven't already posted the same thing
            $wpdb->insert(
                $ppn_db_notes,
                array('postid' => $post_id,
                    'notecontent' => $ppn_post_note,
                    'author' => $current_user->ID,
                    'notetime' => current_time('mysql')
                )
            );
        }
    }
}

/* --------------------------------------------
When a post is deleted, remove all notes related to it
---------------------------------------------*/

function ppn_delete_notes($post_id) {
    global $wpdb, $ppn_db_notes;
    
    $wpdb->query('DELETE FROM ' . $ppn_db_notes . ' WHERE postid = ' . $post_id);
}

/* --------------------------------------------
Show latest notes on dashboard
---------------------------------------------*/

function ppn_add_dashboard() {
    wp_add_dashboard_widget( 'ppn_dashboard', __('Collaboration Notes', 'peters_post_notes'), 'ppn_dashboard' );
}

function ppn_dashboard() {
    global $wpdb, $ppn_db_notes, $ppn_show_which_notes, $ppn_num_notes_limit, $ppn_super_roles, $ppn_super_caps;

    $current_user = wp_get_current_user();

    // Basic variable setup
    $ppn_relevant_post_list = '';
    $ppn_newest_posts = false;
    $ppn_query_options = '';

    // Do we need to filter the list of notes?
    if( $ppn_show_which_notes != 'all' ) {
        // Do another check to see whether this user has an exempted role or capability to be able to view all notes
        $is_super = ppnFunctionCollection::is_super( $current_user, $ppn_super_roles, $ppn_super_caps );

        if( !$is_super ) {
            // Get posts this author has written
            $ppn_author_posts = query_posts('author=' . $current_user->ID . '&orderby=modified&order=DESC&showposts=100');
            $ppn_relevant_posts = array();
            if( $ppn_author_posts ) {
                foreach ($ppn_author_posts as $ppn_author_post) {
                    $ppn_relevant_posts[] = $ppn_author_post->ID;
                }
            }
        
            // Now we want to grab any posts on which this person has created a note
            $ppn_associated_posts = $wpdb->get_results('SELECT postid FROM ' . $ppn_db_notes . ' WHERE author = ' . $current_user->ID . ' ORDER BY notetime DESC', OBJECT);
            if( $ppn_associated_posts ) {
                foreach($ppn_associated_posts as $ppn_associated_post) {
                    $ppn_relevant_posts[] = $ppn_associated_post->postid;
                }
            }
            $ppn_relevant_posts = array_unique($ppn_relevant_posts);
        
            $ppn_relevant_post_list = implode(',', $ppn_relevant_posts);

            // Make sure there are actually relevant posts
            if( '' != $ppn_relevant_post_list ) {

                // Grab notes for posts on which the current user was an author or already made a comment
                $ppn_query_options .= ' WHERE postid IN (' . $ppn_relevant_post_list . ') ';
            
                if( $ppn_show_which_notes == 'others' ) {
                    // Grab notes written by people other than the author, for posts on which the current user was an author or already made a comment
                    $ppn_query_options .= ' AND author != ' . $current_user->ID;
                }
            }
        }
    }

    if( $ppn_show_which_notes == 'all' || $is_super || '' != $ppn_relevant_post_list) {

        if( !$ppn_num_notes_limit ) {
            $ppn_num_notes = '';
        }
        else {
            $ppn_num_notes = ' LIMIT ' . $ppn_num_notes_limit;
        }

        $ppn_newest_posts = $wpdb->get_results('SELECT postid, author, notetime, notecontent FROM ' . $ppn_db_notes . $ppn_query_options . ' ORDER BY notetime DESC' . $ppn_num_notes, OBJECT);
    }

    if( $ppn_newest_posts ) {
        print '<ul>' . "\n";
        foreach ($ppn_newest_posts as $ppn_newest_post) {
            $author_name = ppnFunctionCollection::get_author_name( $ppn_newest_post->author );
            $ppn_post = get_post($ppn_newest_post->postid, OBJECT);
            
            print '<li><strong>' . $author_name . '</strong>: ' . stripslashes($ppn_newest_post->notecontent) . '<br />';
            print '<em>' . date(__('M j, Y \a\t G:i', 'peters_post_notes'), strtotime($ppn_newest_post->notetime)) . '</em> '.__('on', 'peters_post_notes').' ';
            print '<a href="post.php?action=edit&post=' . $ppn_newest_post->postid . '">' . $ppn_post->post_title . '</a> (' . $ppn_post->post_status . ')';
            print '</li>' . "\n";
        }
        
        print '</ul>' . "\n";
    }
    else {
        print '<p>' . __('No relevant notes.', 'peters_post_notes') . '</p>';
    }
}
function ppn_add_dashboard_general() {
    wp_add_dashboard_widget( 'ppn_dashboard_general', __('General Notes', 'peters_post_notes'), 'ppn_dashboard_general' );
}

function ppn_dashboard_general() {
    global $wpdb, $ppn_db_generalnotes;

    $current_user = wp_get_current_user();

    
    if( isset( $_POST['ppn_submit_generalnote'] ) )
    {
    
        if( !empty( $_POST['ppn_post_generalnote'] ) )
        {
            // Is the private checkbox marked?
            $ppn_private = 0;
            if( isset( $_POST['ppn_private'] ) && 'on' == $_POST['ppn_private'] )
            {
                $ppn_private = 1;
            }
            
            $ppn_post_generalnote = ppnFunctionCollection::note_scrub( $_POST['ppn_post_generalnote'] );
            
            $wpdb->insert(
                $ppn_db_generalnotes,
                array('notecontent' => $ppn_post_generalnote,
                    'author' => $current_user->ID,
                    'notetime' => current_time('mysql'),
                    'personal' => $ppn_private
                )
            );
        }
    }

    $ppn_dashboard_general_newest = ppn_dashboard_general_newest(0, 0);
    
    if( !empty($ppn_dashboard_general_newest) ) {
        print '<div id="ppn_dashboard_general_newest">' . "\n";
        print $ppn_dashboard_general_newest;
        print '</div>' . "\n";
    }
    
    $ppn_dashboard_general_personal = ppn_dashboard_general_newest(0, 1);
    
    if( !empty($ppn_dashboard_general_personal) ) {
        print '<hr /><br />';
        print '<h4>Personal notes:</h4>' . "\n";
        print '<div id="ppn_dashboard_general_personal">' . "\n";
        print $ppn_dashboard_general_personal;
        print '</div>' . "\n";
    }
    
    if( empty($ppn_dashboard_general_newest) && empty($ppn_dashboard_general_personal) ) {
       print '<p>' . __('No general notes.', 'peters_post_notes') . '</p>';
    }

    print '<hr /><br />' . "\n";
    print '<form name="ppn_add_generalnote" method="post" action="index.php">';
    print '<label for="ppn_post_generalnote">' . __('Add note:', 'peters_post_notes') . '</label><br />' . "\n";
    print '<textarea rows="3" cols="60" name="ppn_post_generalnote" style="width: 99%;"></textarea><br />' . "\n";
    print '<input type="checkbox" name="ppn_private" /> ' . __('Private note', 'peters_post_notes') . "\n";
    print '<input name="ppn_submit_generalnote" type="submit" class="button" value="' . __('Add', 'peters_post_notes') . '" />' . "\n";
    print '</form>' . "\n";
}

function ppn_dashboard_general_newest($ppn_page=0, $ppn_personal=0) {
    global $wpdb, $ppn_num_notes_limit, $ppn_db_generalnotes, $ppn_superedit_roles, $ppn_superedit_caps;
    
    $out = '';
    
    $current_user = wp_get_current_user();
    
    // Find out what page of entries you are looking for
    $ppn_page = max( intval( $ppn_page ), 0 );
    
    $ppn_personal = min( intval( $ppn_personal ), 1 );
    if( 1 == $ppn_personal ) {
        $ppn_personal_query = $ppn_personal . ' AND author = ' . $current_user->ID;
    }
    else {
        $ppn_personal_query = $ppn_personal;
    }

    if( !$ppn_num_notes_limit ) {
        $ppn_num_notes = '';
    }
    
    else {
        $ppn_start_at = $ppn_page * $ppn_num_notes_limit;
        $ppn_num_notes = ' LIMIT ' . $ppn_start_at . ', ' . $ppn_num_notes_limit;
    }
    
    $ppn_num_newest_posts = $wpdb->get_var('SELECT COUNT(*) FROM ' . $ppn_db_generalnotes . ' WHERE personal = ' . $ppn_personal_query);

    if( $ppn_num_newest_posts < $ppn_start_at ) {
        return 'Not enough entries to go that high.';
    }
    
    // How many pages of content are there?
    $ppn_total_pages = ceil($ppn_num_newest_posts / $ppn_num_notes_limit);

    
    if( $ppn_num_newest_posts ) {
        $ppn_newest_posts = $wpdb->get_results('SELECT noteid, author, notetime, notecontent FROM ' . $ppn_db_generalnotes . ' WHERE personal = ' . $ppn_personal_query . ' ORDER BY notetime DESC' . $ppn_num_notes, OBJECT);
        
        $is_supereditor = ppnFunctionCollection::is_super( $current_user, $ppn_superedit_roles, $ppn_superedit_caps );
        $out = '<ul>' . "\n";
        foreach ($ppn_newest_posts as $ppn_newest_post) {
            $author_name = ppnFunctionCollection::get_author_name( $ppn_newest_post->author );
            
            $out .= '<li id="ppn_entire_note_' . $ppn_newest_post->noteid . '">';
            $out .= '<div id="ppn_noteerror_' . $ppn_newest_post->noteid . '"></div>' . "\n";
            $out .= '<div id="ppn_notecontent_' . $ppn_newest_post->noteid . '">' . '<p>';
            if( 0 == $ppn_personal ) {
                $out .= '<strong>' . $author_name . '</strong>: ';
            }
            $out .= '<span id="ppn_notecontent_p_' . $ppn_newest_post->noteid . '">' . stripslashes($ppn_newest_post->notecontent) . '</span>' . "\n";
            $out .= '<br /><em>' . date(__('M j, Y \a\t G:i', 'peters_post_notes'), strtotime($ppn_newest_post->notetime)) . '</em>';
            if( $current_user->ID == $ppn_newest_post->author || $is_supereditor) {
                $out .= '<br /><a onclick="ppn_ajax_edit_form(' . $ppn_newest_post->noteid . '); return false;" href="javascript:;">Edit</a>';
            }
            $out .= '</p>';
            $out .= '</div>' . "\n";
            if( $current_user->ID == $ppn_newest_post->author || $is_supereditor ) {
                $out .= '<form name="ppn_note_' . $ppn_newest_post->noteid . '">' . "\n";
                $out .= '<div id="ppn_noteform_' . $ppn_newest_post->noteid . '" style="display: none;" >' . "\n";
                $out .= '<p style="float:left;"><a onclick="ppn_ajax_delete_note(' . $ppn_newest_post->noteid . ', 0); return false;" href="#">Delete</a></p>' . "\n";
                $out .= '<p style="clear:both;"><textarea name="ppn_note_text_' . $ppn_newest_post->noteid . '" id="ppn_note_' . $ppn_newest_post->noteid . '" cols="30" style="width: 99%;">' . ppnFunctionCollection::preserve_code( $ppn_newest_post->notecontent ) . '</textarea></p>' . "\n";
                $out .= '<p><input type="hidden" name="note_id" value="' . $ppn_newest_post->noteid . '" /><input type="button" class="button button-highlighted" value="' . __('Save', 'peters_post_notes') . '" onclick="ppn_ajax_edit_note(' . $ppn_newest_post->noteid . ',this.form.ppn_note_text_' . $ppn_newest_post->noteid . ', 0);" />';
                $out .= ' <a onclick="ppn_ajax_edit_form_cancel(' . $ppn_newest_post->noteid . '); return false;" href="javascript:;">' . __('Cancel') . '</a></p>' . "\n";
                $out .= '</div>' . "\n";
                $out .= '</form>' . "\n";
            }
            $out .= '</li>' . "\n";
        }        
        $out .= '</ul>' . "\n";
        if( 1 != $ppn_total_pages ) {
            $out .= '<p>';
                if( ($ppn_page + 1) < $ppn_total_pages ) {
                    $out .= '<a onclick="ppn_ajax_load_page(' . ($ppn_page + 1) . ', ' . $ppn_personal . '); return false;" href="javascript:;">&laquo; prev</a>';
                }
                if( 0 != $ppn_page ) {
                    if( ($ppn_page + 1) != $ppn_total_pages ) {
                        $out .= ' |';
                    }
                    $out .= ' <a onclick="ppn_ajax_load_page(' . ($ppn_page - 1) . ', ' . $ppn_personal . '); return false;" href="javascript:;">next &raquo;</a>';
                }
            $out .= '</p>' . "\n";
        }
    }
    
    return $out;
}

/* --------------------------------------------
Add and remove tables when installing and uninstalling
---------------------------------------------*/

function ppn_install() {
    global $wpdb, $ppn_db_notes, $ppn_db_generalnotes, $ppn_version;
    
    $return = '';
    
    // Add the table to hold post-specific notes
    if($wpdb->get_var('SHOW TABLES LIKE \'' . $ppn_db_notes . '\'') != $ppn_db_notes) {
        $sql = 'CREATE TABLE ' . $ppn_db_notes . ' (
        `noteid` bigint(20) NOT NULL auto_increment,
        `postid` bigint(20) NOT NULL,
        `notecontent` text NOT NULL,
        `author` bigint(20) NOT NULL,
        `notetime` datetime NOT NULL,
        UNIQUE KEY `noteid` (noteid)
        ) AUTO_INCREMENT=1;';
        
        $added_notes_table = $wpdb->query($sql);
        if( $added_notes_table === 0 ) {
            $return .= __('<p>Added notes table!</p>', 'peters_post_notes');
        }
    }

    // Add the table to hold general notes
    if($wpdb->get_var('SHOW TABLES LIKE \'' . $ppn_db_generalnotes . '\'') != $ppn_db_generalnotes) {
        $sql = 'CREATE TABLE ' . $ppn_db_generalnotes . ' (
        `noteid` bigint(20) NOT NULL auto_increment,
        `notecontent` text NOT NULL,
        `author` bigint(20) NOT NULL,
        `notetime` datetime NOT NULL,
        `personal` BINARY NOT NULL DEFAULT \'1\',
        UNIQUE KEY `noteid` (noteid)
        ) AUTO_INCREMENT=1;';

        $added_generalnotes_table = $wpdb->query($sql);
        
        if( $added_generalnotes_table === 0 ) {
            $return .= __('<p>Added general notes table!</p>', 'peters_post_notes');
        }
    }

    // Store version number in the database
    add_option( 'ppn_version', $ppn_version, '', 'no' );
    
    return $return;
}

function ppn_install_link($action_links, $plugin_file, $plugin_data, $context) {
    if( $context == 'active' ) {
        $action_links[] = '<a href="options-general.php?page=' . basename(__FILE__) . '" title="' . __('Only necessary if you updated the plugin manually', 'peters_post_notes') . '">Update DB</a>';
    }
    return $action_links;
}

function ppn_upgrade_page() {
    $ppn_install = ppn_install();
?>
    <div class="wrap">
        <h2><?php _e('Peter\'s Post Notes: status', 'peters_post_notes'); ?></h2>
        <p><?php _e('The only purpose of this settings page is to install the database tables when manually upgrading the plugin.', 'peters_post_notes'); ?></p>
<?php
    if( empty($ppn_install) ) {
        print __('<p>Database tables for this plugin are properly installed.</p>', 'peters_post_notes');
    }
    else {
        print $ppn_install;
    }
?>
    </div>
<?php
}

function ppn_adminmenu()
{
    add_options_page( __( 'Peter\'s Post Notes', 'peters_post_notes' ), __( 'Peter\'s Post Notes', 'peters_post_notes' ), 'activate_plugins', basename( __FILE__ ), 'ppn_upgrade_page' );
}

add_action( 'admin_menu','ppn_adminmenu' );

function ppn_uninstall()
{
    global $wpdb, $ppn_db_notes, $ppn_db_generalnotes;

    if( $ppn_db_notes == $wpdb->get_var('SHOW TABLES LIKE \'' . $ppn_db_notes . '\'') )
    {
        $sql = 'DROP TABLE ' . $ppn_db_notes;
        $wpdb->query($sql);
    }

    if( $ppn_db_generalnotes == $wpdb->get_var('SHOW TABLES LIKE \'' . $ppn_db_generalnotes . '\'') )
    {
        $sql = 'DROP TABLE ' . $ppn_db_generalnotes;
        $wpdb->query($sql);
    }

    delete_option( 'ppn_version' );
}

/* --------------------------------------------
JavaScript used in this plugin
---------------------------------------------*/

function ppn_js_admin_header() {
  wp_enqueue_script( 'sack' );
  // Load jQuery as well, although not for Ajax in our case
  wp_enqueue_script( 'jquery' );
  wp_register_script( 'peters_post_notes', WP_PLUGIN_URL . '/' . dirname(plugin_basename(__FILE__)) . '/peters_post_notes.js', array( 'sack' ) );
  wp_enqueue_script( 'peters_post_notes' );
}

/* --------------------------------------------
Function to edit notes
---------------------------------------------*/

add_action('wp_ajax_ppn_edit_note', 'ppn_edit_note' );

function ppn_edit_note() {
    global $wpdb, $ppn_db_notes, $ppn_db_generalnotes, $ppn_superedit_roles, $ppn_superedit_caps;
    
    $current_user = wp_get_current_user();
    $is_supereditor = ppnFunctionCollection::is_super( $current_user, $ppn_superedit_roles, $ppn_superedit_caps );

    // read submitted information

    $note_id = intval($_POST['note_id']);
    
    $note_text = ppnFunctionCollection::note_scrub( $_POST['note_text'] );
    
    $note_type = substr($_POST['note_type'], 0, 7);
    
    switch( $note_type ) {
        case 0: $ppn_db_table = $ppn_db_generalnotes; break;
        case 1: $ppn_db_table = $ppn_db_notes; break;
    }
    
    $ppn_author = $wpdb->get_var('SELECT author from ' . $ppn_db_table . ' WHERE noteid = ' . $note_id . ' LIMIT 1');

    // If this user allowed to edit this note?
    if( $current_user->ID != $ppn_author && !$is_supereditor) {
        die('document.getElementById("ppn_noteerror_' . $note_id . '").innerHTML = \'<p style="color:red;">' . __('Error: You can only edit notes that you wrote.', 'peters_post_notes') . '</p>\';
        document.getElementById("ppn_noteform_' . $note_id . '").style.display = "none";
        document.getElementById("ppn_notecontent_' . $note_id . '").style.display = "";');
    }
    
    // Edit the note!
    $ppn_editnotesuccess = $wpdb->update(
        $ppn_db_table,
        array ('notecontent' => $note_text),
        array ('noteid' => $note_id));
        
    if( $ppn_editnotesuccess ) {
        $note_text = str_replace( "\n", '\\n', $note_text );
        $note_text = str_replace( "\r", '\\r', $note_text );
        die( 'document.getElementById("ppn_noteerror_' . $note_id . '").innerHTML = "";
            document.getElementById("ppn_notecontent_p_' . $note_id . '").innerHTML = "' . ppnFunctionCollection::prepare_js_from_kses( $note_text ) . '"; ppn_fadeedit("ppn_notecontent_p_' . $note_id . '");
            document.getElementById("ppn_noteform_' . $note_id . '").style.display = "none";
            document.getElementById("ppn_notecontent_' . $note_id . '").style.display = "";');
    }
    // Database error
    elseif( $ppn_editnotesuccess === false ) {
        die( 'document.getElementById("ppn_noteerror_' . $note_id . '").innerHTML = \'<p style="color:red;">' . __('Error: Unknown or database problem when updating note.', 'peters_post_notes') . '</p>\';
            document.getElementById("ppn_noteform_' . $note_id . '").style.display = "none";
            document.getElementById("ppn_notecontent_' . $note_id . '").style.display = "";');
    }
    // Here they actually didn't update anything
    else {
        die( 'document.getElementById("ppn_noteerror_' . $note_id . '").innerHTML = "";
            document.getElementById("ppn_noteform_' . $note_id . '").style.display = "none";
            document.getElementById("ppn_notecontent_' . $note_id . '").style.display = "";');
    }
}

/* --------------------------------------------
Function to delete posts
---------------------------------------------*/

add_action('wp_ajax_ppn_delete_note', 'ppn_delete_note' );

function ppn_delete_note() {
    global $wpdb, $ppn_db_notes, $ppn_db_generalnotes, $ppn_superedit_roles, $ppn_superedit_caps;
    
    $current_user = wp_get_current_user();
    $is_supereditor = ppnFunctionCollection::is_super( $current_user, $ppn_superedit_roles, $ppn_superedit_caps );

    // read submitted information
    $note_id = intval($_POST['note_id']);
    $note_type = substr($_POST['note_type'], 0, 7);
    
    switch($note_type) {
        case 0: $ppn_db_table = $ppn_db_generalnotes; break;
        case 1: $ppn_db_table = $ppn_db_notes; break;
    }
    
    $ppn_author = $wpdb->get_var('SELECT author from ' . $ppn_db_table . ' WHERE noteid = ' . $note_id . ' LIMIT 1');

    // If this user allowed to delete this note?
    if( $current_user->ID != $ppn_author && !$is_supereditor ) {
        die('document.getElementById("ppn_noteerror_' . $note_id . '").innerHTML = \'<p style="color:red;">' . __('Error: You can only delete notes that you wrote.', 'peters_post_notes') . '</p>\';
        document.getElementById("ppn_noteform_' . $note_id . '").style.display = "none";
        document.getElementById("ppn_notecontent_' . $note_id . '").style.display = "";');
    }
    
    // Delete the note!
    $ppn_deletenotesuccess = $wpdb->query('DELETE FROM ' . $ppn_db_table . ' WHERE noteid = ' . $note_id . ' LIMIT 1');
    
    if( $ppn_deletenotesuccess ) {
        die( 'ppn_fadeout("ppn_entire_note_' . $note_id . '");');
    }
    // Database error
    else {
        die( 'document.getElementById("ppn_noteerror_' . $note_id . '").innerHTML = \'<p style="color:red;">' . __('Error: Unknown or database problem when deleting note.', 'peters_post_notes') . '</p>\';
            document.getElementById("ppn_noteform_' . $note_id . '").style.display = "none";
            document.getElementById("ppn_notecontent_' . $note_id . '").style.display = "";');
    }
}

/* --------------------------------------------
Function to load new page of notes
---------------------------------------------*/

add_action('wp_ajax_ppn_load_page', 'ppn_load_page' );

function ppn_load_page() {

    $ppn_page = intval($_POST['ppn_page']);
    $ppn_personal = intval($_POST['ppn_personal']);
    
    $ppn_dashboard_general_newest = ppn_dashboard_general_newest($ppn_page, $ppn_personal);
    $ppn_dashboard_general_newest = str_replace("\n", '\\n', $ppn_dashboard_general_newest);
    $ppn_dashboard_general_newest = str_replace("\r", '\\r', $ppn_dashboard_general_newest);
    
    if( 0 == $ppn_personal ) {
        die('document.getElementById("ppn_dashboard_general_newest").innerHTML = "' . ppnFunctionCollection::prepare_js_from_kses( $ppn_dashboard_general_newest ) . '";');
    }
    if( 1 == $ppn_personal ) {
        die('document.getElementById("ppn_dashboard_general_personal").innerHTML = "' . ppnFunctionCollection::prepare_js_from_kses( $ppn_dashboard_general_newest ) . '";');
    }
}

register_activation_hook( __FILE__, 'ppn_install' );
register_uninstall_hook( __FILE__, 'ppn_uninstall' );
add_action( 'admin_menu', 'ppn_add_meta_box' );
add_action( 'wp_dashboard_setup', 'ppn_add_dashboard' );
add_action( 'wp_dashboard_setup', 'ppn_add_dashboard_general' );
add_action( 'edit_post', 'ppn_save_note', 10, 2 );
add_action( 'delete_post', 'ppn_delete_notes', 10, 1 );
add_filter( 'plugin_action_links_' . basename(__FILE__), 'ppn_install_link', 10, 4 );
// Would be more efficient to only load the plugin's JS on certain admin pages, but with custom post types it's better not to frustrate the developer
//if( $pagenow == 'post.php' || $pagenow == 'page.php' || $pagenow == 'index.php' )
add_action('admin_print_scripts', 'ppn_js_admin_header' );
if( $ppn_show_latest_notes_column )
{
    add_filter( 'manage_posts_columns', array( 'ppnFunctionCollection', 'notes_column_header' ) );
    add_action( 'manage_posts_custom_column', array( 'ppnFunctionCollection', 'notes_column_content' ) );
}

} // This closes that initial check to make sure someone is actually logged in
?>