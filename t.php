<?php

header("Content-Type: application/json");

$files = array_merge(glob("*.png"), glob("*.webm"));
$now = time();

foreach ($files as $file) {
    if (is_file($file) && ($now - filemtime($file) > 300)) {
        unlink($file);
    }
}

if (!isset($_REQUEST['url'])) {
    echo json_encode(["error" => "No URL"]);
    exit;
}

$url = $_REQUEST['url'];

if (strpos($url, ".tgs") !== false) {
    echo json_encode(["error" => "TGS not supported"]);
    exit;
}

$id = uniqid();

$input = "input_$id.webm";

$ch = curl_init($url);
$fp = fopen($input, 'wb');

curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0");

curl_exec($ch);
curl_close($ch);
fclose($fp);

$f1 = "f1_$id.png";
$f2 = "f2_$id.png";
$f3 = "f3_$id.png";

exec("ffmpeg -i $input -ss 1 -vframes 1 $f1");
exec("ffmpeg -i $input -ss 2 -vframes 1 $f2");
exec("ffmpeg -i $input -ss 3 -vframes 1 $f3");

$base = "https://ffmpeg-api-1-wwn7.onrender.com/";

echo json_encode([
    "f1" => $base . $f1,
    "f2" => $base . $f2,
    "f3" => $base . $f3
]);

unlink($input);    echo json_encode(["error" => "Download failed"]);
    curl_close($ch);
    fclose($fp);
    exit;
}

curl_close($ch);
fclose($fp);

if ($is_tgs) {
    $gif = "converted_$id.gif";
    exec("python3 -m lottie.convert $input $gif 2>&1");
    $input = $gif;
}

$f1 = "f1_$id.png";
$f2 = "f2_$id.png";
$f3 = "f3_$id.png";

exec("ffmpeg -i $input -ss 1 -vframes 1 $f1 2>&1", $o1);
exec("ffmpeg -i $input -ss 2 -vframes 1 $f2 2>&1", $o2);
exec("ffmpeg -i $input -ss 3 -vframes 1 $f3 2>&1", $o3);

if (!file_exists($f1) || !file_exists($f2) || !file_exists($f3)) {
    unlink($input);
    echo json_encode([
        "error" => "Processing failed",
        "debug1" => $o1,
        "debug2" => $o2,
        "debug3" => $o3
    ]);
    exit;
}

$base = "https://ffmpeg-api-1-wwn7.onrender.com/";

echo json_encode([
    "f1" => $base . $f1,
    "f2" => $base . $f2,
    "f3" => $base . $f3
]);

unlink($input);
