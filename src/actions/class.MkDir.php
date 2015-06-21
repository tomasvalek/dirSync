<?php
/**
* @file class.MkDir.php
* @brief Make directory.
* @author Tomas Valek
*/
namespace DirSync\Action;

require_once "MkDirInterface.php";

class MkDir implements MkDirInterface {

	/** Constructor.*/
	public function __construct(){}

	/** Make directory.
	 * @param string $dirName
	 * @throws \Exception
	 * @return void.
	 */
	public function makeDir($dirName){

		if (!file_exists($dirName)) {
			$ret = mkdir($dirName, 0777, true);
			if($ret === FALSE)
				throw new Exception("Make directory fail.");
		}
	}

}
