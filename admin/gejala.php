<?php
include 'includes/session.php';
include '../config/database.php';
include 'includes/header.php';

$message = '';
$edit_data = null;

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM gejala WHERE id_gejala = ?");
    if ($stmt->execute([$id])) {
        $message = '<div class="alert alert-success">Data berhasil dihapus!</div>';
    } else {
        $message = '<div class="alert alert-danger">Gagal menghapus data!</div>';
    }
}

// Handle Form Submit (Add/Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_gejala = $_POST['id_gejala'];
    $nama_gejala = $_POST['nama_gejala'];
    $status = $_POST['status'];
    $is_edit = $_POST['is_edit'];

    if ($is_edit) {
        $stmt = $pdo->prepare("UPDATE gejala SET nama_gejala=?, status=? WHERE id_gejala=?");
        if ($stmt->execute([$nama_gejala, $status, $id_gejala])) {
            $message = '<div class="alert alert-success">Data berhasil diupdate!</div>';
        }
    } else {
        // Auto Generate ID
        include_once '../includes/functions.php';
        $new_id = generate_new_id($pdo, 'gejala', 'G');

        $stmt = $pdo->prepare("INSERT INTO gejala (id_gejala, nama_gejala, status) VALUES (?, ?, ?)");
        if ($stmt->execute([$new_id, $nama_gejala, $status])) {
            $message = '<div class="alert alert-success">Data berhasil ditambahkan! ID Baru: <strong>' . $new_id . '</strong></div>';
        }
    }
}

// Handle Edit Request
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM gejala WHERE id_gejala = ?");
    $stmt->execute([$id]);
    $edit_data = $stmt->fetch();
}

// Get All Data
$gejala = $pdo->query("SELECT * FROM gejala ORDER BY LENGTH(id_gejala) ASC, id_gejala ASC")->fetchAll();
?>

<h2 class="mb-4">Kelola Data Gejala</h2>

<?php echo $message; ?>

<div class="card mb-4">
    <div class="card-header">
        <?php echo $edit_data ? 'Edit Gejala' : 'Tambah Gejala Baru'; ?>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="is_edit" value="<?php echo $edit_data ? 1 : 0; ?>">
            <div class="row">
                <div class="col-md-2 mb-3">
                    <label class="form-label">ID Gejala</label>
                    <input type="text" name="id_gejala" class="form-control bg-light" value="<?php echo $edit_data['id_gejala'] ?? '(Otomatis)'; ?>" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Gejala</label>
                    <input type="text" name="nama_gejala" class="form-control" value="<?php echo $edit_data['nama_gejala'] ?? ''; ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="aktif" <?php echo ($edit_data['status'] ?? '') == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                        <option value="nonaktif" <?php echo ($edit_data['status'] ?? '') == 'nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><?php echo $edit_data ? 'Update Data' : 'Simpan Data'; ?></button>
            <?php if ($edit_data): ?>
                <a href="gejala.php" class="btn btn-secondary">Batal</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Daftar Gejala</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="text-white" style="background-color: var(--primary);">
                    <tr>
                        <th>ID</th>
                        <th>Nama Gejala</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($gejala as $g): ?>
                    <tr>
                        <td><?php echo $g['id_gejala']; ?></td>
                        <td><?php echo $g['nama_gejala']; ?></td>
                        <td>
                            <span class="badge rounded-pill <?php echo $g['status'] == 'aktif' ? 'bg-success' : 'bg-secondary'; ?>">
                                <?php echo $g['status']; ?>
                            </span>
                        </td>
                        <td>
                            <a href="gejala.php?edit=<?php echo $g['id_gejala']; ?>" class="btn btn-sm btn-accent">Edit</a>
                            <a href="gejala.php?delete=<?php echo $g['id_gejala']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus data ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
