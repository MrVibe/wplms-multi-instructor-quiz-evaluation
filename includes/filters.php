<?php
/**
 * Configure Wplms_Instructor_Quiz_Filters
 *
 * @class       Wplms_Instructor_Quiz_Filters
 * @author      VibeThemes
 * @category    Admin
 * @package     Wplms_Instructor_Quiz_Filters
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
 



class Wplms_Instructor_Quiz_Filters{


	public static $instance;
	
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Wplms_Instructor_Quiz_Filters();
        return self::$instance;
    }

	private function __construct(){
		add_filter('wplms_quiz_evaluate_from_activity',array($this,'_wplms_quiz_evaluate_from_activity'),10,3);
		add_filter('bp_course_quiz_results_from_activity_meta',array($this,'_bp_course_quiz_results_from_activity_meta'),10,5);
		add_filter('get_quiz_item_value',array($this,'_get_quiz_item_value'),10,3);
		add_filter('get_quiz_item_value',array($this,'_get_quiz_item_value'),10,3);
		add_filter('vibe_eval_quiz_marks',array($this,'_get_quiz_item_value'),10,3);
		add_filter('vibe_marks_roundoff',array($this,'_vibe_marks_roundoff'),10,3);
		
		
		
		//add_filter('bp_course_get_user_question_marks',array($this,'_bp_course_get_user_question_marks'),10,4);
	}

	public function _wplms_quiz_evaluate_from_activity($from_activity,$quiz_id,$user_id){
		return false;
	}

	public function _bp_course_quiz_results_from_activity_meta($from_activity,$quiz_id,$user_id,$course,$results){
		return false;
	}

	public function _bp_course_get_user_question_marks($marks,$quiz_id,$question_id,$user_id){
		$template = BP_Course_Template::init();
		$comment = $template->get_answer_object($quiz_id,$question_id,$user_id);
		if(empty($comment)){return 0;}
		$inst_marks = get_comment_meta($comment->comment_ID,'inst_marks',true);
		$instructor_id = get_current_user_id();
	    if(!empty($inst_marks) && !empty($instructor_id)){
	      foreach ($inst_marks as $key => $mark) {
	        if($mark['inst_id'] ==  $instructor_id){
	          return $mark['marks'];
	        }
	      
	      }
    	}
    	return 0;
	}
	public function _get_quiz_item_value($value,$quiz_id,$user_id){
		$questions = bp_course_get_quiz_questions($quiz_id,$user_id);
		if(!empty($questions)){
			$sum = 0;
			foreach($questions['ques'] as $key=>$question_id){
				$template = BP_Course_Template::init();
				$comment = $template->get_answer_object($quiz_id,$question_id,$user_id);
				$marks = get_comment_meta($comment->comment_ID,'marks',true);
				$sum += ($marks);	
			}
			return $sum;
		}else{
			return $value;
		}
	}

	public function _vibe_marks_roundoff($int_marks,$sum,$marks){
		return $sum+$marks;
	}


}

Wplms_Instructor_Quiz_Filters::init();