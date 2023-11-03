$(document).ready(function(){
	
	$("input[name='issue']").keyup(function(){

		var str_length = $(this).val().length;
		var this_words = $(this).val();

		if(str_length >2 && $.trim(this_words) != ''){

				$.ajax({
	                  type:"post",
	                  url:"feedback-issue-results.php",
	                  datatype:"text",
	                  data: {'issue_text': this_words},
	                  success:function(data)
	                  {

	                  	if(data){
	                  	  $("#issue_box").show().html(data);
	                  	}

	                  }
	            });

		}else{
			$("#issue_box").hide();
		}
	});

var appendthis =  ("<div class='modal-overlay'></div>");
 var tabLinks = $('#help-tabs a');
  $(document).on('click', '.feedback_video', function(e) {
  	e.preventDefault();
  	var video_id = $(this).data('id');
    activate = true; // Activate tab functionality
    tabLinks.eq(2).trigger('click'); // Trigger a click on the third tab link
        var popup_id = "popup"+video_id;
	    //$("body").append(appendthis);
	    //$(".modal-overlay").fadeTo(500, 0.7);
	    var click_id = "click_modal"+video_id;
	    var video_title = $(this).text();
	    setTimeout(function(){
	    	$("input[name='video_search']").val(video_title);
	    	$("#auto_click").val(click_id);
	    	$("#filter_sub").click();
	     },500);
  });

});