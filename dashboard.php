<?php
require_once 'config/koneksi.php';

cekLogin();

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$nama_lengkap = $_SESSION['nama_lengkap'];
$role = $_SESSION['role'];

$query_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id = $user_id");
$user = mysqli_fetch_assoc($query_user);

if (isset($_POST['tambah_kas_masuk'])) {
    $keterangan = clean($_POST['keterangan']);
    $jumlah = clean($_POST['jumlah']);
    $tanggal = clean($_POST['tanggal']);
    
    $query = mysqli_query($koneksi, "INSERT INTO transaksi (user_id, jenis, keterangan, jumlah, tanggal) 
                                   VALUES ($user_id, 'masuk', '$keterangan', $jumlah, '$tanggal')");
    
    if ($query) {
        $success = "Kas masuk berhasil ditambahkan!";
    } else {
        $error = "Terjadi kesalahan: " . mysqli_error($koneksi);
    }
}

if (isset($_POST['tambah_kas_keluar'])) {
    $keterangan = clean($_POST['keterangan']);
    $jumlah = clean($_POST['jumlah']);
    $tanggal = clean($_POST['tanggal']);
    
    $query = mysqli_query($koneksi, "INSERT INTO transaksi (user_id, jenis, keterangan, jumlah, tanggal) 
                                   VALUES ($user_id, 'keluar', '$keterangan', $jumlah, '$tanggal')");
    
    if ($query) {
        $success = "Kas keluar berhasil ditambahkan!";
    } else {
        $error = "Terjadi kesalahan: " . mysqli_error($koneksi);
    }
}

if (isset($_POST['edit_transaksi'])) {
    $transaksi_id = clean($_POST['transaksi_id']);
    $keterangan = clean($_POST['keterangan']);
    $jumlah = clean($_POST['jumlah']);
    $tanggal = clean($_POST['tanggal']);
    
    $query = mysqli_query($koneksi, "UPDATE transaksi SET keterangan = '$keterangan', 
                                    jumlah = $jumlah, tanggal = '$tanggal' 
                                    WHERE id = $transaksi_id");
    
    if ($query) {
        $success = "Data transaksi berhasil diperbarui!";
    } else {
        $error = "Terjadi kesalahan: " . mysqli_error($koneksi);
    }
}

if (isset($_GET['delete_transaksi'])) {
    $transaksi_id = clean($_GET['delete_transaksi']);
    
    $query = mysqli_query($koneksi, "DELETE FROM transaksi WHERE id = $transaksi_id");
    
    if ($query) {
        $success = "Data transaksi berhasil dihapus!";
    } else {
        $error = "Terjadi kesalahan: " . mysqli_error($koneksi);
    }
}

if (isset($_POST['update_profil'])) {
    $nama_lengkap_baru = clean($_POST['nama_lengkap']);
    $kelas_baru = clean($_POST['kelas']);
    $password_baru = $_POST['password'];
    
    $query = "UPDATE users SET nama_lengkap = '$nama_lengkap_baru', kelas = '$kelas_baru'";
    
    if (!empty($password_baru)) {
        $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
        $query .= ", password = '$password_hash'";
    }
    
    $query .= " WHERE id = $user_id";
    
    $update = mysqli_query($koneksi, $query);
    
    if ($update) {
        $_SESSION['nama_lengkap'] = $nama_lengkap_baru;
        $success = "Profil berhasil diperbarui!";
        $query_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id = $user_id");
        $user = mysqli_fetch_assoc($query_user);
    } else {
        $error = "Terjadi kesalahan: " . mysqli_error($koneksi);
    }
}

if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == 0) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $filename = $_FILES['foto_profil']['name'];
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    
    if (in_array(strtolower($ext), $allowed)) {
        $new_filename = "user_" . $user_id . "_" . time() . "." . $ext;
        $upload_path = "assets/uploads/" . $new_filename;
        
        if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $upload_path)) {
            $update = mysqli_query($koneksi, "UPDATE users SET foto_profil = '$new_filename' WHERE id = $user_id");
            
            if ($update) {
                $success = "Foto profil berhasil diperbarui!";
                $query_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id = $user_id");
                $user = mysqli_fetch_assoc($query_user);
            } else {
                $error = "Terjadi kesalahan: " . mysqli_error($koneksi);
            }
        } else {
            $error = "Gagal mengupload file!";
        }
    } else {
        $error = "Format file tidak diizinkan. Gunakan jpg, jpeg, png, atau gif!";
    }
}

