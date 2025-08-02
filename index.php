<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LRC Finder Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        #results::-webkit-scrollbar, #revision-options::-webkit-scrollbar, #manual-search-results::-webkit-scrollbar { width: 8px; }
        #results::-webkit-scrollbar-track, #revision-options::-webkit-scrollbar-track, #manual-search-results::-webkit-scrollbar-track { background: #1f2937; }
        #results::-webkit-scrollbar-thumb, #revision-options::-webkit-scrollbar-thumb, #manual-search-results::-webkit-scrollbar-thumb { background: #4b5563; border-radius: 4px; }
        #results::-webkit-scrollbar-thumb:hover, #revision-options::-webkit-scrollbar-thumb:hover, #manual-search-results::-webkit-scrollbar-thumb:hover { background: #6b7280; }
        .modal { transition: opacity 0.25s ease; }
    </style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-900 text-white pt-8">

    <?php include 'nav.php'; ?>

    <div class="bg-gray-800 p-8 rounded-2xl shadow-2xl w-full max-w-4xl border border-gray-700 mx-auto">
        <h1 class="text-3xl font-bold text-center mb-2 text-purple-400">LRC Batch Finder</h1>
        <p class="text-center text-gray-400 mb-6">Select your music folder to automatically find synced lyrics.</p>

        <div class="mb-4">
            <label for="music-folder-input" class="block mb-2 text-sm font-medium text-gray-300">Step 1: Select Your Music Folder</label>
            <input type="file" id="music-folder-input" webkitdirectory directory multiple class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-purple-600 file:text-white hover:file:bg-purple-700 cursor-pointer"/>
        </div>

        <button id="process-btn" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded-lg transition duration-300 ease-in-out transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed">
            Step 2: Find Lyrics
        </button>

        <div id="progress-container" class="mt-6 hidden">
            <div class="flex justify-between mb-1">
                <span id="status" class="text-base font-medium text-purple-400">Searching...</span>
                <span id="progress-text" class="text-sm font-medium text-purple-400">0%</span>
            </div>
            <div class="w-full bg-gray-700 rounded-full h-2.5">
                <div id="progress-bar" class="bg-purple-600 h-2.5 rounded-full" style="width: 0%"></div>
            </div>
        </div>

        <div id="results-container" class="mt-4 hidden">
            <h2 id="results-header" class="text-xl font-semibold mb-2">Results:</h2>
            <div id="results" class="bg-gray-900 border border-gray-700 rounded-lg p-4 h-96 overflow-y-auto space-y-2">
            </div>
            <form id="download-form" action="get/download.php" method="post" class="mt-4">
                <input type="hidden" name="lyrics_data" id="lyrics-data-input">
                <button type="submit" id="download-btn" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition duration-300 ease-in-out disabled:opacity-50">
                    Download All Found (.zip)
                </button>
            </form>
        </div>
    </div>
    
    <div id="revision-modal" class="modal fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center p-4 hidden z-50">
        <div class="bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl border border-gray-700">
            <div class="p-6 border-b border-gray-700"><h3 class="text-xl font-bold text-purple-400" id="revision-title">Choose Correct Lyrics</h3></div>
            <div id="revision-options" class="p-6 max-h-[60vh] overflow-y-auto space-y-3"></div>
            <div class="p-4 bg-gray-800/50 border-t border-gray-700 rounded-b-2xl"><button onclick="closeModal('revision-modal')" class="w-full bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg">Cancel</button></div>
        </div>
    </div>

    <div id="manual-search-modal" class="modal fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center p-4 hidden z-50">
        <div class="bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl border border-gray-700 flex flex-col">
            <div class="p-6 border-b border-gray-700">
                <h3 class="text-xl font-bold text-purple-400" id="manual-search-title">Manual Search</h3>
                <div class="mt-4 flex space-x-2">
                    <input type="text" id="manual-search-input" placeholder="Enter correct title and artist..." class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <button id="manual-search-btn" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-lg">Search</button>
                </div>
            </div>
            <div id="manual-search-results" class="p-6 max-h-[50vh] overflow-y-auto space-y-3 flex-grow">
                <p class="text-gray-400 text-center">Enter a query to find lyrics.</p>
            </div>
            <div class="p-4 bg-gray-800/50 border-t border-gray-700 rounded-b-2xl">
                 <button onclick="closeModal('manual-search-modal')" class="w-full bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg">Cancel</button>
            </div>
        </div>
    </div>


    <script>
        const folderInput = document.getElementById('music-folder-input');
        const processBtn = document.getElementById('process-btn');
        const statusDiv = document.getElementById('status');
        const progressContainer = document.getElementById('progress-container');
        const progressBar = document.getElementById('progress-bar');
        const progressText = document.getElementById('progress-text');
        const resultsContainer = document.getElementById('results-container');
        const resultsHeader = document.getElementById('results-header');
        const resultsDiv = document.getElementById('results');
        const downloadBtn = document.getElementById('download-btn');
        const downloadForm = document.getElementById('download-form');

        // Modals
        const revisionModal = document.getElementById('revision-modal');
        const revisionTitle = document.getElementById('revision-title');
        const revisionOptions = document.getElementById('revision-options');
        const manualSearchModal = document.getElementById('manual-search-modal');
        const manualSearchTitle = document.getElementById('manual-search-title');
        const manualSearchInput = document.getElementById('manual-search-input');
        const manualSearchBtn = document.getElementById('manual-search-btn');
        const manualSearchResults = document.getElementById('manual-search-results');
        
        let allSearchResults = {};
        let allFoundLyrics = {}; 

        function formatDuration(seconds) {
            if (isNaN(seconds) || seconds === 0) return "00:00";
            const mins = Math.floor(seconds / 60);
            const secs = Math.round(seconds % 60);
            return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
        }

        processBtn.addEventListener('click', async () => {
            const files = folderInput.files;
            if (files.length === 0) { alert('You need to select a folder first.'); return; }
            processBtn.disabled = true;
            processBtn.textContent = 'Processing...';
            statusDiv.textContent = 'Preparing files...';
            progressContainer.classList.remove('hidden');
            progressBar.style.width = '0%';
            progressText.textContent = '0%';
            resultsContainer.classList.add('hidden');
            resultsDiv.innerHTML = '';
            downloadBtn.disabled = true;
            allSearchResults = {};
            allFoundLyrics = {};
            const musicFiles = Array.from(files)
                .filter(file => /\.(mp3|flac|m4a|ogg|wav)$/i.test(file.name))
                .map(file => ({ fullName: file.name, baseName: file.name.replace(/\.[^/.]+$/, "") }));
            if (musicFiles.length === 0) {
                alert('No music files found in the selected folder.');
                processBtn.disabled = false;
                processBtn.textContent = 'Find Lyrics';
                return;
            }
            const BATCH_SIZE = 50;
            const totalBatches = Math.ceil(musicFiles.length / BATCH_SIZE);
            let processedSongs = 0;
            for (let i = 0; i < musicFiles.length; i += BATCH_SIZE) {
                const batch = musicFiles.slice(i, i + BATCH_SIZE);
                const currentBatchNumber = (i / BATCH_SIZE) + 1;
                statusDiv.textContent = `Searching... (Batch ${currentBatchNumber} of ${totalBatches})`;
                try {
                    const response = await fetch('get/search.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ tracks: batch }) });
                    if (!response.ok) throw new Error(`Server error: ${response.statusText}`);
                    const foundResultsInBatch = await response.json();
                    batch.forEach(file => {
                        const resultData = foundResultsInBatch[file.baseName];
                        const resultItem = document.createElement('div');
                        resultItem.setAttribute('data-filename', file.baseName);
                        
                        if (resultData && resultData.status === 'found') {
                            allSearchResults[file.baseName] = resultData.results;
                            const bestMatch = resultData.results[0];
                            updateResultItem(resultItem, file, bestMatch);
                        } else {
                           resultItem.classList.add('p-3', 'rounded-lg', 'text-sm', 'bg-red-800/50', 'border', 'border-red-700');
                           resultItem.innerHTML = `<div class="flex items-center justify-between">
                               <div>
                                   <p class="font-bold">${file.fullName}</p>
                                   <p class="text-red-400 text-xs">Could not find lyrics automatically.</p>
                               </div>
                               <span class="not-found-badge font-semibold px-2 py-1 bg-red-500 text-white rounded-full text-xs transition-all">✗ Not Found</span>
                           </div>`;
                        }
                        resultsDiv.appendChild(resultItem);
                        processedSongs++;
                    });
                } catch (error) {
                    statusDiv.textContent = `Error in Batch ${currentBatchNumber}: ${error.message}`;
                    processBtn.disabled = false;
                    processBtn.textContent = 'Try Again';
                    return;
                }
                const progressPercentage = Math.round((processedSongs / musicFiles.length) * 100);
                progressBar.style.width = `${progressPercentage}%`;
                progressText.textContent = `${progressPercentage}%`;
                resultsDiv.scrollTop = resultsDiv.scrollHeight;
            }
            updateSummaryAndDownloadButton();
            statusDiv.textContent = 'Process Complete!';
            resultsContainer.classList.remove('hidden');
            processBtn.disabled = false;
            processBtn.textContent = 'Search Again';
        });
        
        function updateResultItem(itemElement, file, matchData) {
            const { artist, title, album, duration, lyrics, status } = matchData;
            const isSynced = status === 'synced';
            const statusColor = isSynced ? 'green' : 'yellow';
            const statusTextColor = isSynced ? 'white' : 'gray-900';
            const statusText = isSynced ? '✓ Synced' : '✓ Plain';
            
            itemElement.className = `p-3 rounded-lg text-sm bg-${statusColor}-800/50 border border-${statusColor}-700`;
            itemElement.innerHTML = `
                <div class="flex items-center justify-between">
                    <div class="flex-grow pr-4 min-w-0">
                        <p class="font-bold text-white truncate" title="${file.fullName}">${file.fullName}</p>
                        <p class="text-gray-300 truncate">${title} - ${artist}</p>
                        <p class="text-xs text-gray-400 truncate">${album || 'No album info'}</p>
                    </div>
                    <div class="flex-shrink-0 flex items-center space-x-3">
                        <span class="font-mono text-lg text-purple-300">${formatDuration(duration)}</span>
                        <span class="font-semibold px-2 py-1 bg-${statusColor}-500 text-${statusTextColor} rounded-full text-xs">${statusText}</span>
                        <button onclick="openRevisionModal('${file.baseName}')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded-lg text-xs">Revise</button>
                    </div>
                </div>`;
            
            if (isSynced) {
                allFoundLyrics[file.baseName] = lyrics;
            } else {
                if (allFoundLyrics[file.baseName]) { delete allFoundLyrics[file.baseName]; }
            }
        }
        
        function updateSummaryAndDownloadButton() {
            let syncedCount = 0, plainCount = 0;
            const totalFiles = resultsDiv.children.length;

            document.querySelectorAll('[data-filename]').forEach(item => {
                const badge = item.querySelector('.font-semibold');
                if (badge && badge.textContent.includes('Synced')) syncedCount++;
                else if (badge && badge.textContent.includes('Plain')) plainCount++;
            });
            const notFoundCount = totalFiles - (syncedCount + plainCount);
            resultsHeader.textContent = `Results: ${syncedCount} synced, ${plainCount} plain, ${notFoundCount} not found.`;
            document.getElementById('lyrics-data-input').value = JSON.stringify(allFoundLyrics);
            downloadBtn.disabled = syncedCount === 0;
        }

        // --- MODAL CONTROLS ---
        function openRevisionModal(baseName) {
            const options = allSearchResults[baseName];
            if (!options) return;
            revisionTitle.textContent = `Choose lyrics for: ${baseName}`;
            revisionOptions.innerHTML = ''; 
            options.forEach((option, index) => {
                const optionDiv = createOptionDiv(option);
                optionDiv.addEventListener('click', () => selectRevision(baseName, index));
                revisionOptions.appendChild(optionDiv);
            });
            revisionModal.classList.remove('hidden');
        }

        function selectRevision(baseName, optionIndex) {
            const selectedMatch = allSearchResults[baseName][optionIndex];
            const resultItem = resultsDiv.querySelector(`[data-filename="${baseName}"]`);
            const file = { fullName: `${baseName}.mp3`, baseName: baseName }; 
            updateResultItem(resultItem, file, selectedMatch);
            updateSummaryAndDownloadButton();
            closeModal('revision-modal');
        }

        function closeModal(modalId) { document.getElementById(modalId).classList.add('hidden'); }

        // --- **NEW** MANUAL SEARCH LOGIC ---

        // Event delegation for hover and click on "Not Found" badges
        resultsDiv.addEventListener('mouseover', (e) => {
            if (e.target.classList.contains('not-found-badge')) {
                e.target.textContent = 'Manual Search';
                e.target.classList.remove('bg-red-500');
                e.target.classList.add('bg-blue-600', 'cursor-pointer');
            }
        });
        resultsDiv.addEventListener('mouseout', (e) => {
            if (e.target.classList.contains('not-found-badge')) {
                e.target.textContent = '✗ Not Found';
                e.target.classList.add('bg-red-500');
                e.target.classList.remove('bg-blue-600', 'cursor-pointer');
            }
        });
        resultsDiv.addEventListener('click', (e) => {
            if (e.target.classList.contains('not-found-badge')) {
                const resultItem = e.target.closest('[data-filename]');
                const baseName = resultItem.dataset.filename;
                openManualSearchModal(baseName);
            }
        });

        function openManualSearchModal(baseName) {
            manualSearchTitle.textContent = `Manual Search for: ${baseName}`;
            manualSearchModal.dataset.editingFile = baseName; // Store filename
            manualSearchInput.value = baseName.replace(/ - /g, ' '); // Pre-fill with filename
            manualSearchResults.innerHTML = '<p class="text-gray-400 text-center">Enter a query to find lyrics.</p>';
            manualSearchModal.classList.remove('hidden');
        }
        
        manualSearchBtn.addEventListener('click', performManualSearch);
        manualSearchInput.addEventListener('keyup', (e) => {
            if (e.key === 'Enter') performManualSearch();
        });

        async function performManualSearch() {
            const query = manualSearchInput.value.trim();
            if (!query) return;

            manualSearchResults.innerHTML = '<p class="text-purple-400 text-center">Searching...</p>';

            try {
                const response = await fetch(`get/search.php?q=${encodeURIComponent(query)}`);
                const results = await response.json();
                manualSearchResults.innerHTML = ''; // Clear "Searching..."

                if (results && results.length > 0) {
                    // **IMPORTANT**: The raw result from the GET request needs key mapping
                    const standardizedResults = results.map(res => ({
                        artist: res.artistName ?? 'Unknown Artist',
                        title: res.trackName ?? 'Unknown Title',
                        album: res.albumName ?? 'Unknown Album',
                        duration: res.duration ?? 0,
                        lyrics: res.syncedLyrics || res.plainLyrics,
                        status: res.syncedLyrics ? 'synced' : 'plain'
                    }));

                    standardizedResults.forEach(result => {
                        const optionDiv = createOptionDiv(result);
                        optionDiv.addEventListener('click', () => selectManualResult(result));
                        manualSearchResults.appendChild(optionDiv);
                    });
                } else {
                    manualSearchResults.innerHTML = '<p class="text-red-400 text-center">No results found for that query.</p>';
                }
            } catch (error) {
                manualSearchResults.innerHTML = `<p class="text-red-400 text-center">An error occurred: ${error.message}</p>`;
            }
        }
        
        function selectManualResult(selectedMatch) {
            const baseName = manualSearchModal.dataset.editingFile;
            const resultItem = resultsDiv.querySelector(`[data-filename="${baseName}"]`);
            const file = { fullName: `${baseName}.mp3`, baseName: baseName };

            // Add the selected result to our main collection so "Revise" works later
            if (!allSearchResults[baseName]) allSearchResults[baseName] = [];
            allSearchResults[baseName].unshift(selectedMatch); // Add to beginning

            updateResultItem(resultItem, file, selectedMatch);
            updateSummaryAndDownloadButton();
            closeModal('manual-search-modal');
        }

        // Helper function to create the display div for any option
        function createOptionDiv(optionData) {
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
        }

    </script>
</body>
</html>