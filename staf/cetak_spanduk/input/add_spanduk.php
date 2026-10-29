<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['ses_id'])) {
  echo "<script>location='login.php'</script>";
  exit;
}


$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul_kegiatan = trim($_POST['judul_kegiatan']);
    $tanggal_pasang = $_POST['tanggal_pasang'];
    $lokasi_pasang  = $_POST['lokasi_pasang'];

    if ($judul_kegiatan === '' || $tanggal_pasang === '' || $lokasi_pasang === '') {
        $errors[] = "Semua field wajib diisi.";
    }

    if (empty($errors)) {
        $stmt = $koneksi->prepare("INSERT INTO spanduk (judul_kegiatan, tanggal_pasang) VALUES (?, ?)");
        $stmt->bind_param("ss", $judul_kegiatan, $tanggal_pasang);
        $stmt->execute();
        $id_spanduk = $stmt->insert_id;

        $stmt2 = $koneksi->prepare("INSERT INTO spanduk_lokasi (id_spanduk, lokasi_pasang) VALUES (?, ?)");
        $stmt2->bind_param("is", $id_spanduk, $lokasi_pasang);
        $stmt2->execute();
        $id_lokasi = $stmt2->insert_id;


        if (!empty($_FILES['foto']['name'])) {
            $uploadDir = "uploads/spanduk/" . date("Y/m") . "/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            foreach ($_FILES['foto']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['foto']['error'][$key] === UPLOAD_ERR_OK) {
                    $fileName = time() . "_" . basename($_FILES['foto']['name'][$key]);
                    $targetFile = $uploadDir . $fileName;
                    move_uploaded_file($tmpName, $targetFile);

                    $keterangan = $_POST['keterangan'][$key] ?? null;

                    $stmt3 = $koneksi->prepare("INSERT INTO spanduk_foto (id_lokasi, file_path, keterangan) VALUES (?, ?, ?)");
                    $stmt3->bind_param("iss", $id_lokasi, $targetFile, $keterangan);
                    $stmt3->execute();
                }
            }
        }

        echo "<script>Swal.fire({title:'Berhasil Simpan Data',icon:'success'}).then(()=>{location='index.php?page=i_data_spanduk'})</script>";
    }
}
?>

