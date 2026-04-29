<?php
include 'includes/session.php';
include '../config/database.php';
include 'includes/header.php';

$message = '';
$edit_data = null;

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM penyakit WHERE id_penyakit = ?");
    if ($stmt->execute([$id])) {
        $message = '<div class="alert alert-success">Data berhasil dihapus!</div>';
    } else {
        $message = '<div class="alert alert-danger">Gagal menghapus data!</div>';
    }
}

// Handle Form Submit (Add/Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_penyakit = $_POST['id_penyakit'];
    $nama_penyakit = $_POST['nama_penyakit'];
    $deskripsi = $_POST['deskripsi'];
    $saran_penanganan = $_POST['saran_penanganan'];
    $status = $_POST['status'];
    $is_edit = $_POST['is_edit'];

    if ($is_edit) {
        $stmt = $pdo->prepare("UPDATE penyakit SET nama_penyakit=?, deskripsi=?, saran_penanganan=?, status=? WHERE id_penyakit=?");
        if ($stmt->execute([$nama_penyakit, $deskripsi, $saran_penanganan, $status, $id_penyakit])) {
            $message = '<div class="alert alert-success">Data berhasil diupdate!</div>';
        }
    } else {
        // Auto Generate ID
        include_once '../includes/functions.php';
        $new_id = generate_new_id($pdo, 'penyakit', 'P');
        
        $stmt = $pdo->prepare("INSERT INTO penyakit (id_penyakit, nama_penyakit, deskripsi, saran_penanganan, status) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$new_id, $nama_penyakit, $deskripsi, $saran_penanganan, $status])) {
            $message = '<div class="alert alert-success">Data berhasil ditambahkan! ID Baru: <strong>' . $new_id . '</strong></div>';
        }
    }
}

// Handle Edit Request
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM penyakit WHERE id_penyakit = ?");
    $stmt->execute([$id]);
    $edit_data = $stmt->fetch();
}

// Get All Data
$penyakit = $pdo->query("SELECT * FROM penyakit ORDER BY LENGTH(id_penyakit) ASC, id_penyakit ASC")->fetchAll();
?>

<h2 class="mb-4">Kelola Data Penyakit</h2>

<?php echo $message; ?>

<div class="card mb-4">
    <div class="card-header">
        <?php echo $edit_data ? 'Edit Penyakit' : 'Tambah Penyakit Baru'; ?>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="is_edit" value="<?php echo $edit_data ? 1 : 0; ?>">
            <div class="row">
                <div class="col-md-2 mb-3">
                    <label class="form-label">ID Penyakit</label>
                    <input type="text" name="id_penyakit" class="form-control bg-light" value="<?php echo $edit_data['id_penyakit'] ?? '(Otomatis)'; ?>" readonly>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nama Penyakit</label>
                    <input type="text" name="nama_penyakit" class="form-control" value="<?php echo $edit_data['nama_penyakit'] ?? ''; ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="aktif" <?php echo ($edit_data['status'] ?? '') == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                        <option value="nonaktif" <?php echo ($edit_data['status'] ?? '') == 'nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea name="deskripsi" class="form-control" rows="2"><?php echo $edit_data['deskripsi'] ?? ''; ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Saran Penanganan</label>
                <textarea name="saran_penanganan" class="form-control" rows="2"><?php echo $edit_data['saran_penanganan'] ?? ''; ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><?php echo $edit_data ? 'Update Data' : 'Simpan Data'; ?></button>
            <?php if ($edit_data): ?>
                <a href="penyakit.php" class="btn btn-secondary">Batal</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Daftar Penyakit</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="text-white" style="background-color: var(--primary);">
                    <tr>
                        <th>ID</th>
                        <th>Nama Penyakit</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($penyakit as $p): ?>
                    <tr>
                        <td><?php echo $p['id_penyakit']; ?></td>
                        <td><?php echo $p['nama_penyakit']; ?></td>
                        <td>
                            <span class="badge rounded-pill <?php echo $p['status'] == 'aktif' ? 'bg-success' : 'bg-secondary'; ?>">
                                <?php echo $p['status']; ?>
                            </span>
                        </td>
                        <td>
                            <a href="penyakit.php?edit=<?php echo $p['id_penyakit']; ?>" class="btn btn-sm btn-accent">Edit</a>
                            <a href="penyakit.php?delete=<?php echo $p['id_penyakit']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus data ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
