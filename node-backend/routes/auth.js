const express = require("express");
const router = express.Router();
const bcrypt = require("bcrypt");
const db = require("../config/db");
const jwt = require("jsonwebtoken");
const nodemailer = require("nodemailer");
const fetch = require("node-fetch");
require("dotenv").config();

let pending = {};
let loginAttempts = {};

const transporter = nodemailer.createTransport({
  service: "gmail",
  auth: {
    user: process.env.EMAIL_USER,
    pass: process.env.EMAIL_PASS,
  },
});

// GET /register - Render halaman register
router.get("/register", (req, res) => {
  res.render("register", { error: null, otpSent: false, formData: {} });
});

// POST /register - Proses registrasi
router.post("/register", (req, res) => {
  const { username, email, phone, password, otp, action } = req.body;

  if (action === "sendOtp") {
    const passwordRegex =
      /^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,20}$/;

    if (
      !username ||
      !email.includes("@") ||
      phone.length < 10 ||
      !passwordRegex.test(password)
    ) {
      return res.render("register", {
        error:
          "Data tidak valid. Password harus 8-20 karakter, ada huruf besar, angka, dan simbol.",
        otpSent: false,
        formData: { username, email, phone },
      });
    }

    const kodeOTP = Math.floor(100000 + Math.random() * 900000);
    pending[email] = {
      username,
      email,
      phone,
      password,
      kodeOTP,
      createdAt: Date.now(),
    };

    const mailOptions = {
      from: process.env.EMAIL_USER,
      to: email,
      subject: "Kode OTP Registrasi",
      text: `Kode OTP Anda adalah: ${kodeOTP}`,
    };

    transporter.sendMail(mailOptions, (err) => {
      if (err) {
        return res.render("register", {
          error: "Gagal mengirim OTP ke email.",
          otpSent: false,
          formData: { username, email, phone },
        });
      }

      res.render("register", {
        error: null,
        otpSent: true,
        formData: { username, email, phone },
      });
    });
  } else if (action === "verify") {
    const data = pending[email];

    if (!data || String(data.kodeOTP) !== String(otp)) {
      return res.render("register", {
        error: "OTP salah atau kadaluwarsa.",
        otpSent: true,
        formData: { username, email, phone },
      });
    }

    // Cek apakah OTP kadaluwarsa (5 menit)
    const now = Date.now();
    const otpAge = now - data.createdAt;
    const fiveMinutes = 5 * 60 * 1000;

    if (otpAge > fiveMinutes) {
      return res.render("register", {
        error: "OTP sudah kadaluwarsa. Silakan kirim ulang OTP.",
        otpSent: false,
        formData: { username, email, phone },
      });
    }

    bcrypt.hash(data.password, 10, (err, hash) => {
      if (err) {
        return res.render("register", {
          error: "Gagal mengenkripsi password.",
          otpSent: true,
          formData: { username, email, phone },
        });
      }

      db.query(
        "SELECT * FROM users WHERE username = ? OR email = ?",
        [data.username, data.email],
        (err, results) => {
          if (err) {
            return res.render("register", {
              error: "Terjadi kesalahan pada server.",
              otpSent: true,
              formData: { username, email, phone },
            });
          }

          if (results.length > 0) {
            let errorMsg = "Username atau email sudah digunakan.";
            if (results.some((user) => user.username === data.username)) {
              errorMsg = "Username sudah digunakan.";
            } else if (results.some((user) => user.email === data.email)) {
              errorMsg = "Email sudah digunakan.";
            }

            return res.render("register", {
              error: errorMsg,
              otpSent: true,
              formData: { username, email, phone },
            });
          }

          db.query(
            "INSERT INTO users (username, email, phone, password) VALUES (?, ?, ?, ?)",
            [data.username, data.email, data.phone, hash],
            (err2, result) => {
              if (err2) {
                return res.render("register", {
                  error: "Gagal menyimpan data pengguna.",
                  otpSent: true,
                  formData: { username, email, phone },
                });
              }

              delete pending[email];
              res.redirect(
                "/login?success=Registrasi berhasil! Silakan login."
              );
            }
          );
        }
      );
    });
  }
});

// GET /login - Render halaman login
router.get("/login", (req, res) => {
  const redirect = req.query.redirect || "Dashboard.php"; // Ubah ke Dashboard.php
  const error = req.query.error || null;
  const success = req.query.success || null;

  res.render("login", {
    error: error,
    success: success,
    redirect: redirect,
  });
});

// POST /login - Proses login
router.post("/login", (req, res) => {
  const { username, password, redirect } = req.body;
  const targetRedirect = redirect || "Dashboard.php"; // Ubah ke Dashboard.php

  if (!loginAttempts[username]) {
    loginAttempts[username] = { count: 0, timeout: null };
  }

  const userLogin = loginAttempts[username];

  if (userLogin.timeout && Date.now() < userLogin.timeout) {
    const remaining = Math.ceil((userLogin.timeout - Date.now()) / 1000);
    return res.render("login", {
      error: `Terlalu banyak percobaan. Coba lagi dalam ${remaining} detik.`,
      redirect: targetRedirect,
    });
  }

  db.query(
    "SELECT * FROM users WHERE username = ?",
    [username],
    (err, results) => {
      if (err) {
        return res.render("login", {
          error: "Terjadi kesalahan pada server.",
          redirect: targetRedirect,
        });
      }

      if (results.length === 0) {
        userLogin.count++;
        if (userLogin.count >= 5) {
          userLogin.timeout = Date.now() + 3 * 60 * 1000;
          userLogin.count = 0;
        }
        return res.render("login", {
          error: "Username atau password salah.",
          redirect: targetRedirect,
        });
      }

      const user = results[0];
      bcrypt.compare(password, user.password, (err, isMatch) => {
        if (err || !isMatch) {
          userLogin.count++;
          if (userLogin.count >= 5) {
            userLogin.timeout = Date.now() + 3 * 60 * 1000;
            userLogin.count = 0;
          }
          return res.render("login", {
            error: "Username atau password salah.",
            redirect: targetRedirect,
          });
        }

        // Reset login attempts
        userLogin.count = 0;
        userLogin.timeout = null;

        // Buat JWT token dengan menyertakan user_id
        const token = jwt.sign(
          {
            username: user.username,
            user_id: user.id,
          },
          process.env.JWT_SECRET,
          {
            expiresIn: "1h",
          }
        );

        // Redirect ke halaman PHP dengan token
         res.redirect(
    `http://localhost/project-web-kasir/php-backend/Dashboard.php?token=${token}`
        );
      });
    }
  );
});

// Endpoint untuk verifikasi token
router.post("/verify-token", (req, res) => {
  const token = req.body.token;

  if (!token) {
    return res.json({ valid: false });
  }

  jwt.verify(token, process.env.JWT_SECRET, (err, decoded) => {
    if (err) {
      return res.json({ valid: false });
    }

    // Token valid, kembalikan informasi user termasuk user_id
    res.json({
      valid: true,
      username: decoded.username,
      user_id: decoded.user_id,
    });
  });
});

// GET /logout - Logout dari sistem
router.get("/logout", (req, res) => {
  // Redirect ke logout.php di PHP untuk menghapus session
  res.redirect("http://localhost/project-web-kasir/php-backend/logout.php");
});

module.exports = router;
