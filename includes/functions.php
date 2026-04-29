<?php
/**
 * ============================================
 * FUNCTIONS.PHP - Fungsi-fungsi Sistem Pakar
 * ============================================
 * File ini berisi fungsi untuk menghitung diagnosis
 * menggunakan metode Dempster-Shafer Theory (DST)
 */

/**
 * Ambil aturan berdasarkan kode gejala
 * Contoh: Gejala G01 bisa terkait dengan P01, P03 (2 baris data)
 */
function get_rules_by_symptom($pdo, $id_gejala) {
    $stmt = $pdo->prepare("SELECT kode_penyakit, mb FROM basis_pengetahuan WHERE kode_gejala = ?");
    $stmt->execute([$id_gejala]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Ambil semua ID penyakit yang aktif
 */
function get_all_diseases($pdo) {
    $stmt = $pdo->query("SELECT id_penyakit FROM penyakit WHERE status='aktif'");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * ============================================
 * FUNGSI UTAMA: Hitung Diagnosis dengan DST
 * ============================================
 * 
 * Cara kerja sederhana:
 * 1. Ambil gejala pertama -> dapat nilai belief (MB)
 * 2. Gabungkan dengan gejala kedua menggunakan rumus DST
 * 3. Ulangi sampai semua gejala terproses
 * 4. Urutkan hasil dari tertinggi ke terendah
 * 
 * @param $pdo - Koneksi database
 * @param $selected_gejala - Array kode gejala yang dipilih user
 * @return Array hasil diagnosis dengan nilai belief
 */
function calculate_dst($pdo, $selected_gejala) {
    
    // Ambil semua penyakit sebagai "Frame of Discernment" (Theta)
    $all_diseases = get_all_diseases($pdo);
    
    // Tempat menyimpan hasil perhitungan
    $current_mass = [];
    
    // Jika tidak ada gejala, kembalikan kosong
    if (empty($selected_gejala)) {
        return [];
    }

    // ========== LANGKAH 1: Proses Gejala Pertama ==========
    $first_gejala = $selected_gejala[0];
    $rules = get_rules_by_symptom($pdo, $first_gejala);
    
    if (empty($rules)) {
        return []; 
    }

    // Kumpulkan penyakit yang terkait dengan gejala ini
    $diseases = [];
    $belief = 0;
    
    foreach ($rules as $rule) {
        $diseases[] = $rule['kode_penyakit'];
        // Ambil nilai belief tertinggi
        $belief = max($belief, (float) $rule['mb']);
    }
    
    // Simpan mass function awal
    // m({penyakit}) = belief
    // m(Theta) = 1 - belief (ketidakpastian)
    $current_mass[] = [
        'diseases' => $diseases,
        'value' => $belief
    ];
    $current_mass[] = [
        'diseases' => $all_diseases,
        'value' => 1 - $belief
    ];

    // ========== LANGKAH 2: Kombinasi dengan Gejala Berikutnya ==========
    for ($i = 1; $i < count($selected_gejala); $i++) {
        
        $next_gejala = $selected_gejala[$i];
        $rules = get_rules_by_symptom($pdo, $next_gejala);
        
        if (empty($rules)) continue;

        // Kumpulkan penyakit dari gejala ini
        $diseases_next = [];
        $belief_next = 0;
        
        foreach ($rules as $rule) {
            $diseases_next[] = $rule['kode_penyakit'];
            $belief_next = max($belief_next, (float) $rule['mb']);
        }

        // Mass function untuk gejala baru
        $new_mass = [];
        $new_mass[] = ['diseases' => $diseases_next, 'value' => $belief_next];
        $new_mass[] = ['diseases' => $all_diseases, 'value' => 1 - $belief_next];

        // ===== Kombinasi menggunakan Dempster's Rule =====
        $combined = [];
        $conflict = 0;

        foreach ($current_mass as $m1) {
            foreach ($new_mass as $m2) {
                
                // Cari irisan (intersection) kedua himpunan
                $intersection = array_intersect($m1['diseases'], $m2['diseases']);
                $value = $m1['value'] * $m2['value'];

                if (empty($intersection)) {
                    // Tidak ada irisan = konflik
                    $conflict += $value;
                } else {
                    // Ada irisan = simpan hasilnya
                    sort($intersection);
                    $key = implode(',', $intersection);
                    
                    if (!isset($combined[$key])) {
                        $combined[$key] = ['diseases' => $intersection, 'value' => 0];
                    }
                    $combined[$key]['value'] += $value;
                }
            }
        }

        // Normalisasi: bagi dengan (1 - konflik)
        $final_mass = [];
        if ($conflict < 1) {
            $scale = 1 / (1 - $conflict);
            foreach ($combined as $cm) {
                $final_mass[] = [
                    'diseases' => $cm['diseases'],
                    'value' => $cm['value'] * $scale
                ];
            }
        } else {
            return []; // Konflik total
        }

        $current_mass = $final_mass;
    }

    // ========== LANGKAH 3: Urutkan Hasil ==========
    // Dari nilai tertinggi ke terendah
    usort($current_mass, function($a, $b) {
        return $b['value'] <=> $a['value'];
    });

    return $current_mass;
}

/**
 * Buat ID baru otomatis (P01, P02, ... atau G01, G02, ...)
 */
function generate_new_id($pdo, $table, $prefix) {
    $column = ($table == 'penyakit') ? 'id_penyakit' : 'id_gejala';
    
    // Ambil ID terakhir
    $stmt = $pdo->query("SELECT $column FROM $table ORDER BY LENGTH($column) DESC, $column DESC LIMIT 1");
    $last = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($last) {
        // Ambil angka dari ID terakhir, tambah 1
        $number = (int) substr($last[$column], strlen($prefix));
        $new_number = $number + 1;
    } else {
        $new_number = 1;
    }
    
    // Format: P01, P02, P10, dst
    return $prefix . str_pad($new_number, 2, '0', STR_PAD_LEFT);
}
?>
