<?php
require_once('wpframe.php');

if(!is_single() and isset($GLOBALS['quizzin_client_includes_loaded'])) { #If this is in the listing page - and a quiz is already shown, don't show another.
	printf(t("Please go to <a href='%s'>%s</a> to view the quiz"), get_permalink(), get_the_title());
} else {

global $wpdb;
$GLOBALS['wpframe_plugin_name'] = basename(dirname(__FILE__));
$GLOBALS['wpframe_plugin_folder'] = $GLOBALS['wpframe_wordpress'] . '/wp-content/plugins/' . $GLOBALS['wpframe_plugin_name'];

$answer_display = get_option('quizzin_show_answers');
$all_question = $wpdb->get_results($wpdb->prepare("SELECT ID,question,explanation FROM {$wpdb->prefix}quiz_question WHERE quiz_id=%d ORDER BY ID", $quiz_id));
if($all_question) {
	if(!isset($GLOBALS['quizzin_client_includes_loaded'])) {
?>
<link type="text/css" rel="stylesheet" href="<?php echo $GLOBALS['wpframe_plugin_folder']?>/style.css" />
<script type="text/javascript" src="<?php echo $GLOBALS['wpframe_wordpress']?>/wp-includes/js/jquery/jquery.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['wpframe_plugin_folder']?>/script.js"></script>
<?php
	$GLOBALS['quizzin_client_includes_loaded'] = true; // Make sure that this code is not loaded more than once.
}


if(isset($_REQUEST['action']) and $_REQUEST['action']) { // Quiz Reuslts.
	$score = 0;
	$total = 0;
	
	$result = '';
	$result .= "<p>" . t('All the questions in the quiz along with their answers are shown below. Your answers are bolded. The correct answers have a green background while the incorrect ones have a red background.') . "</p>";
	
	foreach ($all_question as $ques) {
		$result .= "<div class='show-question'>";
		$result .= "<div class='show-question-content'>". stripslashes($ques->question) . "</div>\n";
		$all_answers = $wpdb->get_results("SELECT ID,answer,correct FROM {$wpdb->prefix}quiz_answer WHERE question_id={$ques->ID} ORDER BY sort_order");
		
		$correct = false;
		$result .= "<ul>";
		foreach ($all_answers as $ans) {
			$class = 'answer';
			if($ans->ID == $_REQUEST["answer-" . $ques->ID]) $class .= ' user-answer';
			if($ans->correct == 1) $class .= ' correct-answer';
			if($ans->ID == $_REQUEST["answer-" . $ques->ID] and $ans->correct == 1) {$correct = true; $score++;}
			
			$result .= "<li class='$class'><span class='answer'>" . stripslashes($ans->answer) . "</span></li>\n";
		}
		$result .= "</ul>";
		if(!$_REQUEST["answer-" . $ques->ID]) $result .= "<p class='unanswered'>" . t('Question was not answered') . "</p>";
		$result .= "<p class='explanation'>" . stripslashes($ques->explanation) . "</p>";
		
		$result .= "</div>";
		$total++;
	}
	
	//Find scoring details of this guy.
	$percent = number_format($score / $total * 100, 2);
						//0-9			10-19%,	 	20-29%, 	30-39%			40-49%						
	$all_rating = array(t('Failed'), t('Failed'), t('Failed'), t('Failed'), t('Just Passed'), 
						//																			100%			More than 100%?!
					t('Satisfactory'), t('Competent'), t('Good'), t('Very Good'),t('Excellent'), t('Unbeatable'), t('Cheater'));
	$grade = intval($percent / 10);
	if($percent == 100) $grade = 9;
	if($score == $total) $grade = 10;
	$rating = $all_rating[$grade];
	
	$quiz_details = $wpdb->get_row($wpdb->prepare("SELECT name,final_screen, description FROM {$wpdb->prefix}quiz_quiz WHERE ID=%d", $quiz_id));
	
	$replace_these	= array('%%SCORE%%', '%%TOTAL%%', '%%PERCENTAGE%%', '%%GRADE%%', '%%RATING%%', '%%CORRECT_ANSWERS%%', '%%WRONG_ANSWERS%%', '%%QUIZ_NAME%%',	  '%%DESCRIPTION%%');
	$with_these		= array($score,		 $total,	  $percent,			$grade,		 $rating,		$score,					$total-$score,	   stripslashes($quiz_details->name), stripslashes($quiz_details->description));
	
	// Show the results
	
	print str_replace($replace_these, $with_these, stripslashes($quiz_details->final_screen));
	if($answer_display == 1) print '<hr />' . $result;

} else { // Show The Quiz.
	$single_page = get_option('quizzin_single_page');
?>

<div class="quiz-area <?php if($single_page) echo 'single-page-quiz'; ?>">
<form action="" method="post" class="quiz-form" id="quiz-<?php echo $quiz_id?>">
<?php
$question_count = 1;

foreach ($all_question as $ques) {
	echo "<div class='quizzin-question' id='question-$question_count'>";
	echo "<div class='question-content'>". stripslashes($ques->question) . "</div><br />";
	echo "<input type='hidden' name='question_id[]' value='{$ques->ID}' />";
	$dans = $wpdb->get_results("SELECT ID,answer,correct FROM {$wpdb->prefix}quiz_answer WHERE question_id={$ques->ID} ORDER BY sort_order");
	foreach ($dans as $ans) {
		if($answer_display == 2) {
			$answer_class = 'wrong-answer-label';
			if($ans->correct) $answer_class = 'correct-answer-label';
		}
		echo "<input type='radio' name='answer-{$ques->ID}' id='answer-id-{$ans->ID}' class='answer answer-$question_count $answer_class' value='{$ans->ID}' />";
		echo "<label for='answer-id-{$ans->ID}' id='answer-label-{$ans->ID}' class='$answer_class answer label-$question_count'><span>" . stripslashes($ans->answer) . "</span></label><br />";
	}
	
	echo "</div>";
	$question_count++;
}

?><br />
<?php if($answer_display == 2) { ?>
<input type="button" id="show-answer" value="<?php e("Show Answer") ?>"  /><br />
<?php } else { ?>
<input type="button" id="next-question" value="<?php e("Next") ?> &gt;"  /><br />
<?php } ?>

<input type="submit" name="action" id="action-button" value="<?php e("Show Results") ?>"  />
<input type="hidden" name="quiz_id" value="<?php echo  $quiz_id ?>" />
</form>
</div>

<?php }
}
}
?>