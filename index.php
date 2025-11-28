<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Media Downloader</title>
<!-- Tailwind CSS via CDN -->
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<!-- Custom styles for neon and background animations -->
<link rel="stylesheet" href="style.css">
</head>
<body class="bg-slate-950 text-white min-h-screen flex items-center justify-center relative overflow-hidden">

  <!-- Toast container -->
  <div id="toast-container" class="fixed top-4 right-4 space-y-2 z-50"></div>

  <!-- God Mode Overlay -->
  <div id="godOverlay" class="hidden fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-40">
    <div class="bg-gray-800 border border-gray-600 p-6 rounded-lg w-80">
      <h2 class="text-xl mb-4 font-bold">Admin Panel</h2>
      <button class="w-full mb-3 py-2 bg-pink-500 hover:bg-pink-600 rounded">Maintenance Mode</button>
      <button class="w-full py-2 bg-cyan-500 hover:bg-cyan-600 rounded">System Logs</button>
      <button id="closeGod" class="mt-4 w-full py-2 bg-gray-700 hover:bg-gray-600 rounded">Close</button>
    </div>
  </div>

  <!-- Main card -->
  <div class="backdrop-blur-xl bg-white/5 border border-white/20 p-8 rounded-xl w-full max-w-lg z-10">
    <h1 class="text-3xl font-bold mb-4 neon">Media Downloader</h1>
    <form id="downloadForm">
      <label for="url" class="block mb-2">URL Video/Audio:</label>
      <input id="url" type="text" class="w-full p-3 rounded bg-gray-900 border border-gray-700 focus:ring-2 focus:ring-pink-500 focus:border-pink-500 placeholder-gray-500" placeholder="https://..." required>
      <label for="type" class="block mt-4 mb-2">Pilih Jenis:</label>
      <select id="type" class="w-full p-3 rounded bg-gray-900 border border-gray-700 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
        <option value="video">Video (MP4)</option>
        <option value="audio">Audio (MP3)</option>
      </select>
      <div class="mt-6 flex space-x-4">
        <button type="button" id="checkBtn" class="flex-1 py-3 rounded bg-pink-600 hover:bg-pink-700 transition">CEK VIDEO</button>
        <button type="submit" class="flex-1 py-3 rounded bg-cyan-600 hover:bg-cyan-700 transition">DOWNLOAD</button>
      </div>
    </form>
    <div id="spinner" class="hidden flex justify-center mt-6">
      <div class="spinner"></div>
    </div>
    <div id="result" class="mt-6"></div>
  </div>

  <script>
  const form = document.getElementById('downloadForm');
  const urlInput = document.getElementById('url');
  const typeSelect = document.getElementById('type');
  const spinner = document.getElementById('spinner');
  const resultDiv = document.getElementById('result');
  const toastContainer = document.getElementById('toast-container');

  function showToast(status, message) {
    const toast = document.createElement('div');
    toast.className = 'px-4 py-3 rounded shadow-md transition-opacity';
    toast.classList.add(status === 'success' ? 'bg-green-600' : 'bg-red-600');
    toast.textContent = message;
    toastContainer.appendChild(toast);
    setTimeout(() => {
      toast.style.opacity = '0';
      setTimeout(() => toast.remove(), 500);
    }, 4000);
  }

  // Handler for checking video info
  document.getElementById('checkBtn').addEventListener('click', function() {
    if (!urlInput.value) {
      showToast('error', 'Masukkan URL terlebih dahulu.');
      return;
    }
    spinner.classList.remove('hidden');
    fetch('handler.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: 'url=' + encodeURIComponent(urlInput.value) + '&type=check'
    })
    .then(r => r.json())
    .then(data => {
      spinner.classList.add('hidden');
      if (data.status === 'success') {
        showToast('success', 'Judul: ' + data.title);
      } else {
        showToast('error', data.message || 'Gagal memeriksa video.');
      }
    })
    .catch(e => {
      spinner.classList.add('hidden');
      showToast('error', e.toString());
    });
  });

  // Handler for download
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    if (!urlInput.value) {
      showToast('error', 'Masukkan URL terlebih dahulu.');
      return;
    }
    spinner.classList.remove('hidden');
    fetch('handler.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: 'url=' + encodeURIComponent(urlInput.value) + '&type=' + encodeURIComponent(typeSelect.value)
    })
    .then(response => response.json())
    .then(data => {
      spinner.classList.add('hidden');
      if (data.status === 'success') {
        resultDiv.innerHTML = '<a href="downloads/' + encodeURIComponent(data.file) + '" class="underline text-cyan-400">Klik untuk mengunduh: ' + data.title + '</a>';
        showToast('success', 'Berhasil! File siap diunduh.');
      } else {
        resultDiv.innerHTML = '';
        showToast('error', data.message || 'Terjadi kesalahan.');
      }
    })
    .catch(err => {
      spinner.classList.add('hidden');
      showToast('error', err.toString());
    });
  });

  // God Mode detection
  urlInput.addEventListener('keyup', function() {
    if (urlInput.value.trim() === 'NUEL12') {
      document.getElementById('godOverlay').classList.remove('hidden');
    }
  });

  document.getElementById('closeGod').addEventListener('click', function() {
    document.getElementById('godOverlay').classList.add('hidden');
  });
  </script>
</body>
</html>