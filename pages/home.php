<!-- Hero Section -->
<section class="hero-section" style="background-image: url('assets/images/hero_bg.png');">
    <div class="hero-overlay"></div>
    <div class="container hero-content">
        <div class="row align-items-center">
            <div class="col-lg-8 text-start">
                <span class="hero-badge animate-fade-in"><i class="fa-solid fa-fire-flame-simple me-2"></i> Booking Lapangan Instant & Mudah</span>
                <h1 class="display-3 text-white fw-bold mb-3 animate-slide-up delay-1">DOMINASI LAPANGAN,<br>CETAK PRESTASIMU!</h1>
                <p class="lead text-white-50 mb-4 col-md-10 animate-slide-up delay-2" style="font-weight: 400;">
                    FutsalHub menyediakan lapangan futsal kualitas standar internasional dengan kemudahan booking online secara realtime. Pilih lapanganmu sekarang dan mulai pertandingan.
                </p>
                <div class="d-flex flex-wrap gap-3 animate-slide-up delay-3">
                    <a href="index.php?page=lapangan" class="btn btn-primary-custom px-4 py-3"><i class="fa-solid fa-futbol me-2"></i> Cari Lapangan</a>
                    <a href="index.php?page=about" class="btn btn-outline-custom px-4 py-3"><i class="fa-solid fa-circle-question me-2"></i> Pelajari Selengkapnya</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Search Overlay Section -->
<section class="container mb-5">
    <div class="search-container animate-slide-up delay-3">
        <form action="index.php" method="GET">
            <input type="hidden" name="page" value="lapangan">
            <div class="row g-3 align-items-center">
                <!-- Search Input -->
                <div class="col-lg-4 col-md-6">
                    <label class="label-custom"><i class="fa-solid fa-magnifying-glass me-1"></i> Nama Lapangan</label>
                    <input type="text" name="search" class="form-control form-control-custom" placeholder="Cari Lapangan Arena, Turf...">
                </div>
                <!-- Field Type -->
                <div class="col-lg-3 col-md-6">
                    <label class="label-custom"><i class="fa-solid fa-sliders me-1"></i> Jenis Lapangan</label>
                    <select name="tipe" class="form-select form-control-custom">
                        <option value="">Semua Jenis</option>
                        <option value="vinyl">Premium Vinyl</option>
                        <option value="turf">Synthetic Turf</option>
                        <option value="parquet">Hardwood Parquet</option>
                    </select>
                </div>
                <!-- Date Picker -->
                <div class="col-lg-3 col-md-6">
                    <label class="label-custom"><i class="fa-regular fa-calendar me-1"></i> Tanggal Main</label>
                    <input type="date" class="form-control form-control-custom" value="<?= date('Y-m-d') ?>">
                </div>
                <!-- Submit Button -->
                <div class="col-lg-2 col-md-6 d-grid">
                    <label class="label-custom d-none d-lg-block">&nbsp;</label>
                    <button type="submit" class="btn btn-primary-custom py-2"><i class="fa-solid fa-search me-1"></i> Temukan</button>
                </div>
            </div>
        </form>
    </div>
</section>

