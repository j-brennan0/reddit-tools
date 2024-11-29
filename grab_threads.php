<?php

// edit these elements to fit your data desire

$subreddit = "EVCanada";										// check exact spelling!
$fn_list = "data_EVCanada/list_EVCanada_top_all_2024-11-29_14-27.json";		// the file generated by the grab_list.php in your data_subreddit directory	(needs to contain a data in the format YYYY-MM-DD_HH-MM)
$sortmode = "confidence";										// how to sort the comments (confidence|top|new|controversial|old|random|qa|live)

// do not edit anything below this line
//-----------------------------------------------------

// basic script conf
date_default_timezone_set("UTC");
set_time_limit(3600*5);
ini_set("memory_limit","100M");
ini_set("error_reporting",1);

// set file paths
$workdir = getcwd();
$jsondir = $workdir . "/data_" . $subreddit . "/";

// get date from filename (to associate JSON threads with the specific filename)
$tmp = preg_match("/\d\d\d\d-\d\d-\d\d_\d\d-\d\d/",$fn_list,$out);
$timestamp = $out[0];

// get list and iterate over it
$fullist = json_decode(file_get_contents($jsondir . $fn_list));

echo "\ngetting data: ";

$counter = 0;
foreach($fullist as $listitem) {
	
	$id = $listitem->data->id;
	$thread = "http://www.reddit.com" . $listitem->data->permalink . ".json?sort=".$sortmode;
	$url = $listitem->data->url;
		
	$fn_thread = $jsondir . "thread_" . $id . "_" . $timestamp . ".json";

	if(!file_exists($fn_thread)) {
	
		$data = file_get_contents($thread);
		file_put_contents($fn_thread, $data);
	}
	
	$counter++;
	echo $counter . " ";
	
	sleep(0.2);				// let's just back off a litte and wait for 200ms to be polite
}

echo "\n\nfinished\n\n";

?>