$(document).ready(function(){
     $("#clock1").timer({
        seconds: 0,
        format: "%T"
      });

	$(document).on('click','.stop_btn',function(e){
		e.preventDefault();
		var this_id = $(this).attr('id');
		var stop_id = this_id.split('stop_timer');
		var stop_id_val = stop_id[1];
		 $("#clock"+stop_id_val).timer('pause');
	});
	$(document).on('click','.man_btn',function(e){
		e.preventDefault();
		var this_id = $(this).attr('id');
		var man_id = this_id.split('manual');
		var man_id_val = man_id[1];
		$("#clock"+man_id_val).val('').attr('readonly', false);
		$("#save_namual"+man_id_val).val(1);
		$("#clock"+man_id_val).timer('remove');
	});
   // select on change
   $(document).on("change",".task_change",function(){
   	    var change_val = $(this).val();
   		var change_id = $(this).attr('id');
   		var task_id = change_id.split("ctask");
   		if(change_val == "Yes"){
   			$("#task_url"+task_id[1]).show();
   		}else{
   			$("#task_url"+task_id[1]).hide();
   		}
   });
	// add issue forms in js
	var max_fields      = 10; //maximum input boxes allowed
	var wrapper   		= $(".issue_div"); //Fields wrapper
	var add_button      = $(".add_field_button"); //Add button ID
	
	var x = 1; //initlal text box count
	
	$(add_button).click(function(e){ //on add input button click
		e.preventDefault();
		if(x < max_fields){ //max input box allowed
			x++; //text box increment


			$(wrapper).append('<div><table class="profileTable" style="text-align: left; margin: 0;"><h4>Issue '+x+'</h4> <tr><td> <strong>Duration:</strong> </td><td> <input type="number" name="timer['+x+']" id="clock'+x+'" class="twoDigit" readonly required="">(Seconds) <div class="timer_btn" style="display: inline-flex;"><button id="stop_timer'+x+'" class="stop_btn">Stop</button>&nbsp;&nbsp;&nbsp;<button id="manual'+x+'" class="man_btn">Type Manually</button></div><input type="hidden" name="type_manual['+x+']" id="save_namual'+x+'" value="0"></td></tr><tr> <td><strong>Issue</strong></td><td><input type="text" name="issue['+x+']" class="issue_text" id="call_issue'+x+'" placeholder="Enter Issue" required/></td><input type="hidden" name="issue_id['+x+']" id="issue_id'+x+'"> </tr><tr> <td><strong>Department</strong></td><td> <select class="department_cls" name="department['+x+']" id="dept_name'+x+'" required=""></select> </td></tr><tr> <td><strong>Department Category</strong></td><td> <select class="department_cat" name="department_cat['+x+']" id="dept_cat'+x+'" required=""> <option value="">Select Category</option> </select> </tr><tr> <td><strong>Comment</strong></td><td><textarea name="comment['+x+']"></textarea></td></tr><tr> <td><strong>Next Action</strong></td><td> <textarea name="next_desc['+x+']" placeholder="description"></textarea> <input type="text" class="deadpicker" name="deadline['+x+']" id="deadline'+x+'" placeholder="Deadline"><br><input type="text" name="colleague['+x+']" id="userselect'+x+'" class="multi_users" placeholder="select users"> <input type="hidden" name="slected_users['+x+']" id="user_ids'+x+'"> <select name="priority['+x+']"> <option value="">Select Priority</option> <option value="low">Low</option> <option value="medium">Medium</option> <option value="high">High</option> </select> </td></tr><tr> <td><strong>Task Created ?</strong></td><td> <select name="task_created['+x+']" id="ctask'+x+'" class="task_change" required=""> <option value="">Select Option</option> <option value="Yes">Yes</option> <option value="No">No</option> </select> <input type="text" name="task_url['+x+']"  id="task_url'+x+'" placeholder="Enter URL"  style="display: none;" required=""></td></tr></table><a href="javascript:void(0);" class="remove_field cta" style="background:red;">Remove</a></div>'); //add input box


			  var deptOptions = "<option value=''>Select Department</option>";
				  Object.keys(departments).forEach(function(key) {
				    deptOptions += "<option value="+key+">"+departments[key]+"</option>";
				});
				$("#dept_name"+x).html(deptOptions);

						$("#clock"+x).timer({
					        seconds: 0,
					        format: "%T"
					      });
					  	$( ".deadpicker" ).datetimepicker({
						  	   dateFormat: "yy-mm-dd"
						  	});

				 	if(issue_arr != null){  	
					    $( ".issue_text" ).autocomplete({
					       source: issue_name,
					      	autoFocus: true,
					       select: function( event, ui ) {
					       	  var this_id = $(event.target).attr('id');
					       	  var thisIdval = this_id.split('call_issue');
					       	  var selected_value = ui.item.value;
					       	  Object.keys(issue_arr).forEach(function(k){
								    console.log(k + ' - ' + issue_arr[k]);
								    if(selected_value == issue_arr[k])
								    	$("#issue_id"+thisIdval[1]).val(k);
								});
					       	},change: function( event, ui){
						       		var this_id = $(event.target).attr('id');
						       	    var thisIdval = this_id.split('call_issue');
						       		if(ui.item == null){
						       			$("#issue_id"+thisIdval[1]).val('');
						       		}
						       	 	
									
						       	}
					    });
					}

							function split( val ) {
								return val.split( /,\s*/ );
							}
							function extractLast( term ) {
								return split( term ).pop();
							}

							$( ".multi_users" )
								// don't navigate away from the field on tab when selecting an item
								.on( "keydown", function( event ) {
									if ( event.keyCode === $.ui.keyCode.TAB &&
											$( this ).autocomplete( "instance" ).menu.active ) {
										event.preventDefault();
									}
								})
								.autocomplete({
									minLength: 0,
									source: function( request, response ) {
										// delegate back to autocomplete, but extract the last term
										response( $.ui.autocomplete.filter(
											user_name, extractLast( request.term ) ) );
									},
									focus: function(event, ui) {
										// prevent value inserted on focus
										return false;
									},
									select: function( event, ui ) {
										var terms = split( this.value );
										// remove the current input
										terms.pop();
										// add the selected item
										terms.push( ui.item.value );
										// add placeholder to get the comma-and-space at the end
										terms.push( "" );
										this.value = terms.join( ", " );
									  var this_id = $(event.target).attr('id');
							       	  var thisIdval = this_id.split('userselect');
										var user_ids = [];	
										Object.keys(user_arr).forEach(function(k){
											for(var i in terms){
												if(terms[i] != '' && terms[i] == user_arr[k]){
													user_ids.push(k);
												}
											}
										});
										
										$("#user_ids"+thisIdval[1]).val(user_ids.join());
										return false;
									},
									change: function( event, ui ) {
										var terms = split( this.value );
										console.log(terms);
										// remove the current input
										terms.pop();
										
										// add placeholder to get the comma-and-space at the end
										terms.push( "" );
										this.value = terms.join( ", " );
										var this_id = $(event.target).attr('id');
								       	  var thisIdval = this_id.split('user_ids');
										
										var user_ids = [];	
										Object.keys(user_arr).forEach(function(k){
											for(var i in terms){
												if(terms[i] != '' && terms[i] == user_arr[k]){
													user_ids.push(k);
												}
											}
										});
										$("#user_ids"+thisIdval[1]).val(user_ids.join());
										return false;
									}
								});

		}else{
			$("#issue_error").addClass('error').html("Maximum issues added!").fadeIn(300).fadeOut(1500);
		}
	});
	
	$(wrapper).on("click",".remove_field", function(e){ //user click on remove text
		e.preventDefault(); $(this).parent('div').remove(); x--;
	}) 
});