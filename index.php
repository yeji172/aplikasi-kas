<?php
require_once 'config/koneksi.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";
$success = "";

//login
if (isset($_POST['login'])) {
    $username = clean($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = "Username dan password harus diisi!";
    } else {
        $query = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username'");
        
        if (mysqli_num_rows($query) > 0) {
            $user = mysqli_fetch_assoc($query);
            
            if ($password == $user['password']) { 
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['role'] = $user['role'];
                
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Password yang Anda masukkan salah!";
            }
        } else {
            $error = "Username tidak ditemukan!";
        }
    }
}

//register
if (isset($_POST['register'])) {
    $username = clean($_POST['username']);
    $password = $_POST['password']; 
    $nama_lengkap = clean($_POST['nama_lengkap']);
    $kelas = clean($_POST['kelas']);
    $role = clean($_POST['role']); 
    
    if (empty($username) || empty($password) || empty($nama_lengkap) || empty($kelas)) {
        $error = "Semua data harus diisi!";
    } else {
        $cek_username = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username'");
        
        if (mysqli_num_rows($cek_username) > 0) {
            $error = "Username sudah digunakan!";
        } else {
            $query = mysqli_query($koneksi, "INSERT INTO users (username, password, nama_lengkap, kelas, role) 
                                         VALUES ('$username', '$password', '$nama_lengkap', '$kelas', '$role')");
            
            if ($query) {
                $success = "Pendaftaran berhasil! Silahkan login.";
            } else {
                $error = "Terjadi kesalahan: " . mysqli_error($koneksi);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Pencatatan Kas Kelas</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Aplikasi Pencatatan Kas Kelas</h2>
            </div>
            <div class="auth-body">
                <div class="auth-tabs">
                    <div class="auth-tab active" id="login-tab">Login</div>
                    <div class="auth-tab" id="register-tab">Register</div>
                </div>
                
                <?php if (!empty($error)) : ?>
                <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <?php if (!empty($success)) : ?>
                <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>
                
                <!-- Form Login -->
                <form id="login-form" method="POST" action="">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary btn-block">Login</button>
                </form>
                
                <!-- Form Register -->
                <form id="register-form" method="POST" action="" style="display: none;">
                    <div class="form-group">
                        <label for="reg-username">Username</label>
                        <input type="text" class="form-control" id="reg-username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="reg-password">Password</label>
                        <input type="password" class="form-control" id="reg-password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="nama_lengkap">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
                    </div>
                    <div class="form-group">
                        <label for="kelas">Kelas</label>
                        <input type="text" class="form-control" id="kelas" name="kelas" required>
                    </div>
                    <div class="form-group">
                    <label for="role">Role</label>
                   <select class="form-control" id="role" name="role" required>
                  <option value="bendahara">Bendahara</option>
                 <option value="wali_kelas">Wali Kelas</option>
    </select>
</div>
                    <button type="submit" name="register" class="btn btn-primary btn-block">Register</button>
                </form>
            </div>
        </div>
    </div>
    
    <script src="assets/js/script.js"></script>
</body>
</html>