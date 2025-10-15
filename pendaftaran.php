<?php
include 'config.php';

$editData = null;
$message = '';
$error = '';

// Proses Simpan/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_siswa = $_POST['id_siswa'];
    $id_program = $_POST['id_program'];
    $tgl_daftar = $_POST['tgl_daftar'];
    $status = $_POST['status'] ?? 'Aktif';
    $edit_id = $_POST['edit_id'] ?? null;

    if (!$id_siswa || !$id_program || !$tgl_daftar) {
        $error = "Siswa, program, dan tanggal daftar wajib dipilih!";
    } else {
        try {
            if ($edit_id) {
                $stmt = $pdo->prepare("UPDATE pendaftaran SET id_siswa=?, id_program=?, tgl_daftar=?, status=? WHERE id_pendaftaran=?");
                $stmt->execute([$id_siswa, $id_program, $tgl_daftar, $status, $edit_id]);
                $message = "Pendaftaran berhasil diperbarui!";
            } else {
                $stmt = $pdo->prepare("INSERT INTO pendaftaran (id_siswa, id_program, tgl_daftar, status) VALUES (?, ?, ?, ?)");
                $stmt->execute([$id_siswa, $id_program, $tgl_daftar, $status]);
                $message = "Pendaftaran berhasil ditambahkan!";
            }
            header("Location: pendaftaran.php?status=success&msg=" . urlencode($message));
            exit;
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }

    $editData = [
        'id_pendaftaran' => $edit_id,
        'id_siswa' => $id_siswa,
        'id_program' => $id_program,
        'tgl_daftar' => $tgl_daftar,
        'status' => $status
    ];
}

// Hapus
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM pendaftaran WHERE id_pendaftaran = ?")->execute([$_GET['delete']]);
    header("Location: pendaftaran.php?status=success&msg=Pendaftaran berhasil dihapus!");
    exit;
}

// Edit
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM pendaftaran WHERE id_pendaftaran = ?");
    $stmt->execute([$_GET['edit']]);
    $editData = $stmt->fetch();
}

// Ambil data untuk dropdown
$siswaList = $pdo->query("SELECT id_siswa, nis, nama FROM siswa ORDER BY nama")->fetchAll();
$programList = $pdo->query("SELECT id_program, kode_program, nama_program FROM program ORDER BY nama_program")->fetchAll();

// Ambil data pendaftaran dengan JOIN
$sql = "
    SELECT p.id_pendaftaran, s.nama AS nama_siswa, pr.nama_program, p.tgl_daftar, p.status
    FROM pendaftaran p
    JOIN siswa s ON p.id_siswa = s.id_siswa
    JOIN program pr ON p.id_program = pr.id_program
    ORDER BY p.tgl_daftar DESC
";
$pendaftaran = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Siswa</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>
<body>
<div class="container">
    <h2>📋 Pendaftaran Siswa ke Program</h2>
    <a href="index.php" class="btn btn-add">← Kembali ke Beranda</a>

    <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <h3><?= $editData ? 'Edit Pendaftaran' : 'Tambah Pendaftaran Baru' ?></h3>
        <?php if ($editData && isset($editData['id_pendaftaran'])): ?>
            <input type="hidden" name="edit_id" value="<?= $editData['id_pendaftaran'] ?>">
        <?php endif; ?>
        <p>
            <select name="id_siswa" required>
                <option value="">-- Pilih Siswa --</option>
                <?php foreach ($siswaList as $s): ?>
                    <option value="<?= $s['id_siswa'] ?>" <?= ($editData && $editData['id_siswa'] == $s['id_siswa']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['nama']) ?> (<?= htmlspecialchars($s['nis']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="id_program" required>
                <option value="">-- Pilih Program --</option>
                <?php foreach ($programList as $p): ?>
                    <option value="<?= $p['id_program'] ?>" <?= ($editData && $editData['id_program'] == $p['id_program']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['nama_program']) ?> (<?= htmlspecialchars($p['kode_program']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <input type="date" name="tgl_daftar" value="<?= $editData['tgl_daftar'] ?? date('Y-m-d') ?>" required>
            <select name="status" required>
                <option value="Aktif" <?= ($editData && $editData['status'] == 'Aktif') ? 'selected' : '' ?>>Aktif</option>
                <option value="Nonaktif" <?= ($editData && $editData['status'] == 'Nonaktif') ? 'selected' : '' ?>>Nonaktif</option>
            </select>
        </p>
        <input type="submit" value="<?= $editData ? 'Perbarui Pendaftaran' : 'Simpan Pendaftaran' ?>">
        <?php if ($editData): ?>
            <a href="pendaftaran.php">Batal Edit</a>
        <?php endif; ?>
    </form>

    <h3>Daftar Pendaftaran</h3>
    <table>
        <thead>
            <tr>
                <th>Siswa</th>
                <th>Program</th>
                <th>Tgl Daftar</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($pendaftaran)): ?>
                <tr><td colspan="5" style="text-align:center;">Belum ada pendaftaran.</td></tr>
            <?php else: ?>
                <?php foreach ($pendaftaran as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['nama_siswa']) ?></td>
                    <td><?= htmlspecialchars($p['nama_program']) ?></td>
                    <td><?= htmlspecialchars($p['tgl_daftar']) ?></td>
                    <td><?= htmlspecialchars($p['status']) ?></td>
                    <td>
                        <a href="?edit=<?= $p['id_pendaftaran'] ?>" class="btn btn-edit">Edit</a>
                        <a href="?delete=<?= $p['id_pendaftaran'] ?>" class="btn btn-delete">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>