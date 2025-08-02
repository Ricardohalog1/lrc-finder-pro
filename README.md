
# 🎵 LRC Finder Pro

**LRC Finder Pro** is a powerful and intuitive web-based utility designed to streamline the process of finding and downloading synchronized lyrics (`.lrc` files) for your entire local music library. By leveraging the extensive `lrclib.net` database, this tool automates the tedious task of searching for lyrics one by one—perfect for music lovers and karaoke masters alike.

> 💡 *Tip:* Replace this section with a GIF demo showcasing the app in action!

---

## 📋 Table of Contents

- [Key Features](#-key-features)  
- [Tech Stack](#-tech-stack)  
- [Project Structure](#-project-structure)  
- [Setup and Installation](#-setup-and-installation)  
- [Usage Guide](#-usage-guide)  
- [License](#-license)  
- [Credits and Acknowledgements](#️-credits-and-acknowledgements)

---

## ✨ Key Features

- 📂 **Batch Folder Processing**  
  Select an entire music folder and let the app find lyrics for all supported audio files (`.mp3`, `.flac`, `.m4a`, etc.).

- 🧠 **Intelligent Filename Parsing**  
  Automatically extracts artist and title information from common filename formats (e.g., `Artist - Title.mp3`).

- 📊 **Rich Result Display**  
  See results with clear status indicators: Synced ✅, Plain 📝, Not Found ❌ — plus duration checks!

- ✍️ **Interactive Revision**  
  Not happy with the first result? Use the "Revise" button to pick from alternate matches in the database.

- 🔍 **Manual Search for Misses**  
  Easily search for lyrics manually if a track isn't found automatically.

- 📦 **One-Click ZIP Download**  
  Download all matched `.lrc` files in one neat `.zip` archive.

- 📄 **Standalone Search Page**  
  Includes a separate interface for manual lyric lookups anytime.

---

## 🛠️ Tech Stack

| Category    | Technology                         |
|-------------|-------------------------------------|
| Frontend    | HTML5, Tailwind CSS, JavaScript     |
| Backend     | PHP 8+                              |
| Web Server  | Apache (via XAMPP)                 |
| Search API  | `lrclib` (written in Rust)         |
| Database    | SQLite (from `lrclib.net` dump)    |

---

## 📁 Project Structure

```
LRC-Finder-Pro/
│
├── index.php           # Main batch processing interface
├── manual_search.php   # Standalone manual search page
├── test.php            # Unified API endpoint (batch & manual)
├── download.php        # Handles .zip creation/download
├── nav.php             # Reusable navigation component
└── README.md           # You're reading this!
```

---

## 🚀 Setup and Installation

To run this project locally, you’ll need:

- ✅ **XAMPP** or any Apache+PHP environment  
- ✅ **Rust + Cargo** for running the lyric API backend

---

### 🔧 Step 1: Set up the `lrclib` Backend

1. **Clone the repo:**

   ```bash
   git clone https://github.com/tranxuanthang/lrclib.git
   cd lrclib
   ```

2. **Download the Database:**  
   Go to [lrclib.net Database Dumps](https://lrclib.net/) and download the latest `.sqlite3.gz` file.

3. **Place the Database:**  
   Extract and move `db.sqlite3` into the `lrclib` folder.

4. **Run the Server:**

   ```bash
   cargo run --release -- serve --database db.sqlite3
   ```

   You should now see it running on `http://localhost:3300`.

---

### 🖥️ Step 2: Configure the PHP Web App

1. **Copy the project folder**  
   Move `LRC-Finder-Pro/` into your `htdocs` folder (e.g., `C:\xampp\htdocs\`).

2. **Start Apache**  
   Open the XAMPP Control Panel and enable the Apache module.

3. **Visit in Browser**  
   Go to: [http://localhost/LRC-Finder-Pro/](http://localhost/LRC-Finder-Pro/)

---

## 📖 Usage Guide

1. 🔍 Go to the main page (`index.php`)
2. 📁 Click "Choose Files" and select your music folder.
3. 🚀 Hit "Find Lyrics" and let the magic begin!
4. ⏳ Watch the progress bar.
5. ✅ Review results:
    - Green? You're good!
    - If unsure, click "Revise" for better options.
    - "Not Found"? Click "Manual Search" and try a custom query.
6. 💾 When ready, click "Download All Found (.zip)" to grab your LRC files.

---

## ⚖️ License

This project is licensed under the **MIT License**. See the [LICENSE.md](LICENSE.md) file for full details.

---

## ❤️ Credits and Acknowledgements

This project is made possible thanks to the amazing work of **Trần Xuân Thắng** and the entire [lrclib](https://github.com/tranxuanthang/lrclib) community.

Please support the original creators:

- 🌐 Website: [lrclib.net](https://lrclib.net/)  
- 📦 GitHub Repo: [github.com/tranxuanthang/lrclib](https://github.com/tranxuanthang/lrclib)  
- 👤 Creator: [tranxuanthang](https://github.com/tranxuanthang)

---

🎤 *Sing your heart out—with synced lyrics by your side!*
