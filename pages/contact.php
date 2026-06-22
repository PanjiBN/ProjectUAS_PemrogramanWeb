<?php
$msg_sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $msg_sent = true;
}
?>

<div class="py-5" style="background: var(--bg-dark); min-height: 80vh;">
    <div class="container text-start">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php?page=home" style="color: var(--accent-color); text-decoration: none;">Home</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Hubungi Kami</li>
            </ol>
        </nav>

        <div class="row mb-5">
            <div class="col-lg-6">
                <h1 class="text-white display-5 fw-bold mb-3">HUBUNGI FUTSALHUB</h1>
                <p class="text-muted lead col-md-10">Mempunyai pertanyaan seputar booking, kemitraan lapangan, atau masukan? Layanan bantuan kami siap membantu Anda 24/7.</p>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left: Contact Details Cards -->
            <div class="col-lg-4 d-flex flex-column gap-3">
                <!-- Card Address -->
                <div class="card-custom p-4 text-start">
                    <div class="d-flex align-items-center gap-3">
                        <div class="benefit-icon mb-0" style="width: 50px; height: 50px; min-width: 50px;"><i class="fa-solid fa-location-dot"></i></div>
                        <div>
                            <h6 class="text-white fw-bold mb-1">Alamat Kantor</h6>
                            <p class="text-muted small mb-0">Jl. Stadium Futsal No. 88, Jakarta Selatan, Indonesia</p>
                        </div>
                    </div>
                </div>

                <!-- Card Phone -->
                <div class="card-custom p-4 text-start">
                    <div class="d-flex align-items-center gap-3">
                        <div class="benefit-icon mb-0" style="width: 50px; height: 50px; min-width: 50px;"><i class="fa-solid fa-phone"></i></div>
                        <div>
                            <h6 class="text-white fw-bold mb-1">Telepon & WhatsApp</h6>
                            <p class="text-muted small mb-0">+62 812-3456-7890</p>
                        </div>
                    </div>
                </div>

                <!-- Card Email -->
                <div class="card-custom p-4 text-start">
                    <div class="d-flex align-items-center gap-3">
                        <div class="benefit-icon mb-0" style="width: 50px; height: 50px; min-width: 50px;"><i class="fa-solid fa-envelope"></i></div>
                        <div>
                            <h6 class="text-white fw-bold mb-1">Email Support</h6>
                            <p class="text-muted small mb-0">support@futsalhub.com</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Contact Form -->
            <div class="col-lg-8">
                <div class="card-custom p-4">
                    <h5 class="text-white mb-4"><i class="fa-regular fa-paper-plane text-success me-2" style="color: var(--accent-color) !important;"></i> Kirim Pesan</h5>
                    
                    <?php if ($msg_sent): ?>
                        <div class="alert alert-success bg-opacity-10 border border-success text-success p-3 rounded-3 mb-4" role="alert">
                            <i class="fa-solid fa-circle-check me-2"></i> Terima kasih! Pesan Anda telah sukses terkirim. Admin kami akan membalas via email secepatnya.
                        </div>
                    <?php endif; ?>

                    <form action="index.php?page=contact" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="label-custom">Nama Lengkap</label>
                                <input type="text" name="nama" class="form-control form-control-custom" placeholder="Nama Anda" required>
                            </div>
                            <div class="col-md-6">
                                <label class="label-custom">Alamat Email</label>
                                <input type="email" name="email" class="form-control form-control-custom" placeholder="nama@email.com" required>
                            </div>
                            <div class="col-12">
                                <label class="label-custom">Subjek Pesan</label>
                                <input type="text" name="subjek" class="form-control form-control-custom" placeholder="Contoh: Kemitraan Lapangan / Pertanyaan Pembayaran" required>
                            </div>
                            <div class="col-12">
                                <label class="label-custom">Isi Pesan Anda</label>
                                <textarea name="pesan" class="form-control form-control-custom" rows="5" placeholder="Tuliskan isi pesan atau pertanyaan Anda di sini..." required></textarea>
                            </div>
                            <div class="col-12 text-end mt-4">
                                <button type="submit" class="btn btn-primary-custom px-4 py-2.5">
                                    <i class="fa-solid fa-paper-plane me-2"></i> Kirim Pesan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
