<?php
	/**
	* DB.php file
	* DB class has connection in its constructor and other usable function in context of database
	*
	*/

class DB{

	public $_id;
	private static $_instance = null;
	public $_pdo, $_query, $_results, $_count=0, $error=false;

	// constructor initialize the connection with database through PDO
	private function __construct(){
		try{
			ini_set('max_execution_time', 3000000);
			$this->_pdo = new PDO('mysql:host='.Config::get('mysql/host').';dbname='.Config::get('mysql/db'),Config::get('mysql/user'),Config::get('mysql/pass'),array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4'"));

		}
		catch(PDOException $e){
			die($e->getMessage());
		}
	}

	//Used to instantiate an object
	public static function getInstance(){													 																	
		if(!isset(self::$_instance)){
			self::$_instance=new DB();
		}
		return self::$_instance;
	}

	//Automatically runs query, arguments-($sql - query string, $params - array of parameters to bind)
	public function query($sql,$params=array()){
		$this->_error=false;
		if($this->_query=$this->_pdo->prepare($sql)){
			$x=1; 
			if(count($params)){
				foreach($params as $param){
					$this->_query->bindValue($x,$param);
					$x++;
				}
			
			}

			if($this->_query->execute()){
				$this->_results=$this->_query->fetchAll(PDO::FETCH_OBJ);
				$this->_count=$this->_query->rowCount();
			} else {
				$this->_error =true;
				$this->_errorInfo =$this->_query->errorInfo();
			}
		}

		return $this;
	}

	//returns results as an object
	public function results(){
		return $this->_results;  
	}

	//returns the first row of the results
	public function first(){
		$var = $this->results();
		$var1 = !empty($var[0]) ? $var[0] : "";
		return $var1;

	}

	// to get error from the PDO, returns true
	public function error(){
		return $this->_error;
	}

	// get last inserted id through query in database
	public function last(){
		return $this->_id=$this->_pdo->lastInsertId();
	}


}
?>
