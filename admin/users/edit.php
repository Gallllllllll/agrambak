<?php
require "../../middleware/auth.php";
admin_required();
require "../../config/database.php";

function fv($value) {
    return htmlspecialchars($value ?? '');
}

$id = $_GET['id'];

if ($_POST) {
    $stmt = $pdo->prepare("
        UPDATE users SET nama=?, email=?, telepon=?, role=? WHERE user_id=?
    ");
    $stmt->execute([
        $_POST['nama'],
        $_POST['email'],
        $_POST['telepon'],
        $_POST['role'],
        $id
    ]);
    header("Location: index.php");
    exit;
}

$user = $pdo->prepare("SELECT * FROM users WHERE user_id=?");
$user->execute([$id]);
$user = $user->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSS GLOBAL -->
    <link rel="stylesheet" href="/agrambak/aset/css/dashboard_admin.css">
    <link rel="stylesheet" href="/agrambak/aset/css/users_admin.css">
    <link rel="icon" href="../../aset/img/logo-tranzio2.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <title>Edit User</title>
</head>
<body>
    <?php include __DIR__ . '/../sidebar.php'; ?>

<div class="main-content">

    <div class="dashboard-header">
        <div>
            <h1>Edit User</h1>
            <p>Perbarui data pengguna</p>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">

            <form method="POST" class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control"
                           value="<?= fv($user['nama']) ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control"
                           value="<?= fv($user['email']) ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Telepon</label>
                    <input type="text" name="telepon" class="form-control"
                           value="<?= fv($user['telepon']) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Alamat</label>
                    <input type="text" name="alamat" class="form-control"
                           value="<?= fv($user['alamat']) ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select" required>
                        <option value="user" <?= $user['role']=='user'?'selected':'' ?>>User</option>
                        <option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>Admin</option>
                    </select>
                </div>

                <div class="col-12 d-flex justify-content-between mt-4">
                    <a href="index.php" class="btn btn-light">
                        <i class="fa-solid fa-arrow-left"></i> Kembali
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i> Simpan Perubahan
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>
    
</body>
</html>