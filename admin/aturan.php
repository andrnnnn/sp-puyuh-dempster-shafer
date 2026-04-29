<?php
include 'includes/session.php';
include '../config/database.php';
include 'includes/header.php';

$message = '';
$edit_data = null;

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM basis_pengetahuan WHERE id = ?");
    if ($stmt->execute([$id])) {
        $message = '<div class="alert alert-success">Data berhasil dihapus!</div>';
    }
}

// Handle Delete by Gejala (hapus semua aturan untuk 1 gejala)
if (isset($_GET['delete_gejala'])) {
    $kode = $_GET['delete_gejala'];
    $stmt = $pdo->prepare("DELETE FROM basis_pengetahuan WHERE kode_gejala = ?");
    if ($stmt->execute([$kode])) {
        $message = '<div class="alert alert-success">Semua aturan untuk gejala ' . $kode . ' berhasil dihapus!</div>';
    }
}

// Handle Form Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_gejala = $_POST['kode_gejala'];
    $penyakit_ids = $_POST['penyakit_ids'] ?? [];
    $mb = $_POST['mb'];
    $is_edit = $_POST['is_edit'];

    if (empty($penyakit_ids)) {
        $message = '<div class="alert alert-danger">Pilih minimal satu penyakit!</div>';
    } else {
        if ($is_edit) {
            // Edit: Hapus aturan lama untuk gejala ini, insert yang baru
            $pdo->prepare("DELETE FROM basis_pengetahuan WHERE kode_gejala = ?")->execute([$kode_gejala]);
        }
        
        // Insert satu-satu untuk setiap penyakit yang dipilih
        $inserted = 0;
        foreach ($penyakit_ids as $kode_penyakit) {
            $stmt = $pdo->prepare("INSERT INTO basis_pengetahuan (kode_gejala, kode_penyakit, mb) VALUES (?, ?, ?)");
            if ($stmt->execute([$kode_gejala, $kode_penyakit, $mb])) {
                $inserted++;
            }
        }
        
        if ($inserted > 0) {
            $message = '<div class="alert alert-success">Berhasil menyimpan ' . $inserted . ' aturan untuk gejala ' . $kode_gejala . '!</div>';
        }
    }
}

// Handle Edit Request - ambil semua penyakit untuk gejala ini
$edit_penyakit = [];
if (isset($_GET['edit_gejala'])) {
    $kode = $_GET['edit_gejala'];
    $stmt = $pdo->prepare("SELECT * FROM basis_pengetahuan WHERE kode_gejala = ?");
    $stmt->execute([$kode]);
    $rules = $stmt->fetchAll();
    
    if (!empty($rules)) {
        $edit_data = [
            'kode_gejala' => $kode,
            'mb' => $rules[0]['mb']
        ];
        foreach ($rules as $r) {
            $edit_penyakit[] = $r['kode_penyakit'];
        }
    }
}

// Get Master Data
$all_gejala = $pdo->query("SELECT * FROM gejala ORDER BY id_gejala ASC")->fetchAll();
$all_penyakit = $pdo->query("SELECT * FROM penyakit ORDER BY id_penyakit ASC")->fetchAll();

// Get Rules - GROUP BY gejala untuk tampilan ringkas
$aturan = $pdo->query("
    SELECT b.kode_gejala, g.nama_gejala, 
           GROUP_CONCAT(b.kode_penyakit ORDER BY b.kode_penyakit) as penyakit_list,
           MAX(b.mb) as mb
    FROM basis_pengetahuan b 
    JOIN gejala g ON b.kode_gejala = g.id_gejala 
    GROUP BY b.kode_gejala, g.nama_gejala
    ORDER BY b.kode_gejala ASC
")->fetchAll();
?>

<h2 class="mb-4">Kelola Basis Pengetahuan (Aturan)</h2>

<?php echo $message; ?>

<div class="card mb-4">
    <div class="card-header bg-white fw-bold">
        <?php echo $edit_data ? 'Edit Aturan untuk ' . $edit_data['kode_gejala'] : 'Tambah Aturan Baru'; ?>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="is_edit" value="<?php echo $edit_data ? 1 : 0; ?>">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Gejala</label>
                    <select name="kode_gejala" class="form-select" required <?php echo $edit_data ? 'readonly' : ''; ?>>
                        <option value="">-- Pilih Gejala --</option>
                        <?php foreach ($all_gejala as $g): ?>
                            <option value="<?php echo $g['id_gejala']; ?>" <?php echo ($edit_data['kode_gejala'] ?? '') == $g['id_gejala'] ? 'selected' : ''; ?>>
                                <?php echo $g['id_gejala'] . ' - ' . $g['nama_gejala']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nilai Belief (MB)</label>
                    <input type="number" step="0.01" min="0" max="1" name="mb" class="form-control" 
                           value="<?php echo $edit_data['mb'] ?? '0.8'; ?>" required placeholder="Contoh: 0.8">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Penyakit Terkait (Pilih satu atau lebih)</label>
                <div class="row">
                    <?php foreach ($all_penyakit as $p): ?>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="penyakit_ids[]" 
                                   value="<?php echo $p['id_penyakit']; ?>" 
                                   id="p_<?php echo $p['id_penyakit']; ?>"
                                   <?php echo in_array($p['id_penyakit'], $edit_penyakit) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="p_<?php echo $p['id_penyakit']; ?>">
                                <?php echo $p['id_penyakit'] . ' - ' . $p['nama_penyakit']; ?>
                            </label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <button type="submit" class="btn btn-primary"><?php echo $edit_data ? 'Update Aturan' : 'Simpan Aturan'; ?></button>
            <?php if ($edit_data): ?>
                <a href="aturan.php" class="btn btn-secondary">Batal</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white fw-bold">Daftar Basis Pengetahuan</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="text-white" style="background-color: var(--primary);">
                    <tr>
                        <th>Kode</th>
                        <th>Gejala</th>
                        <th>Penyakit Terkait</th>
                        <th>MB</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($aturan as $a): ?>
                    <tr>
                        <td><?php echo $a['kode_gejala']; ?></td>
                        <td><?php echo $a['nama_gejala']; ?></td>
                        <td>
                            <?php 
                            $penyakit_arr = explode(',', $a['penyakit_list']);
                            foreach ($penyakit_arr as $p): ?>
                                <span class="badge text-white me-1 bg-accent"><?php echo $p; ?></span>
                            <?php endforeach; ?>
                        </td>
                        <td><?php echo $a['mb']; ?></td>
                        <td>
                            <a href="aturan.php?edit_gejala=<?php echo $a['kode_gejala']; ?>" class="btn btn-sm btn-accent">Edit</a>
                            <a href="aturan.php?delete_gejala=<?php echo $a['kode_gejala']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus semua aturan untuk gejala ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
