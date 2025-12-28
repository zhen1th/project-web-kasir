const express = require("express");
const app = express();
const authRoutes = require("./routes/auth");

// Konfigurasi EJS
app.set("view engine", "ejs");
app.set("views", __dirname + "/views");

// Middleware
app.use(express.urlencoded({ extended: true }));

// Route untuk halaman utama
app.get("/", (req, res) => {
  res.redirect("/home");
});

// Route untuk halaman home (HalamanAwal.php)
app.get("/home", (req, res) => {
  res.redirect(
    "http://localhost/project-web-kasir/php-backend/HalamanAwal.php"
  );
});

// Routes untuk autentikasi
app.use("/", authRoutes);

// Jalankan server
const PORT = 3000;
app.listen(PORT, () => {
  console.log(`Server berjalan di http://localhost:${PORT}`);
});
