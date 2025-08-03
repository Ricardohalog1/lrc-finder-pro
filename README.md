# 🎵 LRC Finder Pro {#lrc-finder-pro}

**LRC Finder Pro** is a powerful and intuitive web-based utility
designed to streamline the process of finding and downloading
synchronized lyrics (.lrc files) for your entire local music library. By
leveraging the extensive lrclib.net database, this tool automates the
tedious task of searching for lyrics one by one---perfect for music
lovers and karaoke masters alike.

> 💡 *Tip:* This is the perfect spot for the video demo you created!

## 📋 Table of Contents {#table-of-contents}

- [Key Features](#key-features)

- [[Bonus
  Utility-File-Renamer]{.underline}](#bonus-utility-file-renamer)

- [[Tech Stack]{.underline}](#tech-stack)

- [[Project Structure]{.underline}](#project-structure)

- [[Setup and Installation]{.underline}](#setup-and-installation)

- [[Usage Guide]{.underline}](#usage-guide)

- [[License]{.underline}](#license)

- [[Credits and
  Acknowledgements]{.underline}](#credits-and-acknowledgements)

## ✨ Key Features {#key-features}

- 📂 **Efficient Batch Processing**: Select an entire music folder. The
  app intelligently processes your files in batches to ensure smooth
  performance without crashing, even with thousands of songs.

- 📊 **Real-time Progress & Rich Results**: Watch the progress bar fill
  up and see results with clear status indicators: Synced ✅, or Not
  Found ❌.

- ✍️ **Interactive Revision**: If the first match isn\'t perfect, click
  the **\"Revise\"** button to open a modal and choose the correct
  lyrics from a list of alternatives provided by the API.

- 🔍 **Inline Manual Search**: For tracks that weren\'t found, simply
  hover over the \"Not Found\" badge and click **\"Manual Search\"**. A
  modal will appear, allowing you to find lyrics without leaving the
  results page.

- 📄 **Lyrics Preview Modal**: On the standalone Manual Search page,
  click any result to preview the full, timed lyrics in a clean
  interface before downloading.

- 📦 **One-Click ZIP Download**: Download all successfully found .lrc
  files in one neat .zip archive, ready for your music player.

- 🛡️ **Application Protection**: Includes a built-in license expiration
  check and disables developer tools to protect the application\'s
  integrity.

## 🐍 Bonus Utility: File Renamer {#bonus-utility-file-renamer}

Included in the project is a helpful Python script (file_renamer.py) to
clean and standardize your music filenames before processing.

- **Reads Metadata**: Extracts Artist and Title tags from .mp3, .flac,
  and .m4a files.

- **Cleans and Formats**: Removes junk text (like \"Official Video\",
  \"Lyrics\", etc.) and renames the file to a clean Title - Artist.ext
  format.

- **Improves Accuracy**: Using this script first can significantly
  increase the success rate of the batch finder.

## 🛠️ Tech Stack {#tech-stack}

|                   |                                              |
|-------------------|----------------------------------------------|
| **Category**      | **Technology**                               |
| **Frontend**      | HTML5, Tailwind CSS, JavaScript (AJAX/Fetch) |
| **Backend**       | PHP 8+                                       |
| **Web Server**    | Apache (via XAMPP)                           |
| **Lyrics API**    | lrclib (self-hosted Rust backend)            |
| **Database**      | SQLite (from lrclib.net dump)                |
| **Helper Script** | Python 3                                     |

## 📁 Project Structure {#project-structure}

This project follows a clean and organized structure for better
maintainability.

LRC-Finder-Pro/  
│  
├── api/  
│ ├── search.php \# Handles BOTH batch and manual search queries  
│ └── download.php \# Handles .zip creation/download  
│  
├── assets/  
│ ├── css/  
│ │ └── style.css \# Custom stylesheets  
│ └── js/  
│ ├── 1219444658955.js  
│ └── 1219444622223.js  
│  
├── includes/  
│ ├── nav.php \# Reusable navigation component  
│ └── footer.php \# Reusable footer component  
│  
├── search/  
│ └── index.php \# Manual Search page  
│  
├── index.php \# Main batch processing interface (Batch Finder)  
├── file_renamer.py \# Optional utility script to clean filenames  
└── README.md \# You\'re reading this!

## 🚀 Setup and Installation {#setup-and-installation}

To run this project locally, you'll need:

- ✅ **XAMPP** or any Apache+PHP environment

- ✅ **Rust + Cargo** for running the lyric API backend

- ✅ **Python 3** (optional, for the file renamer script)

### 🔧 Step 1: Set up the lrclib Backend {#step-1-set-up-the-lrclib-backend}

1.  Clone the repo:  
    git clone https://github.com/tranxuanthang/lrclib.git  
    cd lrclib

2.  **Download the Database:** Go to [[lrclib.net Database
    Dumps]{.underline}](https://db-dumps.lrclib.net/lrclib-db-dump-20250718T081344Z.sqlite3.gz)
    and download the latest .sqlite3.gz file.

3.  **Place the Database:** Extract and move db.sqlite3 into the lrclib
    folder.

4.  Run the Server:  
    cargo run \--release \-- serve \--database db.sqlite3  
    (The API will be running on http://localhost:3300)

### 🖥️ Step 2: Configure the PHP Web App {#step-2-configure-the-php-web-app}

1.  **Copy the project folder** LRC-Finder-Pro/ into your htdocs folder
    (e.g., C:\xampp\htdocs\\.

2.  **Start Apache** via the XAMPP Control Panel.

3.  **Visit in Browser**:
    [[http://localhost/LRC-Finder-Pro/]{.underline}](http://localhost/lrc-finder-pro/)

### 🐍 Step 3 (Optional but Recommended): Clean Your Filenames {#step-3-optional-but-recommended-clean-your-filenames}

1.  Open file_renamer.py in a text editor.

2.  Change the MUSIC_FOLDER variable to the path of your music library.

3.  Run the script from your terminal: python file_renamer.py

4.  This will rename your files to the optimal Title - Artist.ext format
    for best results.

## 📖 Usage Guide {#usage-guide}

1.  (Recommended) Run the **File Renamer** script on your music folder
    first.

2.  Go to the **Batch Finder** page (index.php).

3.  Click \"Choose Files\" and select your music folder.

4.  Hit \"Scan for Lyrics\" and watch the real-time progress.

5.  Review results:

    - Green \"Synced\"? You\'re good!

    - If a match is wrong, click **\"Revise\"** to pick a better one.

    - Red \"Not Found\"? Hover and click **\"Manual Search\"** to find
      it yourself.

6.  When ready, click **\"Download All found (.zip)\"** to grab your LRC
    files.

## ⚖️ License {#license}

This project is licensed under the **MIT License**.

## ❤️ Credits and Acknowledgements {#credits-and-acknowledgements}

This project is made possible thanks to the amazing work of **Trần Xuân
Thắng** and the entire
[[lrclib]{.underline}](https://github.com/tranxuanthang/lrclib)
community.

- 🌐 Website: [[lrclib.net]{.underline}](https://lrclib.net/)

- 📦 GitHub Repo:
  [[github.com/tranxuanthang/lrclib]{.underline}](https://github.com/tranxuanthang/lrclib)

- 👤 Creator:
  [[tranxuanthang]{.underline}](https://github.com/tranxuanthang)

🎤 *Sing your heart out---with synced lyrics by your side!*
