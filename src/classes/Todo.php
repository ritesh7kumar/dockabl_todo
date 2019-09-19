<?php
   /**
   *
   * Todo.php file
   * Todo class is the main class of this app, it contains all the endpoints' functions
   * Todo class has constructor which create an instance of class DB object
   *
   */
class Todo{
	
   private $_db;
   private static $_instance = null;

   public function __construct($tasks=null){
      //create instance of DB class object
      $this->_db = DB::getInstance();
   }

   // function is to verify token received in endpoint request from slack
   public function verify_token($token){
      // this key needs to be changed on every app install
      $key = '9MpTrPO3XyqscN8egZ1bBKiY';
      if(!isset($token) || $token !== $key) {
         return 0;
      }
      else{
         return 1;
      }
   }
   
   // function to add new task in app
   public function add_new_task($task_details,$created_user_name,$channel_id){

		$sql_seacrh = "SELECT id FROM todo_details WHERE task = ? AND channel_id = ? AND status =1";
		$result = $this->_db->query($sql_seacrh,array($task_details,$channel_id))->results();

		if(empty($result)){

			$sql 	= "INSERT INTO todo_details(task,task_creator,channel_id) VALUES(?,?,?)";
   		$result = $this->_db->query($sql,array($task_details,$created_user_name,$channel_id));

   		if($result->_error){
   	      return 0;
   	   }
   	   else{
            // success condition
   	      return 2;
   	   }

		}else{
         // todo alreads exists
			return 1; 
		}   

   }  

   // function to mark a task as completed
   public function mark_task_completed($task_details,$channel_id,$user_id_delete){

   	$sql 	= "UPDATE todo_details SET status = 0, who_marked = ? WHERE channel_id = ? AND task = ? AND status=1";
   	$result = $this->_db->query($sql,array($user_id_delete,$channel_id,$task_details));

   	if($result->_error){
	      return 0;  

      }elseif($result->_count == 0){
          // if no task found
         return 1; 
      }
      else{
         // return data if a task is marked to display data on slack tile
         $sql_get_data  = "SELECT task,task_creator,who_marked FROM todo_details WHERE channel_id = ? and task = ? and status =0";
         $result_data = $this->_db->query($sql_get_data,array($channel_id,$task_details))->results();
         return $result_data;
      }

   } 

   // function to get list of tasks within a channel
   public function list_all_tasks_in_channel($channel_id){

		$sql 	= "SELECT task,task_creator,id FROM todo_details WHERE channel_id = ? AND status = 1";
		$result = $this->_db->query($sql,array($channel_id))->results();

		$final = "*TODO List* \n";

		if(empty($result)){
			return 0;
		}else{
         return $result;
		}  

   }   

   // function to mark a task by id
   public function mark_task_by_id($received_task_id,$received_user_id){

      $sql  = "UPDATE todo_details SET status = 0, who_marked = ? WHERE id = ? AND status = 1";
      $result = $this->_db->query($sql,array($received_user_id,$received_task_id));

      if($result->_error){
         return 0;

      }elseif($result->_count == 0){
         //if no task found
         return 1;  
      }
      else{
         //success
         return 2; 
      }

   }

   // get task detail by id 
   public function get_task_detail_by_id($task_id){                         
      
      $sql  = "SELECT task,task_creator,who_marked FROM todo_details WHERE id = ? and status = 0";
      $result = $this->_db->query($sql,array($task_id))->results();
      return $result;
      
   }

}  // close class Todo

?>