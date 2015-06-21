<?php

require_once "DirSyncInterface.php";
require_once "actions/class.Rm.php";
require_once "actions/class.MkDir.php";

class DirSync implements DirSyncInterface {

	const SYNC_CREATE_ONLY = 0;
	const SYNC_REMOVE_ONLY = 1;
	const SYNC_ACTIONS_ONLY = 2;

	private $json = null;		//directory structure in JSON; init
	private $rootDir = null;	//init
	private $options = null;	//init

	/** Constructor.*/
	public function __construct(){

		/*
		If the root directory is not set the Instance should look for
	 	constant "__root__"; if the constant is not provided
	 	then the root is the system root.

		System root??? With: "directory which is not in JSON will be removed" it is crazy.
		*/

		//$this->rootDir = $_SERVER['DOCUMENT_ROOT'];
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

		return;
	}

	/**
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

		return;
	}

	/**
	 * Will provide the library with the JSON input
	 * 
	 * @param string $JSON A raw string JSON
	 * @throws \DirSync\Exception
	 * @return self
	 */
	public function setJsonInput($JSON){
		//TODO
	}

	/**
	 * Simply return the previously given JSON data.
	 * @throws \DirSync\Exception
	 * @return string Return a string JSON data.
	 */
	public function getJsonInput(){

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
	public function sync($options=null){

		//check JSON
		if($this->json == null)
			throw new Exception("No JSON file specified.");

		//check rootDir
		if($this->rootDir == null)
			throw new Exception("No JSON file specified.");

		$this->options = $options;

		//DEBUG:
		/*var_dump($this->json);
		echo "<br>";
		var_dump($this->rootDir);
		echo "<br>---------------<br>";*/

		//Check options
		if($options == self::SYNC_CREATE_ONLY){
			$this->createTree("", $this->json);
		} else if ($options == self::SYNC_REMOVE_ONLY){
			$rm = new \DirSync\Action\Rm();
			$rm->removeDir($this->rootDir);
		} else if ($options == self::SYNC_ACTIONS_ONLY){
			$this->createTree("", $this->json);
		} else { //without options
			/**
			 * directory which is not in JSON will be removed
			 */
			$rm = new \DirSync\Action\Rm();
			$rm->removeDir($this->rootDir);

			$this->makeDir($this->rootDir);
			$this->createTree("", $this->json);
		}
	}

	/* Create tree by JSON structure.
	* @param string $prefix File URI.
	* @param array $array Array from JSON object.
	* return void
	*/
	private function createTree($prefix, $array) {

		foreach($array as $dirName => $value) {

			//DEBUG
			//var_dump($dirName); echo " => "; var_dump($value);echo "<br>";

			if(empty($dirName)) //fix empty key
				continue;

			//Action:
			if($dirName[0] == '#'){

				//creating folder without $dirName, because it is action
				//if more '/' in path does not matter
				//if file already exists does not matter
				if($this->options != self::SYNC_ACTIONS_ONLY){
					(new \DirSync\Action\MkDir())->makeDir($this->rootDir."/".$prefix);
					//echo "Creating: ".$this->rootDir."/".$prefix."<br>";	//DEBUG
				}

				//TODO check action and execute
				continue;
			}

			//Directory:
			if(!is_array($value) && $this->options != self::SYNC_ACTIONS_ONLY){ //not an array
				//if more '/' in path does not matter
				//if file already exists does not matter
				(new \DirSync\Action\MkDir())->makeDir($this->rootDir."/".$prefix."/".$dirName);
				//echo "Creating: ".$this->rootDir."/".$prefix."/".$dirName."<br><br>";	//DEBUG
			} else { //is array

				$this->createTree($prefix."/".$dirName, $value);
			}
		}
	}
}