$query_kas_masuk = mysqli_query($koneksi, "SELECT t.*, u.nama_lengkap FROM transaksi t 
                                         JOIN users u ON t.user_id = u.id 
                                         WHERE t.jenis = 'masuk' 
                                         ORDER BY t.tanggal DESC");

$query_kas_keluar = mysqli_query($koneksi, "SELECT t.*, u.nama_lengkap FROM transaksi t 
                                          JOIN users u ON t.user_id = u.id 
                                          WHERE t.jenis = 'keluar' 
                                          ORDER BY t.tanggal DESC");

$bulan_ini = date('Y-m');
$query_laporan = mysqli_query($koneksi, "SELECT t.*, u.nama_lengkap FROM transaksi t 
                                       JOIN users u ON t.user_id = u.id 
                                       WHERE DATE_FORMAT(t.tanggal, '%Y-%m') = '$bulan_ini' 
                                       ORDER BY t.tanggal DESC");

$query_total_masuk_bulan = mysqli_query($koneksi, "SELECT SUM(jumlah) as total FROM transaksi 
                                                 WHERE jenis = 'masuk' AND DATE_FORMAT(tanggal, '%Y-%m') = '$bulan_ini'");
$total_masuk_bulan = mysqli_fetch_assoc($query_total_masuk_bulan)['total'] ?? 0;

$query_total_keluar_bulan = mysqli_query($koneksi, "SELECT SUM(jumlah) as total FROM transaksi 
                                                  WHERE jenis = 'keluar' AND DATE_FORMAT(tanggal, '%Y-%m') = '$bulan_ini'");
$total_keluar_bulan = mysqli_fetch_assoc($query_total_keluar_bulan)['total'] ?? 0;

$saldo_bulan = $total_masuk_bulan - $total_keluar_bulan;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Aplikasi Pencatatan Kas Kelas</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <!-- Mobile Navigation -->
    <div class="mobile-nav">
        <button class="menu-toggle"><i class="fas fa-bars"></i></button>
        <h3>Kas Kelas</h3>
        <div><i class="fas fa-user"></i> <?= $nama_lengkap ?></div>
    </div>
    
    <div class="overlay"></div>
    
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Aplikasi Kas Kelas</h3>
        </div>
        <div class="sidebar-menu">
            <a href="#" class="sidebar-menu-item active tab-link" data-tab="dashboard-tab">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="#" class="sidebar-menu-item tab-link" data-tab="kas-masuk-tab">
                <i class="fas fa-arrow-circle-down"></i> Kas Masuk
            </a>
            <a href="#" class="sidebar-menu-item tab-link" data-tab="kas-keluar-tab">
                <i class="fas fa-arrow-circle-up"></i> Kas Keluar
            </a>
            <a href="#" class="sidebar-menu-item tab-link" data-tab="laporan-tab">
                <i class="fas fa-file-alt"></i> Laporan
            </a>
            <a href="#" class="sidebar-menu-item tab-link" data-tab="profil-tab">
                <i class="fas fa-user"></i> Profil
            </a>
            <a href="logout.php" class="sidebar-menu-item">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
    
    <!-- Content -->
    <div class="content">
        <?php if (!empty($error)) : ?>
        <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)) : ?>
        <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <!-- Dashboard Tab -->
        <div id="dashboard-tab" class="tab-content">
            <div class="page-header">
                <h2>Dashboard</h2>
                <span>Selamat datang, <?= $nama_lengkap ?></span>
            </div>
            
            <div class="dashboard-stats">
                <div class="stat-card primary">
                    <div class="stat-title">Total Kas Masuk</div>
                    <div class="stat-value"><?= formatRupiah(getTotalKasMasuk()) ?></div>
                    <div>Seluruh waktu</div>
                </div>
                
                <div class="stat-card danger">
                    <div class="stat-title">Total Kas Keluar</div>
                    <div class="stat-value"><?= formatRupiah(getTotalKasKeluar()) ?></div>
                    <div>Seluruh waktu</div>
                </div>
                
                <div class="stat-card success">
                    <div class="stat-title">Saldo Kas</div>
                    <div class="stat-value"><?= formatRupiah(getSaldoKas()) ?></div>
                    <div>Saat ini</div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Transaksi Terbaru</h3>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Keterangan</th>
                                <th>Jenis</th>
                                <th>Jumlah</th>
                                <th>Oleh</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $query_transaksi = mysqli_query($koneksi, "SELECT t.*, u.nama_lengkap FROM transaksi t 
                                                                     JOIN users u ON t.user_id = u.id 
                                                                     ORDER BY t.tanggal DESC LIMIT 5");
                            
                            if (mysqli_num_rows($query_transaksi) > 0) {
                                while ($transaksi = mysqli_fetch_assoc($query_transaksi)) {
                            ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($transaksi['tanggal'])) ?></td>
                                <td><?= $transaksi['keterangan'] ?></td>
                                <td>
                                    <?php if ($transaksi['jenis'] == 'masuk') : ?>
                                    <span class="badge badge-success">Masuk</span>
                                    <?php else : ?>
                                    <span class="badge badge-danger">Keluar</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= formatRupiah($transaksi['jumlah']) ?></td>
                                <td><?= $transaksi['nama_lengkap'] ?></td>
                            </tr>
                            <?php 
                                }
                            } else {
                            ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">Belum ada transaksi</td>
                            </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Kas Masuk Tab -->
        <div id="kas-masuk-tab" class="tab-content" style="display: none;">
            <div class="page-header">
                <h2>Kas Masuk</h2>
                <button class="btn btn-primary" onclick="document.getElementById('form-kas-masuk').style.display = 'block';">
                    <i class="fas fa-plus"></i> Tambah Kas Masuk
                </button>
            </div>
            
            <!-- Form Tambah Kas Masuk -->
            <div class="card" id="form-kas-masuk" style="display: none;">
                <div class="card-header">
                    <h3>Tambah Kas Masuk</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="keterangan">Keterangan</label>
                            <input type="text" class="form-control" id="keterangan" name="keterangan" required>
                        </div>
                        <div class="form-group">
                            <label for="jumlah">Jumlah (Rp)</label>
                            <input type="number" class="form-control" id="jumlah" name="jumlah" required>
                        </div>
                        <div class="form-group">
                            <label for="tanggal">Tanggal</label>
                            <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <button type="submit" name="tambah_kas_masuk" class="btn btn-primary">Simpan</button>
                        <button type="button" class="btn" onclick="document.getElementById('form-kas-masuk').style.display = 'none';">Batal</button>
                    </form>
                </div>
            </div>
            
            <!-- Tabel Kas Masuk -->
            <div class="card">
                <div class="card-header">
                    <h3>Data Kas Masuk</h3>
                </div>
                <div class="card-body">
                    <table class="table">
    <thead>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Keterangan</th>
            <th>Jumlah</th>
            <th>Oleh</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        if (mysqli_num_rows($query_kas_masuk) > 0) {
            while ($kas_masuk = mysqli_fetch_assoc($query_kas_masuk)) {
        ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= date('d/m/Y', strtotime($kas_masuk['tanggal'])) ?></td>
            <td><?= $kas_masuk['keterangan'] ?></td>
            <td><?= formatRupiah($kas_masuk['jumlah']) ?></td>
            <td><?= $kas_masuk['nama_lengkap'] ?></td>
            <td>
                <button class="btn btn-sm btn-warning" onclick="editTransaksi(<?= $kas_masuk['id'] ?>, '<?= $kas_masuk['keterangan'] ?>', <?= $kas_masuk['jumlah'] ?>, '<?= $kas_masuk['tanggal'] ?>')">Edit</button>
                <a href="?delete_transaksi=<?= $kas_masuk['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Hapus</a>
            </td>
        </tr>
        <?php 
            }
        } else {
        ?>
        <tr>
            <td colspan="6" style="text-align: center;">Belum ada data kas masuk</td>
        </tr>
        <?php
        }
        ?>
    </tbody>
</table>
                </div>
            </div>
        </div>
        
        <!-- Kas Keluar Tab -->
        <div id="kas-keluar-tab" class="tab-content" style="display: none;">
            <div class="page-header">
                <h2>Kas Keluar</h2>
                <button class="btn btn-primary" onclick="document.getElementById('form-kas-keluar').style.display = 'block';">
                    <i class="fas fa-plus"></i> Tambah Kas Keluar
                </button>
            </div>
            
            <!-- Form Tambah Kas Keluar -->
            <div class="card" id="form-kas-keluar" style="display: none;">
                <div class="card-header">
                    <h3>Tambah Kas Keluar</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="keterangan">Keterangan</label>
                            <input type="text" class="form-control" id="keterangan" name="keterangan" required>
                        </div>
                        <div class="form-group">
                            <label for="jumlah">Jumlah (Rp)</label>
                            <input type="number" class="form-control" id="jumlah" name="jumlah" required>
                        </div>
                        <div class="form-group">
                            <label for="tanggal">Tanggal</label>
                            <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <button type="submit" name="tambah_kas_keluar" class="btn btn-primary">Simpan</button>
                        <button type="button" class="btn" onclick="document.getElementById('form-kas-keluar').style.display = 'none';">Batal</button>
                    </form>
                </div>
            </div>
            
            <!-- Tabel Kas Keluar -->
            <div class="card">
                <div class="card-header">
                    <h3>Data Kas Keluar</h3>
                </div>
                <div class="card-body">
                    <table class="table">
    <thead>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Keterangan</th>
            <th>Jumlah</th>
            <th>Oleh</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        if (mysqli_num_rows($query_kas_keluar) > 0) {
            while ($kas_keluar = mysqli_fetch_assoc($query_kas_keluar)) {
        ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= date('d/m/Y', strtotime($kas_keluar['tanggal'])) ?></td>
            <td><?= $kas_keluar['keterangan'] ?></td>
            <td><?= formatRupiah($kas_keluar['jumlah']) ?></td>
            <td><?= $kas_keluar['nama_lengkap'] ?></td>
            <td>
                <button class="btn btn-sm btn-warning" onclick="editTransaksi(<?= $kas_keluar['id'] ?>, '<?= $kas_keluar['keterangan'] ?>', <?= $kas_keluar['jumlah'] ?>, '<?= $kas_keluar['tanggal'] ?>')">Edit</button>
                <a href="?delete_transaksi=<?= $kas_keluar['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Hapus</a>
            </td>
        </tr>
        <?php 
            }
        } else {
        ?>
        <tr>
            <td colspan="6" style="text-align: center;">Belum ada data kas keluar</td>
        </tr>
        <?php
        }
        ?>
    </tbody>
</table>
                </div>
            </div>
        </div>
        
        <!-- Laporan Tab -->
        <div id="laporan-tab" class="tab-content" style="display: none;">
            <div class="page-header">
                <h2>Laporan Kas Bulanan</h2>
                <button class="btn btn-primary" onclick="window.print();">
                    <i class="fas fa-print"></i> Cetak Laporan
                </button>
            </div>
            
            <div class="dashboard-stats">
                <div class="stat-card primary">
                    <div class="stat-title">Total Kas Masuk</div>
                    <div class="stat-value"><?= formatRupiah($total_masuk_bulan) ?></div>
                    <div>Bulan ini</div>
                </div>
                
                <div class="stat-card danger">
                    <div class="stat-title">Total Kas Keluar</div>
                    <div class="stat-value"><?= formatRupiah($total_keluar_bulan) ?></div>
                    <div>Bulan ini</div>
                </div>
                
                <div class="stat-card success">
                    <div class="stat-title">Saldo Kas</div>
                    <div class="stat-value"><?= formatRupiah($saldo_bulan) ?></div>
                    <div>Bulan ini</div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Data Transaksi Bulan <?= date('F Y') ?></h3>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Keterangan</th>
                                <th>Jenis</th>
                                <th>Jumlah</th>
                                <th>Oleh</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            if (mysqli_num_rows($query_laporan) > 0) {
                                while ($laporan = mysqli_fetch_assoc($query_laporan)) {
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= date('d/m/Y', strtotime($laporan['tanggal'])) ?></td>
                                <td><?= $laporan['keterangan'] ?></td>
                                <td>
                                    <?php if ($laporan['jenis'] == 'masuk') : ?>
                                    <span class="badge badge-success">Masuk</span>
                                    <?php else : ?>
                                    <span class="badge badge-danger">Keluar</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= formatRupiah($laporan['jumlah']) ?></td>
                                <td><?= $laporan['nama_lengkap'] ?></td>
                            </tr>
                            <?php 
                                }
                            } else {
                            ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">Belum ada transaksi pada bulan ini</td>
                            </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div id="modal-edit-transaksi" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Edit Transaksi</h2>
        <form method="POST" action="">
            <input type="hidden" id="edit-transaksi-id" name="transaksi_id">
            <div class="form-group">
                <label for="edit-keterangan">Keterangan</label>
                <input type="text" class="form-control" id="edit-keterangan" name="keterangan" required>
            </div>
            <div class="form-group">
                <label for="edit-jumlah">Jumlah (Rp)</label>
                <input type="number" class="form-control" id="edit-jumlah" name="jumlah" required>
            </div>
            <div class="form-group">
                <label for="edit-tanggal">Tanggal</label>
                <input type="date" class="form-control" id="edit-tanggal" name="tanggal" required>
            </div>
            <button type="submit" name="edit_transaksi" class="btn btn-primary">Simpan Perubahan</button>
        </form>
    </div>
</div>
        
        <!-- Profil Tab -->
        <div id="profil-tab" class="tab-content" style="display: none;">
            <div class="page-header">
                <h2>Profil Saya</h2>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="profile-header">
                        <img src="<?= !empty($user['foto_profil']) && $user['foto_profil'] != 'default.jpg' ? 'assets/uploads/' . $user['foto_profil'] : 'https://via.placeholder.com/150' ?>" alt="Foto Profil" class="profile-img">
                        <div>
                            <h3 class="profile-name"><?= $user['nama_lengkap'] ?></h3>
                            <p class="profile-info"><?= $user['kelas'] ?> | <?= ucfirst($user['role']) ?></p>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h3>Update Profil</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="nama_lengkap">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?= $user['nama_lengkap'] ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="kelas">Kelas</label>
                                    <input type="text" class="form-control" id="kelas" name="kelas" value="<?= $user['kelas'] ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="password">Password Baru (Kosongkan jika tidak ingin mengubah)</label>
                                    <input type="password" class="form-control" id="password" name="password">
                                </div>
                                <div class="form-group">
                                    <label for="foto_profil">Foto Profil</label>
                                    <input type="file" class="form-control" id="foto_profil" name="foto_profil">
                                </div>
                                <button type="submit" name="update_profil" class="btn btn-primary">Update Profil</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="assets/js/script.js"></script>
    <script>
    	function editTransaksi(id, keterangan, jumlah, tanggal) {
    document.getElementById('edit-transaksi-id').value = id;
    document.getElementById('edit-keterangan').value = keterangan;
    document.getElementById('edit-jumlah').value = jumlah;
    document.getElementById('edit-tanggal').value = tanggal;
    
    document.getElementById('modal-edit-transaksi').style.display = 'block';
}

document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('modal-edit-transaksi');
    var span = document.getElementsByClassName("close")[0];
    
    span.onclick = function() {
        modal.style.display = "none";
    }
    
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
});
</script>
</body>
</html>