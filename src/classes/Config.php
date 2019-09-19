<?php
	/**
	* Config.php file
	* config.php file is used to access global contants through out the app
	*/

class Config{																
	
	public static function get($path=null){
		if($path){
			$config=$GLOBALS['config'];
			$path=explode('/',$path);

			foreach ($path as $val) {
				if(isset($config[$val])){
					$config=$config[$val];
				}
			}
			return $config;
		}

		return false;

	}
}