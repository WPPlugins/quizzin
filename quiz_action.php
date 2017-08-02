<?php
require('../../../wp-blog-header.php');
auth_redirect();
if($wp_version >= '2.6.5') check_admin_referer('quizzin_create_edit_quiz');
require('wpframe.php');

// I could have put this in the quiz_form.php - but the redirect will not work.
if(isset($_REQUEST['submit'])) {
	if($_REQUEST['action'] == 'edit') { //Update goes here
		$wpdb->get_results($wpdb->prepare("UPDATE {$wpdb->prefix}quiz_quiz SET name=%s, description=%s,final_screen=%s WHERE ID=%d", $_REQUEST['name'], $_REQUEST['description'], $_REQUEST['content'], $_REQUEST['quiz']));
		
		wp_redirect($wpframe_home . '/wp-admin/edit.php?page=quizzin/quiz.php&message=updated');
	
	} else {
		$wpdb->get_results($wpdb->prepare("INSERT INTO {$wpdb->prefix}quiz_quiz(name,description,final_screen,added_on) VALUES(%s,%s,%s,NOW())", $_REQUEST['name'], $_REQUEST['description'], $_REQUEST['content']));
		$quiz_id = $wpdb->insert_id;
		wp_redirect($wpframe_home . '/wp-admin/edit.php?page=quizzin/question.php&message=new_quiz&quiz='.$quiz_id);
	}
}
exit;
