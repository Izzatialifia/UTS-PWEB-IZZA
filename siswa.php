<?php
include 'config.php';

$editData = null;
$message = '';
$error = '';

// Proses Simpan/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nis = trim($_POST['nis']);
    $nama = trim($_POST['nama']);
    $tgl_lahir = $_POST['tgl_lahir'];
    $alamat = trim($_POST['alamat']);
    $no_hp = trim($_POST['no_hp']);
    $edit_id = $_POST['edit_id'] ?? null;

    if (empty($nis) || empty($nama) || empty($tgl_lahir) || empty($alamat) || empty($no_hp)) {
        $error = "Semua field wajib diisi!";
    } else {
        try {
            if ($edit_id) {
                $stmt = $pdo->prepare("UPDATE siswa SET nis=?, nama=?, tgl_lahir=?, alamat=?, no_hp=? WHERE id_siswa=?");
                $stmt->execute([$nis, $nama, $tgl_lahir, $alamat, $no_hp, $edit_id]);
                $message = "Data siswa berhasil diperbarui!";
            } else {
                $stmt = $pdo->prepare("INSERT INTO siswa (nis, nama, tgl_lahir, alamat, no_hp) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$nis, $nama, $tgl_lahir, $alamat, $no_hp]);
                $message = "Siswa berhasil ditambahkan!";
            }
            header("Location: siswa.php?status=success&msg=" . urlencode($message));
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "NIS sudah digunakan!";
            } else {
                $error = "Error: " . $e->getMessage();
            }
        }
    }

    $editData = [
        'id_siswa' => $edit_id,
        'nis' => $nis,
        'nama' => $nama,
        'tgl_lahir' => $tgl_lahir,
        'alamat' => $alamat,
        'no_hp' => $no_hp
    ];
}

// Hapus
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM siswa WHERE id_siswa = ?")->execute([$_GET['delete']]);
    header("Location: siswa.php?status=success&msg=Siswa berhasil dihapus!");
    exit;
}

// Edit
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM siswa WHERE id_siswa = ?");
    $stmt->execute([$_GET['edit']]);
    $editData = $stmt->fetch();
}

// Ambil semua data
$siswa = $pdo->query("SELECT * FROM siswa ORDER BY nama")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Siswa</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>
<body>
<div class="container">
    <h2>👦 Data Siswa</h2>
    <a href="index.php" class="btn btn-add">← Kembali ke Beranda</a>

    <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <h3><?= $editData ? 'Edit Siswa' : 'Tambah Siswa Baru' ?></h3>
        <?php if ($editData && isset($editData['id_siswa'])): ?>
            <input type="hidden" name="edit_id" value="<?= $editData['id_siswa'] ?>">
        <?php endif; ?>
        <p>
            <input type="text" name="nis" placeholder="NIS *" value="<?= $editData['nis'] ?? '' ?>" required>
            <input type="text" name="nama" placeholder="Nama Lengkap *" value="<?= $editData['nama'] ?? '' ?>" required>
        </p>
        <p>
            <input type="date" name="tgl_lahir" placeholder="Tanggal Lahir *" value="<?= $editData['tgl_lahir'] ?? '' ?>" required>
            <input type="text" name="no_hp" placeholder="No HP *" value="<?= $editData['no_hp'] ?? '' ?>" required>
        </p>
        <p>
            <textarea name="alamat" placeholder="Alamat *" rows="3" required><?= $editData['alamat'] ?? '' ?></textarea>
        </p>
        <input type="submit" value="<?= $editData ? 'Perbarui Data' : 'Simpan Siswa' ?>">
        <?php if ($editData): ?>
            <a href="siswa.php">Batal Edit</a>
        <?php endif; ?>
    </form>

    <h3>Daftar Siswa</h3>
    <table>
        <thead>
            <tr>
                <th>NIS</th>
                <th>Nama</th>
                <th>Tgl Lahir</th>
                <th>Alamat</th>
                <th>No HP</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($siswa)): ?>
                <tr><td colspan="6" style="text-align:center;">Belum ada data siswa.</td></tr>
            <?php else: ?>
                <?php foreach ($siswa as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s['nis']) ?></td>
                    <td><?= htmlspecialchars($s['nama']) ?></td>
                    <td><?= htmlspecialchars($s['tgl_lahir']) ?></td>
                    <td><?= htmlspecialchars($s['alamat']) ?></td>
                    <td><?= htmlspecialchars($s['no_hp']) ?></td>
                    <td>
                        <a href="?edit=<?= $s['id_siswa'] ?>" class="btn btn-edit">Edit</a>
                        <a href="?delete=<?= $s['id_siswa'] ?>" class="btn btn-delete">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>