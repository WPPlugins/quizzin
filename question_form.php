<?php
require('wpframe.php');
wpframe_stop_direct_call(__FILE__);

$action = 'new';
if($_REQUEST['action'] == 'edit') $action = 'edit';

$question= $wpdb->get_row($wpdb->prepare("SELECT question, explanation FROM {$wpdb->prefix}quiz_question WHERE ID=%d", $_REQUEST['question']));
$all_answers = $wpdb->get_results($wpdb->prepare("SELECT answer,correct FROM {$wpdb->prefix}quiz_answer WHERE question_id=%d ORDER BY sort_order", $_REQUEST['question']));

$answer_count = 4;
if($action == 'edit' and $answer_count < count($all_answers)) $answer_count = count($all_answers) ;

?>

<div class="wrap">
<h2><?php echo t(ucfirst($action) . " Question"); ?></h2>

<div id="titlediv">
<input type="hidden" id="title" name="ignore_me" value="This is here for a workaround for a editor bug" />
</div>

<?php
wpframe_add_editor_js();
?>
<style type="text/css">
.qtrans_title, .qtrans_title_wrap {display:none;}
</style>
<script type="text/javascript">
var answer_count = <?php echo $answer_count?>;

function newAnswer() {
	answer_count++;
	var para = document.createElement("p");
	var textarea = document.createElement("textarea");
	textarea.setAttribute("name", "answer[]");
	textarea.setAttribute("rows", "3");
	textarea.setAttribute("cols", "50");
	para.appendChild(textarea);
	var label = document.createElement("label");
	label.setAttribute("for", "correct_answer_" + answer_count);
	label.appendChild(document.createTextNode("<?php e("Correct Answer"); ?>"));
	para.appendChild(label);
	var input = document.createElement("input");
	input.setAttribute("type", "radio");
	input.setAttribute("name", "correct_answer");
	input.className = "correct_answer";
	input.setAttribute("value", answer_count);
	input.setAttribute("id", "correct_answer_" + answer_count);
	para.appendChild(input);
	
	//$("extra-answers").innerHTML += code.replace(/%%NUMBER%%/g, answer_count);
	document.getElementById("extra-answers").appendChild(para);
}
function init() {
	jQuery("#post").submit(function(e) {
		// Make sure question is suplied
		var contents;
		if(window.tinyMCE && document.getElementById("content").style.display=="none") { // If visual mode is activated.
			contents = tinyMCE.get("content").getContent();
		} else {
			contents = document.getElementById("content").value;
		}
		
		if(!contents) {
			alert("<?php e("Please enter the question"); ?>");
			e.preventDefault();
			e.stopPropagation();
			return true;
		}
		
		// We must have atleast 2 answers.
		var answer_count = 0
		jQuery(".answer").each(function() {
			if(this.value) answer_count++;
		});
		if(answer_count < 2) {
			alert("<?php e("Please enter atleast two answers"); ?>");
			e.preventDefault();
			e.stopPropagation();
			return true;
		}
		
		//A correct answer must be selected.
		var correct_answer_selected = false;
		jQuery(".correct_answer").each(function() {
			if(this.checked) {
				correct_answer_selected = true;
				return true;
			}
		});
		if(!correct_answer_selected) {
			alert("<?php e("Please select a correct answer"); ?>");
			e.preventDefault();
			e.stopPropagation();
		}
	});
}
jQuery(document).ready(init);
</script>

<form name="post" action="edit.php?page=quizzin/question.php&amp;quiz=<?php echo $_REQUEST['quiz']; ?>" method="post" id="post">
<div id="poststuff">

<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">

<div class="postbox">
<h3 class="hndle"><?php e('Question') ?></span></h3>
<div class="inside">
<?php the_editor(stripslashes($question->question)); ?>
</div></div>

<div class="postbox">
<h3 class="hndle"><span><?php e('Answers') ?></span></h3>
<div class="inside">

<?php
for($i=1; $i<=$answer_count; $i++) { ?>
<p><textarea name="answer[]" class="answer" rows="3" cols="50"><?php if($action == 'edit') echo stripslashes($all_answers[$i-1]->answer); ?></textarea>
<label for="correct_answer_<?php echo $i?>"><?php e("Correct Answer"); ?></label>
<input type="radio" class="correct_answer" id="correct_answer_<?php echo $i?>" <?php if($all_answers[$i-1]->correct == 1) echo 'checked="checked"';?> name="correct_answer" value="<?php echo $i?>" /></p>
<?php } ?>

<div id="extra-answers"></div>
<a href="javascript:newAnswer();"><?php e("Add New Answer"); ?></a>

</div>
</div>

<div class="postbox">
<h3 class="hndle"><span><?php e('Explanation') ?></span></h3>
<div class="inside">

<textarea name="explanation" rows="5" cols="50"><?php echo stripslashes($question->explanation)?></textarea>
<br />
<p><?php e('You can use this field to explain the correct answer. This will be shown only at the end of the quiz when the correct answers will be made available.') ?></p>
</div>

</div>
</div>


<p class="submit">
<input type="hidden" name="quiz" value="<?php echo $_REQUEST['quiz']?>" />
<input type="hidden" name="question" value="<?php echo stripslashes($_REQUEST['question'])?>" />
<input type="hidden" id="user-id" name="user_ID" value="<?php echo (int) $user_ID ?>" />
<input type="hidden" name="action" value="<?php echo $action ?>" /> 
<span id="autosave"></span>
<input type="submit" name="submit" value="<?php e('Save') ?>" style="font-weight: bold;" />
</p>
<a href="edit.php?page=quizzin/question.php&amp;quiz=<?php echo $_REQUEST['quiz']?>"><?php e("Go to Questions Page") ?></a>
</div>
</form>

</div>
