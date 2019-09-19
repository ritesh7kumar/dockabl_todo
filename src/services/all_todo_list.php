<?php
	
	/**
	* all_todo_list.php file
	*
	* all_todo_list.php is an endpoint for list of available tasks in app
	* 	which is consumed at slack side.
	*
	* This endpoint needs token, channel_id for successful result.
	*
	**/

	//include autoloader file
	$path_init = $_SERVER['DOCUMENT_ROOT']."/dockabl_todo/src/autoloader.php";
	include_once($path_init); 
	
	// object of Todo task class
	$obj_todo = new Todo();

	//set header for request data
	header('Content-type: application/x-www-form-urlencoded');
	// get data from php input
	parse_str(file_get_contents("php://input"), $data);

	//check if verifying token given by slack and received in payload is same
	$verify_token_res = $obj_todo->verify_token(@$data['token']);
	if($verify_token_res==0){
		http_response_code(400);
	    $response = 'Invalid Token';
	    echo $response; die();
	}

	$channel_id = @$data['channel_id'];

	// call function to get list of task for specific channel
	$result = $obj_todo->list_all_tasks_in_channel($channel_id);  

	if($result){

		// acknowledge with HTTP 200 status
	    http_response_code(200);
	   	header('content-type: application/json');
 		
 		####################################################################
		//code for build of message for slack
		####################################################################

		//declare array variable to use in formatting
	    $outer_section_array = array();
	    $temp_Array = array();
	    $response_final =array();
	    $response_for_divider = array();
	    $response_for_creator = array();
	    $response_for_creator_array = array();
	    $response = array();
	    foreach ($result as $key => $value) {
	    	// empty variables on every iteration
	    	$response_for_creator_array = '';
	    	$response_for_creator = '';
	    	$response_for_divider = '';
	    	$response = '';

	    	//this section is to format heading of task
		    $response['type'] ='section';
		    $response['text'] = new \stdClass();
		    $response['text']->type ='mrkdwn';
		    $response['text']->text =  ++$key.") ".$value->task."\n";
			
     		// this section add format for action button 
			$response['accessory'] = new \stdClass();
			$response['accessory']->type = 'button';
			$response['accessory']->text = new \stdClass();
			$response['accessory']->text->type = 'plain_text'; 	
			$response['accessory']->text->text = 'Mark Completed'; 	
			$response['accessory']->value = 'mark_todo_complete'; 	
			$response['accessory']->action_id = $value->id;	
			$response['accessory']->style = 'primary';	

     		$temp_Array[] = $response;

     		// this section add format for creator of task
     		$response_for_creator['type'] = 'context';
		    $response_for_creator_array['type'] = 'mrkdwn';
		    $response_for_creator_array['text'] =  " \t Created by:  <@".$value->task_creator.">";
		    $response_for_creator['elements'][] = $response_for_creator_array;

		    $temp_Array[] = $response_for_creator;

		    // add a divider between every task
     		$response_for_divider['type'] ='divider';
     		$temp_Array[] = $response_for_divider;

		}  // close foreach

	    
	    $response_final['blocks']=$temp_Array;

	    ##########################
	    // main config of JSON 
	    ##########################
	     // main heading of list 
	    $outer_section_array['text'] = "*Open Tasks*";
	    $outer_section_array['attachments'][] = $response_final;
	    
	    echo json_encode($outer_section_array);

	}else{

		// acknowledge HTTP 404 Not found
	    http_response_code(200);
	    $response = "No task found";
	    echo $response;
	}

?>