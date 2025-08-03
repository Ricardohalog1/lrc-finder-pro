import os
import re
from mutagen.easyid3 import EasyID3
from mutagen.flac import FLAC
from mutagen.mp4 import MP4
from mutagen.id3 import ID3NoHeaderError

MUSIC_FOLDER = r"C:\Users\ricar\Music\'90s Alternative"

def get_tags(filepath):
    """Babasahin ang Artist at Title tags mula sa isang music file."""
    try:
        if filepath.lower().endswith('.mp3'):
            audio = EasyID3(filepath)
            return audio.get('artist', [None])[0], audio.get('title', [None])[0]
        elif filepath.lower().endswith('.flac'):
            audio = FLAC(filepath)
            return audio.get('artist', [None])[0], audio.get('title', [None])[0]
        elif filepath.lower().endswith(('.m4a', '.mp4')):
            audio = MP4(filepath)
            return audio.get('\xa9ART', [None])[0], audio.get('\xa9nam', [None])[0]
    except Exception:
        return None, None

def ultimate_clean(text):
    """Ang pinaka-agresibong cleaning function hanggang ngayon."""
    if not text:
        return ''

    cleaned = re.sub(r'\[.*?\]|\(.*?\)', '', text)

    junk_words = [
        'official', 'music', 'video', 'audio', 'lyric', 'lyrics', 
        'hd', 'hq', 'live', 'remastered', 'explicit', 'with', 'feat', 'ft'
    ]
    pattern = r'\b(' + '|'.join(junk_words) + r')\b'
    cleaned = re.sub(pattern, '', cleaned, flags=re.IGNORECASE)
    cleaned = re.sub(r'[^\w\s-]', '', cleaned, flags=re.UNICODE)
    cleaned = re.sub(r'\s*-\s*', ' - ', cleaned)
    cleaned = re.sub(r'\s+', ' ', cleaned).strip()

    return cleaned

def sanitize_for_filename(name):
    """Final check para sa mga bawal na character sa filename."""
    return re.sub(r'[\/:*?"<>|]', '_', name)

print(f"Starting FINAL File Renamer for folder: {MUSIC_FOLDER}")
print("--- WARNING: This will permanently rename files! Test on a copy first! ---")
renamed_count = 0
skipped_count = 0

for current_filename in os.listdir(MUSIC_FOLDER):
    try:
        filename_base, file_ext = os.path.splitext(current_filename)
        file_ext = file_ext.lower()
    except: continue

    if file_ext not in ['.mp3', '.flac', '.m4a', '.mp4']: continue

    print(f"\n[PROC] Processing: {current_filename}")
    old_filepath = os.path.join(MUSIC_FOLDER, current_filename)
    
    artist, title = get_tags(old_filepath)

    if not artist or not title:
        print("  [SKIP] Walang nakitang Artist o Title tags.")
        skipped_count += 1
        continue

    clean_artist = ultimate_clean(artist)
    clean_title = ultimate_clean(title)
    
    if not clean_title or not clean_artist:
        print("  [SKIP] Naging blangko ang Title o Artist pagkatapos maglinis.")
        skipped_count += 1
        continue
    
    sane_artist = sanitize_for_filename(clean_artist)
    sane_title = sanitize_for_filename(clean_title)
        
    new_filename = f"{sane_title} - {sane_artist}{file_ext}"
    new_filepath = os.path.join(MUSIC_FOLDER, new_filename)

    if old_filepath.lower() == new_filepath.lower():
        print("  [SKIP] ✓ Tamang format na.")
        skipped_count += 1
        continue
        
    if os.path.exists(new_filepath):
        print(f"  [SKIP] ✗ May file nang nagngangalang '{new_filename}'.")
        skipped_count += 1
        continue

    try:
        os.rename(old_filepath, new_filepath)
        print(f"  [DONE] -> Renamed to: {new_filename}")
        renamed_count += 1
    except OSError as e:
        print(f"  [ERROR] Hindi ma-rename ang file: {e}")
        skipped_count += 1

print(f"\nProseso Kumpleto! {renamed_count} files na-rename, {skipped_count} files na-skip.")