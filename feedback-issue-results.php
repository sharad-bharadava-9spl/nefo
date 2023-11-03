<?php

require_once 'cOnfig/connection.php';
session_start();

 if(isset($_SESSION['lang'])){
  $current_lang = $_SESSION['lang'];
 }else{
  $current_lang = 'en';
 }

$issue_text = str_replace("'","\'",str_replace('%', '&#37;', trim($_POST['issue_text'])));

if(!empty($issue_text) && $issue_text != ''){
  if($current_lang == 'en'){
      $video_limit = "AND (MATCH(video_title_en) AGAINST ('".$issue_text."' IN NATURAL LANGUAGE MODE) OR LOCATE('".$issue_text."', video_title_en)>0)";
  }else if($current_lang == 'es'){
     $video_limit = "AND (MATCH(video_title_es) AGAINST ('".$issue_text."' IN NATURAL LANGUAGE MODE) OR LOCATE('".$issue_text."', video_title_es)>0)";
  }else if($current_lang == 'ca'){
     $video_limit = "AND (MATCH(video_title_ca) AGAINST ('".$issue_text."' IN NATURAL LANGUAGE MODE) OR LOCATE('".$issue_text."', video_title_ca)>0)";
  }else if($current_lang == 'fr'){
     $video_limit = "AND (MATCH(video_title_fr) AGAINST ('".$issue_text."' IN NATURAL LANGUAGE MODE) OR LOCATE('".$issue_text."', video_title_fr)>0)";
  }else if($current_lang == 'nl'){
     $video_limit = "AND (MATCH(video_title_nl) AGAINST ('".$issue_text."' IN NATURAL LANGUAGE MODE) OR LOCATE('".$issue_text."', video_title_nl)>0)";
  }else if($current_lang == 'it'){
     $video_limit = "AND (MATCH(video_title_it) AGAINST ('".$issue_text."' IN NATURAL LANGUAGE MODE) OR LOCATE('".$issue_text."', video_title_it)>0)";
  }
}else{
  $video_limit = '';
}
  $selectVideos = "SELECT * from help_videos WHERE 1 $video_limit order by id desc";
        try
        {
          $vid_result = $pdo2->prepare("$selectVideos");
          $vid_result->execute();
          $total_results = $vid_result->rowCount(); 
        }
        catch (PDOException $e)
        {
            $error = 'Error fetching user: ' . $e->getMessage();
            echo $error;
            exit();
        }
  if($total_results > 0){   ?>

 <strong>We've found some videos that can help you:</strong><br><br>
 	<ul>
<?php    while($video = $vid_result->fetch()){
              $videoid =$video['id'];
              //$vid_desc = $video['description'];
              $video_title_en = $video['video_title_en'];
              $video_title_es = $video['video_title_es'];
              $video_title_ca = $video['video_title_ca'];
              $video_title_fr = $video['video_title_fr'];
              $video_title_nl = $video['video_title_nl'];
              $video_title_it = $video['video_title_it'];

             if($current_lang == 'en'){
                $vid_title = $video_title_en;
              }else if($current_lang == 'es'){
                $vid_title = $video_title_es;
              }else if($current_lang == 'ca'){
                $vid_title = $video_title_ca;
              }else if($current_lang == 'fr'){
                $vid_title = $video_title_fr;
              }else if($current_lang == 'nl'){
                $vid_title = $video_title_nl;
              }else if($current_lang == 'it'){
                $vid_title = $video_title_it;
              }

              echo "<li><a href='javascript:void(0);' class='feedback_video' data-id='".$videoid."'>".$vid_title."</a></li>";

        }
?>
	</ul>
 <?php }

 die;