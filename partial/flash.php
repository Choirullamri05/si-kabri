<?php
$succ = $_SESSION['flash_success'] ?? null;
$err  = $_SESSION['flash_error']  ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<?php if($succ || $err): ?>
  <!-- Bootstrap alert fallback (non JS) -->
  <?php if($succ): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fas fa-check-circle mr-1"></i> <?= $succ ?>
      <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
  <?php endif; ?>
  <?php if($err): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="fas fa-exclamation-triangle mr-1"></i> <?= $err ?>
      <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
  <?php endif; ?>

  <!-- SweetAlert2 (kalau sudah include) -->
  <script>
    document.addEventListener('DOMContentLoaded', function(){
      <?php if($succ): ?>Swal.fire({icon:'success', title:'Berhasil', html:<?= json_encode($succ) ?>});<?php endif; ?>
      <?php if($err): ?>Swal.fire({icon:'error',   title:'Gagal',    html:<?= json_encode($err)  ?>});<?php endif; ?>
    });
  </script>
<?php endif; ?>
