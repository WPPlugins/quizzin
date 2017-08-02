<?php
require('wpframe.php');
wpframe_stop_direct_call(__FILE__);

$action = 'new';
if($_REQUEST['action'] == 'edit') $action = 'edit';

if(isset($_REQUEST['submit'])) {
	if($action == 'edit'){ //Update goes here
		$wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}quiz_question SET question=%s, explanation=%s WHERE ID=%d", $_REQUEST['content'], $_REQUEST['explanation'], $_REQUEST['question']));
		$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}quiz_answer WHERE question_id=%d", $_REQUEST['question']));
		
		wpframe_message('Question updated.');
		
	} else {
		$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}quiz_question(quiz_id, question, explanation) VALUES(%d, %s, %s)", $_REQUEST['quiz'], $_REQUEST['content'], $_REQUEST['explanation']));//Inserting the questions
		wpframe_message('Question added.');
		$_REQUEST['question'] = $wpdb->insert_id;
		$action='edit';
	}
	$question_id = $_REQUEST['question'];
	
	//Yes, we need 2 different counters - the $counter will skip over empty answers - $sort_order_counter won't.
	$counter = 1;
	$sort_order_counter = 1;
	foreach ($_REQUEST['answer'] as $answer_text) {
		$correct=0;
		if($_REQUEST['correct_answer'] == $counter) $correct=1;
		
		if($answer_text) {
			$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}quiz_answer(question_id,answer,correct, sort_order) 
				VALUES(%d, %s, %s, %d)", $question_id, $answer_text, $correct, $sort_order_counter)); 
			$sort_order_counter++;
		}
		$counter++;
	}
}


if($_REQUEST['message'] == 'new_quiz') {
	wpframe_message('New quiz added');
}

if($_REQUEST['action'] == 'delete') {
	$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}quiz_answer WHERE question_id=%d", $_REQUEST['question']));
	$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}quiz_question WHERE ID=%d", $_REQUEST['question']));
	wpframe_message('Question Deleted.');
}
$quiz_name = stripslashes($wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}quiz_quiz WHERE ID=%d", $_REQUEST['quiz'])));
?>

<div class="wrap">
<h2><?php echo t("Manage Questions in ") . $quiz_name; ?></h2>

<?php
wp_enqueue_script( 'listman' );
wp_print_scripts();
?>

<p><?php e('To add this quiz to your blog, insert the code ') ?> [QUIZZIN <?php echo $_REQUEST['quiz'] ?>] <?php e('into any post.') ?></p>

<table class="widefat">
	<thead>
	<tr>
		<th scope="col"><div style="text-align: center;">#</div></th>
		<th scope="col"><?php e('Question') ?></th>
		<th scope="col"><?php e('Number Of Answers') ?></th>
		<th scope="col" colspan="3"><?php e('Action') ?></th>
	</tr>
	</thead>

	<tbody id="the-list">
<?php
// Retrieve the questions
$all_question = $wpdb->get_results("SELECT Q.ID,Q.question,(SELECT COUNT(*) FROM {$wpdb->prefix}quiz_answer WHERE question_id=Q.ID) AS answer_count
										FROM `{$wpdb->prefix}quiz_question` AS Q
										WHERE Q.quiz_id=$_REQUEST[quiz] ORDER BY Q.ID");

if (count($all_question)) {
	$bgcolor = '';
	$class = ('alternate' == $class) ? '' : 'alternate';
	$question_count = 0;
	foreach($all_question as $question) {
		$question_count++;
		print "<tr id='question-{$question->ID}' class='$class'>\n";
		?>
		<th scope="row" style="text-align: center;"><?php echo $question_count ?></th>
		<td><?php echo stripslashes($question->question) ?></td>
		<td><?php echo $question->answer_count ?></td>
		<td><a href='edit.php?page=quizzin/question_form.php&amp;question=<?php echo $question->ID?>&amp;action=edit&amp;quiz=<?php echo $_REQUEST['quiz']?>' class='edit'><?php e('Edit'); ?></a></td>
		<td><a href='edit.php?page=quizzin/question.php&amp;action=delete&amp;question=<?php echo $question->ID?>&amp;quiz=<?php echo $_REQUEST['quiz']?>' class='delete' onclick="return confirm('<?php echo addslashes(t("You are about to delete this question. This will delete the answers to this question. Press 'OK' to delete and 'Cancel' to stop."))?>');"><?php e('Delete')?></a></td>
		</tr>
<?php
		}
	} else {
?>
	<tr style='background-color: <?php echo $bgcolor; ?>;'>
		<td colspan="4"><?php e('No questiones found.') ?></td>
	</tr>
<?php
}
?>
	</tbody>
</table>

<a href="edit.php?page=quizzin/question_form.php&amp;action=new&amp;quiz=<?php echo $_REQUEST['quiz'] ?>"><?php e('Create New Question')?></a>
</div>
