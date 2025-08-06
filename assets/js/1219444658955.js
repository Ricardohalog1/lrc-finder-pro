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

const revisionSearchInput = document.getElementById('revision-search-input');
const revisionSearchBtn = document.getElementById('revision-search-btn');
const revisionSearchResults = document.getElementById('revision-search-results');

const audioPlayer = document.getElementById('audio-player');
let musicFileObjects = []; 
let processedMusicFiles = []; 
let currentlyPlaying = {
    element: null,
    baseName: null,
    lrcData: [],
    audioURL: null,
    lineIndex: -1
};

let trueDurations = new Map();

let allSearchResults = {};
let allFoundLyrics = {};
let selectedFolderName = 'SyncedLyrics'; 

function escapeJsString(str) {
    if (!str) return '';
    return str.replace(/\\/g, '\\\\').replace(/'/g, "\\'");
}

function escapeHTML(str) {
    if (!str) return '';
    const p = document.createElement('p');
    p.textContent = str;
    return p.innerHTML;
}

function formatDuration(seconds) {
    if (isNaN(seconds) || seconds === 0) return "00:00";
    const mins = Math.floor(seconds / 60);
    const secs = Math.round(seconds % 60);
    return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
}

processBtn.addEventListener('click', async () => {
    const files = folderInput.files;
    if (files.length === 0) { alert('You need to select a folder first.'); return; }
    
    if (!audioPlayer.paused) {
        audioPlayer.pause();
        if (currentlyPlaying.audioURL) URL.revokeObjectURL(currentlyPlaying.audioURL);
    }
    currentlyPlaying = { element: null, baseName: null, lrcData: [], audioURL: null, lineIndex: -1 };
    
    trueDurations.clear();
    allSearchResults = {};
    allFoundLyrics = {};

    processBtn.disabled = true;
    processBtn.textContent = 'Processing...';
    statusDiv.textContent = 'Preparing files...';
    progressContainer.classList.remove('hidden');
    progressBar.style.width = '0%';
    progressText.textContent = '0%';
    resultsContainer.classList.add('hidden');
    resultsDiv.innerHTML = '';
    downloadBtn.disabled = true;

    musicFileObjects = Array.from(files).filter(file => /\.(mp3|flac|m4a|ogg|wav)$/i.test(file.name));
    const musicFiles = musicFileObjects.map(file => ({ fullName: file.name, baseName: file.name.replace(/\.[^/.]+$/, "") }));
    
    processedMusicFiles = musicFiles;

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
                   allSearchResults[file.baseName] = [];
                   resultItem.classList.add('p-3', 'rounded-lg', 'text-sm', 'bg-red-800/50', 'border', 'border-red-700');
                   resultItem.innerHTML = `<div class="flex items-center justify-between">
                       <div>
                           <p class="font-bold">${escapeHTML(file.fullName)}</p>
                           <p class="text-red-400 text-xs">Could not find lyrics automatically.</p>
                       </div>
                       <button class="not-found-badge font-semibold px-2 py-1 bg-red-500 text-white rounded-full text-xs transition-all">✗ Not Found</button>
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
    const reviseButtonText = 'Re-sync';
    
    const displayDuration = duration;
    
    const safeBaseNameForJs = escapeJsString(file.baseName);
    const safeFullNameForHTML = escapeHTML(file.fullName);
    const safeTitleForHTML = escapeHTML(title);
    const safeArtistForHTML = escapeHTML(artist);
    const safeAlbumForHTML = escapeHTML(album) || 'No album info';

    itemElement.className = `p-3 rounded-lg text-sm bg-gray-800/50 border border-gray-700 transition-all duration-300`;
    itemElement.innerHTML = `
        <div class="flex items-center justify-between">
            <div class="flex-grow pr-4 min-w-0 cursor-pointer" onclick="togglePlayback(this.closest('[data-filename]'), '${safeBaseNameForJs}', ${isSynced})">
                <p class="font-bold text-white truncate" title="${safeFullNameForHTML}">${safeFullNameForHTML}</p>
                <p class="text-gray-300 truncate">${safeTitleForHTML} - ${safeArtistForHTML}</p>
                <p class="text-xs text-gray-400 truncate">${safeAlbumForHTML}</p>
            </div>
            <div class="flex-shrink-0 flex items-center space-x-3">
                <span class="font-mono text-lg text-purple-300">${formatDuration(displayDuration)}</span>
                <span class="status-badge font-semibold px-2 py-1 bg-${statusColor}-500 text-${statusTextColor} rounded-full text-xs">${statusText}</span>
                <button onclick="openRevisionModal('${safeBaseNameForJs}')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded-lg text-xs">${reviseButtonText}</button>
            </div>
        </div>
        <div class="karaoke-player mt-3 hidden">
            <div class="karaoke-lyrics-container h-16 flex flex-col items-center justify-center mb-2">
                <p class="karaoke-lyrics-current text-center text-xl">
                    <span class="lyrics-text"></span>
                    <span class="lyrics-fill"></span>
                </p>
                <p class="karaoke-lyrics-next text-center text-md font-semibold text-gray-500 transition-opacity duration-300 mt-1"></p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="time-current text-xs font-mono w-10 text-right">00:00</span>
                <div class="w-full bg-gray-700 rounded-full h-1.5 progress-bar-wrapper">
                    <div class="progress-bar bg-purple-600 h-1.5 rounded-full" style="width: 0%"></div>
                </div>
                <span class="time-duration text-xs font-mono w-10 text-left">${formatDuration(displayDuration)}</span>
            </div>
        </div>`;
    
    if (status === 'synced' || status === 'plain') {
        allFoundLyrics[file.baseName] = lyrics;
    } else {
        if (allFoundLyrics[file.baseName]) { delete allFoundLyrics[file.baseName]; }
    }
}

// BINAGO: Mas accurate na pag-parse para sa line-level endTime
function parseLRC(lrcContent) {
    if (!lrcContent) return [];
    const lines = lrcContent.split('\n');
    let timings = [];
    const lineTimeRegex = /\[(\d{2}):(\d{2})\.(\d{2,3})\]/;
    
    for (const line of lines) {
        const lineMatch = line.match(lineTimeRegex);
        if (!lineMatch) continue;

        const minutes = parseInt(lineMatch[1], 10);
        const seconds = parseInt(lineMatch[2], 10);
        const milliseconds = parseInt(lineMatch[3].padEnd(3, '0'), 10);
        const time = minutes * 60 + seconds + milliseconds / 1000;
        
        // Simpleng pagkuha ng text, tinatanggal ang word timings
        const plainText = line.replace(lineTimeRegex, '').replace(/<\d{2}:\d{2}\.\d{2,3}>/g, '').replace(/\s+/g, ' ').trim();

        if(plainText) {
            timings.push({ time, text: plainText });
        }
    }
    
    timings.sort((a, b) => a.time - b.time);

    // I-calculate ang endTime para sa bawat linya
    timings = timings.map((line, i) => {
        const nextLine = timings[i + 1];
        return {
            ...line,
            endTime: nextLine ? nextLine.time : line.time + 5, // Default 5 seconds para sa huling linya
        };
    });

    return timings;
}


function resetPlayingUI() {
    if (currentlyPlaying.element) {
        currentlyPlaying.element.querySelector('.karaoke-player').classList.add('hidden');
        currentlyPlaying.element.classList.remove('border-purple-500', 'bg-gray-700/50');
        currentlyPlaying.element.classList.add('border-gray-700', 'bg-gray-800/50');
    }
}

function togglePlayback(element, baseName, isSynced) {
    if (!element) return;
    if (currentlyPlaying.baseName === baseName) {
        if (audioPlayer.paused) {
            audioPlayer.play();
        } else {
            audioPlayer.pause();
        }
        return;
    }

    if (!audioPlayer.paused) {
        audioPlayer.pause();
        if (currentlyPlaying.audioURL) URL.revokeObjectURL(currentlyPlaying.audioURL);
    }
    
    const fileObject = musicFileObjects.find(f => f.name.startsWith(baseName + '.'));
    if (!fileObject) {
        alert('Could not find the music file to play.');
        return;
    }
    
    const onMetadataLoaded = () => {
        const realDuration = audioPlayer.duration;
        trueDurations.set(baseName, realDuration);
        const playerDurationEl = element.querySelector('.time-duration');
        if (playerDurationEl) playerDurationEl.textContent = formatDuration(realDuration);
        audioPlayer.removeEventListener('loadedmetadata', onMetadataLoaded);
    };
    
    audioPlayer.addEventListener('loadedmetadata', onMetadataLoaded);

    const audioURL = URL.createObjectURL(fileObject);
    audioPlayer.src = audioURL;
    
    const lyrics = allFoundLyrics[baseName] || '';
    
    resetPlayingUI();

    currentlyPlaying = {
        element: element,
        baseName: baseName,
        lrcData: isSynced ? parseLRC(lyrics) : [],
        audioURL: audioURL,
        lineIndex: -1
    };
    
    element.querySelector('.karaoke-player').classList.remove('hidden');
    element.classList.add('border-purple-500', 'bg-gray-700/50');
    element.classList.remove('border-gray-700', 'bg-gray-800/50');

    audioPlayer.play();
}

// BINAGO: Ito na ang pinaka-smooth na animation logic gamit ang CSS Animation
audioPlayer.addEventListener('timeupdate', () => {
    if (!currentlyPlaying.element || audioPlayer.paused || !audioPlayer.duration) return;

    const currentTime = audioPlayer.currentTime;
    const duration = audioPlayer.duration;
    const progressEl = currentlyPlaying.element.querySelector('.progress-bar');
    const timeCurrentEl = currentlyPlaying.element.querySelector('.time-current');

    if (progressEl) progressEl.style.width = `${(currentTime / duration) * 100}%`;
    if (timeCurrentEl) timeCurrentEl.textContent = formatDuration(currentTime);

    const currentLineFillEl = currentlyPlaying.element.querySelector('.lyrics-fill');
    const currentLineTextEl = currentlyPlaying.element.querySelector('.lyrics-text');
    const nextLineEl = currentlyPlaying.element.querySelector('.karaoke-lyrics-next');

    if (currentLineFillEl && nextLineEl && currentlyPlaying.lrcData.length > 0) {
        let newIndex = currentlyPlaying.lrcData.findIndex(line => currentTime >= line.time && currentTime < line.endTime);
        
        if (newIndex !== currentlyPlaying.lineIndex) {
            currentlyPlaying.lineIndex = newIndex;

            const currentLineData = currentlyPlaying.lrcData[newIndex];
            const nextLineData = currentlyPlaying.lrcData[newIndex + 1];

            // I-reset ang animation
            currentLineFillEl.classList.remove('animate-fill');
            // Force reflow para ma-restart ang animation
            void currentLineFillEl.offsetWidth;

            if (currentLineData) {
                const lineDuration = currentLineData.endTime - currentLineData.time;
                const timeIntoLine = currentTime - currentLineData.time;

                currentLineTextEl.textContent = currentLineData.text;
                currentLineFillEl.textContent = currentLineData.text;
                nextLineEl.textContent = nextLineData ? nextLineData.text : '';

                // I-set ang animation properties at i-trigger ito
                currentLineFillEl.style.animationDuration = `${lineDuration}s`;
                currentLineFillEl.style.animationDelay = `-${timeIntoLine}s`;
                currentLineFillEl.classList.add('animate-fill');
            } else {
                // Kung walang current line (nasa intro/outro), i-clear
                currentLineTextEl.textContent = '';
                currentLineFillEl.textContent = '';
                nextLineEl.textContent = currentlyPlaying.lrcData[0]?.text || '';
            }
        }
    }
});


audioPlayer.addEventListener('ended', () => {
    resetPlayingUI();
    if(currentlyPlaying.audioURL) URL.revokeObjectURL(currentlyPlaying.audioURL);
    currentlyPlaying = { element: null, baseName: null, lrcData: [], audioURL: null, lineIndex: -1 };
});

function updateSummaryAndDownloadButton() {
    let syncedCount = 0, plainCount = 0;
    const totalFiles = resultsDiv.children.length;

    document.querySelectorAll('[data-filename]').forEach(item => {
        const badge = item.querySelector('.status-badge');
        if (badge && badge.textContent.includes('Synced')) syncedCount++;
        else if (badge && badge.textContent.includes('Plain')) plainCount++;
    });
    const notFoundCount = totalFiles - (syncedCount + plainCount);
    resultsHeader.textContent = `Results: ${syncedCount} synced, ${plainCount} plain, ${notFoundCount} not found.`;
    
    document.getElementById('lyrics-data-input').value = JSON.stringify(allFoundLyrics);
    document.getElementById('folder-name-input').value = selectedFolderName;
    
    const foundCount = syncedCount + plainCount;
    downloadBtn.disabled = foundCount === 0;
}

document.addEventListener('contextmenu', function(e) {
    e.preventDefault();
});

function openRevisionModal(baseName) {
    const options = allSearchResults[baseName];
    if (!options) return;
    revisionTitle.textContent = `Choose lyrics for: ${baseName}`;
    revisionModal.dataset.editingFile = baseName;
    revisionOptions.innerHTML = ''; 
    options.forEach((option) => {
        const optionDiv = createOptionDiv(option);
        optionDiv.addEventListener('click', () => selectRevision(baseName, option));
        revisionOptions.appendChild(optionDiv);
    });

    revisionOptions.classList.remove('hidden');
    revisionSearchResults.classList.add('hidden');
    revisionSearchInput.value = '';
    revisionSearchResults.innerHTML = '';

    revisionModal.classList.remove('hidden');
}

function selectRevision(baseName, selectedMatch) { 
    const wasPlaying = currentlyPlaying.baseName === baseName;
    if (wasPlaying) {
        audioPlayer.pause();
        resetPlayingUI();
        if (currentlyPlaying.audioURL) {
            URL.revokeObjectURL(currentlyPlaying.audioURL);
        }
    }
    
    const safeBaseNameForSelector = baseName.replace(/\\/g, '\\\\').replace(/"/g, '\\"');
    const resultItem = resultsDiv.querySelector(`[data-filename="${safeBaseNameForSelector}"]`);
    
    const originalFile = processedMusicFiles.find(f => f.baseName === baseName) || { fullName: `${baseName}.mp3`, baseName: baseName };
    
    const existingIndex = allSearchResults[baseName].findIndex(r => r.lyrics === selectedMatch.lyrics && r.title === selectedMatch.title);
    if (existingIndex === -1) {
        allSearchResults[baseName].unshift(selectedMatch);
    }

    updateResultItem(resultItem, originalFile, selectedMatch);
    updateSummaryAndDownloadButton();
    closeModal('revision-modal');

    if (wasPlaying) {
        currentlyPlaying = { element: null, baseName: null, lrcData: [], audioURL: null, lineIndex: -1 };
        const updatedResultItem = resultsDiv.querySelector(`[data-filename="${safeBaseNameForSelector}"]`);
        togglePlayback(updatedResultItem, baseName, selectedMatch.status === 'synced');
    }
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
    const safeBaseNameForSelector = baseName.replace(/\\/g, '\\\\').replace(/"/g, '\\"');
    const resultItem = resultsDiv.querySelector(`[data-filename="${safeBaseNameForSelector}"]`);
    
    const originalFile = processedMusicFiles.find(f => f.baseName === baseName) || { fullName: `${baseName}.mp3`, baseName: baseName };

    if (!Array.isArray(allSearchResults[baseName])) {
        allSearchResults[baseName] = [];
    }
    allSearchResults[baseName].unshift(selectedMatch);

    updateResultItem(resultItem, originalFile, selectedMatch);
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
                <p class="font-bold text-white truncate">${escapeHTML(title)}</p>
                <p class="text-sm text-gray-300 truncate">${escapeHTML(artist)}</p>
                <p class="text-xs text-gray-400 truncate">${escapeHTML(album) || 'No album info'}</p>
            </div>
            <div class="flex-shrink-0 flex items-center space-x-3">
                 <span class="font-mono text-lg text-purple-300">${formatDuration(duration)}</span>
                 <span class="font-semibold px-2 py-1 bg-${statusColor}-500 text-${statusTextColor} rounded-full text-xs">${isSynced ? 'Synced' : 'Plain'}</span>
            </div>
        </div>`;
    return optionDiv;
}

revisionSearchBtn.addEventListener('click', performRevisionSearch);
revisionSearchInput.addEventListener('keyup', (e) => {
    if (e.key === 'Enter') performRevisionSearch();
});

async function performRevisionSearch() {
    const query = revisionSearchInput.value.trim();
    if (!query) return;

    revisionOptions.classList.add('hidden');
    revisionSearchResults.classList.remove('hidden');
    revisionSearchResults.innerHTML = '<p class="text-purple-400 text-center">Searching...</p>';
    const baseName = revisionModal.dataset.editingFile;

    try {
        const response = await fetch(`./api/search.php?q=${encodeURIComponent(query)}`);
        const results = await response.json();
        revisionSearchResults.innerHTML = '';
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
                optionDiv.addEventListener('click', () => selectRevision(baseName, result));
                revisionSearchResults.appendChild(optionDiv);
            });
        } else {
            revisionSearchResults.innerHTML = '<p class="text-red-400 text-center">No results found for that query.</p>';
        }
    } catch (error) {
        revisionSearchResults.innerHTML = `<p class="text-red-400 text-center">An error occurred: ${error.message}</p>`;
    }
}
