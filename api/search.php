<?php
header('Content-Type: application/json');

$base_api_url = 'http://localhost:3300/api/search';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);
    $track_objects = isset($data['tracks']) ? $data['tracks'] : [];

    if (empty($track_objects)) {
        echo json_encode([]);
        exit;
    }

    $found_lyrics_collection = [];

    foreach ($track_objects as $track) {
        $filename = $track['baseName'];
        $artist = '';
        $title = $filename;

        if (strpos($filename, ' - ') !== false) {
            list($artist_guess, $title_guess) = explode(' - ', $filename, 2);
            $artist = trim($artist_guess);
            $title = trim($title_guess);
        } elseif (preg_match('/(.+?)\s+by\s+(.+)/i', $filename, $matches)) {
            $title = trim($matches[1]);
            $artist = trim($matches[2]);
        } else {
            $title = $filename;
            $artist = ''; 
        }
        
        $junk_patterns = [
            '/\s*\(.*?\)\s*/',
            '/\s*\[.*?\]\s*/',
            '/(\s*-\s*)?(official|music|video|audio|lyrics|lyric|hd|hq|live|remastered|explicit|audio|track)/i'
        ];
        $clean_title = preg_replace($junk_patterns, '', $title);
        if (!empty(trim($clean_title))) {
            $title = trim($clean_title);
        }
        
        $search_query = trim($title . ' ' . $artist);
        $query_params = ['q' => $search_query];
        $request_url = $base_api_url . '?' . http_build_query($query_params);

        $response_json = @file_get_contents($request_url);
        
        $file_results = [];
        if ($response_json !== false) {
            $response_data = json_decode($response_json, true);
            
            if (is_array($response_data) && !empty($response_data)) {
                foreach ($response_data as $result) {
                    $lyrics_content = null;
                    $lyrics_type = 'none';

                    if (isset($result['syncedLyrics']) && !empty($result['syncedLyrics'])) {
                        $lyrics_content = $result['syncedLyrics'];
                        $lyrics_type = 'synced';
                    } elseif (isset($result['plainLyrics']) && !empty($result['plainLyrics'])) {
                        $lyrics_content = $result['plainLyrics'];
                        $lyrics_type = 'plain';
                    }

                    if ($lyrics_content) {
                        $file_results[] = [
                            'artist' => $result['artistName'] ?? 'Unknown Artist',
                            'title' => $result['trackName'] ?? 'Unknown Title',
                            'album' => $result['albumName'] ?? 'Unknown Album',
                            'duration' => $result['duration'] ?? 0,
                            'lyrics' => $lyrics_content,
                            'status' => $lyrics_type
                        ];
                    }
                }
            }
        }

        if (!empty($file_results)) {
             $found_lyrics_collection[$track['baseName']] = [
                'status' => 'found',
                'results' => $file_results
            ];
        } else {
            $found_lyrics_collection[$track['baseName']] = ['status' => 'not_found'];
        }
    }

    echo json_encode($found_lyrics_collection);
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = isset($_GET['q']) ? trim($_GET['q']) : '';
    if (empty($query)) {
        echo json_encode([]);
        exit;
    }

    $request_url = $base_api_url . '?' . http_build_query(['q' => $query]);
    $response_json = @file_get_contents($request_url);
    echo $response_json ? $response_json : json_encode([]);
}
?>