<div class="container-fluid">
  <!-- Form Card -->
  <div class="card shadow mb-4">
    <div class="card-header py-3 d-flex align-items-center justify-content-between">
      <h6 class="m-0 font-weight-bold text-primary">
        <i class="fas fa-edit mr-2"></i>Form Input Spanduk Kegiatan
      </h6>
      <span class="badge badge-info">Form Wajib Diisi</span>
    </div>
    
    <div class="card-body">
      <!-- Alert Errors -->
      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <strong><i class="fas fa-exclamation-triangle mr-2"></i>Terdapat kesalahan:</strong>
          <ul class="mb-0 mt-2">
            <?php foreach ($errors as $e): ?>
              <li><?php echo htmlspecialchars($e); ?></li>
            <?php endforeach; ?>
          </ul>
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      <?php endif; ?>

      <!-- Info Alert -->
      <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="fas fa-info-circle mr-2"></i>
        <strong>Informasi:</strong> Pastikan semua data diisi dengan benar dan lengkap. Foto spanduk maksimal 5 MB per file.
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form method="post" enctype="multipart/form-data" id="formSpanduk">
        
        <!-- Section: Informasi Kegiatan -->
        <div class="mb-4">
          <h6 class="text-primary font-weight-bold border-bottom pb-2 mb-3">
            <i class="fas fa-clipboard-list mr-2"></i>Informasi Kegiatan
          </h6>
          
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="judul_kegiatan" class="font-weight-bold text-gray-700">
                  Judul Kegiatan <span class="text-danger">*</span>
                </label>
                <input 
                  type="text" 
                  id="judul_kegiatan"
                  name="judul_kegiatan" 
                  class="form-control" 
                  placeholder="Masukkan judul kegiatan, contoh: Peringatan HUT RI Ke-78"
                  required
                  maxlength="200">
                <small class="form-text text-muted">
                  <i class="fas fa-info-circle"></i> Maksimal 200 karakter
                </small>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="tanggal_pasang" class="font-weight-bold text-gray-700">
                  Tanggal Pemasangan <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                  </div>
                  <input 
                    type="date" 
                    id="tanggal_pasang"
                    name="tanggal_pasang" 
                    class="form-control" 
                    required>
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="form-group">
                <label for="lokasi_pasang" class="font-weight-bold text-gray-700">
                  Lokasi Pemasangan <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                  </div>
                  <input 
                    type="text" 
                    id="lokasi_pasang"
                    name="lokasi_pasang" 
                    class="form-control" 
                    placeholder="Contoh: Jl. Gatot Subroto No. 45, Medan"
                    required
                    maxlength="255">
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Section: Upload Foto -->
        <div class="mb-4">
          <h6 class="text-primary font-weight-bold border-bottom pb-2 mb-3">
            <i class="fas fa-camera mr-2"></i>Dokumentasi Foto Spanduk
          </h6>

          <div class="alert alert-warning" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <strong>Perhatian:</strong> 
            <ul class="mb-0 mt-2">
              <li>Format file yang diperbolehkan: JPG, JPEG, PNG</li>
              <li>Ukuran maksimal per file: 5 MB</li>
              <li>Pastikan foto terlihat jelas dan tidak buram</li>
            </ul>
          </div>

          <div id="foto-container">
            <div class="foto-item border rounded p-3 mb-3 bg-light">
              <div class="d-flex align-items-center mb-2">
                <span class="badge badge-primary mr-2">Foto 1</span>
                <small class="text-muted">File wajib diupload</small>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group mb-2">
                    <label class="font-weight-bold text-gray-700">
                      Pilih File <span class="text-danger">*</span>
                    </label>
                    <div class="custom-file">
                      <input 
                        type="file" 
                        name="foto[]" 
                        class="custom-file-input foto-input" 
                        accept="image/jpeg,image/jpg,image/png"
                        required
                        onchange="updateFileName(this)">
                      <label class="custom-file-label">Pilih file...</label>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group mb-2">
                    <label class="font-weight-bold text-gray-700">Keterangan Foto</label>
                    <input 
                      type="text" 
                      name="keterangan[]" 
                      class="form-control" 
                      placeholder="Contoh: Tampak depan spanduk"
                      maxlength="100">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <button 
            type="button" 
            class="btn btn-success btn-sm" 
            onclick="addFotoField()"
            id="btnTambahFoto">
            <i class="fa fa-plus mr-1"></i> Tambah Foto Lainnya
          </button>
          <small class="text-muted ml-2">
            <i class="fas fa-info-circle"></i> Anda dapat menambahkan beberapa foto sekaligus
          </small>
        </div>

        <!-- Action Buttons -->
        <div class="border-top pt-3 mt-4">
          <div class="d-flex justify-content-between align-items-center">
            <a href="?page=i_data_spanduk" class="btn btn-secondary">
              <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
            <div>
              <button type="reset" class="btn btn-warning mr-2" onclick="return confirm('Yakin ingin mereset form?')">
                <i class="fas fa-undo mr-1"></i> Reset Form
              </button>
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Simpan Data
              </button>
            </div>
          </div>
        </div>

      </form>
    </div>
  </div>
</div>

<script>
let fotoCounter = 1;
const maxFoto = 10; // Batas maksimal foto

