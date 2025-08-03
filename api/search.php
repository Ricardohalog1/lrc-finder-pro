<?php
header('Content-Type: application/json');

$offline_api_url = 'http://localhost:3300/api/search';
$online_api_url = 'https://lrclib.net/api/search';

function fetch_lyrics_from_url($url) {
    $options = [
        "http" => [
            "timeout" => 5,
        ],
        "ssl" => [
            "verify_peer" => false,
            "verify_peer_name" => false,
        ],
    ];
    $context = stream_context_create($options);
    return @file_get_contents($url, false, $context);
}

function process_results($response_json) {
    $file_results = [];
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
    return $file_results;
}

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
            list($artist, $title) = array_map('trim', explode(' - ', $filename, 2));
        }
        
        $offline_query_params = ['q' => "$artist $title"];
        $offline_request_url = $offline_api_url . '?' . http_build_query($offline_query_params);
        $response_json = fetch_lyrics_from_url($offline_request_url);
        
        $file_results = process_results($response_json);

        if (empty($file_results)) {
            $online_query_params = ['artist_name' => $artist, 'track_name' => $title];
            $online_request_url = $online_api_url . '?' . http_build_query($online_query_params);
            $response_json = fetch_lyrics_from_url($online_request_url);
            $file_results = process_results($response_json);
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

    $offline_query_params = ['q' => $query];
    $offline_request_url = $offline_api_url . '?' . http_build_query($offline_query_params);
    $response_json = fetch_lyrics_from_url($offline_request_url);

    $decoded_results = json_decode($response_json, true);
    if ($response_json !== false && !empty($decoded_results)) {
        echo $response_json;
        exit;
    }

    $artist_name = '';
    $track_name = '';

    if (strpos($query, ' - ') !== false) {
        list($artist_name, $track_name) = array_map('trim', explode(' - ', $query, 2));
    } else {
        $track_name = $query;
    }

    $online_query_params = array_filter(['artist_name' => $artist_name, 'track_name' => $track_name]);
    $online_request_url = $online_api_url . '?' . http_build_query($online_query_params);
    $response_json = fetch_lyrics_from_url($online_request_url);
    $decoded_results = json_decode($response_json, true);

    if (empty($decoded_results)) {
        $catch_all_params = ['artist_name' => $query, 'track_name' => $query];
        $online_request_url = $online_api_url . '?' . http_build_query($catch_all_params);
        $response_json = fetch_lyrics_from_url($online_request_url);
    }

    echo $response_json ? $response_json : json_encode([]);
}
?>

