<?php
require('pdf/fpdf.php');
date_default_timezone_set('Asia/Jakarta');

// Ambil data dari POST
$data_json = $_POST['data_struk'] ?? '[]';
$UangBayar = isset($_POST['UangBayar']) ? (int) str_replace('.', '', $_POST['UangBayar']) : 0;
$kembalian = isset($_POST['kembalian']) ? (int) str_replace('.', '', $_POST['kembalian']) : 0;
$data_struk = json_decode($data_json, true);

// Set header sebelum output
header("Content-Type: application/pdf");

$pdf = new FPDF('P', 'mm', [58, 200]); // ukuran struk kecil
$pdf->AddPage();
$pdf->SetFont('Courier', '', 8);

// Header toko
$pdf->Cell(0, 4, 'Dompo$', 0, 1, 'C');
$pdf->Cell(0, 4, 'Program Kasir Toko Anda', 0, 1, 'C');
$pdf->Ln(1);
$pdf->Cell(0, 4, '------------------------------', 0, 1, 'C');
$pdf->Ln(1);

$totalHarga = 0;

if (!empty($data_struk) && is_array($data_struk)) {
    foreach ($data_struk as $item) {
        if (!isset($item['Nama_Produk'], $item['jumlah'], $item['harga'])) continue;

        $nama = $item['Nama_Produk'];
        $qty = $item['jumlah'];
        $harga = $item['harga'];
        $subtotal = $qty * $harga;
        $totalHarga += $subtotal;

        $pdf->Cell(0, 4, strtoupper(substr($nama, 0, 32)), 0, 1);
        $line = sprintf("%2dx Rp%5s = Rp%6s", $qty, number_format($harga, 0, ',', '.'), number_format($subtotal, 0, ',', '.'));
        $pdf->Cell(0, 4, $line, 0, 1);
    }
} else {
    $pdf->Cell(0, 4, 'Struk kosong!', 0, 1, 'C');
}

$pdf->Ln(1);
$pdf->Cell(0, 4, '------------------------------', 0, 1, 'C');

// Total
$pdf->SetFont('Courier', 'B', 9);
$pdf->Ln(1);
$pdf->Cell(0, 4, 'Total: Rp ' . number_format($totalHarga, 0, ',', '.'), 0, 1, 'R');
$pdf->Cell(0, 4, 'Uang Dibayar : Rp ' . number_format($UangBayar, 0, ',', '.'), 0, 1, 'R');
$pdf->Cell(0, 4, 'Kembalian : Rp ' . number_format($kembalian, 0, ',', '.'), 0, 1, 'R');

// Footer
$pdf->Ln(2);
$pdf->Cell(0, 4, '------------------------------', 0, 1, 'C');
$pdf->SetFont('Courier', '', 8);
$pdf->Cell(0, 4, 'Terima kasih!', 0, 1, 'C');
$pdf->Cell(0, 4, date('d/m/Y H:i:s'), 0, 1, 'C');

// Output PDF
$pdf->Output();
exit;
?>
