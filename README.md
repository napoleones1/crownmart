# 👑 CrownMart — Hybrid Marketplace

<div align="center">

![CrownMart Banner](https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=1200&h=300&fit=crop&q=80)

[![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![JavaScript](https://img.shields.io/badge/JavaScript-ES2022-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
[![XAMPP](https://img.shields.io/badge/XAMPP-Compatible-FB7A24?style=for-the-badge&logo=apache&logoColor=white)](https://apachefriends.org)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)

**Proyek Tugas Akhir Mata Kuliah E-Commerce**  
*Program Studi Teknik Informatika*

[🚀 Demo Fitur](#-fitur-utama) • [📦 Instalasi](#-instalasi) • [🗂️ Struktur](#️-struktur-proyek) • [📊 Database](#-database) • [👤 Akun Demo](#-akun-demo)

</div>

---

## 📖 Tentang Proyek

**CrownMart** adalah aplikasi web marketplace *hybrid* yang menggabungkan konsep **toko online retail** (seperti Amazon) dan **sistem lelang real-time** (seperti eBay). Proyek ini dibuat sebagai tugas kuliah **E-Commerce** untuk memahami konsep sistem perdagangan elektronik berbasis web.

### 🎯 Tujuan Pembelajaran
- Memahami alur **sistem transaksi pembayaran** e-commerce
- Implementasi **CRUD** lengkap (Create, Read, Update, Delete) pada produk
- Membangun **sistem admin** dengan laporan penjualan
- Penerapan **sistem autentikasi** (Login, Register, Role-based Access)
- Mengelola **status pemesanan** dari Pending hingga Delivered
- Integrasi **multi metode pembayaran**: Wallet, Kartu Debit/Credit, COD

---

## ✨ Fitur Utama

### 🛒 Marketplace
| Fitur | Keterangan |
|-------|-----------|
| 🏷️ **Buy It Now** | Beli produk langsung dengan 1-klik |
| 🔨 **Live Auction** | Sistem lelang real-time dengan countdown timer |
| 🔍 **Search & Filter** | Cari produk, filter kategori & format, sort by harga/rating |
| ❤️ **Watchlist** | Bookmark produk favorit + quick bid langsung dari watchlist |
| 🛒 **Shopping Cart** | Keranjang belanja dengan update quantity |
| ⭐ **Review & Rating** | Sistem ulasan produk dari pembeli |
| 📍 **Auto Geolocation** | Deteksi lokasi otomatis via GPS & IP |
| 🌐 **Multi Bahasa** | EN / ID / DE / JA |
| 💱 **Multi Mata Uang** | USD / IDR / EUR / GBP / JPY |

### 💳 Sistem Pembayaran
| Metode | Keterangan |
|--------|-----------|
| 💰 **Wallet** | Saldo virtual, potong otomatis saat checkout |
| 💳 **Debit / Credit Card** | Form input nomor kartu, expiry, CVV — diproses langsung |
| 🚚 **COD** | Bayar di tempat saat barang tiba |

### 📦 Status Pemesanan
```
Pending → Processing → Shipped → Out for Delivery → Delivered
```

### 👑 Admin Dashboard
- 📊 **Overview** — Statistik lengkap (Revenue, Orders, Users, Sellers, Bids)
- 📦 **Product Management** — Lihat & hapus semua produk
- 🚚 **Order Management** — Update status pesanan semua user
- 🔨 **Bids & Auctions** — Riwayat semua bid
- 👥 **User Management** — Ubah role, suspend/aktifkan akun
- 🏪 **Seller Management** — Daftar khusus seller
- 📈 **Sales Reports** — Grafik revenue harian, pie chart metode bayar, top produk, **Export CSV**

### 🏪 Seller Dashboard
- 📊 **Dashboard** — Revenue, order, items sold, grafik
- 📦 **My Products** — CRUD produk milik sendiri (Add, Edit, Delete)
- 🚚 **My Orders** — Pesanan yang mengandung produk seller
- 📈 **Sales Reports** — Laporan penjualan personal + Export CSV

### 🔐 Sistem Autentikasi
- Register sebagai **Buyer** atau **Seller**
- Role-based access: `buyer`, `seller`, `admin`
- Login modal langsung di halaman marketplace (tanpa pindah halaman)
- Marketplace bisa di-browse tanpa login, aksi beli/bid/jual butuh login

---

## 🗄️ Database

CrownMart menggunakan **11 tabel** standar e-commerce:

```
crownmart/
├── users               — Data pengguna (id, name, email, username, password, role, balance)
├── products            — Katalog produk (fixed & auction)
├── categories          — Kategori produk
├── cart                — Keranjang belanja per user
├── orders              — Data pesanan
├── order_items         — Item detail per pesanan
├── payments            — Riwayat transaksi pembayaran
├── bids                — Riwayat bid lelang
├── reviews             — Ulasan produk
├── watchlist           — Produk yang di-bookmark user
└── shipping_addresses  — Alamat tersimpan per user
```

### Relasi Utama
```
users ──< orders ──< order_items >── products
users ──< cart   >── products
users ──< bids   >── products
users ──< watchlist >── products
orders ──< payments
products >── categories
```

---

## 🚀 Instalasi

### Persyaratan
- **XAMPP** (Apache + MySQL + PHP 8.0+)
- Browser modern (Chrome, Firefox, Edge)

### Langkah Instalasi

**1. Clone repository**
```bash
git clone https://github.com/napoleones1/crownmart.git
```

**2. Copy ke htdocs XAMPP**
```
Salin folder crownmart-php ke:
C:\xampp\htdocs\crownmart-php\
```

**3. Start XAMPP**
- Buka XAMPP Control Panel
- Start **Apache** dan **MySQL**

**4. Import Database**
- Buka browser → `http://localhost/phpmyadmin`
- Klik tab **Import**
- Import file `db/database.sql` → klik **Go**
- Import file `db/migrate_auth.sql` → klik **Go**
- Import file `db/migrate_v2.sql` → klik **Go**

**5. Konfigurasi (opsional)**

Edit `api/config.php` jika MySQL kamu menggunakan password:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');        // ← isi password MySQL kamu
define('DB_NAME', 'crownmart');
```

**6. Buka di browser**
```
http://localhost/crownmart-php/
```

---

## 👤 Akun Demo

| Username | Password | Role | Akses |
|----------|----------|------|-------|
| `admin` | `password` | 👑 Admin | Admin Dashboard + Semua fitur |
| `seller` | `password` | 🏪 Seller | Seller Dashboard + Jual produk |
| `emily` | `password` | 👩 Buyer | Belanja, bid, watchlist |

> 💡 Kamu juga bisa **Register** akun baru sebagai Buyer atau Seller

---

## 🗂️ Struktur Proyek

```
crownmart-php/
│
├── 📄 index.php              ← Halaman utama marketplace
├── 📄 login.php              ← Halaman login & register
│
├── 📁 api/                   ← Backend PHP REST API
│   ├── config.php            ← Koneksi DB & session helper
│   ├── auth.php              ← Login, register, logout
│   ├── products.php          ← CRUD produk
│   ├── cart.php              ← Keranjang belanja
│   ├── orders.php            ← Checkout & riwayat pesanan
│   ├── bids.php              ← Sistem lelang
│   ├── reviews.php           ← Ulasan produk
│   ├── user.php              ← Profil & watchlist
│   ├── admin.php             ← API khusus admin
│   ├── report.php            ← Laporan admin
│   └── seller_report.php     ← Laporan seller
│
├── 📁 admin/
│   └── index.php             ← Dashboard Admin
│
├── 📁 seller/
│   └── index.php             ← Dashboard Seller
│
├── 📁 css/
│   └── style.css             ← Semua styling
│
├── 📁 js/
│   └── app.js                ← Logika frontend JavaScript
│
└── 📁 db/
    ├── database.sql          ← Setup database utama
    ├── migrate_auth.sql      ← Migrasi auth (login/register)
    └── migrate_v2.sql        ← Migrasi v2 (payment method, dll)
```

---

## 🛠️ Teknologi yang Digunakan

| Layer | Teknologi |
|-------|-----------|
| **Frontend** | HTML5, CSS3, JavaScript (Vanilla ES2022) |
| **Backend** | PHP 8.0+ |
| **Database** | MySQL 8.0 (via XAMPP) |
| **Web Server** | Apache (via XAMPP) |
| **Grafik** | Chart.js 4.4 |
| **Font & Icon** | Emoji native + CSS |
| **Geocoding** | Nominatim (OpenStreetMap) + ipapi.co |

---

## 📸 Tampilan

| Halaman | Keterangan |
|---------|-----------|
| 🏠 Marketplace | Grid produk dengan filter, search, dan live auction countdown |
| 🔨 Auction Modal | Detail lelang + riwayat bid + quick bid increment |
| 🛒 Cart Sidebar | Keranjang dengan pilihan metode bayar (Wallet/Transfer/COD) |
| 📊 Admin Dashboard | Statistik lengkap + grafik + manajemen user/produk/order |
| 🏪 Seller Dashboard | Laporan penjualan personal + CRUD produk |

---

## 📝 Cara Penggunaan

### Sebagai Pembeli (Buyer)
1. Buka `http://localhost/crownmart-php/`
2. Browse produk tanpa perlu login
3. Klik **Add to Cart** atau **Buy Now** → akan diminta login
4. Login / Register akun Buyer
5. Pilih **metode pembayaran**: Wallet, Transfer, atau COD
6. Cek status pesanan di tab **My Purchases**

### Sebagai Penjual (Seller)
1. Register akun dengan tipe **Seller**
2. Klik **📊 My Dashboard** di navbar
3. Di Seller Dashboard → **Add Product** untuk tambah produk
4. Pilih tipe: **Buy It Now** (retail) atau **Live Auction**
5. Pantau penjualan di tab **Sales Reports**

### Sebagai Admin
1. Login dengan `admin / password`
2. Otomatis diarahkan ke `http://localhost/crownmart-php/admin/`
3. Kelola produk, user, pesanan dari sidebar
4. Update status pesanan di tab **Orders**
5. Lihat laporan di tab **Sales Reports** → Export CSV

---

## 🔗 API Endpoints

| Method | Endpoint | Keterangan |
|--------|----------|-----------|
| GET | `/api/products.php` | Ambil semua produk (+ filter) |
| POST | `/api/products.php` | Tambah produk baru |
| PUT | `/api/products.php` | Update produk |
| DELETE | `/api/products.php` | Hapus produk |
| GET | `/api/cart.php` | Isi keranjang |
| POST | `/api/cart.php` | Tambah ke keranjang |
| POST | `/api/orders.php` | Checkout |
| GET | `/api/orders.php` | Riwayat pesanan |
| POST | `/api/bids.php` | Tempatkan bid |
| POST | `/api/auth.php` | Login / Register / Logout |
| GET | `/api/report.php` | Laporan admin |
| GET | `/api/seller_report.php` | Laporan seller |

---

## 💳 Detail Metode Pembayaran

### 💰 Wallet
- Saldo virtual tersimpan di akun
- Top-up gratis via tombol di navbar
- Saldo terpotong otomatis saat checkout
- Order langsung **Processing**

### 💳 Debit / Credit Card
- Input form: Nomor kartu (16 digit), Expiry (MM/YY), CVV, Nama pemegang
- Format otomatis saat mengetik
- Validasi client-side sebelum submit
- Diproses langsung → order **Processing**
- Dilindungi simulasi 256-bit SSL

### 🚚 COD (Cash on Delivery)
- Bayar tunai saat kurir tiba
- Order langsung **Processing**
- Cocok untuk area yang tidak ingin bayar online

---

## 👨‍💻 Tim Pengembang

Proyek ini dikerjakan oleh **4 mahasiswa** Program Studi Teknik Informatika sebagai tugas kelompok mata kuliah **E-Commerce**.

<div align="center">

| No | Nama | GitHub |
|----|------|--------|
| 1 | **Muhamad Haikal** | [![GitHub](https://img.shields.io/badge/napoleones1-181717?style=flat&logo=github)](https://github.com/napoleones1) |
| 2 | **Novan Wisnu Pratama** | [![GitHub](https://img.shields.io/badge/Nopvan-181717?style=flat&logo=github)](https://github.com/Nopvan) |
| 3 | **Fadil Muhammad** | [![GitHub](https://img.shields.io/badge/fvdilm-181717?style=flat&logo=github)](https://github.com/fvdilm) |
| 4 | **Falah Rabiusani** | [![GitHub](https://img.shields.io/badge/filthyfal-181717?style=flat&logo=github)](https://github.com/filthyfal) |

</div>

---

## 📄 Lisensi

Proyek ini dibuat untuk keperluan **tugas kelompok** mata kuliah **E-Commerce**, Program Studi Teknik Informatika.  
Bebas digunakan sebagai referensi pembelajaran.

---

<div align="center">

Made with ❤️ by **Tim CrownMart** — Teknik Informatika

**Muhamad Haikal · Novan Wisnu Pratama · Fadil Muhammad · Falah Rabiusani**

</div>
