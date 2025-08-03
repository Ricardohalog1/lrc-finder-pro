<?php

if (!isset($_POST['lyrics_data'])) {
    die('Error: No lyrics data provided.');
}

$lyrics_data = json_decode($_POST['lyrics_data'], true);

if (empty($lyrics_data) || !is_array($lyrics_data)) {
    die('Error: Invalid lyrics data.');
}

$zip_file = tempnam(sys_get_temp_dir(), 'lyrics') . '.zip';
$zip = new ZipArchive();

if ($zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die('Error: Cannot create zip file.');
}

foreach ($lyrics_data as $song_title => $synced_lyrics) {
    $filename = preg_replace('/[\/:*?"<>|]/', '_', $song_title) . '.lrc';
    $zip->addFromString($filename, $synced_lyrics);
}

$zip->close();

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="SyncedLyrics.zip"');
header('Content-Length: ' . filesize($zip_file));
header('Pragma: no-cache');
header('Expires: 0');

readfile($zip_file);

unlink($zip_file);

exit;
?>