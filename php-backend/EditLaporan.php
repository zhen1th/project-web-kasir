<?php
include "service/dbkeuangan1.php";
$kode = $_GET['kode'];
$query = mysqli_query($koneksidata, "SELECT * FROM laporan_analitik WHERE Id_Pemasukkan = '$kode'");
$data = mysqli_fetch_array($query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data</title>
    <!-- FAVICON -->
    <link href="Assets/Dompo$Hitam.png" rel="icon"
        media="(prefers-color-scheme: light)" />

    <link href="Assets/Dompo$Putih.png" rel="icon"
        media="(prefers-color-scheme: dark)" />
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h3 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            color: black;
            font-weight: bold;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
        }

        button {
            background-color: #2c2c2c;
            border: none;
            color: white;
            padding: 12px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            transition: background 0.2s ease-in-out;
        }

        button:hover {
            background-color: #f9f9f9;
            color: black;
        }

        .editnotif {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .edit-content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            font-weight: bold;
            width: 300px;
        }

        .close-btn {
            background-color: #212529;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
            border-radius: 6px;
            margin-top: 10px;
        }

        .close-btn:hover {
            background-color: #f8f9fa;
            color: black;
        }
    </style>
</head>

<body>

    <div class="container">
        <a href="Laporan.php" class="back-link">← Kembali</a>
        <h3>Edit Data Laporan</h3>

        <form method="post">
            <input type="text" placeholder="Masukkan Nominal" name="Nominal_Pemasukkan" required
                value="<?php echo htmlspecialchars($data['Nominal_Pemasukkan']); ?>">
            <button type="submit" name="Simpan">Simpan</button>
        </form>
    </div>

    <div class="editnotif" id="BerhasilEdit">
        <div class="edit-content">
            <p>Data Berhasil Diubah!</p>
            <button class="close-btn" onclick="closeModal()">Sip!</button>
        </div>
    </div>

    <script>
        function showModal() {
            document.getElementById("BerhasilEdit").style.display = "flex";
        }

        function closeModal() {
            document.getElementById("BerhasilEdit").style.display = "none";
            window.location.href = "Laporan.php"; // Redirect ke halaman laporan
        }
    </script>
</body>

</html>

<?php
if (isset($_POST['Simpan'])) {
    $nominal_pemasukkan = $_POST['Nominal_Pemasukkan'];
    mysqli_query($koneksidata, "UPDATE laporan_analitik SET Nominal_Pemasukkan = '$nominal_pemasukkan' WHERE Id_Pemasukkan = '$kode'")
        or die(mysqli_error($koneksidata));

    if (mysqli_affected_rows($koneksidata) > 0) {
        echo '<script>
        showModal();
        </script>';
    }
}
?>