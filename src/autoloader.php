	<?php
	/**
	* this is autoloader.php file, used for auto registering classes and set the global constants 
	*/

	// error reporting off in production env
	error_reporting(E_ALL);
	ini_set('display_errors', 'On');
	
	define("APP_ROOT", dirname(__FILE__));
 	
 	//Globals array to store standard constant values
	$GLOBALS['config']=array(		
						'mysql' => 	array(		
											'host' => '$hostname',
	                                        'user'=>'$username',
	                                        'pass'=>'$password',
	                                        'db'=>'dockabl_todo'
										)
							);
	
	// register all the available classes in classes folder
	spl_autoload_register(function($class){						
		require_once(APP_ROOT.'/classes/'.$class.'.php');		
	});
	 
	
	 