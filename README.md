# **🎵 LRC Finder Pro**

**LRC Finder Pro** is a powerful and intuitive web-based utility designed to streamline the process of finding and downloading synchronized lyrics (.lrc files) for your entire local music library. By leveraging the extensive lrclib.net database, this tool automates the tedious task of searching for lyrics one by one—perfect for music lovers and karaoke masters alike.

💡 *Tip:* This is the perfect spot for the video demo you just created\!

## **📋 Table of Contents**

* [Key Features](https://www.google.com/search?q=%23-key-features)  
* [Tech Stack](https://www.google.com/search?q=%23-tech-stack)  
* [Project Structure](https://www.google.com/search?q=%23-project-structure)  
* [Setup and Installation](https://www.google.com/search?q=%23-setup-and-installation)  
* [Usage Guide](https://www.google.com/search?q=%23-usage-guide)  
* [License](https://www.google.com/search?q=%23-license)  
* [Credits and Acknowledgements](https://www.google.com/search?q=%23%EF%B8%8F-credits-and-acknowledgements)

## **✨ Key Features**

* 📂 **Batch Folder Processing** Select an entire music folder and let the app find lyrics for all supported audio files (.mp3, .flac, .m4a, etc.).  
* 📊 **Real-time Progress & Rich Results** Watch the progress bar fill up and see results with clear status indicators: Synced ✅, Not Found ❌.  
* ✍️ **Interactive Revision** If the first match isn't perfect, click "Revise" to open a modal and choose the correct lyrics from a list of alternatives.  
* 🔍 **Inline Manual Search** For tracks that weren't found, use the "Manual Search" button right on the results list to find lyrics without leaving the page.  
* 📄 **Lyrics Preview Modal** On the Manual Search page, click any result to preview the full, timed lyrics before downloading.  
* 📦 **One-Click ZIP Download** Download all successfully found .lrc files in one neat .zip archive, ready for your music player.  
* 🌐 **Standalone Manual Search Page** Includes a separate, clean interface for looking up lyrics for any individual song anytime.

## **🛠️ Tech Stack**

| Category | Technology |
| :---- | :---- |
| Frontend | HTML5, Tailwind CSS, JavaScript (AJAX/Fetch) |
| Backend | PHP 8+ |
| Web Server | Apache (via XAMPP) |
| Search API | lrclib (written in Rust) |
| Database | SQLite (from lrclib.net dump) |

## **📁 Project Structure**

This project follows a clean and organized structure for better maintainability.

LRC-Finder-Pro/  
│  
├── api/  
│   ├── scan.php            \# Handles batch folder scanning  
│   ├── search.php          \# Handles manual search queries  
│   └── download.php        \# Handles .zip creation/download  
│  
├── assets/  
│   ├── css/  
│   │   └── style.css       \# Custom stylesheets  
│   └── js/  
│       └── ...             \# Your JavaScript files  
│  
├── includes/  
│   ├── nav.php             \# Reusable navigation component  
│   └── footer.php          \# Reusable footer component  
│  
├── index.php               \# Main batch processing interface (Batch Finder)  
├── manual\_search.php       \# Standalone manual search page  
└── README.md               \# You're reading this\!

## **🚀 Setup and Installation**

To run this project locally, you’ll need:

* ✅ **XAMPP** or any Apache+PHP environment  
* ✅ **Rust \+ Cargo** for running the lyric API backend

### **🔧 Step 1: Set up the lrclib Backend**

1. **Clone the repo:**  
   git clone https://github.com/tranxuanthang/lrclib.git  
   cd lrclib

2. **Download the Database:** Go to [lrclib.net Database Dumps]([https://lrclib.net/](https://db-dumps.lrclib.net/lrclib-db-dump-20250718T081344Z.sqlite3.gz)) and download the latest .sqlite3.gz file.  
3. **Place the Database:** Extract and move db.sqlite3 into the lrclib folder.  
4. **Run the Server:**  
   cargo run \--release \-- serve \--database db.sqlite3

   You should now see it running on http://localhost:3300.

### **🖥️ Step 2: Configure the PHP Web App**

1. **Copy the project folder** Move LRC-Finder-Pro/ into your htdocs folder (e.g., C:\\xampp\\htdocs\\).  
2. **Start Apache** Open the XAMPP Control Panel and enable the Apache module.  
3. **Visit in Browser** Go to: [http://localhost/LRC-Finder-Pro/](https://www.google.com/search?q=http://localhost/LRC-Finder-Pro/)

## **📖 Usage Guide**

1. 🔍 Go to the **Batch Finder** page (index.php).  
2. 📁 Click "Choose Files" and select your music folder.  
3. 🚀 Hit "Scan for Lyrics" and let the magic begin\!  
4. ⏳ Watch the progress bar.  
5. ✅ Review results:  
   * Green "Synced"? You're good\!  
   * If a match is wrong, click "Revise" to pick a better one.  
   * Red "Not Found"? Click "Manual Search" and try a custom query.  
6. 💾 When ready, click "Download All found (.zip)" to grab your LRC files.

## **⚖️ License**

This project is licensed under the **MIT License**. See the [LICENSE.md](https://raw.githubusercontent.com/Ricardohalog1/lrc-finder-pro/refs/heads/main/LICENSE) file for full details.

## **❤️ Credits and Acknowledgements**

This project is made possible thanks to the amazing work of **Trần Xuân Thắng** and the entire [lrclib](https://github.com/tranxuanthang/lrclib) community.

Please support the original creators:

* 🌐 Website: [lrclib.net](https://lrclib.net/)  
* 📦 GitHub Repo: [github.com/tranxuanthang/lrclib](https://github.com/tranxuanthang/lrclib)  
* 👤 Creator: [tranxuanthang](https://github.com/tranxuanthang)

🎤 *Sing your heart out—with synced lyrics by your side\!*
