# **🎵 LRC Finder Pro**

**LRC Finder Pro** is a powerful and intuitive web-based utility designed to streamline the process of finding and downloading synchronized lyrics (.lrc files) for your entire local music library. By leveraging the extensive lrclib.net database, this tool automates the tedious task of searching for lyrics one by one—perfect for music lovers and karaoke masters alike.

💡 *Tip:* This is the perfect spot for the video demo you created\!

## **📋 Table of Contents**

* [Key Features](#-key-features)  
* [Bonus Utility](#-bonus-utility-file-renamer)  
* [Tech Stack](#%EF%B8%8F-tech-stack)  
* [Project Structure](#-project-structure)  
* [Setup and Installation](#-setup-and-installation)  
* [Usage Guide](#-usage-guide)  
* [License](#%EF%B8%8F-license)  
* [Credits and Acknowledgements](#%EF%B8%8F-credits-and-acknowledgements)

## **✨ Key Features**

* 📂 **Efficient Batch Processing**: Select an entire music folder. The app intelligently processes your files in batches to ensure smooth performance without crashing, even with thousands of songs.  
* 📊 **Real-time Progress & Rich Results**: Watch the progress bar fill up and see results with clear status indicators: Synced ✅, or Not Found ❌.  
* ✍️ **Interactive Revision**: If the first match isn't perfect, click the **"Revise"** button to open a modal and choose the correct lyrics from a list of alternatives provided by the API.  
* 🔍 **Inline Manual Search**: For tracks that weren't found, simply hover over the "Not Found" badge and click **"Manual Search"**. A modal will appear, allowing you to find lyrics without leaving the results page.  
* 📄 **Lyrics Preview Modal**: On the standalone Manual Search page, click any result to preview the full, timed lyrics in a clean interface before downloading.  
* 📦 **One-Click ZIP Download**: Download all successfully found .lrc files in one neat .zip archive, ready for your music player.  
* 🛡️ **Application Protection**: Includes a built-in license expiration check and disables developer tools to protect the application's integrity.

## **🐍 Bonus Utility: File Renamer**

Included in the project is a helpful Python script (file\_renamer.py) to clean and standardize your music filenames before processing.

* **Reads Metadata**: Extracts Artist and Title tags from .mp3, .flac, and .m4a files.  
* **Cleans and Formats**: Removes junk text (like "Official Video", "Lyrics", etc.) and renames the file to a clean Title \- Artist.ext format.  
* **Improves Accuracy**: Using this script first can significantly increase the success rate of the batch finder.

## **🛠️ Tech Stack**

| Category | Technology |
| :---- | :---- |
| **Frontend** | HTML5, Tailwind CSS, JavaScript (AJAX/Fetch) |
| **Backend** | PHP 8+ |
| **Web Server** | Apache (via XAMPP) |
| **Lyrics API** | lrclib (self-hosted Rust backend) |
| **Database** | SQLite (from lrclib.net dump) |
| **Helper Script** | Python 3 |

## **📁 Project Structure**

This project follows a clean and organized structure for better maintainability.

LRC-Finder-Pro/  
│  
├── api/  
│   ├── search.php  
│   └── download.php  
│  
├── assets/  
│   ├── css/  
│   │   └── style.css  
│   └── js/  
│       ├── 1219444658955.js)  
│       └── 1219444622223.js)  
│  
├── includes/  
│   ├── nav.php  
│   └── footer.php  
│  
├── search/  
│   └── index.php  
│  
├── index.php  
├── file\_renamer.py  
└── README.md

## **🚀 Setup and Installation**

To run this project locally, you’ll need:

* ✅ **XAMPP** or any Apache+PHP environment  
* ✅ **Rust \+ Cargo** for running the lyric API backend  
* ✅ **Python 3** (optional, for the file renamer script)

### **🔧 Step 1: Set up the lrclib Backend**

1. Clone the repo:  
   git clone https://github.com/tranxuanthang/lrclib.git  
   cd lrclib  
2. **Download the Database:** Go to [lrclib.net Database Dumps](https://db-dumps.lrclib.net/lrclib-db-dump-20250718T081344Z.sqlite3.gz) and download the latest .sqlite3.gz file.  
3. **Place the Database:** Extract and move db.sqlite3 into the lrclib folder.  
4. Run the Server:  
   cargo run \--release \-- serve \--database db.sqlite3  
   (The API will be running on [http://localhost:3300](http://localhost:3300/api/search?q=hello%20adele)

### **🖥️ Step 2: Configure the PHP Web App**

1. **Copy the project folder** LRC-Finder-Pro/ into your htdocs folder (e.g., C:\\xampp\\htdocs\\).  
2. **Start Apache** via the XAMPP Control Panel.  
3. **Visit in Browser**: [http://localhost/LRC-Finder-Pro/](http://localhost/lrc-finder-pro/)

### **🐍 Step 3 (Optional but Recommended): Clean Your Filenames**

1. Open file\_renamer.py in a text editor.  
2. Change the MUSIC\_FOLDER variable to the path of your music library.  
3. Run the script from your terminal: python file\_renamer.py  
4. This will rename your files to the optimal Title \- Artist.ext format for best results.

## **📖 Usage Guide**

1. (Recommended) Run the **File Renamer** script on your music folder first.  
2. Go to the **Batch Finder** page (index.php).  
3. Click "Choose Files" and select your music folder.  
4. Hit "Scan for Lyrics" and watch the real-time progress.  
5. Review results:  
   * Green "Synced"? You're good\!  
   * If a match is wrong, click **"Revise"** to pick a better one.  
   * Red "Not Found"? Hover and click **"Manual Search"** to find it yourself.  
6. When ready, click **"Download All found (.zip)"** to grab your LRC files.

## **⚖️ License**

This project is licensed under the [**MIT License**](main?tab=MIT-1-ov-file).

## **❤️ Credits and Acknowledgements**

This project is made possible thanks to the amazing work of **Trần Xuân Thắng** and the entire [lrclib](https://github.com/tranxuanthang/lrclib) community.

* 🌐 Website: [lrclib.net](https://lrclib.net/)  
* 📦 GitHub Repo: [github.com/tranxuanthang/lrclib](https://github.com/tranxuanthang/lrclib)  
* 👤 Creator: [tranxuanthang](https://github.com/tranxuanthang)

🎤 *Sing your heart out—with synced lyrics by your side\!*
