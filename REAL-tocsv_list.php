<?php

// Edit these elements to fit your data
$subreddit = "EVCanada";
$fn_list = "list_EVCanada_top_all_2024-11-29_14-27.json"; // Generated JSON file
$filetype = ".csv"; 
$delimiter = ",";

// Basic script configuration
date_default_timezone_set("UTC");
set_time_limit(3600 * 5);
ini_set("memory_limit", "100M");
ini_set("error_reporting", 1);

// Set file paths
$workdir = getcwd();
$jsondir = $workdir . "/data_" . $subreddit . "/";
$fn_output = $workdir . "/" . preg_replace("/\.json/", $filetype, $fn_list);

// Validate JSON file
$jsonFilePath = $jsondir . $fn_list;
if (!file_exists($jsonFilePath)) {
    die("JSON file does not exist: $jsonFilePath");
}

$jsonContent = file_get_contents($jsonFilePath);
if ($jsonContent === false) {
    die("Failed to read JSON file: $jsonFilePath");
}

$json = json_decode($jsonContent);
if ($json === null) {
    die("Failed to decode JSON data. Check file content: $jsonFilePath");
}

// Open output file
$fp = fopen($fn_output, "w");
if ($fp === false) {
    die("Failed to open output file for writing: $fn_output");
}

// Write headers with UTF-8 BOM for Excel compatibility
$headers = array("\xEF\xBB\xBFid", "author", "title", "score", "ups", "created", "domain", "url");
fputcsv($fp, $headers, $delimiter);

// Iterate over posts and write to CSV
foreach ($json->data->children as $post) {
    $ln = array(
        $post->data->id,
        $post->data->author,
        html_entity_decode($post->data->title, ENT_QUOTES),
        $post->data->score,
        $post->data->ups,
        $post->data->created,
        $post->data->domain,
        preg_replace("/&amp;/", "&", $post->data->url)
    );
    fputcsv($fp, $ln, $delimiter);
}

// Close the output file
if (!fclose($fp)) {
    die("Failed to properly close the output file: $fn_output");
}

echo "\n\nFinished\n\n";

?>
