<?php
include '../config/database.php';
include '../includes/functions.php';
include '../includes/header.php';

// Cek Login: Mencegah akses langsung tanpa login
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    echo "<script>alert('Silakan login terlebih dahulu untuk melihat hasil diagnosis!'); window.location='../auth/login.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['gejala'])) {
    echo "<script>alert('Silakan pilih gejala terlebih dahulu!'); window.location='../index.php';</script>";
    exit;
}

$selected_gejala = $_POST['gejala'];

// Validasi: Minimal 3 gejala agar hasil diagnosis bermakna
if (count($selected_gejala) < 3) {
    echo "<script>alert('Pilih minimal 3 gejala agar diagnosis lebih akurat!'); window.location='../index.php';</script>";
    exit;
}

// Validasi: Batasi maksimal 10 gejala agar hasil akurat
if (count($selected_gejala) > 10) {
    echo "<script>alert('Terlalu banyak gejala! Maksimal pilih 10 gejala yang paling dominan agar diagnosis akurat.'); window.location='../index.php';</script>";
    exit;
}
$results = calculate_dst($pdo, $selected_gejala);

// Inisialisasi variabel
$penyakit_data = null;
$confidence = 0;
$ambiguous_ids = [];

// [OPTIMASI] Cache semua nama penyakit untuk mencegah N+1 Query
$stmt_all = $pdo->query("SELECT id_penyakit, nama_penyakit FROM penyakit");
$disease_cache = [];
while ($row = $stmt_all->fetch(PDO::FETCH_ASSOC)) {
    $disease_cache[$row['id_penyakit']] = $row['nama_penyakit'];
}

// Ambil detail penyakit untuk ditampilkan
if (!empty($results) && isset($results[0])) {
    $top_result = $results[0];
    
    if (isset($top_result['diseases']) && count($top_result['diseases']) == 1) {
        $id_penyakit = $top_result['diseases'][0]; // Ambil elemen pertama dengan index
        $stmt = $pdo->prepare("SELECT * FROM penyakit WHERE id_penyakit = ?");
        $stmt->execute([$id_penyakit]);
        $penyakit_data = $stmt->fetch(PDO::FETCH_ASSOC);
        $confidence = isset($top_result['value']) ? $top_result['value'] * 100 : 0;
    } else {
        // Kasus ambigu (hasil masih berupa himpunan beberapa penyakit)
        $confidence = isset($top_result['value']) ? $top_result['value'] * 100 : 0;
        $ambiguous_ids = isset($top_result['diseases']) ? $top_result['diseases'] : [];
    }
}

// Ambil nama gejala yang dipilih
$selected_gejala_names = [];
foreach ($selected_gejala as $gid) {
    $stmt = $pdo->prepare("SELECT nama_gejala FROM gejala WHERE id_gejala = ?");
    $stmt->execute([$gid]);
    $g = $stmt->fetch();
    if ($g) $selected_gejala_names[] = $g['nama_gejala'];
}

