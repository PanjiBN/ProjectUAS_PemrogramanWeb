<!-- Footer Section -->
<footer class="py-5">
    <div class="container">
        <div class="row g-4 justify-content-between">
            <div class="col-lg-4 col-md-6">
                <a class="navbar-brand text-white d-flex align-items-center mb-3" href="index.php?page=home" style="font-size: 1.7rem; font-weight: 800;">
                    <span style="color: var(--accent-color); margin-right: 5px;"><i class="fa-solid fa-circle-nodes"></i></span>
                    FUTSAL<span style="color: var(--accent-color);">HUB</span>
                </a>
                <p class="pe-lg-4">Sistem booking lapangan futsal online terbaik dan terlengkap. Booking lapangan favorit Anda dalam hitungan menit secara instan, aman, dan 24 jam nonstop.</p>
                <div class="d-flex gap-3 mt-4">
                    <a href="#" class="fs-5 text-white" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="fs-5 text-white" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" class="fs-5 text-white" aria-label="Twitter"><i class="fa-brands fa-twitter"></i></a>
                    <a href="#" class="fs-5 text-white" aria-label="Youtube"><i class="fa-brands fa-youtube"></i></a>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-6 col-6">
                <h5 class="text-white mb-3">Navigasi</h5>
                <ul class="list-unstyled d-flex flex-column gap-2">
                    <li><a href="index.php?page=home">Home</a></li>
                    <li><a href="index.php?page=lapangan">Lapangan</a></li>
                    <li><a href="index.php?page=about">Tentang Kami</a></li>
                    <li><a href="index.php?page=contact" style="color:var(--accent-color);font-weight:600;">
                        <i class="fa-solid fa-envelope me-1" style="font-size:0.8rem;"></i>Hubungi Kami
                    </a></li>
                </ul>
            </div>
            
            <div class="col-lg-2 col-md-6 col-6">
                <h5 class="text-white mb-3">Tipe Lapangan</h5>
                <ul class="list-unstyled d-flex flex-column gap-2">
                    <li><a href="index.php?page=lapangan">Premium Vinyl</a></li>
                    <li><a href="index.php?page=lapangan">Synthetic Turf</a></li>
                    <li><a href="index.php?page=lapangan">Hardwood Parquet</a></li>
                </ul>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <h5 class="text-white mb-3">Kontak</h5>
                <ul class="list-unstyled d-flex flex-column gap-3">
                    <li class="d-flex align-items-start gap-2">
                        <i class="fa-solid fa-location-dot mt-1 text-success" style="color: var(--accent-color) !important;"></i>
                        <span>Jl. Stadium Futsal No. 88, Jakarta Selatan, Indonesia</span>
                    </li>
                    <li class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-phone text-success" style="color: var(--accent-color) !important;"></i>
                        <span>+62 812-3456-7890</span>
                    </li>
                    <li class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-envelope text-success" style="color: var(--accent-color) !important;"></i>
                        <span>support@futsalhub.com</span>
                    </li>
                </ul>
            </div>
        </div>
        
        <hr class="my-4 border-secondary opacity-25">
        
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start">
                <p class="mb-0">&copy; <?= date('Y') ?> FutsalHub. Hak Cipta Dilindungi.</p>
            </div>
            <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">
                <p class="mb-0">Designed for Premium Sport Performance</p>
            </div>
        </div>
    </div>
</footer> 


<!-- Bootstrap 5 JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>

<!-- Navbar Scrolled State JS -->
<script>
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar-custom');
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
</script>
</body>
</html>
