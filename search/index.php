<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual Search - LRC Finder Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        #results::-webkit-scrollbar, #modal-lyrics::-webkit-scrollbar { width: 8px; }
        #results::-webkit-scrollbar-track, #modal-lyrics::-webkit-scrollbar-track { background: #1f2937; }
        #results::-webkit-scrollbar-thumb, #modal-lyrics::-webkit-scrollbar-thumb { background: #4b5563; border-radius: 4px; }
        #results::-webkit-scrollbar-thumb:hover, #modal-lyrics::-webkit-scrollbar-thumb:hover { background: #6b7280; }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-900 text-white pt-8">
    
    <?php include '../nav.php'; ?>

    <div class="bg-gray-800 p-8 rounded-2xl shadow-2xl w-full max-w-3xl border border-gray-700 mx-auto">
        <h1 class="text-3xl font-bold text-center mb-2 text-purple-400">Manual Search</h1>
        <p class="text-center text-gray-400 mb-6">Mag-type ng kanta o artist para maghanap.</p>

        <div class="flex space-x-2 mb-4">
            <input type="text" id="search-input" placeholder="e.g., Wind of Change - Scorpions" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
            <button id="search-btn" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded-lg transition">
                Search
            </button>
        </div>

        <div id="status" class="text-center text-gray-500 my-4"></div>
        <div id="results" class="space-y-2 max-h-[60vh] overflow-y-auto pr-2 hidden">
            </div>
    </div>

    <div id="lyrics-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center p-4 z-50">
        <div class="bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl border border-gray-700 flex flex-col">
            <div class="p-6 border-b border-gray-700">
                <h3 id="modal-title" class="text-2xl font-bold text-purple-400"></h3>
                <p id="modal-artist" class="text-md text-gray-400"></p>
            </div>
            <div class="p-6 flex-grow">
                <pre id="modal-lyrics" class="bg-gray-900 text-gray-300 p-4 rounded-md h-64 overflow-y-auto whitespace-pre-wrap font-mono text-sm"></pre>
            </div>
            <div class="bg-gray-800/50 p-4 flex justify-end space-x-4 rounded-b-lg border-t border-gray-700">
                <button id="modal-close-btn" class="px-4 py-2 bg-gray-600 hover:bg-gray-500 rounded-lg text-white">Close</button>
                <button id="modal-download-btn" class="px-4 py-2 bg-green-600 hover:bg-green-700 rounded-lg text-white font-bold disabled:opacity-50" disabled>Download .lrc</button>
            </div>
        </div>
    </div>

    <script>
        const searchInput = document.getElementById('search-input');
        const searchBtn = document.getElementById('search-btn');
        const statusDiv = document.getElementById('status');
        const resultsDiv = document.getElementById('results');

        // Modal elements
        const lyricsModal = document.getElementById('lyrics-modal');
        const modalTitle = document.getElementById('modal-title');
        const modalArtist = document.getElementById('modal-artist');
        const modalLyrics = document.getElementById('modal-lyrics');
        const modalCloseBtn = document.getElementById('modal-close-btn');
        const modalDownloadBtn = document.getElementById('modal-download-btn');

        let currentTrackForModal = null;

        const formatDuration = (secs) => {
            if (isNaN(secs) || !secs) return "00:00";
            const minutes = Math.floor(secs / 60);
            const seconds = Math.floor(secs % 60).toString().padStart(2, '0');
            return `${minutes}:${seconds}`;
        };

        // **NEW**: Ito yung function na galing sa index.php para consistent ang itsura!
        const createOptionDiv = (optionData) => {
            const { artist, title, album, duration, status } = optionData;
            const isSynced = status === 'synced';
            const statusColor = isSynced ? 'green' : 'yellow';
            const statusTextColor = isSynced ? 'white' : 'gray-900';
            const optionDiv = document.createElement('div');
            optionDiv.className = `p-3 rounded-lg bg-gray-700 hover:bg-gray-600 cursor-pointer border border-transparent hover:border-purple-500`;
            optionDiv.innerHTML = `
                <div class="flex items-center justify-between">
                    <div class="flex-grow pr-4 min-w-0">
                        <p class="font-bold text-white truncate">${title}</p>
                        <p class="text-sm text-gray-300 truncate">${artist}</p>
                        <p class="text-xs text-gray-400 truncate">${album || 'No album info'}</p>
                    </div>
                    <div class="flex-shrink-0 flex items-center space-x-3">
                         <span class="font-mono text-lg text-purple-300">${formatDuration(duration)}</span>
                         <span class="font-semibold px-2 py-1 bg-${statusColor}-500 text-${statusTextColor} rounded-full text-xs">${isSynced ? 'Synced' : 'Plain'}</span>
                    </div>
                </div>`;
            return optionDiv;
        };
        
        const openModal = (track) => {
            currentTrackForModal = track;
            modalTitle.textContent = track.trackName || 'Unknown Title';
            modalArtist.textContent = track.artistName || 'Unknown Artist';
            modalLyrics.textContent = track.syncedLyrics || track.plainLyrics || 'No lyrics available.';
            
            modalDownloadBtn.disabled = !(track.syncedLyrics && track.syncedLyrics.trim() !== '');
            lyricsModal.classList.remove('hidden');
        };

        const closeModal = () => {
            lyricsModal.classList.add('hidden');
            currentTrackForModal = null;
        };

        const performSearch = async () => {
            const query = searchInput.value.trim();
            if (!query) return;

            statusDiv.textContent = 'Searching...';
            searchBtn.disabled = true;
            resultsDiv.innerHTML = '';
            resultsDiv.classList.add('hidden');

            try {
                const response = await fetch(`../get/search.php?q=${encodeURIComponent(query)}`);
                const results = await response.json();
                searchBtn.disabled = false;

                if (!results || results.length === 0) {
                    statusDiv.textContent = `No results found for "${query}"`;
                    return;
                }

                statusDiv.textContent = `Found ${results.length} tracks. Click one to preview.`;
                resultsDiv.classList.remove('hidden');

                // **UPDATED**: Gumagamit na tayo ng bagong display function
                results.forEach(track => {
                    // Standardize keys for the display function
                    const standardizedResult = {
                        artist: track.artistName ?? 'Unknown Artist',
                        title: track.trackName ?? 'Unknown Title',
                        album: track.albumName ?? 'Unknown Album',
                        duration: track.duration ?? 0,
                        status: track.syncedLyrics ? 'synced' : 'plain'
                    };
                    
                    const resultItem = createOptionDiv(standardizedResult);
                    resultsDiv.appendChild(resultItem);

                    // Add click event to open the preview modal
                    if(track.syncedLyrics || track.plainLyrics) {
                        resultItem.addEventListener('click', () => openModal(track));
                    } else {
                        resultItem.classList.add('opacity-50', 'cursor-not-allowed');
                    }
                });
            } catch (error) {
                statusDiv.textContent = 'An error occurred during search.';
                searchBtn.disabled = false;
            }
        };
        
        searchBtn.addEventListener('click', performSearch);
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') performSearch();
        });

        // Modal event listeners
        modalCloseBtn.addEventListener('click', closeModal);
        lyricsModal.addEventListener('click', (e) => {
            if (e.target === lyricsModal) closeModal();
        });
        
        modalDownloadBtn.addEventListener('click', () => {
            if (!currentTrackForModal || !currentTrackForModal.syncedLyrics) return;

            const filename = `${currentTrackForModal.artistName} - ${currentTrackForModal.trackName}`.replace(/[\/:*?"<>|]/g, '_') + '.lrc';
            const blob = new Blob([currentTrackForModal.syncedLyrics], { type: 'text/plain;charset=utf-8' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            closeModal();
        });

    </script>
</body>
</html>