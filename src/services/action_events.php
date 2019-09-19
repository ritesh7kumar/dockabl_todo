<?php
	
	/**
	* action_events.php file
	*
	* action_events.php is endpoint for all the action-events
	* 	which are configured at slack for interactive messages.
	*
	* This file currently has one action i.e. mark_todo_complete to complete task.
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
 	
	$payload = json_decode(@$data['payload']);

	//check if verifying token given by slack and received in payload is same
	$verify_token_res = $obj_todo->verify_token(@$payload->token);
	if($verify_token_res==0){
		http_response_code(400);
	    $response = 'Invalid Token';
	    echo $response; die();
	}

	$received_response_url = $payload->response_url;
	$received_value = $payload->actions[0]->value;
	$received_task_id = $payload->actions[0]->action_id;
	$received_user_id = $payload->user->id;

	//check if action-event is for mark todo complete
	if($received_value == 'mark_todo_complete'){
		
		// call mark_task_by_id function to mark the task as completed for given id
		$result = $obj_todo->mark_task_by_id($received_task_id,$received_user_id);   

		// send immediate HTTP code based on result to acknowledge slack
		if($result==2){
			http_response_code(200);
		}
		else{
			http_response_code(400);
			die();
		}
		 
		
		// call get_task_detail_by_id function to get data of marked task to send a message in channel
		$result_for_task =$obj_todo->get_task_detail_by_id($received_task_id);

		if($result_for_task){

		
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
		    $response['text']->text = "*Task:* ".$result_for_task[0]->task."\n";
			
     		$temp_Array['blocks'][] = $response;

     		// this section add format for creator of task
     		$response_for_creator['type'] = 'context';
		    $response_for_creator_array['type'] = 'mrkdwn';
		    $response_for_creator_array['text'] =  " \t Created by:  <@".$result_for_task[0]->task_creator.">";
		    $response_for_creator['elements'][] = $response_for_creator_array;

		    $temp_Array['blocks'][] = $response_for_creator;

		    // add color to attachment border
		    $temp_Array['color'] = '#007a5a';

		    ##########################
		    // main config of JSON 
		    ##########################
		    //in_channel is used to publish a message in channel for all users 
		    $outer_section_array['response_type'] = 'in_channel';
		    // delete_original deletes the original message on which this action was taken
		    $outer_section_array['delete_original'] = 'true';
		    // main heading of new message
		    $outer_section_array['text'] = '<@'.$received_user_id.'> marked a task as *Completed*';
		    
		    $outer_section_array['attachments'][] = $temp_Array;
		    $send_data_to_curl = json_encode($outer_section_array);


		    #############################################################
		    //curl to response back slack
		    #############################################################

			$curl = curl_init();

			curl_setopt_array($curl, array(
			  CURLOPT_URL => $received_response_url,//received_response_url is received in payload of action
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS =>  $send_data_to_curl,
			  CURLOPT_HTTPHEADER => array(
			    "cache-control: no-cache",
			    "content-type: application/json"
			  ),
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
		}
		else{
			// no acknowledge should be made because one is made already
		}
		 
	}else{
		http_response_code(400);
	}
	
 ?>