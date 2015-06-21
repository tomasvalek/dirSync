<?php

	require_once "class.DirSync.php";

	// creating a new instance of \DirSync
	$dirSync = new \DirSync();

	// define a valid path of a json file
	$filePath = "../input.json";

	// define a root path to the root dir
	$rootPath = "../tests/tmp";	//BE CAREFUL! directory of rootPath will be deleted

	try {
		// provide the instance with a JSON data file
		$dirSync->fromFile($filePath);

		// set the root directory in which the directory sync will be applied
		$dirSync->setRootDir($rootPath);

		// trigger the synchronization process
		$dirSync->sync();
	} catch (Exception $e){
		echo $e->getMessage();
	}

	// print back the current directory tree
	//print_r(scandir(__DIR__));