<!-- Featured Fields Section -->
<section class="py-5">
    <div class="container">
        <div class="row mb-5 align-items-end">
            <div class="col-md-6">
                <span class="text-success fw-bold text-uppercase tracking-wider" style="color: var(--accent-color) !important; font-size: 0.85rem;">Pilihan Terbaik</span>
                <h2 class="text-white display-5 fw-bold mt-1">LAPANGAN REKOMENDASI</h2>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <a href="index.php?page=lapangan" class="btn btn-outline-custom">Lihat Semua Lapangan <i class="fa-solid fa-arrow-right ms-2"></i></a>
            </div>
        </div>

        <div class="row g-4">
            <!-- Field 1 -->
            <div class="col-lg-4 col-md-6">
                <div class="card card-custom h-100">
                    <div class="card-img-wrapper">
                        <span class="card-badge"><i class="fa-solid fa-star text-warning me-1"></i> 4.9</span>
                        <img src="assets/images/field_vinyl.png" class="card-img-custom" alt="Premium Vinyl Arena">
                    </div>
                    <div class="card-body p-4 d-flex flex-column">
                        <h4 class="text-white mb-2">Grand Arena Vinyl</h4>
                        <p class="text-muted small mb-3"><i class="fa-solid fa-location-dot me-1"></i> Jakarta Selatan • Court A</p>
                        
                        <div class="row g-2 mb-4 py-2 border-top border-bottom border-secondary border-opacity-25">
                            <div class="col-6 text-muted small"><i class="fa-solid fa-layer-group me-1 text-success" style="color: var(--accent-color) !important;"></i> Vinyl Premium</div>
                            <div class="col-6 text-muted small"><i class="fa-solid fa-arrows-up-down-left-right me-1 text-success" style="color: var(--accent-color) !important;"></i> 16 x 26 Meter</div>
                        </div>

                        <div class="mt-auto d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small">Harga / Jam</span>
                                <div class="fs-5 text-white fw-bold">Rp 150.000</div>
                            </div>
                            <a href="index.php?page=detail_lapangan&id=1" class="btn btn-primary-custom py-2 px-3">Detail & Book</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Field 2 -->
            <div class="col-lg-4 col-md-6">
                <div class="card card-custom h-100">
                    <div class="card-img-wrapper">
                        <span class="card-badge"><i class="fa-solid fa-star text-warning me-1"></i> 4.8</span>
                        <img src="assets/images/field_turf.png" class="card-img-custom" alt="Synthetic Turf Arena">
                    </div>
                    <div class="card-body p-4 d-flex flex-column">
                        <h4 class="text-white mb-2">Stadion Hijau Turf</h4>
                        <p class="text-muted small mb-3"><i class="fa-solid fa-location-dot me-1"></i> Jakarta Selatan • Court B</p>
                        
                        <div class="row g-2 mb-4 py-2 border-top border-bottom border-secondary border-opacity-25">
                            <div class="col-6 text-muted small"><i class="fa-solid fa-layer-group me-1 text-success" style="color: var(--accent-color) !important;"></i> Rumput Sintetis</div>
                            <div class="col-6 text-muted small"><i class="fa-solid fa-arrows-up-down-left-right me-1 text-success" style="color: var(--accent-color) !important;"></i> 18 x 28 Meter</div>
                        </div>

                        <div class="mt-auto d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small">Harga / Jam</span>
                                <div class="fs-5 text-white fw-bold">Rp 175.000</div>
                            </div>
                            <a href="index.php?page=detail_lapangan&id=2" class="btn btn-primary-custom py-2 px-3">Detail & Book</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Field 3 -->
            <div class="col-lg-4 col-md-6">
                <div class="card card-custom h-100">
                    <div class="card-img-wrapper">
                        <span class="card-badge"><i class="fa-solid fa-star text-warning me-1"></i> 4.7</span>
                        <img src="assets/images/field_parquet.png" class="card-img-custom" alt="Hardwood Parquet Arena">
                    </div>
                    <div class="card-body p-4 d-flex flex-column">
                        <h4 class="text-white mb-2">Elite Wood Parquet</h4>
                        <p class="text-muted small mb-3"><i class="fa-solid fa-location-dot me-1"></i> Jakarta Barat • Court C</p>
                        
                        <div class="row g-2 mb-4 py-2 border-top border-bottom border-secondary border-opacity-25">
                            <div class="col-6 text-muted small"><i class="fa-solid fa-layer-group me-1 text-success" style="color: var(--accent-color) !important;"></i> Parquet Kayu</div>
                            <div class="col-6 text-muted small"><i class="fa-solid fa-arrows-up-down-left-right me-1 text-success" style="color: var(--accent-color) !important;"></i> 15 x 25 Meter</div>
                        </div>

                        <div class="mt-auto d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small">Harga / Jam</span>
                                <div class="fs-5 text-white fw-bold">Rp 200.000</div>
                            </div>
                            <a href="index.php?page=detail_lapangan&id=3" class="btn btn-primary-custom py-2 px-3">Detail & Book</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="py-5 border-top border-secondary border-opacity-25">
    <div class="container py-4">
        <div class="text-center mb-5 max-w-600 mx-auto">
            <span class="text-success fw-bold text-uppercase" style="color: var(--accent-color) !important; font-size: 0.85rem;">Fasilitas & Layanan</span>
            <h2 class="text-white display-5 fw-bold mt-1">MENGAPA MEMILIH FUTSALHUB?</h2>
            <p class="text-muted mt-2">Kami menawarkan keunggulan eksklusif untuk memastikan kenyamanan bermain Anda.</p>
        </div>

        <div class="row g-4">
            <!-- Item 1 -->
            <div class="col-md-4">
                <div class="card-custom p-4 h-100 d-flex flex-column align-items-start text-start">
                    <div class="benefit-icon">
                        <i class="fa-solid fa-bolt-lightning"></i>
                    </div>
                    <h4 class="text-white mb-2">Booking Instan Real-Time</h4>
                    <p class="text-muted mb-0">Lihat ketersediaan jam secara langsung dan konfirmasikan pemesanan Anda tanpa perlu menunggu persetujuan manual.</p>
                </div>
            </div>

            <!-- Item 2 -->
            <div class="col-md-4">
                <div class="card-custom p-4 h-100 d-flex flex-column align-items-start text-start">
                    <div class="benefit-icon">
                        <i class="fa-solid fa-award"></i>
                    </div>
                    <h4 class="text-white mb-2">Lapangan Standar Pro</h4>
                    <p class="text-muted mb-0">Pilihan jenis lantai Vinyl impor, Rumput Sintetis lembut, dan Parquet kayu standar internasional yang terawat prima.</p>
                </div>
            </div>

            <!-- Item 3 -->
            <div class="col-md-4">
                <div class="card-custom p-4 h-100 d-flex flex-column align-items-start text-start">
                    <div class="benefit-icon">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <h4 class="text-white mb-2">Harga Terbaik & Transparan</h4>
                    <p class="text-muted mb-0">Tanpa biaya tambahan tersembunyi. Sistem harga transparan dengan kemudahan pembayaran multi-platform e-wallet & bank transfer.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-5 border-top border-secondary border-opacity-25" style="background: rgba(0,0,0,0.15);">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="text-success fw-bold text-uppercase" style="color: var(--accent-color) !important; font-size: 0.85rem;">Kata Mereka</span>
            <h2 class="text-white display-5 fw-bold mt-1">TESTIMONI PEMAIN</h2>
        </div>

        <div class="row g-4">
            <!-- Testimonial 1 -->
            <div class="col-md-4">
                <div class="testimonial-card">
                    <div class="testimonial-rating">
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                    </div>
                    <p class="text-muted mb-4">"Aplikasinya sangat memudahkan untuk booking jam main selepas kerja. Lapangan vinyl-nya masih empuk, fasilitas shower & ruang gantinya sangat bersih."</p>
                    <div class="d-flex align-items-center gap-3">
                        <div style="width: 45px; height: 45px; border-radius: 50%; background: var(--accent-color); display: flex; align-items: center; justify-content: center; font-weight: 700; color: #000;">
                            AN
                        </div>
                        <div>
                            <h6 class="text-white mb-0">Aris Nugraha</h6>
                            <small class="text-muted">Kapten FC Karyawan</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Testimonial 2 -->
            <div class="col-md-4">
                <div class="testimonial-card">
                    <div class="testimonial-rating">
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star-half-stroke"></i>
                    </div>
                    <p class="text-muted mb-4">"Sangat suka dengan jadwal real-time. Nggak perlu lagi capek WA admin lapangan bolak-balik nanyain jam kosong. Begitu bayar langsung dapat tiket booking."</p>
                    <div class="d-flex align-items-center gap-3">
                        <div style="width: 45px; height: 45px; border-radius: 50%; background: #00E676; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #000;">
                            RF
                        </div>
                        <div>
                            <h6 class="text-white mb-0">Reza Fahlevi</h6>
                            <small class="text-muted">Pemain Futsal Amatir</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Testimonial 3 -->
            <div class="col-md-4">
                <div class="testimonial-card">
                    <div class="testimonial-rating">
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                    </div>
                    <p class="text-muted mb-4">"Lapangan elite wood parquet di Jakarta Barat keren banget! Lantainya mulus tidak licin. Booking lewat FutsalHub sangat lancar tanpa kendala."</p>
                    <div class="d-flex align-items-center gap-3">
                        <div style="width: 45px; height: 45px; border-radius: 50%; background: #00B0FF; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #000;">
                            HS
                        </div>
                        <div>
                            <h6 class="text-white mb-0">Hendra Saputra</h6>
                            <small class="text-muted">Ketua Liga Mahasiswa</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5" style="background: linear-gradient(135deg, rgba(0, 200, 83, 0.9) 0%, rgba(0, 150, 60, 0.9) 100%), url('assets/images/hero_bg.png'); background-blend-mode: overlay; background-size: cover; background-position: center;">
    <div class="container py-5 text-center">
        <h2 class="display-4 fw-bold text-black mb-3">Tunggu Apa Lagi? Mainkan Lagamu!</h2>
        <p class="lead text-black-50 mb-4 mx-auto" style="max-width: 600px; font-weight: 500;">
            Gabung dengan ribuan tim futsal lainnya yang sudah beralih menggunakan kemudahan booking digital di FutsalHub.
        </p>
        <a href="index.php?page=lapangan" class="btn btn-dark btn-lg px-5 py-3 rounded-pill fw-bold text-white border-0 shadow-lg" style="transition: all 0.3s ease; background: #12161c;">
            <i class="fa-solid fa-calendar-check me-2"></i> BOOKING LAPANGAN SEKARANG
        </a>
    </div>
</section>
