<?php
// Check if lyrics data is posted
if (!isset($_POST['lyrics_data'])) {
    die('Error: No lyrics data provided.');
}

$lyrics_data = json_decode($_POST['lyrics_data'], true);

if (empty($lyrics_data) || !is_array($lyrics_data)) {
    die('Error: Invalid lyrics data.');
}

// Create a temporary file for the zip archive
$zip_file = tempnam(sys_get_temp_dir(), 'lyrics') . '.zip';
$zip = new ZipArchive();

if ($zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die('Error: Cannot create zip file.');
}

// Add each lyric data as a .lrc file to the zip
foreach ($lyrics_data as $song_title => $synced_lyrics) {
    // Sanitize filename to prevent issues
    $filename = preg_replace('/[\/:*?"<>|]/', '_', $song_title) . '.lrc';
    $zip->addFromString($filename, $synced_lyrics);
}

$zip->close();

// Set headers to force download the zip file
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="SyncedLyrics.zip"');
header('Content-Length: ' . filesize($zip_file));
header('Pragma: no-cache');
header('Expires: 0');

// Read the zip file and send it to the browser
readfile($zip_file);

// Delete the temporary zip file
unlink($zip_file);

exit;
?>