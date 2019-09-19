<?php
	
	/**
	* add_new_todo.php file
	*
	* add_new_todo.php is an endpoint for add new task in app
	* 	which is consumed at slack side.
	*
	* This endpoint needs token, text, user_id,channel_id for successful result.
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

	$task_title = @$data['text'];
	$creator_user_id = @$data['user_id'];
	$channel_id = @$data['channel_id'];
	$result = 0;
	//condition if task title is empty
	if($task_title == ''){
		http_response_code(200);
	    $response = 'Please give some title to task';
	    echo $response; die();

	}else{
		// call function to add a task to app
		$result = $obj_todo->add_new_task($task_title,$creator_user_id,$channel_id);
	}

	if($result == 1){
		// acknowledge with HTTP 200 status
		http_response_code(200);
		// response if task with same $task_title matches
	    $response = 'Task with "'.$task_title.'" title is already present, please try again with different title';
	    echo $response;
	    
	}elseif($result){

		http_response_code(200);
	    header('content-type: application/json');

	    ####################################################################
		//code for build of message for slack
		####################################################################

		//declare array variable to use in formatting
		$outer_section_array = array();
	    $temp_Array = array(); 
	    $response = array();

		//this section is to format heading of task
	    $response['type'] ='section';
	    $response['text'] = new \stdClass();
	    $response['text']->type ='mrkdwn';
	    $response['text']->text = "*Task:* ".$task_title."\n";
		
 		$temp_Array['blocks'][] = $response;

	    ##########################
	    // main config of JSON 
	    ##########################
	    //in_channel is used to publish a message in channel for all users 
	    $outer_section_array['response_type'] = 'in_channel';
	    // main heading of new message
	    $outer_section_array['text'] = '<@'.$creator_user_id.'> *created* a new task';
	    
	    $outer_section_array['attachments'][] = $temp_Array;

	    echo json_encode($outer_section_array);
	}
	else{
	    http_response_code(200);
	    $response = "Something went wrong! Please try again";
	    echo $response;
	}

?>