# 🎵 LRC Finder Pro

**LRC Finder Pro** is a powerful and intuitive web-based utility designed to streamline the process of finding and downloading synchronized lyrics (.lrc files) for your entire local music library. By leveraging a hybrid search system, this tool automates the tedious task of searching for lyrics one by one—perfect for music lovers and karaoke masters alike.

💡 *Tip: This is the perfect spot for a video demo of the application in action!*

---

## ✨ Key Features

* 📂 **Efficient Batch Processing**: Select an entire music folder. The app intelligently processes your files in batches to ensure smooth performance without crashing, even with thousands of songs.
* ⚡ **Hybrid Search Architecture**: Prioritizes a high-speed **offline (local) server** for initial searches. If no results are found or the local server is unavailable, it seamlessly falls back to the **online `lrclib.net` API** for maximum reliability.
* ✍️ **Interactive Revision**: If the first match isn't perfect, click the **"Revise"** button to open a modal and choose the correct lyrics from a list of alternatives provided by the API.
* 🔍 **Inline Manual Search**: For tracks that weren't found, simply hover over the "Not Found" badge and click **"Manual Search"**. A modal will appear, allowing you to find lyrics without leaving the results page.
* 📦 **One-Click ZIP Download**: Download all successfully found .lrc files in one neat .zip archive, conveniently named after your source folder.

## 🚀 Future Development: Native Android App

To provide a fully integrated, standalone experience, the next major goal for this project is to evolve it into a **native Android application using Android Studio**.

This architectural shift will involve:
* **Rebuilding the UI** with native Android components (XML or Jetpack Compose).
* **Rewriting the backend logic** (currently in PHP) in **Kotlin** or **Java**. This includes making direct API calls to `lrclib.net` from within the app.
* **Integrating all utilities**, including the File Renamer and a future Duplicate Song Remover, directly into the app using native Android APIs for file system access.

This will result in a true, installable mobile application that no longer requires a separate web server.

## 🐍 Bonus Utility: File Renamer

Included in the suite is a standalone Python script (`file_renamer.py`) to clean and standardize your music filenames before processing.

* **Reads Metadata**: Extracts Artist and Title tags from `.mp3`, `.flac`, and `.m4a` files.
* **Intelligent Formatting**: Removes superfluous text (e.g., "Official Video," "Lyrics") and renames the file to a clean `Title - Artist.ext` format.
* **Improves Match Accuracy**: Using this script first significantly increases the success rate of the Batch Finder.

## 🛠️ Tech Stack

| Category      | Technology                                             |
| :------------ | :----------------------------------------------------- |
| **Frontend** | HTML5, Tailwind CSS, JavaScript (AJAX/Fetch)           |
| **Backend** | PHP 8+                                                 |
| **Web Server**| Apache (via XAMPP)                                     |
| **Lyrics API**| Hybrid: Self-hosted `lrc-server` & Online `lrclib.net` |
| **Helper Script**| Python 3                                            |

## 📁 Project Structure
`lrc-finder-pro/` | `api/` | `download.php` | `search.php` | `assets/` | `css/` | `style.css` | `js/` | `1219444622223.js` // For Manual Search | `1219444658955.js` // For Batch Finder | `tailwindcss.js` | `includes/` | `footer.php` | `nav.php` | `search/` | `index.php` // Manual Search Page | `file_renamer.py` // Standalone utility | `index.php` // Main Batch Finder Page | `README.md`

## 🚀 Setup and Installation

To run this project locally, you will need:
* ✅ **XAMPP** (or any Apache+PHP environment)
* ✅ **Rust + Cargo** (for the optional offline API backend)
* ✅ **Python 3** (for the optional file renamer script)

### Step 1: Set up the Offline lrclib Backend (Optional)

For the fastest search experience, you can host the lyrics database locally.

1.  **Clone the repository**:
    * *Note: Use this specific fork as it contains fixes for local deployment.*
    ```bash
    git clone [https://github.com/RiczzIoT/lrclib-fixed.git](https://github.com/RiczzIoT/lrclib-fixed.git)
    cd lrclib-fixed
    ```
2.  **Download the Database**: Obtain the latest database dump from [lrclib.net Database Dumps]([https://db-dumps.lrclib.net/](https://db-dumps.lrclib.net/lrclib-db-dump-20250718T081344Z.sqlite3.gz)).
3.  **Place the Database**: Extract the downloaded archive and move the `db.sqlite3` file into the `lrclib-fixed` directory.
4.  **Run the Server**:
    ```bash
    cargo run --release -- serve --database db.sqlite3
    ```
    The local API will now be running at `http://localhost:3300`.

### Step 2: Configure the PHP Web Application

1.  **Deploy Project Files**: Copy the entire `lrc-finder-pro` project folder into your XAMPP `htdocs` directory (e.g., `C:\xampp\htdocs\lrc-finder-pro`).
2.  **Start Apache**: Launch the XAMPP Control Panel and start the **Apache** service.
3.  **Access the App**: Open your browser and navigate to `http://localhost/lrc-finder-pro/`.

### Step 3: Clean Your Filenames (Optional but Recommended)

1.  Open `file_renamer.py` in a text editor.
2.  Change the `MUSIC_FOLDER` variable to the path of your music library.
3.  Run the script from your terminal: `python file_renamer.py`.
4.  This will rename your files to the optimal `Title - Artist.ext` format for best results.

## 📖 Usage Guide

1.  (Recommended) Run the **File Renamer** script on your music folder first.
2.  Navigate to the **Batch Finder** page (`index.php`).
3.  Click "Choose Files" and select your music folder.
4.  Hit "Scan for Lyrics" and watch the real-time progress.
5.  Review results:
    * Green "Synced"? You're good!
    * If a match is wrong, click **"Revise"** to pick a better one.
    * Red "Not Found"? Hover and click **"Manual Search"** to find it yourself.
6.  When ready, click **"Download All found (.zip)"** to grab your LRC files.

## ❤️ Credits and Acknowledgements

This project is made possible thanks to the amazing work of **Trần Xuân Thắng** and the entire [lrclib](https://github.com/tranxuanthang/lrclib) community.

* 🌐 Website: [lrclib.net](https://lrclib.net/)
* 📦 GitHub Repo: [github.com/tranxuanthang/lrclib](https://github.com/tranxuanthang/lrclib)
* 👤 Creator: [tranxuanthang](https://github.com/tranxuanthang)

🎤 *Sing your heart out—with synced lyrics by your side!*
