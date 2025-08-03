<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LRC Finder Pro</title>
    <script src="assets/js/tailwindcss.js"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-900 text-white min-h-screen flex flex-col">

    <?php include 'includes/nav.php'; ?>

     <main class="flex-grow flex items-center justify-center">

    <div class="bg-gray-800 p-8 rounded-2xl shadow-2xl w-full max-w-4xl border border-gray-700">
        <h1 class="text-3xl font-bold text-center ...">LRC Batch Finder</h1>
        <p class="text-center text-gray-400 mb-6">Select your music folder to automatically find synced lyrics.</p>

        <div class="mb-4">
            <label for="music-folder-input" class="block mb-2 text-sm font-medium text-gray-300">Select Your Music Folder</label>
            <input type="file" id="music-folder-input" webkitdirectory directory multiple class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-purple-600 file:text-white hover:file:bg-purple-700 cursor-pointer"/>
        </div>

        <button id="process-btn" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded-lg transition duration-300 ease-in-out transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed">
            Scan for Lyrics
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
            <form id="download-form" action="api/download.php" method="post" class="mt-4">
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
    </main>
    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/1219444658955.js"></script>
</body>
</html>