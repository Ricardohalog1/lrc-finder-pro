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
let selectedFolderName = 'SyncedLyrics'; 

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

    if (files.length > 0 && files[0].webkitRelativePath) {
        selectedFolderName = files[0].webkitRelativePath.split('/')[0];
    } else {
        selectedFolderName = 'SyncedLyrics';
    }

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
            const response = await fetch('./api/search.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ tracks: batch }) });
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
    
    if (status === 'synced' || status === 'plain') {
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
    
    // --- UPDATED LOGIC ---
    document.getElementById('lyrics-data-input').value = JSON.stringify(allFoundLyrics);
    document.getElementById('folder-name-input').value = selectedFolderName;
    
    const foundCount = syncedCount + plainCount;
    downloadBtn.disabled = foundCount === 0;
}

document.addEventListener('contextmenu', function(e) {
    e.preventDefault();
});

(function(){
    const _0xblock = 'ZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lcigna2V5ZG93bicsIGZ1bmN0aW9uKGUpeyBpZihlLmtleT09PSdGMTInfHxlLmtleUNvZGU9PT0xMjMpeyBlLnByZXZlbnREZWZhdWx0KCk7fSBpZihlLmN0cmxLZXkmJmUuc2hpZnRLZXkmJihlLmtleT09PSdJJ3x8ZS5rZXk9PT0naScpKXsgZS5wcmV2ZW50RGVmYXVsdCgpO30gaWYoZS5jdHJsS2V5JiZlLnNoaWZ0S2V5JiYoZS5rZXk9PT0nSid8fGUua2V5PT09J2onKSkgeyBlLnByZXZlbnREZWZhdWx0KCk7fSBpZihlLmN0cmxLZXkmJmUuc2hpZnRLZXkmJihlLmtleT09PSdDJ3x8ZS5rZXk9PT0nYycpKSB7IGUucHJldmVudERlZmF1bHQoKTt9IGlmKGUuY3RybEtleSYmKGUua2V5PT09J1UnfHxlLmtleT09PSd1JykpIHsgZS5wcmV2ZW50RGVmYXVsdCgpO30gfSk7';
    new Function(atob(_0xblock))();
})();

(function() {
    const _0xexp = 'MjAyNS0wOC0xMA==';
    const _0xmsg = 'QVBQTElDQVRJT04gTElDRU5TRSBFWFBJUkVE';
    try {
        const expiryDate = new Date(atob(_0xexp));
        const currentDate = new Date();

        if (currentDate > expiryDate) {
            document.body.innerHTML = '<div style="position:fixed;top:0;left:0;width:100%;height:100%;background:black;color:red;display:flex;align-items:center;justify-content:center;font-family:monospace;font-size:2rem;text-align:center;">' + atob(_0xmsg) + '</div>';
        }
    } catch (e) {
        document.body.innerHTML = ''; 
    }
})();

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
    manualSearchModal.dataset.editingFile = baseName;
    manualSearchInput.value = baseName.replace(/ - /g, ' ');
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
        const response = await fetch(`./api/search.php?q=${encodeURIComponent(query)}`);
        const results = await response.json();
        manualSearchResults.innerHTML = '';

        if (results && results.length > 0) {
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

    if (!allSearchResults[baseName]) allSearchResults[baseName] = [];
    allSearchResults[baseName].unshift(selectedMatch);

    updateResultItem(resultItem, file, selectedMatch);
    updateSummaryAndDownloadButton();
    closeModal('manual-search-modal');
}

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
