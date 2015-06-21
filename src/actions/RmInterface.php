<?php
/**
* @file RmInterface.php
* @brief Interface for remove directories and files.
* @author Tomas Valek
*/
namespace DirSync\Action;

interface RmInterface {

    public function __construct();

    /** Remove dir(s).
	 * @param string $dirName A valid path to a existing directory.
	 * @throws \Exception
	 * @return void.
	 */
	public function removeDir($dirName);

	/** Remove file.
	 * @param string $fileName A valid path to a existing file.
	 * @throws \Exception
	 * @return void.
	 */
	public function removeFile($fileName);
}
