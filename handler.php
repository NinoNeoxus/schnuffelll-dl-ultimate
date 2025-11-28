<?php
// Return JSON responses
header('Content-Type: application/json');

/**
 * Helper to send JSON response and exit.
 *
 * @param string $status Response status (success|error)
 * @param array $data Additional data to merge into response
 */
function respond($status, array $data = []) {
    echo json_encode(array_merge(['status' => $status], $data));
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond('error', ['message' => 'Invalid request method']);
}

$url  = isset($_POST['url']) ? trim($_POST['url']) : '';
$type = isset($_POST['type']) ? trim($_POST['type']) : 'video';

if ($url === '') {
    respond('error', ['message' => 'URL tidak boleh kosong.']);
}

// Base command for yt-dlp
$cmdBase = 'yt-dlp';
$cookiesOption = '';
// Use cookies file if present
if (file_exists(__DIR__ . '/cookies.txt')) {
    // wrap in quotes to handle spaces
    $cookiesPath = __DIR__ . '/cookies.txt';
    $cookiesOption = ' --cookies "' . addslashes($cookiesPath) . '"';
}

// Handle metadata check
if ($type === 'check') {
    // Retrieve the title without downloading
    $command = $cmdBase . ' --skip-download --print title' . $cookiesOption . ' ' . escapeshellarg($url);
    exec($command . ' 2>&1', $out, $ret);
    if ($ret === 0) {
        $title = trim(implode("\n", $out));
        respond('success', ['title' => $title]);
    }
    respond('error', ['message' => 'Gagal mendapatkan informasi video. Pastikan URL valid.']);
}

// Prepare downloads directory
$outputDir = __DIR__ . '/downloads';
if (!file_exists($outputDir)) {
    mkdir($outputDir, 0777, true);
}

// Build command based on type
switch ($type) {
    case 'audio':
        // Strict MP3 mode: extract audio, convert to mp3, embed thumbnail
        $command = $cmdBase . ' -x --audio-format mp3 --audio-quality 192 --embed-thumbnail' . $cookiesOption .
                   ' -o "' . addslashes($outputDir) . '/%(title)s.%(ext)s" ' . escapeshellarg($url);
        break;
    case 'video':
    default:
        // Best video & audio combined to mp4
        $command = $cmdBase . ' -f bestvideo+bestaudio --merge-output-format mp4' . $cookiesOption .
                   ' -o "' . addslashes($outputDir) . '/%(title)s.%(ext)s" ' . escapeshellarg($url);
        break;
}

// Execute the download command
exec($command . ' 2>&1', $downloadOutput, $returnCode);
if ($returnCode !== 0) {
    // Return error if command fails
    respond('error', ['message' => implode("\n", $downloadOutput)]);
}

// List downloaded files in descending order to pick the newest
$files = scandir($outputDir, SCANDIR_SORT_DESCENDING);
$fileName = '';
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') {
        $fileName = $file;
        break;
    }
}

if ($fileName === '') {
    respond('error', ['message' => 'File tidak ditemukan setelah diunduh.']);
}

// Clean up unnecessary files when extracting audio
if ($type === 'audio') {
    foreach ($files as $f) {
        if (preg_match('/\.(webm|m4a|opus)$/i', $f)) {
            @unlink($outputDir . '/' . $f);
        }
    }
}

$title = pathinfo($fileName, PATHINFO_FILENAME);
respond('success', [
    'file'  => $fileName,
    'title' => $title
]);

?>