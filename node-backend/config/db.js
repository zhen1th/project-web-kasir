const mysql = require("mysql");

const db = mysql.createConnection({
  host: "localhost",
  user: "root",
  password: "",
  database: "dompos",
});

db.connect((err) => {
  if (err) {
    console.error("Gagal koneksi ke database:", err);
  } else {
    console.log("Terhubung ke database MySQL");
  }
});

module.exports = db;
