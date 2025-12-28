<?php
$hostname = "localhost";
$username = "root";
$password = "";
$databasename = "projectkasir_keuangan";
$koneksidata = mysqli_connect($hostname,$username,$password,$databasename);

if(mysqli_connect_error()){
    echo "Koneksi Database Gagal!";
}
?>