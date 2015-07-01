<?php
/**
* @file RmInterface.php
* @brief Interface for remove directories and files.
* @author Tomas Valek
*/
namespace DirSync\Action;

interface RmInterface {

    public function __construct();

    /** Remove dir(s) or file.
	 * @param string A valid path to a existing directory.
	 * @throws \Exception
	 * @return self
	 */
	public function remove($dirOrFile);

	/** Remove file.
	 * @param string A valid path to a existing file.
	 * @throws \Exception
	 * @return self
	 */
	public function removeFile($fileName);
}
