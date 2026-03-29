<?php

header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ❌ no URL
if (!isset($_REQUEST['url'])) {
    echo json_encode(["error" => "No URL provided"]);
    exit;
}

$url = $_REQUEST['url'];

// 📥 download file
$tempInput = "input_" . time() . ".webm";
file_put_contents($tempInput, file_get_contents($url));

// 🗂 output files
$firstFrame = "first_" . time() . ".png";
$lastFrame  = "last_" . time() . ".png";

try {

    // 🖼 First frame
    exec("ffmpeg -i $tempInput -vf \"select=eq(n\\,0)\" -vframes 1 $firstFrame 2>&1", $out1);

    // 🖼 Last frame
    exec("ffmpeg -sseof -0.3 -i $tempInput -vframes 1 $lastFrame 2>&1", $out2);

    // ❌ check
    if (!file_exists($firstFrame) || !file_exists($lastFrame)) {
        echo json_encode([
            "error" => "Frame extraction failed",
            "debug1" => $out1,
            "debug2" => $out2
        ]);
        unlink($tempInput);
        exit;
    }

    // 📤 base64 encode
    $first = base64_encode(file_get_contents($firstFrame));
    $last  = base64_encode(file_get_contents($lastFrame));

    // 🧹 cleanup
    unlink($tempInput);
    unlink($firstFrame);
    unlink($lastFrame);

    // ✅ response
    echo json_encode([
        "first" => $first,
        "last"  => $last
    ]);

} catch (Exception $e) {
    echo json_encode([
        "error" => $e->getMessage()
    ]);
}
