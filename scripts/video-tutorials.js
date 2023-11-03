$(document).ready(function(){
		$("#filter_label").click(function(){
			$('#label_section').slideToggle('fast');
			$(this).toggleClass("tag_open");
			if($(this).hasClass("tag_open")){
				$("#open_arrow").show();
				$("#close_arrow").hide();
			}else{
				$("#close_arrow").show();
				$("#open_arrow").hide();
			}
		});
	});

	$(function(){
		console.log(domain, user_id);
		function setVideoID(myValue) {
		     $('#current_video_id').val(myValue)
		                 .trigger('change');
		}
	     function stopVideo(video_id){
	     	 var video = document.getElementById(video_id);
	          video.pause();
	          video.currentTime = 0;
	     }
		var appendthis =  ("<div class='modal-overlay'></div>");
          // open video popup box
		  $(document).on('click','a[data-modal-id]',function(e) {
		    e.preventDefault();
		    console.log($(this));
		    $("body").append(appendthis);
		    $(".modal-overlay").fadeTo(500, 0.7);
		    //$(".js-modalbox").fadeIn(500);
		    var modalBox = $(this).attr('data-modal-id');
		    var splitId = modalBox.split('popup');
			var video_id = splitId[1];
		    $('#'+modalBox).fadeIn($(this).data());
		    //$("#current_video_id").val('vidid'+video_id);
		    setVideoID('vidid'+video_id);
		    	$.ajax({
	                  type:"post",
	                  url:"video-views.php",
	                  datatype:"text",
	                  data: {'domain': domain,"user_id": user_id, "video_id": video_id, "video_action":"open"}
	            });  
		   
		  });  
		  
		  // close video popup
		$(document).on("click",".js-modal-close",function() {
		  $(".modal-box, .modal-overlay").fadeOut(500, function() {
		    $(".modal-overlay").remove();
		  });
		  var popup_id = $(this).parent().attr('id');
		  var video_id_arr = popup_id.split('popup');
		  var video_id = video_id_arr[1];
		  var html_video_id = "vidid"+video_id;
		  stopVideo(html_video_id);
		  //$("#current_video_id").val('');
		   setVideoID('');
		  		  $.ajax({
	                  type:"post",
	                  url:"video-views.php",
	                  datatype:"text",
	                  data: {'domain': domain,"user_id": user_id, "video_id": video_id, "video_action":"close"}
	            });
		  //var viseo_id = $()
		});
		// close video popup
		$(document).on("click",".modal-overlay",function(e) {
			var popup_id = $(".modal-box:visible").attr('id');
			//var video = element.querySelector( 'video' );
			
		  $(".modal-box, .modal-overlay").fadeOut(500, function() {
		    $(".modal-overlay").remove();
		  });
			  var video_id_arr = popup_id.split('popup');
			  var video_id = video_id_arr[1];
			  var html_video_id = "vidid"+video_id;
			  stopVideo(html_video_id);
			   //$("#current_video_id").val('');
			   setVideoID('');
			  	$.ajax({
	                  type:"post",
	                  url:"video-views.php",
	                  datatype:"text",
	                  data: {'domain': domain,"user_id": user_id, "video_id": video_id, "video_action":"close"}
	            });
		});
		// close video popup
		$(document).on('keyup',function(e) {
			e.preventDefault();
		  	if (e.keyCode === 27){
		  		var popup_id = $(".modal-box:visible").attr('id');
				//var video = element.querySelector( 'video' );
				
			  $(".modal-box, .modal-overlay").fadeOut(500, function() {
			    $(".modal-overlay").remove();
			  });
			  var video_id_arr = popup_id.split('popup');
			  var video_id = video_id_arr[1];
			  var html_video_id = "vidid"+video_id;
			  stopVideo(html_video_id);
			   //$("#current_video_id").val('');
			   setVideoID('');
			  	$.ajax({
	                  type:"post",
	                  url:"video-views.php",
	                  datatype:"text",
	                  data: {'domain': domain,"user_id": user_id, "video_id": video_id, "video_action":"close"}
	            });
		  	} 
		});
/*		$(window).resize(function() {
		  $(".modal-box").css({
		  //	top: $(".modal-box").outerHeight() / 2,
		    top: ($(window).height() - $(".modal-box").outerHeight()) / 2,
		    left: ($(window).width() - $(".modal-box").outerWidth()) / 2
		  });
		});*/
		 
		//$(window).resize();
	 
	});
	$(".tag_item span").click(function(){
		var this_id = $(this).attr('id');
		var tag_id_val = this_id.split('tag_');
        var tag_id = tag_id_val[1];
        var selected_ids = $("#filter_tag_ids").val();
        var tag_id_string = ''; 
        var this_text = $(this).text();
        $("#auto_click").val('');
        //tag_id_arr.push(tag_id);
        var selected_flag = 0;

        
		if(!$(this).hasClass('tag_active')){
			 $(".tag_item_remove").each(function(){
	        	var this_remove_id = $(this).children().attr('id');
	        	var split_tag = this_remove_id.split('select_tag_');
	        	if(tag_id == split_tag[1]){
	        		selected_flag = 1;
	        	}
	         });
	         console.log(selected_flag);
	        if(selected_flag == 0){
				if(selected_ids == ''){
	        	  tag_id_string = '';
	        	}else{
					tag_id_string = selected_ids+'|';
				}
				$(".tag_item span").each(function(){
					var tag_text = $(this).text();
					if(this_text == tag_text){
						$(this).addClass('tag_active');
					}
				});
		        $(this).addClass('tag_active');
		        var this_name = $(this).text();
			        $(".selected_tags").append("<a href='javascript:void(0);' class='tag_item_remove'><span id='select_"+this_id+"' class='usergrouptext tag_active'>"+this_name+"</span></a>");
			        tag_id_string += tag_id;
			         $("#filter_tag_ids").val(tag_id_string);
	     	}
    	}else{
    			$(".tag_item span").each(function(){
					var tag_text = $(this).text();
					if(this_text == tag_text){
						$(this).removeClass('tag_active');
					}
				});
    	   $(this).removeClass('tag_active');
	        var this_name = $(this).text();
	        $("#select_"+this_id).parent().remove();
	        var new_id_string = selected_ids.replace(tag_id,"");
            var new_id_arr = new_id_string.split('|');
            new_id_arr = new_id_arr.filter(Boolean);
	         $("#filter_tag_ids").val(new_id_arr.join('|'));
    	}
    	if(selected_flag == 0){
	    	   $(".video_section").hide();
	    	    $('#loader_img').show();
		    	var search_val = $("input[name='video_search']").val();
			 	var filter_tags = $("#filter_tag_ids").val();
			 	var page_no = 1;
			 	var records_per_page = $("#total_records_per_page").val();
			 	$.ajax({
	                  type:"post",
	                  url:"video-search-filter.php",
	                  datatype:"text",
	                  data: {'video_text': search_val,"filter_tags": filter_tags, "page": page_no,"records":records_per_page},
	                  cache: false,
	                  success:function(data)
	                  {
	                  	  setTimeout(function(){
		                  	   	$('#loader_img').hide();
		                      	$(".video_section").show().html(data);
	                  	 },1000);

	                  }
	            });  
            } 
	});


	// remove tag
	$(document).on("click",".tag_item_remove span", function(){
		var this_id = $(this).attr('id');
		var tag_id_val = this_id.split('select_tag_');
        var tag_id = tag_id_val[1];
        var selected_ids = $("#filter_tag_ids").val();
        var tag_id_string = ''; 
        var this_text = $(this).text();
        $("#auto_click").val('');
    	 $(".tag_item span").each(function(){
			var tag_text = $(this).text();
			if(this_text == tag_text){
				$(this).removeClass('tag_active');
			}
		});
        if($("#tag_"+tag_id).hasClass('tag_active')){
        	$("#tag_"+tag_id).removeClass('tag_active');
        }
        $("#"+this_id).parent().remove();
        var new_id_string = selected_ids.replace(tag_id,"");
        var new_id_arr = new_id_string.split('|');
        new_id_arr = new_id_arr.filter(Boolean);
         $("#filter_tag_ids").val(new_id_arr.join('|'));

         	   $(".video_section").hide();
	    	    $('#loader_img').show();
		    	var search_val = $("input[name='video_search']").val();
			 	var filter_tags = $("#filter_tag_ids").val();
			 	var page_no = 1;
			 	var records_per_page = $("#total_records_per_page").val();
			 	$.ajax({
	                  type:"post",
	                  url:"video-search-filter.php",
	                  datatype:"text",
	                  data: {'video_text': search_val,"filter_tags": filter_tags, "page": page_no,"records":records_per_page},
	                  cache: false,
	                  success:function(data)
	                  {
	                  	  setTimeout(function(){
		                  	   	$('#loader_img').hide();
		                      	$(".video_section").show().html(data);
	                  	 },1000);

	                  }
	            });
	});

	$("input[name='video_search']").keyup(function(){
		$("#auto_click").val('');
	});

	// serach filters
	$("#filter_sub").click(function(e){
		e.preventDefault();
			$('#loader_img').show();
			$(".video_section").hide();
		 	var search_val = $("input[name='video_search']").val();
		 	var filter_tags = $("#filter_tag_ids").val();
		 	var page_no = 1;
		 	var records_per_page = $("#total_records_per_page").val();
		 	var auto_click = $("#auto_click").val();
		 	$.ajax({
                  type:"post",
                  url:"video-search-filter.php",
                  datatype:"text",
                  data: {'video_text': search_val,"filter_tags": filter_tags,"page":page_no,"records": records_per_page},
                  cache: false,
                  success:function(data)
                  {
                  	  setTimeout(function(){ 
	                  	  $('#loader_img').hide();
	                      $(".video_section").show().html(data);
	                      if(auto_click !='' && auto_click !=null){
	                      	  $("#"+auto_click).trigger('click');
	                      }
                  	}, 1000);

                  }
            });
	});
		 	//alert(filter_tags);
		
			// serach filters
	$(document).on('click', '.pagination_nav li a', function(e){
		e.preventDefault();
		  var page_no = $(this).data('page');
		  $("#auto_click").val('');
		  if(page_no != '' && page_no != null){
				$('#loader_img').show();
				$(".video_section").hide();
				var records_per_page = $("#total_records_per_page").val();
				$("#page_no").val(page_no);
			 	var search_val = $("input[name='video_search']").val();
			 	var filter_tags = $("#filter_tag_ids").val();
			 	$.ajax({
	                  type:"post",
	                  url:"video-search-filter.php",
	                  datatype:"text",
	                  data: {'video_text': search_val,"filter_tags": filter_tags, "page":page_no,"records":records_per_page},
	                  cache: false,
	                  success:function(data)
	                  {
	                  	  setTimeout(function(){ 
		                  	  $('#loader_img').hide();
		                      $(".video_section").show().html(data);
	                  	}, 1000);

	                  }
	            });
		 }
	});

var current_video_id;

$(document).on('change', '#current_video_id', function(e){	
	
	 current_video_id = e.target.value;
console.log(current_video_id);
	if(current_video_id != '' &&  current_video_id != null){
	var split_video = current_video_id.split('vidid');
	var current_videoID = split_video[1];

		$("#"+current_video_id).bind('ended', function(e) {
	  		 $.ajax({
	              type:"post",
	              url:"video-views.php",
	              datatype:"text",
	              data: {'domain': domain,"user_id": user_id, "video_id": current_videoID, "video_action":"stop"}
	        });
		});
		$("#"+current_video_id).bind('play', function(e) {
			  		  $.ajax({
		                  type:"post",
		                  url:"video-views.php",
		                  datatype:"text",
		                  data: {'domain': domain,"user_id": user_id, "video_id": current_videoID, "video_action":"play"}
		            });
		});
		$("#"+current_video_id).bind('pause', function(e) {
			  		  $.ajax({
		                  type:"post",
		                  url:"video-views.php",
		                  datatype:"text",
		                  data: {'domain': domain,"user_id": user_id, "video_id": current_videoID, "video_action":"pause"}
		            });
		});

	}
});	
	

	


