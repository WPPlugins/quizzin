var current_question = 1;
var total_questions = 0;
var mode = "show";

function checkAnswer(e) {
	var answered = false;
	
	jQuery("#question-" + current_question + " .answer").each(function(i) {
		if(this.checked) {
			answered = true;
			return true;
		}
	});
	if(!answered) {
		if(!confirm("You did not select any answer. Are you sure you want to continue?")) {
			e.preventDefault();
			e.stopPropagation();
			return false;
		}
	}
	return true;
}

function nextQuestion(e) {
	if(!checkAnswer(e)) return;
	
	jQuery("#question-" + current_question).hide();
	current_question++;
	jQuery("#question-" + current_question).show();
	
	if(total_questions <= current_question) {
		jQuery("#next-question").hide();
		jQuery("#action-button").show();
	}
}

// This part is used only if the answers are show on a per question basis.
function showAnswer(e) {
	if(!checkAnswer(e)) return;
	
	if(mode == "next") {
		mode = "show";
		
		jQuery("#question-" + current_question).hide();
		current_question++;
		jQuery("#question-" + current_question).show();
	
		jQuery("#show-answer").val("Show Answer");
		return;
	}
	
	mode = "next";
	
	jQuery(".correct-answer-label.label-"+current_question).addClass("correct-answer");
	jQuery(".answer-"+current_question).each(function(i) {
		if(this.checked && this.className.match(/wrong\-answer/)) {
			var number = this.id.toString().replace(/\D/g,"");
			if(number) {
				jQuery("#answer-label-"+number).addClass("user-answer");
			}
		}
	});
	
	if(total_questions <= current_question) {
		jQuery("#show-answer").hide();
		jQuery("#action-button").show();
	} else {
		jQuery("#show-answer").val("Next >");
	}
}

function quizzinInit() {
	jQuery("#question-1").show();
	total_questions = jQuery(".quizzin-question").length;
	
	if(total_questions == 1) {
		jQuery("#action-button").show();
		jQuery("#next-question").hide();
		jQuery("#show-answer").hide();
	
	} else {
		jQuery("#next-question").click(nextQuestion);
		jQuery("#show-answer").click(showAnswer);
	}
}

jQuery(document).ready(quizzinInit);
