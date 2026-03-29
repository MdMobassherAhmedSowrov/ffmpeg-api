<?php
header("Content-Type: application/json");

$files = array_merge(glob("*.png"), glob("*.webm"), glob("*.tgs"), glob("*.gif"));
$now = time();
foreach ($files as $file) {
    if (is_file($file) && ($now - filemtime($file) > 300)) {
        @unlink($file);
    }
}

if (!isset($_REQUEST['url'])) {
    echo json_encode(["error" => "No URL provided"]);
    exit;
}

$url = $_REQUEST['url'];
$is_tgs = (strpos(strtolower($url), ".tgs") !== false);
$id = uniqid();

$input = $is_tgs ? "input_$id.tgs" : "input_$id.webm";
$ch = curl_init($url);
$fp = fopen($input, 'wb');

curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0");

curl_exec($ch);
if (curl_errno($ch)) {
    echo json_encode(["error" => "Download failed"]);
    curl_close($ch);
    fclose($fp);
    exit;
}
curl_close($ch);
fclose($fp);

$process_file = $input;

if ($is_tgs) {
    $gif = "converted_$id.gif";
    
    $commands = [
        "lottie_convert.py",
        "lottie_convert",
        "/usr/local/bin/lottie_convert.py",
        "/usr/local/bin/lottie_convert",
        "python3 /usr/local/bin/lottie_convert.py"
    ];
    
    $success = false;
    $debug_logs = [];
    
    foreach ($commands as $cmd) {
        $output = [];
        $return_var = -1;
        $full_cmd = $cmd . " " . escapeshellarg($input) . " " . escapeshellarg($gif) . " 2>&1";
        exec($full_cmd, $output, $return_var);
        
        if (file_exists($gif) && filesize($gif) > 0) {
            $success = true;
            break;
        } else {
            $debug_logs[$cmd] = $output;
        }
    }
    
    if (!$success) {
        echo json_encode([
            "error" => "TGS conversion failed completely", 
            "debug" => $debug_logs
        ]);
        @unlink($input);
        exit;
    }
    $process_file = $gif;
}

$f1 = "f1_$id.png";
$f2 = "f2_$id.png";
$f3 = "f3_$id.png";

exec("ffmpeg -y -i " . escapeshellarg($process_file) . " -ss 1 -vframes 1 " . escapeshellarg($f1) . " 2>&1");
exec("ffmpeg -y -i " . escapeshellarg($process_file) . " -ss 2 -vframes 1 " . escapeshellarg($f2) . " 2>&1");
exec("ffmpeg -y -i " . escapeshellarg($process_file) . " -ss 2.9 -vframes 1 " . escapeshellarg($f3) . " 2>&1");

if (!file_exists($f1) || !file_exists($f2) || !file_exists($f3)) {
    if (!file_exists($f1)) exec("ffmpeg -y -i " . escapeshellarg($process_file) . " -vframes 1 " . escapeshellarg($f1) . " 2>&1");
    if (!file_exists($f2)) @copy($f1, $f2);
    if (!file_exists($f3)) @copy($f1, $f3);
}

$base = "https://" . $_SERVER['HTTP_HOST'] . "/";

echo json_encode([
    "f1" => $base . $f1,
    "f2" => $base . $f2,
    "f3" => $base . $f3
]);

@unlink($input);
if($is_tgs) @unlink($process_file);
?>
