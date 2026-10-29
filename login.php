<?php
include "inc/koneksi.php";
session_start();
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>SI-KABRI | Login</title>
  <link rel="icon" href="dist/img/kominfo_logo.png" type="image/x-icon">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Font Awesome (SB Admin 2) -->
  <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
  <!-- SB Admin 2 CSS -->
  <link href="dist/css/sb-admin-2.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body {
      margin: 0;
      padding: 0;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: url("dist/img/bglogin/bg2.jpg") no-repeat center center fixed;
      background-size: cover;
      transition: background-image 1.5s ease-in-out;
    }

    /* 🔹 Card Glass Effect */
    .card-glass {
      background: rgba(255, 255, 255, 0.15);
      border-radius: 20px;
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
    }

    .login-title {
      color: #fff;
    }

    .text-center p,
    .text-center span {
      color: #eaeaea;
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-xl-5 col-lg-6 col-md-8">
        <div class="card card-glass o-hidden border-0">
          <div class="card-body p-0">
            <div class="p-5">
              <div class="text-center mb-4">
                <img src="dist/img/kominfo_logo.png" alt="Logo Kominfo" class="brand-logo mb-2" style="max-height:80px;">
                <h1 class="h4 login-title">
                  <strong>SI-KABRI</strong>
                </h1>
                <p>Sistem Informasi Klasifikasi Berita Prioritas</p>
              </div>

              <form action="" method="post" class="user">
                <div class="form-group mb-3">
                  <div class="input-group">
                    <input type="text" class="form-control form-control-user" name="username" placeholder="Username" required>
                    <div class="input-group-append">
                      <span class="input-group-text"><i class="fas fa-user"></i></span>
                    </div>
                  </div>
                </div>

                <div class="form-group mb-3">
                  <div class="input-group">
                    <input type="password" class="form-control form-control-user" name="password" placeholder="Password" required>
                    <div class="input-group-append">
                      <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    </div>
                  </div>
                </div>

                <button type="submit" class="btn btn-primary btn-user btn-block w-100" name="btnLogin" title="Masuk Sistem">
                  <i class="fas fa-sign-in-alt me-1"></i><b>LOGIN</b>
                </button>
              </form>

              <hr>
              <div class="text-center small mb-0">
                <span>Copyright &copy; Divisi Komunikasi Publik Diskominfo Kota Medan 2025</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    const images = [
      "dist/img/bglogin/bg4.jpg",
      "dist/img/bglogin/bg2.jpg",
      "dist/img/bglogin/bg3.jpg"
    ];

    images.forEach(src => {
      const img = new Image();
      img.src = src;
    });

    let index = 0;

    function changeBackground() {
      document.body.style.backgroundImage = `url('${images[index]}')`;
      index = (index + 1) % images.length;
    }

    changeBackground();

    setInterval(changeBackground, 7000);
  </script>

  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="build/js/sb-admin-2.min.js"></script>

</body>

</html>

<?php
if (isset($_POST['btnLogin'])) {
  $username = mysqli_real_escape_string($koneksi, $_POST['username']);
  $password = $_POST['password'];

  // Cek user
  $sql = "SELECT * FROM pengguna WHERE username = '$username' LIMIT 1";
  $query = mysqli_query($koneksi, $sql);

  if (mysqli_num_rows($query) == 1) {
    $data = mysqli_fetch_assoc($query);

    // Cek password hash
    if (password_verify($password, $data['password_hash'])) {
      $_SESSION['ses_id']    = $data['id_pengguna'];
      $_SESSION['ses_user']  = $data['username'];
      $_SESSION['ses_level'] = $data['level'];

      echo "<script>
        Swal.fire({
          title: 'Login Berhasil',
          text: 'Selamat datang $username',
          icon: 'success',
          confirmButtonText: 'OK'
        }).then(() => {
          window.location = 'index.php';
        });
        </script>";
      exit;
    } else {
      echo "<script>
            Swal.fire({
              title: 'Login Gagal',
              text: 'Username atau Password salah!',
              icon: 'error',
              confirmButtonText: 'Coba Lagi'
            }).then(() => {
              window.location = 'login.php';
            });
            </script>";
    }
  } else {
    echo "<script>
        Swal.fire({
          title: 'Login Gagal',
          text: 'Username atau Password salah!',
          icon: 'error',
          confirmButtonText: 'Coba Lagi'
        }).then(() => {
          window.location = 'login.php';
        });
        </script>";
  }
}
?>