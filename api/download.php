<?php

if (!isset($_POST['lyrics_data'])) {
    http_response_code(400);
    die('Error: No lyrics data provided.');
}

$folder_name = $_POST['folder_name'] ?? 'SyncedLyrics';

$safe_folder_name = trim(preg_replace('/[\/:*?"<>|]/', '_', $folder_name), ' .');

if (empty($safe_folder_name)) {
    $safe_folder_name = 'SyncedLyrics';
}

$zip_filename = $safe_folder_name . '.zip';

$lyrics_data = json_decode($_POST['lyrics_data'], true);

if (json_last_error() !== JSON_ERROR_NONE || empty($lyrics_data) || !is_array($lyrics_data)) {
    http_response_code(400);
    die('Error: Invalid or empty lyrics data.');
}

$zip_file_path = tempnam(sys_get_temp_dir(), 'lyrics_zip_');
if ($zip_file_path === false) {
    http_response_code(500);
    die('Error: Cannot create temporary file.');
}

$zip_file_final_path = $zip_file_path . '.zip';
rename($zip_file_path, $zip_file_final_path);


$zip = new ZipArchive();

if ($zip->open($zip_file_final_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    http_response_code(500);
    die('Error: Cannot create zip file.');
}

foreach ($lyrics_data as $song_title => $synced_lyrics) {

    $filename_in_zip = preg_replace('/[\/:*?"<>|]/', '_', $song_title) . '.lrc';
    $zip->addFromString($filename_in_zip, $synced_lyrics);
}

$zip->close();

if (!file_exists($zip_file_final_path) || !is_readable($zip_file_final_path)) {
    http_response_code(500);
    die('Error: Zip file is not accessible.');
}

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zip_filename . '"');
header('Content-Length: ' . filesize($zip_file_final_path));
header('Pragma: no-cache');
header('Expires: 0');
header('Connection: close');

ob_clean();
flush();
readfile($zip_file_final_path);

unlink($zip_file_final_path);

exit;
?>

