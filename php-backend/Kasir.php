<?php

use Dom\Mysql;

session_start();

// Periksa apakah sudah ada session yang valid
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    $user_id = $_SESSION['user_id'];
}
// Jika ada token di URL (saat pertama kali login)
else if (!empty($_GET['token'])) {
    $token = $_GET['token'];

    // Verifikasi token dengan Node.js
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost:3000/verify-token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['token' => $token]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    if (!$result || !$result['valid']) {
        // Token tidak valid, redirect ke login
        header('Location: http://localhost:3000/login?redirect=' . basename($_SERVER['PHP_SELF']));
        exit;
    }

    // Token valid, simpan informasi user di session
    $_SESSION['loggedin'] = true;
    $_SESSION['username'] = $result['username'];
    $_SESSION['user_id'] = $result['user_id'];
    $_SESSION['login_time'] = time();

    $user_id = $_SESSION['user_id'];
}
// Jika tidak ada session dan tidak ada token
else {
    // Redirect ke login dengan parameter redirect ke Dashboard.php
    header('Location: http://localhost:3000/login?redirect=Dashboard.php');
    exit;
}

// Koneksi database dompos
$host = "localhost";
$user = "root";
$password = "";
$dbname = "dompos";

$koneksiDatabase = new mysqli($host, $user, $password, $dbname);

if ($koneksiDatabase->connect_error) {
    die("Koneksi database gagal: " . $koneksiDatabase->connect_error);
}

// Kode Transaksi Otomatis
// Menyeleksi Kolom Kode_Pemasukkan Dari Tabel pemasukkan Dimana user_id Diambil Dari Session
    $AmbilKode = mysqli_query(
        $koneksiDatabase,
        "SELECT Kode_Pemasukkan 
        FROM pemasukkan 
        WHERE user_id = '{$_SESSION['user_id']}'
        ORDER BY Kode_Pemasukkan DESC 
        LIMIT 1"
    );

        $User  = str_pad($_SESSION['user_id'], 3, '0', STR_PAD_LEFT);
        $Tahun = date('y');
        $Bulan = date('m');

        $Data     = mysqli_fetch_assoc($AmbilKode);
        $LastCode = $Data['Kode_Pemasukkan'] ?? null;

        if ($LastCode) {
            $DbYear  = substr($LastCode, 4, 2);
            $DbMonth = substr($LastCode, 6, 2);
            $OldCode = substr($LastCode, 8, 3);

            if ($Tahun == $DbYear && $Bulan == $DbMonth) {
                $NewCode = str_pad((int)$OldCode + 1, 3, '0', STR_PAD_LEFT);
            } else {
                $NewCode = "001";
            }
        } else {
            $NewCode = "001";
        }

        $kodeTransaksi = "D" . $User . $Tahun . $Bulan . $NewCode;


