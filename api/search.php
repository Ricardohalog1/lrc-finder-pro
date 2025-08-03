<?php
header('Content-Type: application/json');

// --- API Endpoints ---
$offline_api_url = 'http://localhost:3300/api/search'; // Ang iyong local server
$online_api_url = 'https://lrclib.net/api/search';   // Ang online fallback

/**
 * Function to get contents from a URL.
 * It includes options to handle potential SSL errors on local servers.
 * It also sets a timeout to prevent long waits for an unresponsive offline server.
 */
function fetch_lyrics_from_url($url) {
    $options = [
        "http" => [
            "timeout" => 5, // Mag-give up after 5 seconds kung hindi ma-contact ang server
        ],
        "ssl" => [
            "verify_peer" => false,
            "verify_peer_name" => false,
        ],
    ];
    $context = stream_context_create($options);
    return @file_get_contents($url, false, $context);
}

/**
 * Function to process search results from an API response.
 * Returns an array of formatted results.
 */
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


// --- Main Logic ---

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- BATCH FINDER LOGIC ---

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
        
        // --- Try OFFLINE first ---
        $offline_query_params = ['q' => "$artist $title"];
        $offline_request_url = $offline_api_url . '?' . http_build_query($offline_query_params);
        $response_json = fetch_lyrics_from_url($offline_request_url);
        
        $file_results = process_results($response_json);

        // --- If OFFLINE fails or has no results, try ONLINE ---
        if (empty($file_results)) {
            $online_query_params = ['artist_name' => $artist, 'track_name' => $title];
            $online_request_url = $online_api_url . '?' . http_build_query($online_query_params);
            $response_json = fetch_lyrics_from_url($online_request_url);
            $file_results = process_results($response_json);
        }

        // --- Collate results ---
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
    // --- MANUAL SEARCH LOGIC (FIXED) ---

    $query = isset($_GET['q']) ? trim($_GET['q']) : '';
    if (empty($query)) {
        echo json_encode([]);
        exit;
    }

    // --- 1. Try OFFLINE first ---
    $offline_query_params = ['q' => $query];
    $offline_request_url = $offline_api_url . '?' . http_build_query($offline_query_params);
    $response_json = fetch_lyrics_from_url($offline_request_url);

    $decoded_results = json_decode($response_json, true);
    if ($response_json !== false && !empty($decoded_results)) {
        echo $response_json;
        exit;
    }

    // --- 2. If OFFLINE fails, try ONLINE with smarter parsing ---
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

    // --- 3. If smart parse fails, try a "catch-all" online search ---
    if (empty($decoded_results)) {
        $catch_all_params = ['artist_name' => $query, 'track_name' => $query];
        $online_request_url = $online_api_url . '?' . http_build_query($catch_all_params);
        $response_json = fetch_lyrics_from_url($online_request_url);
    }

    echo $response_json ? $response_json : json_encode([]);
}
?>
