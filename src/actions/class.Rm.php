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

	/** Remove dir(s) or file.
	 * @param string A valid path to a existing directory.
	 * @throws \Exception
	 * @return self.
	 */
	public function remove($dirOrFile){

		if (file_exists($dirOrFile)) {
			
			if(is_dir($dirOrFile)){ //Directory
				$listOfFiles = scandir($dirOrFile); //get list of all files
				
				if($listOfFiles === FALSE)
					throw new \Exception("Remove directory failed.");
				
				$files = array_diff($listOfFiles, array('.','..'));
				
				foreach ($files as $file) {
					if(is_dir("$dirOrFile/$file")){ //directory
						$this->remove("$dirOrFile/$file");
					} else { //file
						$this->removeFile("$dirOrFile/$file");
					}
				}
				
				if(rmdir($dirOrFile) === FALSE)
					throw new \Exception("Remove directory failed.");
				
			} else if (is_file($dirOrFile)){ //File
				$this->removeFile($dirOrFile);
			}
		}
		
		return $this;
	}

	/** Remove file.
	 * @param string A valid path to a existing file.
	 * @throws \Exception
	 * @return self.
	 */
	public function removeFile($fileName){

		if (file_exists($fileName)) {

			if(unlink($fileName) === FALSE)
				throw new \Exception("Remove directory failed.");
		}
		
		return $this;
	}

}
