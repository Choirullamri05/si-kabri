<div class="card shadow mb-4">
	<div class="card-header py-3">
		<h6 class="m-0 font-weight-bold text-primary">Data User</h6>
	</div>
	<div class="card-body">
		<div class="mb-3">
			<a href="?page=MyApp/add_pengguna" class="btn btn-primary">
				<i class="fa fa-edit"></i> Tambah Data
			</a>
		</div>
		<div class="table-responsive">
			<table class="table table-bordered text-gray-800" id="dataTable" width="100%" cellspacing="0">
				<thead>
					<tr>
						<th>No</th>
						<th>Nama User</th>
						<th>Jabatan</th>
						<th>Username</th>
						<th>Level</th>
						<th>Aksi</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$sql = $koneksi->query("
            SELECT 
              p.id_pengguna,
              p.username,
              p.level,
              COALESCE(b.nama_lengkap, p.username) AS nama_lengkap,
              b.jabatan
            FROM pengguna p
            LEFT JOIN biodata b ON b.id_pengguna = p.id_pengguna
            ORDER BY p.id_pengguna DESC
          ");

					$no = 1;
					if ($sql && $sql->num_rows > 0):
						while ($row = $sql->fetch_assoc()):
							$id        = (int)$row['id_pengguna'];
							$nama      = htmlspecialchars($row['nama_lengkap'] ?? '', ENT_QUOTES, 'UTF-8');
							$jabatan   = htmlspecialchars($row['jabatan'] ?? '-', ENT_QUOTES, 'UTF-8');
							$username  = htmlspecialchars($row['username'] ?? '', ENT_QUOTES, 'UTF-8');
							$level     = htmlspecialchars($row['level'] ?? '', ENT_QUOTES, 'UTF-8');
					?>
							<tr>
								<td><?= $no++; ?></td>
								<td><?= $nama; ?></td>
								<td><?= $jabatan !== '' ? $jabatan : '-'; ?></td>
								<td><?= $username; ?></td>
								<td><?= $level; ?></td>
								<td class="text-nowrap">
									<a href="?page=MyApp/edit_pengguna&kode=<?= $id; ?>" title="Ubah" class="btn btn-success btn-circle btn-sm">
										<i class="bi bi-pencil-fill"></i>
									</a>
									<a href="?page=MyApp/del_pengguna&kode=<?= $id; ?>"
										onclick="return confirm('Apakah anda yakin hapus data ini ?')"
										title="Hapus" class="btn btn-danger btn-circle btn-sm">
										<i class="fa fa-trash"></i>
									</a>
								</td>
							</tr>
						<?php
						endwhile;
					else:
						?>
						<tr>
							<td colspan="6" class="text-center">Belum ada data pengguna.</td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>