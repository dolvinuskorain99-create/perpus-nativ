<footer class="bg-dark text-white pt-5 pb-4 mt-5">
    <div class="container">
        <div class="row g-4 justify-content-between">
            
            <!-- Logo & Informasi Singkat -->
            <div class="col-lg-4 col-md-6 text-center text-md-start">
                <div class="d-flex align-items-center justify-content-center justify-content-md-start mb-3">
                    <!-- Path logo, sesuaikan dengan direktori file Anda (gunakan 'assets/img/logo.png' jika di root utama) -->
                    <img src="../assets/img/logo.png" width="50" class="me-3" alt="Logo USTJ">
                    <div>
                        <h5 class="mb-0 fw-bold">
                            Perpustakaan USTJ
                        </h5>
                        <small class="text-white-50">
                            Universitas Sains dan Teknologi Jayapura
                        </small>
                    </div>
                </div>
                <p class="text-white-50 small px-3 px-md-0" style="line-height:1.8;">
                    Pusat layanan literasi digital dan referensi akademik yang mendukung Tri Dharma Perguruan Tinggi melalui koleksi buku dan arsip skripsi.
                </p>
            </div>

            <!-- Menu Cepat -->
            <div class="col-lg-3 col-md-6 text-center text-md-start">
                <h6 class="fw-bold mb-3 text-uppercase tracking-wider fs-6">
                    Menu Cepat
                </h6>
                <ul class="list-unstyled footer-menu">
                    <li class="mb-2">
                        <a href="index.php" class="text-decoration-none text-white-50 hover-light">Beranda</a>
                    </li>
                    <li class="mb-2">
                        <a href="katalog.php" class="text-decoration-none text-white-50 hover-light">Katalog Buku</a>
                    </li>
                    <li class="mb-2">
                        <a href="katalog_skripsi.php" class="text-decoration-none text-white-50 hover-light">Skripsi</a>
                    </li>
                    <li>
                        <a href="tentang.php" class="text-decoration-none text-white-50 hover-light">Tentang</a>
                    </li>
                </ul>
            </div>

            <!-- Kontak & Sosial Media -->
           <div class="col-lg-4 col-md-12 text-center text-md-start">
    <h6 class="fw-bold mb-3 text-uppercase tracking-wider fs-6">
        Hubungi Kami
    </h6>
    
    <!-- Alamat (Link ke Google Maps) -->
    <p class="small text-white-50 mb-2 d-flex align-items-center justify-content-center justify-content-md-start">
        <i class="bi bi-geo-alt-fill me-2 text-primary fs-5"></i>
        <a href="https://maps.google.com/?q=USTJ+Abepura+Jayapura" target="_blank" rel="noopener noreferrer" class="text-white-50 text-decoration-none">
            Abepura, Kota Jayapura, Papua
        </a>
    </p>

    <!-- Email (Klik untuk membuka Email Client) -->
    <p class="small text-white-50 mb-2 d-flex align-items-center justify-content-center justify-content-md-start">
        <i class="bi bi-envelope-fill me-2 text-primary fs-5"></i>
        <a href="mailto:perpus@ustj.ac.id" class="text-white-50 text-decoration-none">
            perpus@ustj.ac.id
        </a>
    </p>

    <!-- Nomor Telepon (Klik untuk Panggil Langsung) -->
    <p class="small text-white-50 mb-3 d-flex align-items-center justify-content-center justify-content-md-start">
        <i class="bi bi-telephone-fill me-2 text-primary fs-5"></i>
        <a href="tel:0967000000" class="text-white-50 text-decoration-none">
            (0967) 000000
        </a>
    </p>
    
    <!-- Tombol Sosial Media -->
    <div class="d-flex gap-2 justify-content-center justify-content-md-start mt-3">
        <!-- Facebook -->
        <a href="https://facebook.com/namaprofileatauHalaman" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px;" aria-label="Facebook">
            <i class="bi bi-facebook fs-6"></i>
        </a>

        <!-- Instagram -->
        <a href="https://instagram.com/ustjlibrary" target="_blank" rel="noopener noreferrer" class="btn btn-danger btn-sm rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px;" aria-label="Instagram">
            <i class="bi bi-instagram fs-6"></i>
        </a>

        <!-- WhatsApp -->
        <a href="https://wa.me/6281234567890?text=Halo%20Perpustakaan%20USTJ,%20saya%20ingin%20bertanya" target="_blank" rel="noopener noreferrer" class="btn btn-success btn-sm rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px;" aria-label="WhatsApp">
            <i class="bi bi-whatsapp fs-6"></i>
        </a>
    </div>
</div>

        </div>

        <hr class="border-secondary my-4 opacity-50">

        <!-- Hak Cipta & Kreator -->
        <div class="row align-items-center text-center text-md-start">
            <div class="col-md-7 mb-2 mb-md-0">
                <small class="text-white-50">
                    &copy; <?= date('Y') ?> Universitas Sains dan Teknologi Jayapura. All Rights Reserved.
                </small>
            </div>
            <div class="col-md-5 text-md-end">
                <small class="text-white-50">
                   MADE <span class="text-danger">❤️</span> BY SAVOKORAIN SRAHWATA
                </small>
            </div>
        </div>
    </div>
</footer>

<!-- CSS Responsif Khusus Footer -->
<style>
.hover-light:hover {
    color: #ffffff !important;
    padding-left: 4px;
    transition: all 0.2s ease-in-out;
}
.footer-menu li {
    transition: transform 0.2s ease;
}
@media (max-width: 768px) {
    footer {
        text-align: center;
    }
    footer .d-flex.gap-2 {
        justify-content: center !important;
    }
}
</style>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>