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
 



class Wplms_Instructor_Quiz_Ajax{


	public static $instance;
	
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Wplms_Instructor_Quiz_Ajax();
        return self::$instance;
    }

	private function __construct(){
		add_action('wp_ajax_save_instructor_marks',array($this,'save_instructor_marks'));
	}

	public function save_instructor_marks(){
		$comment_id = $_POST['comment_id'];
      	$given_marks = $_POST['marks'];
		  $instructor_id = get_current_user_id();
      	$marks = array();
      	if(!empty($comment_id) && is_numeric($given_marks) && !empty($instructor_id)){
      		$marks = get_comment_meta($comment_id,'inst_marks',true);
		    if(!empty($marks) && is_array($marks)){
		    	$t = 0;
				foreach ($marks as $key => $mark) {
					if($mark['inst_id'] ==  $instructor_id){
					  $marks[$key] = array(
					  	'inst_id' => $instructor_id,
					  	'marks' => $given_marks
					  );
					  $t = 1;
					  break;
					}
				}
				if($t == 0){
					$marks[]=array(
					  	'inst_id' => $instructor_id,
					  	'marks' => $given_marks
					);
		        }
		    }else{
		    	$marks = array();
		    	$marks[]=array(
		          	'inst_id' => $instructor_id,
		          	'marks' => $given_marks
		        );
		    }
      		update_comment_meta( $comment_id,'inst_marks',$marks);

      		// for average calculation
      		$mark = 0;
      		if(!empty($marks) && is_array($marks)){
      			foreach ($marks as $key => $value) {
      				$mark = $mark + $value['marks'];
	      		}
	      		$mark = $mark/count($marks);
      		}
      		update_comment_meta( $comment_id,'marks',$mark);
      		echo __('Updated','vibe');
      	}else{
      		echo __('Not updated','vibe');
      	}
      	die();
	}

}

Wplms_Instructor_Quiz_Ajax::init();