<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// --- Fungsi sederhana untuk escape HTML (jika belum ada helper esc()) ---
if (!function_exists('esc')) {
  function esc($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
  }
}

$errors = [];

if (isset($_POST['Simpan'])) {
  $tgl_input    = trim($_POST['tgl_input'] ?? '');
  $nama_media   = trim($_POST['nama_media'] ?? '');
  $judul_berita = trim($_POST['judul_berita'] ?? '');
  $sentimen     = $_POST['sentimen'] ?? '';

  $sentimen = in_array($sentimen, ['Positif', 'Negatif'], true) ? $sentimen : '';

  // === Validasi dasar ===
  if ($tgl_input === '') {
    $errors[] = "Tanggal wajib diisi.";
  } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl_input)) {
    $errors[] = "Format tanggal tidak valid (YYYY-MM-DD).";
  }
  if ($nama_media === '')   $errors[] = "Nama media wajib diisi.";
  if ($judul_berita === '') $errors[] = "Judul berita wajib diisi.";
  if ($sentimen === '')     $errors[] = "Sentimen tidak valid.";

  // === Jalankan prediksi kategori via Python ===
  $kategori = '';
  if (!$errors) {
    try {
      // Jalankan skrip Python
      $judul_escaped = escapeshellarg($judul_berita);

      // Gunakan path absolut Python agar pasti dikenali di Windows
      $pythonPath = "D:\\laragon\\bin\\python\\python-3.10\\python.exe";

      // Gunakan path absolut untuk predict.py juga (pastikan sesuai)
      $scriptPath = __DIR__ . DIRECTORY_SEPARATOR . "predict.py";

      $command = "\"$pythonPath\" \"$scriptPath\" $judul_escaped";
      $output = shell_exec($command);

      if ($output === null || trim($output) === '') {
        $errors[] = "⚠️ Gagal memanggil model prediksi (pastikan Python dan predict.py sudah benar).";
      } else {
        $kategori = trim($output);

        // Normalisasi hasil (kadang output ikut newline)
        $kategori = str_replace(["\n", "\r"], '', $kategori);

        if (!in_array($kategori, ['Prioritas', 'Non Prioritas'])) {
          $kategori = 'Non Prioritas'; // fallback default
        }
      }
    } catch (Throwable $e) {
      $errors[] = "Kesalahan prediksi: " . $e->getMessage();
    }
  }

  // === Simpan ke database ===
  if (!$errors) {
    try {
      $stmt = $koneksi->prepare("
        INSERT INTO kliping_berita (nama_media, judul_berita, sentimen, kategori_program, tgl_input)
        VALUES (?, ?, ?, ?, ?)
      ");
      $stmt->bind_param('sssss', $nama_media, $judul_berita, $sentimen, $kategori, $tgl_input);
      $stmt->execute();

      if ($stmt->affected_rows < 1) {
        throw new Exception("Gagal menyimpan data kliping.");
      }

      $stmt->close();
      echo "<script>
              Swal.fire({
                title:'Tambah Data Berhasil',
                html:'Kategori otomatis: <b>$kategori</b>',
                icon:'success'
              }).then(()=>{location='index.php?page=i_data_klp'})
            </script>";
      exit;
    } catch (Throwable $e) {
      $errors[] = $e->getMessage();
    }
  }

  if ($errors) {
    echo "<script>
            Swal.fire({
              title:'Gagal',
              html:'" . esc(implode('<br>', $errors)) . "',
              icon:'error'
            })
          </script>";
  }
}
?>

<!-- ================= FORM INPUT ================= -->
<div class="card shadow-lg border-0">
  <div class="card-header text-gray d-flex align-items-center">
    <h5 class="mb-0">
      <i class="fas fa-newspaper mr-2"></i> Form Entri Kliping Berita
    </h5>
  </div>

  <form method="post" class="p-3">
    <div class="card-body">

      <!-- Tanggal Input -->
      <div class="form-group row">
        <label class="col-sm-2 col-form-label font-weight-bold">Tanggal Input</label>
        <div class="col-sm-4">
          <input type="date" class="form-control" name="tgl_input"
            value="<?= esc($_POST['tgl_input'] ?? date('Y-m-d')) ?>" required>
          <small class="form-text text-muted">
            Tanggal ketika berita diterbitkan.
          </small>
        </div>
      </div>

      <!-- Nama Media -->
      <div class="form-group row">
        <label class="col-sm-2 col-form-label font-weight-bold">Sumber Media</label>
        <div class="col-sm-6">
          <input type="text" class="form-control" placeholder="Contoh: Tribun Medan, Waspada Online, dll."
            name="nama_media" value="<?= esc($_POST['nama_media'] ?? '') ?>" required>
          <small class="form-text text-muted">
            Isi nama media penerbit berita.
          </small>
        </div>
      </div>

      <!-- Judul Berita -->
      <div class="form-group row">
        <label class="col-sm-2 col-form-label font-weight-bold">Judul Berita</label>
        <div class="col-sm-8">
          <textarea class="form-control" name="judul_berita" rows="3" maxlength="250" placeholder="Tuliskan judul lengkap berita..." required><?= esc($_POST['judul_berita'] ?? '') ?></textarea>
          <small class="text-muted">
            Judul berita akan diproses oleh sistem AI untuk menentukan kategori prioritas otomatis.
          </small>
        </div>
      </div>

      <!-- Sentimen -->
      <div class="form-group row">
        <label class="col-sm-2 col-form-label font-weight-bold">Sentimen Berita</label>
        <div class="col-sm-4">
          <select name="sentimen" class="form-control" required>
            <option value="">-- Pilih Sentimen --</option>
            <option value="Positif" <?= (($_POST['sentimen'] ?? '') === 'Positif') ? 'selected' : ''; ?>>Positif</option>
            <option value="Negatif" <?= (($_POST['sentimen'] ?? '') === 'Negatif') ? 'selected' : ''; ?>>Negatif</option>
          </select>
          <small class="form-text text-muted">
            Penilaian subjektif berdasarkan isi berita.
          </small>
        </div>
      </div>

      <hr>

      <!-- Catatan Bantuan -->
      <div class="alert alert-info">
        <i class="fas fa-robot mr-2"></i>
        <strong>Catatan:</strong> Sistem akan secara otomatis mengklasifikasikan berita menjadi
        <span class="badge badge-info">Prioritas</span> atau
        <span class="badge badge-secondary">Non Prioritas</span>
        menggunakan model Naïve Bayes .
      </div>

      <!-- Tombol Aksi -->
      <div class="card-footer bg-white text-left">
        <button type="submit" name="Simpan" class="btn btn-success">
          <i class="fas fa-save"></i> Simpan & Proses
        </button>
        <a href="?page=i_data_klp" class="btn btn-secondary">
          <i class="fas fa-arrow-left"></i> Kembali
        </a>
      </div>

    </div>
  </form>
</div>

