<?php
/**
* @file class.Rm.php
* @brief Remove directories and files.
* @author Tomas Valek
*/
namespace DirSync\Action;

require_once "RmInterface.php";

class Rm implements RmInterface {

	/** Constructor.*/
	public function __construct(){}

	/** Remove dir(s).
	 * @param string $dirName A valid path to a existing directory.
	 * @throws \Exception
	 * @return void.
	 */
	public function removeDir($dirName){

		if (file_exists($dirName)) {

			$listOfFiles = scandir($dirName); //get list of all files

			if($listOfFiles === FALSE)
				throw new \Exception("Remove directory failed.");

			//get all the entries from $listOfFiles that are not present in any of the other arrays. 
			$files = array_diff($listOfFiles, array('.','..'));

			foreach ($files as $file) {
				if(is_dir("$dirName/$file")){
					$this->removeDir("$dirName/$file");
				} else {
					$this->removeFile("$dirName/$file");
				}
			}

			if(rmdir($dirName) === FALSE)
				throw new \Exception("Remove directory failed.");
		}
	}

	/** Remove file.
	 * @param string $fileName A valid path to a existing file.
	 * @throws \Exception
	 * @return void.
	 */
	public function removeFile($fileName){

		if (file_exists($fileName)) {

			if(unlink($fileName) === FALSE)
				throw new \Exception("Remove directory failed.");
		}
	}

}
