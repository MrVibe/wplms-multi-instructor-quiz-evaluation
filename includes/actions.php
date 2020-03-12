<?php
/**
 * Configure Wplms_Instructor_Quiz_Actions
 *
 * @class       Wplms_Instructor_Quiz_Actions
 * @author      VibeThemes
 * @category    Admin
 * @package     Wplms_Instructor_Quiz_Actions
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
 



class Wplms_Instructor_Quiz_Actions{


	public static $instance;
	
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Wplms_Instructor_Quiz_Actions();
        return self::$instance;
    }

	private function __construct(){
     
		// add_action('wplms_quiz_evaluate_question_html',array($this,'show_script'),10,2);
    // add_action('wplms_quiz_evaluate_per_question_html',array($this,'_wplms_quiz_evaluate_per_question_html'),10,4);
    // add_action('wplms_quiz_results_per_question_html',array($this,'_wplms_quiz_results_per_question_html'),10,3);
    
    // reset (comment_id,inst_marks)
    // add_action('before_wplms_quiz_course_retake_reset',array($this,'_before_wplms_quiz_course_retake_reset'),10,2);
    // add_action('before_wplms_quiz_reset',array($this,'_before_wplms_quiz_course_retake_reset'),10,2);
    
    // for unattempted comment //
   add_action('wplms_quiz_evaluate_question_html',array($this,'show_script_unattempted'),10,2);  //script
   add_action('wplms_quiz_evaluate_per_question_html',array($this,'_wplms_quiz_evaluate_per_question_html_unattempted'),10,4);  // button
   // Ajax
   add_action('wp_ajax_enable_unattempted_give_marks',array($this,'enable_unattempted_give_marks'));  //form view
   add_action('wp_ajax_save_unattempted_question_marks',array($this,'save_unattempted_question_marks'));  //save marks
		
	}

  function show_script($quiz_id,$user_id){
    ?>
    <script>
      jQuery('ul.quiz_questions span.marking').remove();
      jQuery('.give_marks_inst').click(function(event){
        event.preventDefault(); 
        var $this = jQuery(this);
        var marks = $this.closest('.custom_marking').find('.inst_question_marks').val();
        var comment_id = $this.data('comment-id');
        $this.prepend('<i class="fa fa-spinner animated spin"></i>');
        jQuery.ajax({
          type: "POST",
          url: ajaxurl,
          dataType: 'html',
          data: { action: 'save_instructor_marks',
                  comment_id: comment_id,
                  marks: marks,
                },
          cache: false,
          success: function (json) {
              $this.find('i').remove();
              $this.html(vibe_course_module_strings.marks_saved);
          }
        });
      });
      setTimeout(function(){
        jQuery('.inst_question_marks').trigger('change');
      },500);
      
      jQuery('.inst_question_marks').on('change',function(){
      var marks=0;
      jQuery('body').find('.inst_question_marks').each(function(){
      marks += parseInt(jQuery(this).val()); 
      jQuery('#total_marks span').text(marks);})
      });
    </script>
    <?php
  }

	public function _wplms_quiz_evaluate_per_question_html($question,$quiz_id,$user_id,$marked_answer_id){
    // echo 'marks_id';
    // print_r($marked_answer_id);
    $course_id = $_POST['id'];
    $questions = bp_course_get_quiz_questions($quiz_id,$user_id);
    $k = -1;
    if(!empty($questions) && $questions['ques']){
      foreach ($questions['ques'] as $key => $value) {
        if($question == $value){
          $k = $key;
        }
      }
    }
    

    $marks = get_comment_meta($marked_answer_id,'inst_marks',true);
    $inst_marks = 0;
    $instructor_id = get_current_user_id();
    if(!empty($marks)){
      foreach ($marks as $key => $mark) {
        if($mark['inst_id'] ==  $instructor_id){
          $inst_marks = $mark['marks'];
        }
      
      }
    }
     if(!empty($marked_answer_id)){
	       echo '<span class="custom_marking">'.__('Marks Obtained','vibe').'<input type="number"  class="form_field inst_question_marks" value="'.$inst_marks.'" placeholder="'.__('Give marks','vibe').'">';
          echo '<a href="#" class="give_marks_inst button" data-comment-id="'.$marked_answer_id.'" >'.__('Give marks','vibe').'</a><span></span><span class="custom_marking_total"> '.__('Total Marks','vibe').' : '.$questions['marks'][$key].'</span>
          </span>';
      }
      if(!empty($course_id)){
        // printing marks for each user
        if(!empty($marks)){
          if(!empty($instructor_id)){
            $post_author_id = get_post_field( 'post_author', $course_id );
            if(($post_author_id == $instructor_id) || user_can($instructor_id,'manage_options')){
              echo '<div class="marks_obtained_multi">';
              foreach ($marks as $key => $value) {
                if($value['marks'] > 0){
                  $name_inst = bp_core_get_user_displayname($value['inst_id']);
                  echo '<span>'.__('MARKS OBTAINED FROM ','vibe').$name_inst.' <i class="icon-check"></i> '.':'.$value['marks'].'</span>';
                }else{
                  echo '<span>'.__('MARKS OBTAINED FROM ','vibe').$name_inst.' <i class="icon-x"></i> '.':'.$value['marks'].'</span>';
                }
              }
              echo '<div>';
            }
          }
        }
      }

		          
	}

  public function _wplms_quiz_results_per_question_html($quiz_id,$question,$user_id){
   ?>
   <script type="text/javascript">
     
   </script>
   <?php

    $marks = array(); 
    $template = BP_Course_Template::init();
    $comment = $template->get_answer_object($quiz_id,$question,$user_id);
    if(empty($comment)){return;}
    $inst_marks = get_comment_meta($comment->comment_ID,'inst_marks',true);
    if(!empty($inst_marks) && is_array($inst_marks)){
      $marks = $inst_marks; 
    }
    if(!empty($marks)){
      echo '<div class="marks_obtained_multi">';
      foreach ($marks as $key => $value) {
        if($value['marks'] > 0){
          $name_inst = bp_core_get_user_displayname($value['inst_id']);
          echo '<span>'.__('MARKS OBTAINED FROM ','vibe').$name_inst.' <i class="icon-check"></i> '.':'.$value['marks'].'</span>';
        }else{
          echo '<span>'.__('MARKS OBTAINED FROM ','vibe').$name_inst.' <i class="icon-x"></i> '.':'.$value['marks'].'</span>';
        }
      }
      echo '<div>';
    }
    
  }

  function _before_wplms_quiz_course_retake_reset($quiz_id,$user_id){
    $questions = bp_course_get_quiz_questions($quiz_id,$user_id);
    if(isset($questions) && is_array($questions) && is_Array($questions['ques'])){
      foreach($questions['ques'] as $question){
        global $wpdb;
        if(isset($question) && $question !='' && is_numeric($question)){
          $template = BP_Course_Template::init();
          $comment = $template->get_answer_object($quiz_id,$question,$user_id);
          if(!empty($comment)){
            $comment_id = $comment->comment_ID;
            update_comment_meta( $comment_id,'inst_marks',[]);
          }
        }
      }
    }
  }

  function _wplms_quiz_evaluate_per_question_html_unattempted($question_id,$quiz_id,$user_id,$marked_answer_id){
    if(empty($marked_answer_id) && !empty($question_id)){
      echo '<button href="#" class="enable_unattempted_give_marks button" data-question-id='.$question_id.' 
      data-quiz-id='.$quiz_id.' >'.__('Show Form For Unattempt Marking','vibe').'</button>';
    }  
  }
  function show_script_unattempted($quiz_id,$user_id){
    ?>
    <script>
    jQuery(document).ready(function(){
      let user_id = <?php echo $user_id; ?>
      // Ajax call
       jQuery(".quiz_questions").on('click', '.give_marks_unattempted', function() {
        let $this = jQuery(this);
        console.log('submitting unattempted marks');
        let comment_id = $this.data('comment-id');
        let security = $this.data('security');
        var marks = $this.closest('.unattempted_marking').find('.unattempted_marks').val();
        $this.prepend('<i class="fa fa-spinner animated spin"></i>');
        jQuery.ajax({
          type: "POST",
          url: ajaxurl,
          dataType: 'html',
          data: { action: 'save_unattempted_question_marks',
                  comment_id: comment_id,
                  security : security,
                  marks: marks,
                },
          cache: false,
          success: function (data) {
              $this.find('i').remove();
              $this.html(data);
          }
        });
      })  
      
      // Hide and Show form
      jQuery('.enable_unattempted_give_marks').click(function(event){
        event.preventDefault(); 
        let $this = jQuery(this);
        let question_id = $this.data('question-id');
        let quiz_id = $this.data('quiz-id');
        console.log('quiz_id',quiz_id)
        console.log('question_id',question_id)
        if(quiz_id && question_id ){
            $this.prepend('<i class="fa fa-spinner animated spin"></i>');
            jQuery.ajax({
              type: "POST",
              url: ajaxurl,
              dataType: 'html',
              data: { 
                action: 'enable_unattempted_give_marks',
                quiz_id: quiz_id,
                question_id : question_id,
                user_id  : user_id
              },
              cache: false,
              success: function (data) {
                  $this.find('i').remove();
                  $this.after(data);
              }
            });
          }
        })
      });
    </script>
    <?php
  }
  function enable_unattempted_give_marks(){
    //create fake comment and share give marks form
    $quiz_id = $_POST['quiz_id'];
    $question_id = $_POST['question_id'];
    $user_id = $_POST['user_id'];
    if(!empty($question_id) && !empty($quiz_id) && !empty($user_id)){
      // copy like bp_course_save_question_quiz_answer //fake comment
       $question_answer_args = apply_filters('bp_course_save_question_quiz_answer',array(
        'comment_post_ID'=>$question_id,
        'user_id'=>$user_id,
        'comment_content'=>apply_filters('enable_unattempted_give_marks_ans',null),
        'comment_date' => current_time('mysql'),
        'comment_approved' => 1,
      ));
      global $wpdb;
      $comment_id = $wpdb->get_var("SELECT m.comment_id FROM {$wpdb->comments} as c LEFT JOIN {$wpdb->commentmeta} as m ON c.comment_ID = m.comment_id WHERE c.user_id = $user_id AND c.comment_post_ID = $question_id AND m.meta_key = 'quiz_id' AND m.meta_value = $quiz_id");
      if(!empty($comment_id) && is_numeric($comment_id)){
          $question_answer_args['comment_ID'] = $comment_id;
          wp_update_comment($question_answer_args);
      }else{
          $comment_id = wp_insert_comment($question_answer_args);
          if(!is_wp_error($comment_id)){
              update_comment_meta($comment_id,'quiz_id',$quiz_id);
          }
      }
      // form show
      if(!empty($comment_id)){
        echo '<span class="unattempted_marking">'.__('Marks Obtained','vibe').'<input type="number" value=0 class="form_field unattempted_marks" value=0 placeholder="'.__('Give marks','vibe').'">
          <button class="give_marks_unattempted button" data-comment-id='.$comment_id.'  data-security='.wp_create_nonce('save_unattempted_question_marks').'>'.__('Give marks','vibe').'</button><span></span>
          </span>';
      }
      die();
    }
  }
  function save_unattempted_question_marks(){
    if(isset($_POST['security']) && wp_verify_nonce($_POST['security'],'save_unattempted_question_marks') && is_user_logged_in()){
      $comment_id = $_POST['comment_id'];
      $marks = $_POST['marks'];
      if(is_numeric($comment_id) && is_numeric($marks)){
        update_comment_meta( $comment_id, 'marks',$marks);
        echo __('Updated','vibe');
      }
    }else{
       echo __('Security Failed','vibe');
    }
    die();
  }
}

Wplms_Instructor_Quiz_Actions::init();