?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Transaksi - Dompo$</title>

    <!-- FAVICON -->
    <link href="Assets/Dompo$Hitam.png" rel="icon" media="(prefers-color-scheme: light)" />

    <link href="Assets/Dompo$Putih.png" rel="icon" media="(prefers-color-scheme: dark)" />

    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
    /* CSS tetap sama seperti sebelumnya */
    body {
        height: 100vh;
        margin: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #fbebdaff;
    }

    .topbar {
        height: 70px;
        background-color: #005246;
        color: white;
        padding: 0 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom-left-radius: 20px;
        border-bottom-right-radius: 20px;
    }

    .topbar a {
        width: 10px;
    }

    .topbar img {
        width: 140px;
        height: 40px;
        margin-left: 1090px;
    }


    .container-full {
        height: calc(100vh - 50px);
        display: flex;
        gap: 1rem;
        overflow: hidden;
        padding: 1rem;
        box-sizing: border-box;
    }

    .kiri {
        flex: 1;
        min-width: 400px;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        background-color: #ffffffff;
        border-radius: 10px;
        padding: 15px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .konten-kiri {
        flex: 1;
        overflow-y: auto;
        padding: 10px;
    }

    .bawah-kiri {
        margin-top: auto;
        padding-top: 15px;
        border-top: 1px solid #dee2e6;
    }

    .kiri h5 {
        font-weight: 700;
        margin-top: 10px;
    }

    .kanan {
        flex: 2;
        background-color: #005246;
        color: white;
        display: flex;
        flex-direction: column;
        padding: 1rem;
        border-radius: 10px;
        box-sizing: border-box;
        overflow: hidden;
    }

    .kanan button {
        background-color: #F37721;
        color: #ffffffff;
        border: none;
    }

    .kanan button:hover {
        background-color: #de5d01ff;
        color: #ffffffff;
        border: none;
    }

    .kategori-container {
        padding: 10px;
        background-color: #00473cff;
        border-radius: 8px;
        margin-bottom: 15px;
    }

    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 42px;
        overflow-y: auto;
        flex: 1;
        padding: 5px;
        align-content: start;
    }

    .product-grid button {
        width: 180px;
        font-size: 15px;
        background-color: #ffffffff;
        color: black;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    }

    .product-grid button:hover {
        background-color: #ffffffff;
        color: black;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
    }

    .product-btn {
        width: 100%;
        height: 80px;
        font-size: 14px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        margin: 0;
        padding: 5px;
        min-height: 80px;
        box-sizing: border-box;
    }

    .product-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .tab-btn {
        min-width: 100px;
        transition: all 0.3s;
    }

    .tab-btn.active {
        background-color: #d15700;
        color: white;
        border-color: #F37721;
    }

    .struk-list {
        background-color: white;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .struk-item {
        margin-bottom: 10px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }

    .total-display {
        font-size: 1.2rem;
        font-weight: bold;
        color: #333;
        padding: 10px;
        background-color: #e9ecef;
        border-radius: 5px;
    }

    .payment-input {
        margin-bottom: 15px;
    }

    .btn-action {
        font-weight: 500;
        letter-spacing: 0.5px;
    }

    /* Scrollbar styling */
    ::-webkit-scrollbar {
        width: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Validasi Pembayaran */
    #customAlert {
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        background-color: #f1f1f1;
        color: #2c2c2c;
        padding: 16px 24px;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        font-weight: bold;
        z-index: 1000;
        transition: opacity 0.3s ease;
    }

    .hidden {
        display: none;
    }

    /* Validasi Batal */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 999;
    }

    .modal-box {
        background-color: white;
        padding: 24px;
        border-radius: 16px;
        text-align: center;
        max-width: 400px;
        width: 90%;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }

    .modal-title {
        font-size: 1.5rem;
        font-weight: bold;
        margin-bottom: 12px;
    }

    .modal-title.warning {
        color: #2c2c2c;
    }

    .modal-title.confirm {
        color: #2c2c2c;
    }

    .modal-button {
        padding: 10px 20px;
        margin: 10px 5px 0;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: bold;
        transition: background-color 0.2s ease;
    }

    .modal-button.danger {
        background-color: #e53935;
        color: white;
    }

    .modal-button.danger:hover {
        background-color: #c62828;
    }

    .modal-button.confirm {
        background-color: rgb(17, 168, 27);
        color: white;
    }

    .modal-button.confirm:hover {
        background-color: #55a630;
    }

    .modal-actions {
        display: flex;
        justify-content: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    /* Validasi Bayar */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 999;
    }

    .modal-box {
        background-color: white;
        padding: 24px;
        border-radius: 16px;
        text-align: center;
        max-width: 400px;
        width: 90%;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }

    .modal-title.warning {
        color: #2c2c2c;
        font-size: 1.5rem;
        font-weight: bold;
        margin-bottom: 12px;
    }

    .modal-button.danger {
        background-color: #e53935;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        margin-top: 12px;
        cursor: pointer;
        font-weight: bold;
    }

    .modal-button.danger:hover {
        background-color: #c62828;
    }

    .modal-actions {
        display: flex;
        justify-content: center;
        gap: 12px;
        margin-top: 20px;
    }

    .modal-button {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        background-color: #ccc;
        color: #000;
        transition: background 0.2s;
    }

    .modal-button.danger {
        background-color: #e53935;
        color: white;
    }

    .modal-button:hover {
        opacity: 0.9;
    }

    /* Validasi Proses */
    .alert-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        color: #2c2c2c;
        font-weight: bold;
    }

    .alert-box {
        background: white;
        padding: 24px 32px;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        text-align: center;
        max-width: 400px;
        width: 80%;
    }

    .alert-close-btn {
        margin-top: 16px;
        padding: 8px 20px;
        background: #55a630;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
    }

    .alert-close-btn:hover {
        background: #55a630;
    }

    .Transaksi label {
        font-weight: 500;
    }

    .Transaksi input {
        background-color: #ffffff;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
    </style>
</head>

<body>
    <!-- TOPBAR -->
    <div class="topbar">
        <a href="Dashboard.php" id="home-btn"><img src="assets/image/Logo Dompos Navbar Orange.png"></a>
    </div>

    <!-- BODY -->
    <div class="container-full">
        <!-- KIRI - Bagian Transaksi -->
        <div class="kiri">
            <div class="konten-kiri">

                <!-- Kode Transaksi Read Only -->
                <div class="Transaksi">
                    <label>Kode Transaksi</label> <br>
                    <input type="text" name="Kode_Pemasukkan" class="form-control" value="<?php echo $kodeTransaksi ?>"
                        readonly>
                </div>

                <!-- Daftar Belanja -->
                <h5 class="mb-3">DETAIL PESANAN</h5>
                <div id="struk-list" class="struk-list"></div>

                <!-- Total Pembayaran -->
                <div class="total-display d-flex justify-content-between align-items-center">
                    <span>Total:</span>
                    <span id="total-harga">Rp 0</span>
                </div>

                <!-- Input Pembayaran -->
                <div class="payment-input">
                    <label for="UangBayar" class="form-label">Jumlah Pembayaran</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" class="form-control" id="UangBayar" placeholder="Masukkan jumlah pembayaran">
                    </div>
                </div>

                <!-- Kembalian -->
                <div class="mb-3">
                    <label class="form-label">Kembalian:</label>
                    <div class="alert alert-info p-2" id="kembalian">-</div>
                </div>
            </div>

            <div class="bawah-kiri">
                <div class="d-flex gap-2">
                    <button id="batal-btn" class="btn btn-danger btn-action flex-fill">
                        <i class="bi bi-x-circle"></i> Batal
                    </button>
                    <button id="bayar-btn" class="btn btn-success btn-action flex-fill">
                        <i class="bi bi-credit-card"></i> Bayar
                    </button>
                </div>
            </div>
        </div>

        <!-- Validasi Pembayaran -->
        <div id="customAlert" class="hidden">
            <p id="alertMessage"></p>
        </div>

        <!-- Validasi Batal -->
        <!-- Modal Peringatan Tidak Ada Transaksi -->
        <div id="noTransactionModal" class="modal-overlay">
            <div class="modal-box">
                <h2 class="modal-title warning">Tidak ada transaksi!</h2>
                <p>Belum ada item yang ditambahkan ke transaksi.</p>
                <button onclick="closeNoTransactionModal()" class="modal-button danger">Tutup</button>
            </div>
        </div>

        <!-- Modal Konfirmasi Pembatalan -->
        <div id="confirmCancelModal" class="modal-overlay">
            <div class="modal-box">
                <h2 class="modal-title confirm">Batalkan Transaksi?</h2>
                <p>Apakah Anda yakin ingin membatalkan transaksi ini?</p>
                <div class="modal-actions">
                    <button onclick="cancelTransaction()" class="modal-button confirm">Ya, Batalkan</button>
                    <button onclick="closeConfirmCancelModal()" class="modal-button neutral">Tidak</button>
                </div>
            </div>
        </div>

        <!-- Validasi Bayar -->
        <!-- Modal Tidak Ada Transaksi untuk Dibayar -->
        <div id="noPaymentModal" class="modal-overlay">
            <div class="modal-box">
                <h2 class="modal-title warning">Transaksi Kosong!</h2>
                <p>Tidak ada transaksi yang bisa dibayar.</p>
                <button onclick="closeNoPaymentModal()" class="modal-button danger">Tutup</button>
            </div>
        </div>

        <!-- Validasi Home -->
        <!-- Modal Konfirmasi Batal Transaksi -->
        <div id="confirmCancelModal" class="modal-overlay">
            <div class="modal-box">
                <h2 class="modal-title danger">Batalkan Transaksi?</h2>
                <p>Apakah Anda yakin ingin membatalkan transaksi?</p>
                <div class="modal-actions">
                    <button onclick="confirmCancelTransaction()" class="modal-button danger">Ya, Batalkan</button>
                    <button onclick="closeConfirmModal()" class="modal-button">Tidak</button>
                </div>
            </div>
        </div>

        <!-- Validasi Proses -->
        <div id="alertModal" class="alert-modal">
            <div class="alert-box">
                <p id="alertMessage">Pembayaran Berhasil Dicatat!</p>
                <button onclick="closeAlert()" class="alert-close-btn">Sip!</button>
            </div>
        </div>

        <!-- KANAN - Daftar Produk -->
        <div class="kanan">
            <!-- Pencarian Produk -->
            <form method="POST" class="mb-3 d-flex">
                <input type="text" class="form-control me-2" placeholder="Cari Produk..." name="cariproduk" value="<?php if (isset($_POST['cariproduk'])) {
                echo htmlspecialchars($_POST['cariproduk']);
                } ?>" />
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Cari
                </button>
            </form>
            <!-- Kategori Produk -->
            <div class="kategori-container">
                <h5 class="text-white mb-3">Kategori Produk</h5>
                <form method="GET" class="d-flex flex-wrap gap-2">
                    <button type="submit" name="Makanan" value="Makanan"
                        class="btn btn-light tab-btn <?php echo isset($_GET['Makanan']) ? 'active' : ''; ?>">
                        <i class="bi bi-egg-fried"></i> Makanan
                    </button>
                    <button type="submit" name="Minuman" value="Minuman"
                        class="btn btn-light tab-btn <?php echo isset($_GET['Minuman']) ? 'active' : ''; ?>">
                        <i class="bi bi-cup-straw"></i> Minuman
                    </button>
                    <button type="submit" name="Snack" value="Snack"
                        class="btn btn-light tab-btn <?php echo isset($_GET['Snack']) ? 'active' : ''; ?>">
                        <i class="bi bi-cookie"></i> Snack
                    </button>
                    <button type="submit" name="Lain-Lain" value="Lain-Lain"
                        class="btn btn-light tab-btn <?php echo isset($_GET['Lain-Lain']) ? 'active' : ''; ?>">
                        <i class="bi bi-grid"></i> Lain-Lain
                    </button>
                </form>
            </div>

            <!-- Daftar Produk -->
            <div class="product-grid" id="product-grid">
                <?php
                // Fungsi untuk menampilkan produk
                function displayProducts($query, $koneksiDatabase)
                {
                    $tampilkandata = mysqli_query($koneksiDatabase, $query);

                    if (mysqli_num_rows($tampilkandata) > 0) {
                        while ($row = mysqli_fetch_assoc($tampilkandata)) {
                            echo "<button class='btn btn-outline-light product-btn Nama_Produk-btn' 
                    data-id_produk='" . htmlspecialchars($row['Id_Produk']) . "' 
                    data-nama='" . htmlspecialchars($row['Nama_Produk']) . "' 
                    data-harga='" . htmlspecialchars($row['Harga_Produk']) . "'>
                    <div style='font-weight:500;'>" . htmlspecialchars($row['Nama_Produk']) . "</div>
                    <div style='font-size:0.8rem;'>Rp " . number_format($row['Harga_Produk'], 0, ',', '.') . "</div>
                  </button>";
                        }
                    } else {
                        echo "<div class='col-12 text-center text-white py-4'>
                    <i class='bi bi-exclamation-circle' style='font-size:2rem;'></i>
                    <p>Tidak ada produk ditemukan</p>
                  </div>";
                    }
                }

                // Logika menampilkan produk berdasarkan kategori atau pencarian
                if (isset($_POST['cariproduk'])) {
                    $searching = mysqli_real_escape_string($koneksiDatabase, $_POST['cariproduk']);
                    $syntaxquery = "SELECT * FROM newproduct WHERE user_id = $user_id AND Nama_Produk LIKE '%$searching%'";
                    displayProducts($syntaxquery, $koneksiDatabase);
                } elseif (isset($_GET['Makanan'])) {
                    displayProducts("SELECT * FROM newproduct WHERE user_id = $user_id AND Kategori = 'Makanan'", $koneksiDatabase);
                } elseif (isset($_GET['Minuman'])) {
                    displayProducts("SELECT * FROM newproduct WHERE user_id = $user_id AND Kategori = 'Minuman'", $koneksiDatabase);
                } elseif (isset($_GET['Snack'])) {
                    displayProducts("SELECT * FROM newproduct WHERE user_id = $user_id AND Kategori = 'Snack'", $koneksiDatabase);
                } elseif (isset($_GET['Lain-Lain'])) {
                    displayProducts("SELECT * FROM newproduct WHERE user_id = $user_id AND Kategori = 'Lain-Lain'", $koneksiDatabase);
                } else {
                    displayProducts("SELECT * FROM newproduct WHERE user_id = $user_id", $koneksiDatabase);
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Hidden form for printing receipt -->
    <form id="formCetak" action="CetakStruk.php" method="POST" target="_blank">
        <input type="hidden" name="Kode_Pemasukkan" value="<?php echo $kodeTransaksi ?>">
        <input type="hidden" name="data_struk" id="data_struk">
        <input type="hidden" name="UangBayar" id="UangBayarHidden">
        <input type="hidden" name="kembalian" id="kembalianHidden">
    </form>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    // Format Rupiah
    function formatRupiah(angka) {
        return angka.toLocaleString('id-ID', {
            style: 'currency',
            currency: 'IDR'
        });
    }

    let struk = {};
    let totalHarga = 0;

    // Initialize on page load
    document.addEventListener("DOMContentLoaded", function() {
        // Load saved struk from localStorage
        const savedStruk = localStorage.getItem('strukData');
        const savedTotalHarga = localStorage.getItem('totalHarga');

        if (savedStruk) {
            struk = JSON.parse(savedStruk);
            totalHarga = parseInt(savedTotalHarga);
            renderStruk();
        }

        // Add event listeners to product buttons
        document.querySelectorAll('.Nama_Produk-btn').forEach(button => {
            button.addEventListener('click', () => {
                const nama = button.dataset.nama;
                const harga = parseInt(button.dataset.harga);

                if (!struk[nama]) {
                    struk[nama] = {
                        jumlah: 1,
                        harga
                    };
                } else {
                    struk[nama].jumlah += 1;
                }

                renderStruk();
            });
        });
    });

    // Render the struk list
    function renderStruk() {
        const strukList = document.getElementById('struk-list');
        strukList.innerHTML = '';
        totalHarga = 0;

        const strukArray = Object.entries(struk).map(([nama, data]) => ({
            nama,
            jumlah: data.jumlah,
            harga: data.harga,
            subtotal: data.jumlah * data.harga
        }));

        for (let i = 0; i < strukArray.length; i++) {
            const item = document.createElement('div');
            item.className = 'struk-item';
            item.innerHTML = `
          <div style="margin-bottom: 5px;">
            <strong>${strukArray[i].nama}</strong><br>
            <button class="btn btn-sm btn-outline-secondary" onclick="ubahQty('${strukArray[i].nama}', -1)">-</button>
            <span> ${strukArray[i].jumlah} </span>
            <button class="btn btn-sm btn-outline-secondary" onclick="ubahQty('${strukArray[i].nama}', 1)">+</button>
            x ${formatRupiah(strukArray[i].harga)} = <strong>${formatRupiah(strukArray[i].subtotal)}</strong>
            <button class="btn btn-sm btn-outline-danger" onclick="hapusItem('${strukArray[i].nama}')">Hapus</button>
          </div>
        `;
            strukList.appendChild(item);
            totalHarga += strukArray[i].subtotal;
        }

        document.getElementById('total-harga').textContent = totalHarga.toLocaleString('id-ID');

        // Save struk to localStorage
        localStorage.setItem('strukData', JSON.stringify(struk));
        localStorage.setItem('totalHarga', totalHarga);
    }

    // Change quantity
    function ubahQty(nama, delta) {
        if (struk[nama]) {
            struk[nama].jumlah += delta;
            if (struk[nama].jumlah <= 0) {
                delete struk[nama];
            }
            renderStruk();
        }
    }

    // Remove item
    function hapusItem(nama) {
        if (struk[nama]) {
            delete struk[nama];
            renderStruk();
        }
    }

    // Calculate change
    document.getElementById('UangBayar').addEventListener('input', function() {
        const totalText = document.getElementById('total-harga').textContent.replace(/\D/g, '');
        const total = parseInt(totalText, 10) || 0;

        const bayarText = this.value.replace(/\D/g, '');
        const bayar = parseInt(bayarText, 10) || 0;

        const kembalian = bayar - total;

        document.getElementById('kembalian').textContent =
            kembalian >= 0 ? `Rp ${kembalian.toLocaleString('id-ID')}` : 'Uang kurang';
    });


    // Tombol Home
    document.getElementById("home-btn").addEventListener("click", function(event) {
        if (totalHarga === 0) {
            // Tidak ada transaksi, langsung ke dashboard
            return;
        }

        event.preventDefault(); // Cegah langsung navigasi
        document.getElementById("confirmCancelModal").style.display = "flex";
    });

    // Fungsi untuk menutup modal konfirmasi
    function closeConfirmModal() {
        document.getElementById("confirmCancelModal").style.display = "none";
    }

    // Fungsi untuk mengonfirmasi pembatalan transaksi
    function confirmCancelTransaction() {
        // Hapus data lokal transaksi
        localStorage.removeItem("strukData");
        localStorage.removeItem("totalHarga");
        localStorage.removeItem("kembalian");
        localStorage.removeItem("UangBayar");

        // Reset variabel dan UI
        struk = {};
        totalHarga = 0;
        renderStruk();
        document.getElementById("UangBayar").value = "";
        document.getElementById("kembalian").textContent = "-";

        // Tutup modal
        closeConfirmModal();

        // Arahkan ke dashboard
        window.location.href = "Dashboard.php";
    }

    // Cancel button
    document.getElementById("batal-btn").addEventListener("click", function() {
        if (totalHarga === 0) {
            document.getElementById("noTransactionModal").style.display = "flex";
            return;
        }
        document.getElementById("confirmCancelModal").style.display = "flex";
    });

    function closeNoTransactionModal() {
        document.getElementById("noTransactionModal").style.display = "none";
    }

    function closeConfirmCancelModal() {
        document.getElementById("confirmCancelModal").style.display = "none";
    }

    function cancelTransaction() {
        localStorage.removeItem("strukData");
        localStorage.removeItem("totalHarga");
        localStorage.removeItem("kembalian");
        localStorage.removeItem("UangBayar");

        struk = {};
        totalHarga = 0;
        renderStruk();
        document.getElementById("UangBayar").value = "";
        document.getElementById("kembalian").textContent = "-";

        closeConfirmCancelModal();
    }

    function showCustomAlert(message) {
        const alertBox = document.getElementById("customAlert");
        const alertMessage = document.getElementById("alertMessage");

        alertMessage.textContent = message;
        alertBox.style.display = "block";

        // Sembunyikan otomatis setelah 3 detik
        setTimeout(() => {
            alertBox.style.display = "none";
        }, 3000);
    }

    // Validasi saat klik tombol Bayar
    document.getElementById("bayar-btn").addEventListener("click", function() {
        const uangBayarValue = document.getElementById("UangBayar").value;

        if (!uangBayarValue || parseInt(uangBayarValue.replace(/\D/g, '')) < totalHarga) {
            showCustomAlert("Jumlah pembayaran tidak valid atau kurang!");
            return;
        }
    });

    // Pay button
    document.getElementById("bayar-btn").addEventListener("click", function() {
        if (totalHarga === 0) {
            document.getElementById("noPaymentModal").style.display = "flex";
            return;
        }

        const uangBayarValue = document.getElementById('UangBayar').value.replace(/\D/g, '');
        const kembalianValue = document.getElementById('kembalian').textContent;

        if (!uangBayarValue || parseInt(uangBayarValue) < totalHarga) {
            showCustomAlert("JUMLAH PEMBAYARAN TIDAK VALID ATAU KURANG!");
            return;
        }

        document.getElementById('UangBayarHidden').value = uangBayarValue;
        document.getElementById('kembalianHidden').value = kembalianValue.replace(/\D/g, '');

        const kodetransaksi = document.querySelector('input[name="Kode_Pemasukkan"]').value;

        const transaksiData = {
            total: totalHarga
        };

        fetch("ProsesBayar.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    Kode_Pemasukkan: "<?= $kodeTransaksi ?>",
                    total: totalHarga
                })
            })

            .then(response => response.text())
            .then(result => {
                let [status, nextKode] = result.split("|");

                showAlert(status); // tetap pakai popup kamu

                if (status === "OK" && nextKode) {
                    document.querySelector('input[name="Kode_Pemasukkan"]').value = nextKode;
                }

                const strukData = localStorage.getItem('strukData');
                if (!strukData) {
                    showAlert("Struk tidak tersedia!");
                    return;
                }


                try {
                    const parsed = JSON.parse(strukData);
                    const formattedData = Object.entries(parsed).map(([nama, data]) => ({
                        Nama_Produk: nama,
                        jumlah: data.jumlah,
                        harga: data.harga
                    }));

                    document.getElementById('data_struk').value = JSON.stringify(formattedData);
                    document.getElementById('formCetak').submit();

                    // Reset data
                    localStorage.removeItem("strukData");
                    localStorage.removeItem("totalHarga");
                    localStorage.removeItem("kembalian");
                    localStorage.removeItem("UangBayar");

                    document.getElementById("UangBayar").value = "";
                    document.getElementById("kembalian").innerText = "-";

                    struk = {};
                    totalHarga = 0;
                    renderStruk();
                } catch (e) {
                    showAlert("Gagal parsing struk.");
                }
            })
            .catch(error => showAlert("Terjadi kesalahan: " + error));
    });


    // Fungsi untuk menutup modal tidak ada transaksi
    function closeNoPaymentModal() {
        document.getElementById("noPaymentModal").style.display = "none";
    }

    function showAlert(message) {
        document.getElementById("alertMessage").textContent = message;
        document.getElementById("alertModal").style.display = "flex";
    }

    function closeAlert() {
        document.getElementById("alertModal").style.display = "none";
    }

    // Tambahkan token ke URL jika ada di localStorage
    document.addEventListener('DOMContentLoaded', function() {
        const token = localStorage.getItem('dompos_token');
        if (token) {
            // Tambahkan token ke semua form action
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                const url = new URL(form.action, window.location.href);
                url.searchParams.set('token', token);
                form.action = url.toString();
            });

            // Tambahkan token ke semua link
            const links = document.querySelectorAll('a');
            links.forEach(link => {
                if (link.href.includes('Dashboard.php') ||
                    link.href.includes('Kasir.php') ||
                    link.href.includes('produks.php') ||
                    link.href.includes('KeuanganKasir.php')) {

                    const url = new URL(link.href);
                    url.searchParams.set('token', token);
                    link.href = url.toString();
                }
            });
        }
    });
    </script>
</body>

</html>