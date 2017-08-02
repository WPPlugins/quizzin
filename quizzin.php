<?php
/*
Plugin Name: Quizzin
Plugin URI: http://www.bin-co.com/tools/wordpress/plugins/quizzin/
Description: Quizzin lets you add quizzes to your blog. This plugin is designed to be as easy to use as possible. Quizzes, questions and answers can be added from the admin side. This will appear in your post if you add a small HTML snippet in your post.
Version: 1.01.4
Author: Binny V A
Author URI: http://binnyva.com/
*/

/**
 * Add a new menu under Manage, visible for all users with template viewing level.
 */
add_action( 'admin_menu', 'quizzin_add_menu_links' );
function quizzin_add_menu_links() {
	global $wp_version, $_registered_pages;
	$view_level= 'administrator';
	$page = 'edit.php';
	if($wp_version >= '2.7') $page = 'tools.php';
	
	add_submenu_page($page, __('Manage Quiz', 'quizzin'), __('Manage Quiz', 'quizzin'), $view_level, 'quizzin/quiz.php');
	$code_pages = array('quiz_form.php','quiz_action.php', 'question_form.php', 'question.php');
	foreach($code_pages as $code_page) {
		$hookname = get_plugin_page_hookname("quizzin/$code_page", '' );
		$_registered_pages[$hookname] = true;
	}
}

/// Initialize this plugin. Called by 'init' hook.
add_action('init', 'quizzin_init');
function quizzin_init() {
	load_plugin_textdomain('quizzin', 'wp-content/plugins' );
}

/// Add an option page for Quizzin
add_action('admin_menu', 'quizzin_option_page');
function quizzin_option_page() {
	add_options_page(__('Quizzin Settings', 'quizzin'), __('Quizzin Settings', 'quizzin'), 'administrator', basename(__FILE__), 'quizzin_options');
}
function quizzin_options() {
	if ( function_exists('current_user_can') && !current_user_can('manage_options') ) die(__("Cheatin' uh?", 'quizzin'));
	if (! user_can_access_admin_page()) wp_die( __('You do not have sufficient permissions to access this page', 'quizzin') );

	require(ABSPATH. '/wp-content/plugins/quizzin/options.php');
}

/**
 * This will scan all the content pages that wordpress outputs for our special code. If the code is found, it will replace the requested quiz.
 */
 add_shortcode( 'QUIZZIN', 'quizzin_shortcode' );
function quizzin_shortcode( $attr ) {
	$quiz_id = $attr[0];
	
	$contents = '';
	if(is_numeric($quiz_id)) { // Basic validiation - more on the show_quiz.php file.
		ob_start();
		include(ABSPATH . 'wp-content/plugins/quizzin/show_quiz.php');
		$contents = ob_get_contents();
		ob_end_clean();
	}
	return $contents;
}

add_action('activate_quizzin/quizzin.php','quizzin_activate');
function quizzin_activate() {
	global $wpdb;
	
	$database_version = '3';
	$installed_db = get_option('quizzin_db_version');
	// Initial options.
	add_option('quizzin_show_answers', 1);
	add_option('quizzin_single_page', 0);
	
	if($database_version != $installed_db) {
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	
		$sql = "CREATE TABLE {$wpdb->prefix}quiz_answer (
					ID int(11) unsigned NOT NULL auto_increment,
					question_id int(11) unsigned NOT NULL,
					answer varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					correct enum('0','1') NOT NULL default '0',
					sort_order int(3) NOT NULL default 0,
					PRIMARY KEY  (ID)
				);
				CREATE TABLE {$wpdb->prefix}quiz_question (
					ID int(11) unsigned NOT NULL auto_increment,
					quiz_id int(11) unsigned NOT NULL,
					question mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					sort_order int(3) NOT NULL default 0,
					explanation mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					PRIMARY KEY  (ID),
					KEY quiz_id (quiz_id)
				);
				CREATE TABLE {$wpdb->prefix}quiz_quiz (
					ID int(11) unsigned NOT NULL auto_increment,
					name varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					description mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					final_screen mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					added_on datetime NOT NULL,
					PRIMARY KEY  (ID)
				);";
		dbDelta($sql);
		update_option( "quizzin_db_version", $database_version );
	}
}
