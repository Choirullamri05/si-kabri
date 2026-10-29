<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['ses_id'])) {
    header("Location: login.php");
    exit;
}

$userId  = (int)$_SESSION['ses_id'];
$isAdmin = (($_SESSION['ses_level'] ?? '') === 'Admin');

// CSRF
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
$csrf = $_SESSION['csrf'];

// Ambil data awal
$sql = "SELECT p.username, p.level, b.nama_lengkap, b.foto_profile, b.no_hp, b.nip_nik, b.jabatan
        FROM pengguna p
        LEFT JOIN biodata b ON b.id_pengguna = p.id_pengguna
        WHERE p.id_pengguna = $userId
        LIMIT 1";
$res = mysqli_query($koneksi, $sql);
$me  = $res ? mysqli_fetch_assoc($res) : null;

$nama_lengkap = $me['nama_lengkap'] ?? '';
$no_hp        = $me['no_hp']        ?? '';
$nip_nik      = $me['nip_nik']      ?? '';
$jabatan      = $me['jabatan']      ?? '';
$foto_profile = $me['foto_profile'] ?? '';
$username     = $me['username']     ?? '';
$level        = $me['level']        ?? '';

$err = $ok = '';

// Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        $err = "Sesi tidak valid.";
    } else {
        $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
        $no_hp        = trim($_POST['no_hp'] ?? '');
        $nip_nik      = trim($_POST['nip_nik'] ?? '');
        $jabatan      = trim($_POST['jabatan'] ?? '');

        if ($nama_lengkap === '') $err = "Nama lengkap wajib diisi.";

        // Upload foto (opsional)
        $uploadedName = $foto_profile;
        if (!$err && isset($_FILES['foto_profile']) && $_FILES['foto_profile']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['foto_profile']['error'] === UPLOAD_ERR_OK) {
                $tmp  = $_FILES['foto_profile']['tmp_name'];
                $orig = $_FILES['foto_profile']['name'];
                $size = (int)$_FILES['foto_profile']['size'];

                $ext   = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
                $allow = ['jpg', 'jpeg', 'png', 'webp'];
                if (!in_array($ext, $allow, true)) $err = "Format gambar harus jpg/jpeg/png/webp.";
                if ($size > 2 * 1024 * 1024)           $err = "Ukuran maksimal 2MB.";

                if (!$err) {
                    // Folder upload (perhatikan path relatif 2 level ke atas)
                    $destDir = __DIR__ . "/../../uploads/avatars";
                    if (!is_dir($destDir)) mkdir($destDir, 0777, true);

                    $newName  = "u{$userId}_" . bin2hex(random_bytes(6)) . "." . $ext;
                    $destPath = $destDir . "/" . $newName;

                    // Validasi MIME
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime  = finfo_file($finfo, $tmp);
                    finfo_close($finfo);
                    if (strpos($mime, 'image/') !== 0) $err = "File bukan gambar yang valid.";

                    if (!$err && move_uploaded_file($tmp, $destPath)) {
                        $uploadedName = $newName;
                        // Hapus foto lama jika ada
                        if (!empty($foto_profile) && $foto_profile !== $uploadedName) {
                            $oldPath = $destDir . "/" . $foto_profile;
                            if (is_file($oldPath)) @unlink($oldPath);
                        }
                    } elseif (!$err) {
                        $err = "Gagal menyimpan file upload.";
                    }
                }
            } else {
                $err = "Upload error code: " . (int)$_FILES['foto_profile']['error'];
            }
        }

        if (!$err) {

            $stmt = mysqli_prepare($koneksi, "UPDATE biodata 
        SET nama_lengkap=?, no_hp=?, nip_nik=?, jabatan=?, foto_profile=?, updated_at=NOW()
        WHERE id_pengguna=?");
            mysqli_stmt_bind_param($stmt, "sssssi", $nama_lengkap, $no_hp, $nip_nik, $jabatan, $uploadedName, $userId);
            mysqli_stmt_execute($stmt);

            if (mysqli_stmt_affected_rows($stmt) === 0) {
                mysqli_stmt_close($stmt);
                $stmt = mysqli_prepare($koneksi, "INSERT INTO biodata
          (id_pengguna, nama_lengkap, no_hp, nip_nik, jabatan, foto_profile, created_at)
          VALUES (?, ?, ?, ?, ?, ?, NOW())");
                mysqli_stmt_bind_param($stmt, "isssss", $userId, $nama_lengkap, $no_hp, $nip_nik, $jabatan, $uploadedName);
                mysqli_stmt_execute($stmt);
            }
            mysqli_stmt_close($stmt);

            // Refresh SESSION supaya sidebar berubah
            $_SESSION['ses_nama'] = $nama_lengkap;
            $_SESSION['ses_foto'] = $uploadedName;


            // Perbarui variabel tampilan
            $ok           = "Profil berhasil diperbarui.";
            $foto_profile = $uploadedName;
            $level        = $_SESSION['ses_level'] ?? $level;
        }
    }
}