// Siapkan data untuk PDF (di-embed sebagai JSON)
date_default_timezone_set('Asia/Jakarta');
$pdf_data = [
    'nama'       => $_SESSION['user_name'] ?? 'Pengguna',
    'username'   => $_SESSION['username'] ?? '-',
    'tanggal'    => date('d F Y'),
    'waktu'      => date('H:i') . ' WIB',
    'gejala'     => $selected_gejala_names,
    'diagnosis'  => $penyakit_data ? $penyakit_data['nama_penyakit'] : null,
    'confidence' => number_format($confidence, 1),
    'deskripsi'  => $penyakit_data ? $penyakit_data['deskripsi'] : null,
    'saran'      => $penyakit_data ? $penyakit_data['saran_penanganan'] : null,
    'ambigu'     => array_map(fn($pid) => $disease_cache[$pid] ?? $pid, $ambiguous_ids),
    'dst_detail' => array_map(function($res) use ($pdo, $disease_cache) {
        $names = [];
        foreach ($res['diseases'] as $pid) {
            $names[] = $disease_cache[$pid] ?? $pid;
        }
        return ['penyakit' => implode(', ', $names), 'belief' => number_format($res['value'], 4)];
    }, $results),
];
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="result-card animate-in">
                
                <!-- Header Hasil -->
                <div class="result-header">
                    <h4 class="mb-3 fw-bold">Hasil Diagnosis</h4>
                    
                    <?php if (!empty($results) && $penyakit_data): ?>
                        <h2 class="mb-3"><?php echo $penyakit_data['nama_penyakit']; ?></h2>
                        <div class="confidence-badge">
                            Tingkat Keyakinan: <?php echo number_format($confidence, 1); ?>%
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Body Hasil -->
                <div class="p-4 p-md-5">
                    
                    <?php if (empty($results)): ?>
                        <!-- Tidak ada hasil -->
                        <div class="alert alert-warning">
                            <strong>Tidak dapat menentukan diagnosis.</strong><br>
                            Silakan coba lagi dengan gejala yang lebih spesifik.
                        </div>
                        
                    <?php elseif ($penyakit_data): ?>
                        
                        <!-- Gejala yang Dipilih -->
                        <div class="mb-4">
                            <h5 class="section-title">Gejala yang Dipilih</h5>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach($selected_gejala_names as $name): ?>
                                <span class="badge bg-light text-dark border px-3 py-2">
                                    <?php echo $name; ?>
                                </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Deskripsi Penyakit -->
                        <div class="mb-4">
                            <h5 class="section-title">Deskripsi Penyakit</h5>
                            <p class="text-muted" style="line-height: 1.8;">
                                <?php echo nl2br($penyakit_data['deskripsi']); ?>
                            </p>
                        </div>
                        
                        <!-- Saran Penanganan -->
                        <div class="mb-4">
                            <h5 class="section-title">Saran Penanganan</h5>
                            <div class="info-box">
                                <p class="mb-0" style="line-height: 1.8;">
                                    <?php echo nl2br($penyakit_data['saran_penanganan']); ?>
                                </p>
                            </div>
                        </div>
                        
                    <?php else: ?>
                        <!-- Hasil ambigu - beberapa penyakit -->
                        <div class="alert alert-info">
                            <strong>Hasil Belum Spesifik</strong>
                                <p class="mb-2">
                                    Sistem mendeteksi kemungkinan beberapa penyakit dengan keyakinan 
                                    <strong><?php echo number_format($confidence, 1); ?>%</strong>:
                                </p>
                                <ul class="mb-0">
                                    <?php foreach ($ambiguous_ids as $pid): ?>
                                        <li><?php echo $disease_cache[$pid] ?? $pid; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Detail Perhitungan (Accordion) -->
                    <div class="accordion mt-4" id="detailAccordion">
                        <div class="accordion-item border-0">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed bg-light text-muted" 
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#detailCollapse">
                                    <small>Lihat Detail Perhitungan DST</small>
                                </button>
                            </h2>
                            <div id="detailCollapse" class="accordion-collapse collapse" data-bs-parent="#detailAccordion">
                                <div class="accordion-body px-0">
                                    <table class="table table-sm table-hover" style="font-size: 0.85rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Kemungkinan Penyakit</th>
                                                <th>Nilai Belief</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($results as $res): ?>
                                            <tr>
                                                <td>
                                                    <?php 
                                                    if (count($res['diseases']) == count(get_all_diseases($pdo))) {
                                                        echo "Semua Penyakit (Θ)";
                                                    } else {
                                                        $names = [];
                                                        foreach ($res['diseases'] as $pid) {
                                                            $names[] = $disease_cache[$pid] ?? $pid;
                                                        }
                                                        echo implode(", ", $names);
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary">
                                                        <?php echo number_format($res['value'], 4); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tombol Aksi -->
                    <div class="text-center mt-5 d-flex justify-content-center gap-3 flex-wrap">
                        <?php if ($penyakit_data || !empty($ambiguous_ids)): ?>
                        <button onclick="exportPDF()" class="btn btn-submit d-flex align-items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                                <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                            </svg>
                            Export PDF
                        </button>
                        <?php endif; ?>
                        <a href="../index.php" class="btn btn-outline-secondary d-flex align-items-center gap-2" style="padding: 1rem 3rem; font-size: 1.1rem; font-weight: 600; border-radius: 50px;">
                            Konsultasi Ulang
                        </a>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Data untuk PDF -->
<script id="pdfData" type="application/json">
<?php echo json_encode($pdf_data, JSON_UNESCAPED_UNICODE); ?>
</script>

<!-- jsPDF CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

<script>
function exportPDF() {
    const raw = document.getElementById('pdfData').textContent;
    const d = JSON.parse(raw);
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });

    const W = doc.internal.pageSize.getWidth();
    const H = doc.internal.pageSize.getHeight();
    const green = [5, 150, 105];
    const teal  = [13, 148, 136];
    const dark  = [31, 41, 55];
    const muted = [107, 114, 128];
    const light = [240, 253, 244];

    // ── HEADER BANNER ─────────────────────────────────────────
    doc.setFillColor(...green);
    doc.rect(0, 0, W, 33, 'F');

    // Judul
    doc.setTextColor(255, 255, 255);
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(15);
    doc.text('LAPORAN HASIL DIAGNOSIS', W / 2, 14, { align: 'center' });
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(9);
    doc.text('Sistem Pakar Penyakit Puyuh — Metode Dempster-Shafer Theory', W / 2, 21, { align: 'center' });

    // ── INFO IDENTITAS ─────────────────────────────────────────
    let y = 48;
    doc.setFillColor(...light);
    doc.roundedRect(14, y - 5, W - 28, 15, 3, 3, 'F');
    doc.setDrawColor(...green);
    doc.setLineWidth(0.4);
    doc.roundedRect(14, y - 5, W - 28, 15, 3, 3, 'S');

    const col1 = 20, col2 = W / 2 + 5;
    const rowH = 7;
    doc.setFontSize(8);

    // Baris 1
    doc.setFont('helvetica', 'bold'); doc.setTextColor(...muted);
    doc.text('Nama Pengguna', col1, y);
    doc.text('Tanggal Diagnosis', col2, y);
    doc.setFont('helvetica', 'normal'); doc.setTextColor(...dark);
    doc.text(': ' + d.nama, col1 + 32, y);
    doc.text(': ' + d.tanggal, col2 + 37, y);

    y += rowH;
    doc.setFont('helvetica', 'bold'); doc.setTextColor(...muted);
    doc.text('Username', col1, y);
    doc.text('Waktu', col2, y);
    doc.setFont('helvetica', 'normal'); doc.setTextColor(...dark);
    doc.text(': ' + d.username, col1 + 32, y);
    doc.text(': ' + d.waktu, col2 + 37, y);

    // ── GEJALA YANG DIPILIH ───────────────────────────────────
    y += 16;
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(10);
    doc.setTextColor(...green);
    doc.text('Gejala yang Dilaporkan', 14, y);
    doc.setDrawColor(...green);
    doc.setLineWidth(0.5);
    doc.line(14, y + 1.5, W - 14, y + 1.5);

    y += 7;
    const gejalaBullets = d.gejala.map((g, i) => [(i + 1) + '.', g]);
    doc.autoTable({
        startY: y,
        head: [],
        body: gejalaBullets,
        theme: 'plain',
        styles: { fontSize: 9, cellPadding: 1.8, textColor: dark },
        columnStyles: { 0: { cellWidth: 8, fontStyle: 'bold', textColor: green }, 1: { cellWidth: W - 40 } },
        margin: { left: 16, right: 14 },
    });
    y = doc.lastAutoTable.finalY + 8;

    // ── HASIL DIAGNOSIS ───────────────────────────────────────
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(10);
    doc.setTextColor(...green);
    doc.text('Hasil Diagnosis', 14, y);
    doc.setDrawColor(...green);
    doc.line(14, y + 1.5, W - 14, y + 1.5);
    y += 8;

    if (d.diagnosis) {
        // Kotak Diagnosis Utama
        doc.setFillColor(...green);
        doc.roundedRect(14, y, W - 28, 22, 3, 3, 'F');
        doc.setTextColor(255, 255, 255);
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(13);
        doc.text(d.diagnosis, W / 2, y + 9, { align: 'center' });
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(9);
        doc.text('Tingkat Keyakinan (Belief): ' + d.confidence + '%', W / 2, y + 17, { align: 'center' });
        y += 28;

        // Progress bar keyakinan
        const barW = W - 40;
        const fillW = barW * (parseFloat(d.confidence) / 100);
        doc.setFillColor(220, 252, 231);
        doc.roundedRect(20, y, barW, 5, 2, 2, 'F');
        doc.setFillColor(...green);
        doc.roundedRect(20, y, fillW, 5, 2, 2, 'F');
        doc.setTextColor(...muted);
        doc.setFontSize(7);
        doc.text('0%', 20, y + 9);
        doc.text('100%', 20 + barW, y + 9, { align: 'right' });
        y += 16;

    } else if (d.ambigu && d.ambigu.length > 0) {
        doc.setFillColor(254, 243, 199);
        doc.roundedRect(14, y, W - 28, 14 + d.ambigu.length * 7, 3, 3, 'F');
        doc.setTextColor(146, 64, 14);
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(10);
        doc.text('Hasil Belum Spesifik (' + d.confidence + '% keyakinan)', W / 2, y + 8, { align: 'center' });
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(9);
        d.ambigu.forEach((p, i) => {
            doc.text('• ' + p, W / 2, y + 16 + i * 7, { align: 'center' });
        });
        y += 18 + d.ambigu.length * 7;
    }

    // ── DESKRIPSI & SARAN ─────────────────────────────────────
    const checkPage = (needed) => {
        if (y + needed > H - 30) { doc.addPage(); y = 20; }
    };

    if (d.deskripsi) {
        checkPage(30);
        doc.setFont('helvetica', 'bold'); doc.setFontSize(10); doc.setTextColor(...green);
        doc.text('Deskripsi Penyakit', 14, y);
        doc.line(14, y + 1.5, W - 14, y + 1.5);
        y += 7;
        doc.setFont('helvetica', 'normal'); doc.setFontSize(9); doc.setTextColor(...dark);
        const deskLines = doc.splitTextToSize(d.deskripsi, W - 30);
        checkPage(deskLines.length * 5.5 + 5);
        doc.text(deskLines, 14, y);
        y += deskLines.length * 5.5 + 8;
    }

    if (d.saran) {
        checkPage(30);
        doc.setFont('helvetica', 'bold'); doc.setFontSize(10); doc.setTextColor(...green);
        doc.text('Saran Penanganan', 14, y);
        doc.line(14, y + 1.5, W - 14, y + 1.5);
        y += 7;
        // Kotak info
        const saranLines = doc.splitTextToSize(d.saran, W - 40);
        const boxH = saranLines.length * 5.5 + 10;
        checkPage(boxH + 5);
        doc.setFillColor(...light);
        doc.setDrawColor(...green);
        doc.setLineWidth(0);
        doc.roundedRect(14, y - 3, W - 28, boxH, 3, 3, 'F');
        doc.setFillColor(...green);
        doc.rect(14, y - 3, 3, boxH, 'F');
        doc.setFont('helvetica', 'normal'); doc.setFontSize(9); doc.setTextColor(...dark);
        doc.text(saranLines, 22, y + 4);
        y += boxH + 8;
    }

    // ── TABEL DETAIL DST ──────────────────────────────────────
    if (d.dst_detail && d.dst_detail.length > 0) {
        checkPage(40);
        doc.setFont('helvetica', 'bold'); doc.setFontSize(10); doc.setTextColor(...green);
        doc.text('Detail Perhitungan Dempster-Shafer', 14, y);
        doc.line(14, y + 1.5, W - 14, y + 1.5);
        y += 5;
        doc.autoTable({
            startY: y,
            head: [['Kemungkinan Penyakit', 'Nilai Belief (m)']],
            body: d.dst_detail.map(r => [r.penyakit, r.belief]),
            theme: 'grid',
            headStyles: { fillColor: green, textColor: 255, fontStyle: 'bold', fontSize: 9 },
            bodyStyles: { fontSize: 8.5, textColor: dark },
            alternateRowStyles: { fillColor: light },
            columnStyles: { 1: { halign: 'center', fontStyle: 'bold' } },
            margin: { left: 14, right: 14 },
        });
        y = doc.lastAutoTable.finalY + 10;
    }

    // ── DISCLAIMER ────────────────────────────────────────────
    checkPage(30);
    y += 1;
    doc.setDrawColor(220, 220, 220);
    doc.setLineWidth(0.3);
    doc.line(14, y, W - 14, y);
    y += 7;

    doc.setFontSize(8.5); doc.setTextColor(...muted); doc.setFont('helvetica', 'italic');
    doc.text('Dokumen ini digenerate secara otomatis oleh Sistem Pakar Penyakit Puyuh.', W / 2, y, { align: 'center' });
    doc.text('Hasil diagnosis bersifat sebagai rekomendasi awal. Konsultasikan dengan dokter hewan untuk penanganan lebih lanjut.', W / 2, y + 4, { align: 'center' });

    // ── FOOTER STRIPE ─────────────────────────────────────────
    doc.setFillColor(...green);
    doc.rect(0, H - 8, W, 8, 'F');
    doc.setTextColor(255, 255, 255);
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(7);
    doc.text('SP Puyuh — Sistem Pakar Diagnosis Penyakit Burung Puyuh', W / 2, H - 3, { align: 'center' });

    // Simpan PDF
    const filename = 'Laporan_Diagnosis_' + d.nama.replace(/\s+/g, '_') + '_' + d.tanggal.replace(/\s+/g, '-') + '.pdf';
    doc.save(filename);
}
</script>

<?php include '../includes/footer.php'; ?>
