<?php

require_once "DirSyncInterface.php";
require_once "actions/class.Rm.php";
require_once "actions/class.MkDir.php";

define("__root__", "/var/www/dirSync"); //simulate defined const 

class DirSync implements DirSyncInterface {

	//first is not 0 because of implicit conversion NULL to 0
	const SYNC_CREATE_ONLY = 1;
	const SYNC_REMOVE_ONLY = 2;
	const SYNC_ACTIONS_ONLY = 3;
	
	const ACTION_RM = "#rm";
	
	private $json = null;		//directory structure in JSON; init
	private $rootDir = null;	//init
	private $options = null;	//init
	private $remainDirs = Array();	//init, array of remainDirs which is not in JSON

	/** Constructor.*/
	public function __construct(){
		
		/**
		* If the root directory is not set the Instance should look for
		* constant "__root__"; if the constant is not provided
		* then the root is the system root.
		* */
		
		//init const for myself system, can be change (for CLI DOCUMENT_ROOT is not set)
		$this->rootDir = $_SERVER['DOCUMENT_ROOT']."/dirSync";
		
		//Check const __root__ is defined
		if( defined("__root__") )
			$this->rootDir = __root__;
	}

	/**
	 * Will set the root directory in which the directory 
	 * sync will be applied.
	 * If the root directory is not set the Instance should look for
	 * constant "__root__"; if the constant is not provided
	 * then the root is the system root.
	 * @param string $path A valid path to a existing directory
	 * @return self
	 */
	public function setRootDir($path){

		//check is path exists
		if(file_exists($path) === FALSE)
			throw new Exception("setRootDir(): Path is not correct.");

		$this->rootDir = $path;

		return $this;
	}
	
	/*
	 * Will read the JSON string directly from a file path;
	 * 
	 * @param string $filePath A valid json file path
	 * @throws \DirSync\Exception
	 * @return self
	 */
	public function fromFile($filePath){

		$readData = file_get_contents($filePath);

		//check file_get_contents
		if($readData === FALSE)
			throw new Exception("file_get_contents() fail.");

		$json = json_decode($readData, true);

		//check json_decode
		switch (json_last_error()) {
			case JSON_ERROR_NONE:
				//no error continue
			break;
			case JSON_ERROR_DEPTH:
				throw new Exception("json_decode(): Maximum stack depth exceeded.");
			break;
			case JSON_ERROR_STATE_MISMATCH:
				throw new Exception("json_decode(): Underflow or the modes mismatch.");
			break;
			case JSON_ERROR_CTRL_CHAR:
				throw new Exception("json_decode(): Unexpected control character found.");
			break;
			case JSON_ERROR_SYNTAX:
				throw new Exception("json_decode(): Syntax error, malformed JSON.");
			break;
			case JSON_ERROR_UTF8:
				throw new Exception("json_decode(): Malformed UTF-8 characters, possibly incorrectly encoded.");
			break;
			default:
				throw new Exception("json_decode(): Unknown error.");
			break;
		}

		$this->json = $json;
		
		return $this;
	}

	/**
	 * Will provide the library with the JSON input
	 * 
	 * @param string $JSON A raw string JSON
	 * @throws \DirSync\Exception
	 * @return self
	 */
	public function setJsonInput($JSON){
		$this->json = $JSON;
        return $this;
	}

	/**
	 * Simply return the previously given JSON data.
	 * @throws \DirSync\Exception
	 * @return string Return a string JSON data.
	 */
	public function getJsonInput(){
		
		if($this->json === NULL)
			throw new Exception("JSON input not found.");
		
		$json = json_encode($this->json, JSON_PRETTY_PRINT);
		if($json === FALSE)
			throw new Exception("JSON encode fail.");

		return $json;
	}

	/**
	 * Will begin the process of the synchronization. 
	 * The process can have the following options:
	 *
	 *  \DirSync::SYNC_CREATE_ONLY - creating directories only;<br>
	 *  \DirSync::SYNC_REMOVE_ONLY - only removing directories;<br>
	 *  \DirSync::SYNC_ACTIONS_ONLY - just run the action but do 
	 *  not change the directory tree in any way;<br>
	 * 
	 * @param mixed [optional] Additional options for the directory sync process
	 * @throws \DirSync\Exception
	 * @return self|array
	 */
	public function sync($options = null){

		//check JSON
		if($this->json == null)
			throw new Exception("No JSON input specified.");

		//check rootDir
		if($this->rootDir == null)
			throw new Exception("No rootDir specified.");
		
		$this->options = $options;

		$this->remainDirs = $this->getAllDirs($this->rootDir."/*"); // /* means without rootDir
		//print_r($this->remainDirs); //DEBUG
		
		$this->iterateJSON("", $this->json);
		
		//Check dirs which is not in JSON will be removed
		foreach($this->remainDirs as $dir){
			$this->removeFromDisk($dir);
		}

		return $this;
	}