function addFotoField() {
  if (fotoCounter >= maxFoto) {
    alert('Maksimal ' + maxFoto + ' foto dapat diupload');
    return;
  }

  fotoCounter++;
  let container = document.getElementById('foto-container');
  let div = document.createElement('div');
  div.className = "foto-item border rounded p-3 mb-3 bg-light position-relative";
  div.innerHTML = `
    <div class="d-flex align-items-center justify-content-between mb-2">
      <div>
        <span class="badge badge-primary mr-2">Foto ${fotoCounter}</span>
        <small class="text-muted">Opsional</small>
      </div>
      <button type="button" class="btn btn-danger btn-sm" onclick="removeFotoField(this)">
        <i class="fas fa-trash-alt mr-1"></i> Hapus
      </button>
    </div>
    <div class="row">
      <div class="col-md-6">
        <div class="form-group mb-2">
          <label class="font-weight-bold text-gray-700">Pilih File</label>
          <div class="custom-file">
            <input 
              type="file" 
              name="foto[]" 
              class="custom-file-input foto-input" 
              accept="image/jpeg,image/jpg,image/png"
              onchange="updateFileName(this)">
            <label class="custom-file-label">Pilih file...</label>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group mb-2">
          <label class="font-weight-bold text-gray-700">Keterangan Foto</label>
          <input 
            type="text" 
            name="keterangan[]" 
            class="form-control" 
            placeholder="Contoh: Tampak samping spanduk"
            maxlength="100">
        </div>
      </div>
    </div>
  `;
  container.appendChild(div);
  
  // Update nomor urut foto
  updateFotoNumbers();
}

function removeFotoField(btn) {
  if (confirm('Yakin ingin menghapus foto ini?')) {
    btn.closest('.foto-item').remove();
    fotoCounter--;
    updateFotoNumbers();
  }
}

function updateFotoNumbers() {
  const fotoItems = document.querySelectorAll('.foto-item');
  fotoItems.forEach((item, index) => {
    const badge = item.querySelector('.badge-primary');
    if (badge) {
      badge.textContent = 'Foto ' + (index + 1);
    }
  });
}

function updateFileName(input) {
  const fileName = input.files[0] ? input.files[0].name : 'Pilih file...';
  const label = input.nextElementSibling;
  label.textContent = fileName;
  
  // Validasi ukuran file (5MB = 5 * 1024 * 1024 bytes)
  if (input.files[0] && input.files[0].size > 5 * 1024 * 1024) {
    alert('Ukuran file terlalu besar! Maksimal 5 MB.');
    input.value = '';
    label.textContent = 'Pilih file...';
    return;
  }
}

// Validasi form sebelum submit
document.getElementById('formSpanduk').addEventListener('submit', function(e) {
  const requiredInputs = this.querySelectorAll('[required]');
  let isValid = true;
  
  requiredInputs.forEach(input => {
    if (!input.value.trim()) {
      isValid = false;
      input.classList.add('is-invalid');
    } else {
      input.classList.remove('is-invalid');
    }
  });
  
  if (!isValid) {
    e.preventDefault();
    alert('Mohon lengkapi semua field yang wajib diisi!');
    // Scroll ke field pertama yang error
    const firstInvalid = this.querySelector('.is-invalid');
    if (firstInvalid) {
      firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
      firstInvalid.focus();
    }
  }
});

// Set tanggal hari ini sebagai default
document.addEventListener('DOMContentLoaded', function() {
  const today = new Date().toISOString().split('T')[0];
  document.getElementById('tanggal_pasang').value = today;
});
</script>

<style>
.foto-item {
  transition: all 0.3s ease;
}

.foto-item:hover {
  box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
  transform: translateY(-2px);
}

.custom-file-label::after {
  content: "Browse";
}

.form-control:focus,
.custom-file-input:focus ~ .custom-file-label {
  border-color: #4e73df;
  box-shadow: 0 0 0 0.2rem rgba(78,115,223,0.25);
}

.is-invalid {
  border-color: #e74a3b !important;
}

.breadcrumb {
  font-size: 0.875rem;
}

.breadcrumb-item + .breadcrumb-item::before {
  content: "›";
}
</style>
