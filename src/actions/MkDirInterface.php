<?php
/**
* @file MkDirInterface.php
* @brief Interface for make directory.
* @author Tomas Valek
*/
namespace DirSync\Action;

interface MkDirInterface {

    public function __construct();

    /** Make directory.
	 * @param string $dirName
	 * @throws \Exception
	 * @return void.
	 */
	public function makeDir($dirName);

}
