<?php

// Edit these elements to fit your data
$subreddit = "EVCanada"; // Exact subreddit spelling
$fn_list = "list_EVCanada_top_all_2024-11-29_14-27.csv"; // Filename generated by grab_list.php
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
$fn_output = $workdir . "/" . preg_replace("/list_/", "comments_", preg_replace("/\.json/", $filetype, $fn_list));

// Get date from filename
if (!preg_match("/\d\d\d\d-\d\d-\d\d_\d\d-\d\d/", $fn_list, $out)) {
    die("Failed to extract timestamp from filename.");
}
$timestamp = $out[0];

// Read files in the directory
$filelist = scandir($jsondir);

// Open output file and write headers
$fp = fopen($fn_output, "w");
if ($fp === false) {
    die("Failed to open output file: $fn_output");
}

$headers = [
    "\xEF\xBB\xBFpost_id", "post_author", "post_title", "post_url", "post_score",
    "post_ups", "post_downs", "post_created", "post_created_mysql",
    "comment_id", "comment_author", "comment_permalink", "comment_content",
    "comment_nesting", "comment_score", "comment_downs", "comment_ups",
    "comment_created_unix", "comment_created_mysql"
];
fputcsv($fp, $headers, $delimiter);

// Process each file
echo "\n\nReading files: ";
$counter = 0;

foreach ($filelist as $fn) {
    if (!preg_match("/$timestamp/", $fn) || !preg_match("/thread_/", $fn)) {
        continue;
    }

    $fcontent = file_get_contents($jsondir . $fn);
    if ($fcontent === false) {
        echo "Failed to read file: $fn\n";
        continue;
    }

    $fcontent = json_decode($fcontent);
    if ($fcontent === null) {
        echo "Invalid JSON in file: $fn\n";
        continue;
    }

    $postdata = $fcontent[0]->data->children[0]->data;
    processlisting($fcontent[1]->data->children, 0, $postdata, $fp, $delimiter);

    echo $counter . " ";
    $counter++;
}

fclose($fp);
echo "\n\nFinished\n\n";

function processlisting($commentlist, $depth, $postdata, $fp, $delimiter) {
    $depth++;
    foreach ($commentlist as $comment) {
        if ($comment->kind === "more") {
            continue;
        }

        $ln = [
            $postdata->name,
            $postdata->author,
            clean($postdata->title),
            "https://www.reddit.com" . $postdata->permalink,
            $postdata->score,
            $postdata->ups,
            $postdata->downs,
            $postdata->created,
            date("Y-m-d H:i:s", $postdata->created),
            $comment->data->name,
            $comment->data->author,
            "https://www.reddit.com" . $postdata->permalink . $comment->data->id,
            clean($comment->data->body),
            $depth,
            $comment->data->score,
            $comment->data->downs,
            $comment->data->ups,
            $comment->data->created,
            date("Y-m-d H:i:s", $comment->data->created)
        ];

        fputcsv($fp, $ln, $delimiter);

        if (isset($comment->data->replies) && is_object($comment->data->replies)) {
            processlisting($comment->data->replies->data->children, $depth, $postdata, $fp, $delimiter);
        }
    }
}

function clean($text) {
    return preg_replace("/[\n\t\r]/", "", $text);
}

?>