$fotoUrl = (!empty($foto_profile))
    ? "uploads/avatars/" . rawurlencode($foto_profile)
    : "dist/img/undraw_profile_1.svg";
?>

<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Profil Saya</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="../../dist/css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        .avatar-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #e3e6f0
        }
    </style>
</head>

<body class="bg-light">
    <div class="container mt-4">
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <img class="avatar-preview" src="<?= esc($fotoUrl) ?>" alt="Avatar">

                        <h5 class="mb-0 mt-2"><?= esc($nama_lengkap ?: '—') ?></h5>
                        <div class="text-muted small">@<?= esc($username ?: '-') ?></div>
                        <div class="mt-1">
                            <span class="badge badge-info"><?= esc($level ?: '-') ?></span>
                        </div>

                        <hr>

                        <div class="text-left small mt-2">
                            <div><strong>Nama Lengkap</strong><br><?= esc($nama_lengkap ?: '—') ?></div>
                            <div class="mt-1"><strong>Username</strong><br><?= esc($username ?: '-') ?></div>
                            <div class="mt-1"><strong>No HP</strong><br><?= esc($no_hp ?: '—') ?></div>
                            <div class="mt-1"><strong>NIP/NIK</strong><br><?= esc($nip_nik ?: '—') ?></div>
                            <div class="mt-1"><strong>Jabatan</strong><br><?= esc($jabatan ?: '—') ?></div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="col-lg-8 mb-4">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-user-cog mr-1"></i> Edit Profil</h6>
                    </div>
                    <div class="card-body">
                        <?php if ($ok): ?>
                            <div class="alert alert-success"><?= esc($ok) ?></div>
                        <?php endif; ?>
                        <?php if ($err): ?>
                            <div class="alert alert-danger"><?= esc($err) ?></div>
                        <?php endif; ?>

                        <form method="post" enctype="multipart/form-data" class="user" autocomplete="off">
                            <input type="hidden" name="csrf" value="<?= esc($csrf) ?>">

                            <div class="form-group">
                                <label>Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nama_lengkap" required value="<?= esc($nama_lengkap) ?>">
                            </div>

                            <div class="form-group">
                                <label>Nomor HP</label>
                                <input type="text" class="form-control" name="no_hp" value="<?= esc($no_hp) ?>" placeholder="0812xxxxxxx">
                            </div>

                            <div class="form-group">
                                <label>NIP/NIK</label>
                                <input type="text" class="form-control" name="nip_nik" value="<?= esc($nip_nik) ?>" placeholder="NIP atau NIK">
                            </div>

                            <div class="form-group">
                                <label>Jabatan <?= $isAdmin ? '' : '<span class="text-muted">(hanya Admin yang bisa mengubah)</span>' ?></label>
                                <input type="text" class="form-control" name="jabatan" value="<?= esc($jabatan) ?>" <?= $isAdmin ? '' : 'readonly' ?>>
                            </div>

                            <div class="form-group">
                                <label>Foto Profil</label>
                                <div class="custom-file">
                                    <!-- PENTING: name HARUS 'foto_profile' agar dibaca PHP -->
                                    <input type="file" class="custom-file-input" id="foto_profile" name="foto_profile" accept=".jpg,.jpeg,.png,.webp">
                                    <label class="custom-file-label" for="foto_profile">Pilih file...</label>
                                </div>
                                <small class="text-muted d-block mt-1">JPG/PNG/WEBP, maks 2MB. Foto baru akan menggantikan foto lama.</small>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Simpan Perubahan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../vendor/jquery/jquery.min.js"></script>
    <script src="../../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../../dist/js/sb-admin-2.min.js"></script>
    <script>
        $('.custom-file-input').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });
    </script>
</body>

</html>