	/* Recursive iterate throught JSON struct.
	* @param string Prefix file URI to plunge in dirs.
	* @param array JSON object in Array.
	* @return
	*/
	private function iterateJSON($prefix, $JSONarray) {
		
		foreach($JSONarray as $key => $value) {
			
			//DEBUG
			//var_dump($key); echo " => "; var_dump($value);echo "<br>";	//DEBUG TODO

			if(empty($key)) //fix empty key
				continue;
			
			//TODO null/false

			//Key started on '#' it is action:
			if(isset($key[0]) && $key[0] == '#'){

				//creating folder without $dirName, because it is action
				//if file already exists does not matter
				//if action is on top-level in JSON, does not matter
				$dirName = $this->checkURI($this->rootDir."/".$prefix);
				$this->createDirectory($dirName);
				
				//Remove item from remainDirs
				$this->removeFromArrayByValue($dirName);

				//TODO check actions and executes
				
				//Action RM
				if($key == self::ACTION_RM){
					//echo "<br>Remove Action<br>";	//TODO
					
					foreach($value as $item) {
						
						$fileName = $this->checkURI($this->rootDir."/".$prefix."/".$item);
						//echo "<br>Deleting: ".$fileName."<br>"; //DEBUG //TODO
						
						$this->removeFromDisk($fileName);
					}
				}
				
				continue;
			}

			//Directory:
			if(!is_array($value)){ //not an array

				//if file already exists does not matter
				$dirName = $this->checkURI($this->rootDir."/".$prefix."/".$key);
				$this->createDirectory($dirName);
				
				//Remove item from remainDirs
				$this->removeFromArrayByValue($dirName);
			} else { //it is array
				
				//Remove item from remainDirs
				$dirNameForRemoveFromArrayByValue = $this->checkURI($this->rootDir."/".$prefix."/".$key);
				$this->removeFromArrayByValue($dirNameForRemoveFromArrayByValue);
				
				$dirName = $this->checkURI($prefix."/".$key);
				$this->iterateJSON($dirName, $value);
			}
		}
		
	}
	
	/** Create directory.
	 * @param string Name of directory.
	 * @return
	 * */
	private function createDirectory($dirName){
		
		//Check options
		if($this->options != self::SYNC_ACTIONS_ONLY && $this->options != self::SYNC_REMOVE_ONLY){
			$md = new \DirSync\Action\MkDir();
			$md->makeDir($dirName);
		}
		
		return;
	}
	
	/** Remove dir or file.
	 * @param string Name of dir. or file.
	 * @return
	 * */
	private function removeFromDisk($dirOrFile){
		
		//Check options
		if($this->options != self::SYNC_CREATE_ONLY && $this->options != self::SYNC_ACTIONS_ONLY){
			
			$rm = new \DirSync\Action\Rm();
			$rm->remove($dirOrFile);

			//echo "<br>Removing: ".$dirOrFile."<br>";	//DEBUG TODO
		}

		return;
	}
	
	/** Get all directories in directory.
	 * @param string Name of directory.
	 * @return Array of directories in directory without '.' and "..".
	 * */
	private function getAllDirs($dirName){

		$files = glob($dirName, GLOB_ONLYDIR); //get all directories in directory to array
		
		foreach ($files as $f) {
			
			if (is_dir($f)) {
				$files = array_merge($files, $this->getAllDirs($f .'/*')); // scan subfolder
			}
		}
		return $files;
	}
	
	/**Check URI of path.
	 * @param string URI.
	 * @return Checked URI.
	 * */
	private function checkURI($uri){
		//if more '/' in path:
		return preg_replace("@[/]{2,}@", "/", $uri);
	}
	
	/** Remove item from array remainDirs by value.
	 * @param string Value which will be delete.
	 * @return 
	 * */
	private function removeFromArrayByValue($deleteValue){

		if(($key = array_search($deleteValue, $this->remainDirs)) !== FALSE) {
			unset($this->remainDirs[$key]);
		}

	}
	
} // end of class.DirSync.php
