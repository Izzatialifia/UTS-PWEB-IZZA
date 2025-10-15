<?php
include 'config.php';

$editData = null;
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_program = trim($_POST['kode_program']);
    $nama_program = trim($_POST['nama_program']);
    $jenjang = trim($_POST['jenjang']);
    $biaya = (float)$_POST['biaya'];
    $edit_id = $_POST['edit_id'] ?? null;

    if (empty($kode_program) || empty($nama_program) || empty($jenjang) || $biaya <= 0) {
        $error = "Kode, nama, jenjang, dan biaya wajib diisi!";
    } else {
        try {
            if ($edit_id) {
                $stmt = $pdo->prepare("UPDATE program SET kode_program=?, nama_program=?, jenjang=?, biaya=? WHERE id_program=?");
                $stmt->execute([$kode_program, $nama_program, $jenjang, $biaya, $edit_id]);
                $message = "Program berhasil diperbarui!";
            } else {
                $stmt = $pdo->prepare("INSERT INTO program (kode_program, nama_program, jenjang, biaya) VALUES (?, ?, ?, ?)");
                $stmt->execute([$kode_program, $nama_program, $jenjang, $biaya]);
                $message = "Program berhasil ditambahkan!";
            }
            header("Location: program.php?status=success&msg=" . urlencode($message));
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "Kode program sudah ada!";
            } else {
                $error = "Error: " . $e->getMessage();
            }
        }
    }
    $editData = ['id_program' => $edit_id, 'kode_program' => $kode_program, 'nama_program' => $nama_program, 'jenjang' => $jenjang, 'biaya' => $biaya];
}

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM program WHERE id_program = ?")->execute([$_GET['delete']]);
    header("Location: program.php?status=success&msg=Program berhasil dihapus!");
    exit;
}

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM program WHERE id_program = ?");
    $stmt->execute([$_GET['edit']]);
    $editData = $stmt->fetch();
}

$programs = $pdo->query("SELECT * FROM program ORDER BY nama_program")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Program Bimbel</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>
<body>
<div class="container">
    <h2>📚 Program Bimbel</h2>
    <a href="index.php" class="btn btn-add">← Kembali ke Beranda</a>

    <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <h3><?= $editData ? 'Edit Program' : 'Tambah Program Baru' ?></h3>
        <?php if ($editData && isset($editData['id_program'])): ?>
            <input type="hidden" name="edit_id" value="<?= $editData['id_program'] ?>">
        <?php endif; ?>
        <p>
            <input type="text" name="kode_program" placeholder="Kode Program *" value="<?= $editData['kode_program'] ?? '' ?>" required>
            <input type="text" name="nama_program" placeholder="Nama Program *" value="<?= $editData['nama_program'] ?? '' ?>" required>
        </p>
        <p>
            <input type="text" name="jenjang" placeholder="Jenjang (SD/SMP/SMA)" value="<?= $editData['jenjang'] ?? '' ?>" required>
            <input type="number" name="biaya" placeholder="Biaya *" step="0.01" min="0" value="<?= $editData['biaya'] ?? '' ?>" required>
        </p>
        <input type="submit" value="<?= $editData ? 'Perbarui Data' : 'Simpan Program' ?>">
        <?php if ($editData): ?>
            <a href="program.php">Batal Edit</a>
        <?php endif; ?>
    </form>

    <h3>Daftar Program</h3>
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama Program</th>
                <th>Jenjang</th>
                <th>Biaya</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($programs)): ?>
                <tr><td colspan="5" style="text-align:center;">Belum ada program bimbel.</td></tr>
            <?php else: ?>
                <?php foreach ($programs as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['kode_program']) ?></td>
                    <td><?= htmlspecialchars($p['nama_program']) ?></td>
                    <td><?= htmlspecialchars($p['jenjang']) ?></td>
                    <td>Rp <?= number_format($p['biaya'], 2, ',', '.') ?></td>
                    <td>
                        <a href="?edit=<?= $p['id_program'] ?>" class="btn btn-edit">Edit</a>
                        <a href="?delete=<?= $p['id_program'] ?>" class="btn btn-delete">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>