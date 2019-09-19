<?php

	/**
	* mark_todo.php file
	*
	* mark_todo.php is an endpoint to mark a task as completed in app
	* 	which is consumed at slack side.
	*
	* This endpoint needs token, channel_id, user_id, text for successful result.
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

	$task = @$data['text'];
	$channel_id   = @$data['channel_id']; 
	$user_id   = @$data['user_id']; 

	// condition for an empty argument
	if($task == ''){
		http_response_code(200);
	    $response = 'Please enter a task to mark';
	    echo $response; die();
	}

	// call the mark_todo_task function to mark task as completed
	$result = $obj_todo->mark_task_completed($task,$channel_id,$user_id);

	if($result == 1){
		// if no task found for given title 
		http_response_code(200);
	    $response='"'.$task.'" task not found';
	  	echo ($response);
	    
	}elseif($result==0){
		
		// if something wrong happened with query or data
	    http_response_code(400);
	    $response = "Something went wrong! Please try again";
	    echo $response;
		
	}
	else{

		http_response_code(200);
		header('content-type: application/json');

		####################################################################
		//code for build of message for slack
		####################################################################

		//declare array variable to use in formatting
		$outer_section_array = array();
	    $temp_Array = array(); 
	    $response_for_creator = array();
	    $response_for_creator_array = array();
	    $response = array();

		//this section is to format heading of task
	    $response['type'] ='section';
	    $response['text'] = new \stdClass();
	    $response['text']->type ='mrkdwn';
	    $response['text']->text = "*Task:* ".$result[0]->task."\n";
		
 		$temp_Array['blocks'][] = $response;

 		// this section add format for creator of task
 		$response_for_creator['type'] = 'context';
	    $response_for_creator_array['type'] = 'mrkdwn';
	    $response_for_creator_array['text'] =  " \t Created by:  <@".$result[0]->task_creator.">";
	    $response_for_creator['elements'][] = $response_for_creator_array;

	    $temp_Array['blocks'][] = $response_for_creator;

	    // add color to attachment border
	    $temp_Array['color'] = '#007a5a';

	    ##########################
	    // main config of JSON 
	    ##########################
	    //in_channel is used to publish a message in channel for all users 
	    $outer_section_array['response_type'] = 'in_channel';
	    // main heading of new message
	    $outer_section_array['text'] = '<@'.$user_id.'> marked a task as *Completed*';
	    
	    $outer_section_array['attachments'][] = $temp_Array;

	    echo json_encode($outer_section_array);
		 
		 
	}

?>