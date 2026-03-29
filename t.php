<?php

header("Content-Type: application/json");

if (!isset($_REQUEST['url'])) {
    echo json_encode(["error" => "No URL"]);
    exit;
}

$url = $_REQUEST['url'];

// download file
$input = "input.webm";
file_put_contents($input, file_get_contents($url));

// output files
$first = "first.png";
$last  = "last.png";

// extract frames
exec("ffmpeg -i $input -vf \"select=eq(n\\,0)\" -vframes 1 $first");
exec("ffmpeg -sseof -0.3 -i $input -vframes 1 $last");

// 🔥 return public URLs
$base_url = "https://ffmpeg-api-1-wwn7.onrender.com/";

echo json_encode([
    "first" => $base_url . $first,
    "last"  => $base_url . $last
]);
