<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduSphere - Sistem Manajemen Sekolah Futuristik</title>
    @include('frontend.includes.styles')
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#">EduSphere</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Fitur</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#dashboard">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#testimonials">Testimoni</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#pricing">Harga</a>
                    </li>
                    @auth
                        <!-- Jika sudah login -->
                        <li class="nav-item ms-lg-3">
                            <a class="btn btn-primary" href="{{ route('home') }}">Home</a>
                        </li>
                    @else
                        <!-- Jika belum login -->
                        <li class="nav-item ms-lg-3">
                            <a class="btn btn-outline-primary me-2 mb-2" href="{{ route('login') }}">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary" href="{{ route('register') }}">Register</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content">
                    <h1>Revolusi Sistem Manajemen Sekolah dengan Teknologi AI</h1>
                    <p>Platform all-in-one untuk mengelola seluruh operasional sekolah dengan efisien, modern, dan
                        berorientasi masa depan.</p>
                    <div class="hero-btns d-flex">
                        <a href="#pricing" class="btn btn-primary">Mulai Sekarang</a>
                        <a href="#features" class="btn btn-outline-light">Pelajari Fitur</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <img src="https://images.unsplash.com/photo-1603791440384-56cd371ee9a7?auto=format&fit=crop&w=800&q=80"
                        alt="Dashboard Preview" class="img-fluid floating d-none d-lg-block">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <div class="section-title">
                <h2>Fitur Unggulan EduSphere</h2>
                <p>Solusi lengkap untuk transformasi digital sekolah Anda dengan teknologi terkini dan antarmuka yang
                    intuitif.</p>
            </div>

            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="lni lni-dashboard"></i>
                        </div>
                        <h3>Dashboard Cerdas</h3>
                        <p>Analisis data real-time dengan visualisasi interaktif untuk pengambilan keputusan berbasis
                            data.</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="lni lni-school"></i>
                        </div>
                        <h3>Manajemen Sekolah</h3>
                        <p>Kelola siswa, guru, kelas, jadwal, dan inventaris sekolah dalam satu platform terintegrasi.
                        </p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="lni lni-book"></i>
                        </div>
                        <h3>E-Learning Modern</h3>
                        <p>Sistem pembelajaran online dengan kelas virtual, materi interaktif, dan sistem penilaian
                            otomatis.</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="lni lni-certificate"></i>
                        </div>
                        <h3>E-Raport Digital</h3>
                        <p>Generate raport otomatis dengan analisis kompetensi dan prediksi kelulusan berbasis AI.</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="lni lni-robot"></i>
                        </div>
                        <h3>AI Learning Assistant</h3>
                        <p>Asisten virtual untuk pembuatan materi otomatis dan penilaian esai cerdas.</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="lni lni-users"></i>
                        </div>
                        <h3>Portal Orang Tua</h3>
                        <p>Monitoring perkembangan anak, pembayaran digital, dan komunikasi langsung dengan guru.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Dashboard Preview -->
    <section class="dashboard-preview" id="dashboard">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-5">
                    <h2>Antarmuka Modern & Intuitif</h2>
                    <p class="mb-4">Dashboard yang dirancang khusus untuk kemudahan penggunaan dengan pengalaman
                        pengguna yang optimal.</p>
                    <ul class="list-unstyled">
                        <li class="mb-3"><i class="lni lni-checkmark-circle text-primary me-2"></i> Desain responsif
                            untuk semua perangkat</li>
                        <li class="mb-3"><i class="lni lni-checkmark-circle text-primary me-2"></i> Navigasi sederhana
                            dengan akses cepat</li>
                        <li class="mb-3"><i class="lni lni-checkmark-circle text-primary me-2"></i> Customizable
                            sesuai kebutuhan sekolah</li>
                        <li class="mb-3"><i class="lni lni-checkmark-circle text-primary me-2"></i> Dark mode untuk
                            kenyamanan mata</li>
                    </ul>
                </div>
                <div class="col-lg-7">
                    <div class="dashboard-img">
                        <img src="https://images.unsplash.com/photo-1603791440384-56cd371ee9a7?auto=format&fit=crop&w=800&q=80"
                            alt="Dashboard Preview" class="img-fluid floating d-none d-lg-block">

                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials" id="testimonials">
        <div class="container">
            <div class="section-title">
                <h2>Apa Kata Mereka</h2>
                <p>Testimoni dari sekolah-sekolah yang telah mengalami transformasi digital dengan EduSphere.</p>
            </div>

            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="testimonial-card">
                        <div class="testimonial-header">
                            <img src="https://randomuser.me/api/portraits/women/45.jpg" alt="Testimonial"
                                class="testimonial-avatar">
                            <div class="testimonial-author">
                                <h5>Dr. Ani Wijaya</h5>
                                <p>Kepala Sekolah SMA Futura</p>
                            </div>
                        </div>
                        <p class="testimonial-quote">"Sejak menggunakan EduSphere, efisiensi administrasi sekolah kami
                            meningkat 70%. Sistem yang sangat membantu untuk manajemen modern."</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="testimonial-card">
                        <div class="testimonial-header">
                            <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Testimonial"
                                class="testimonial-avatar">
                            <div class="testimonial-author">
                                <h5>Budi Santoso</h5>
                                <p>Guru Matematika SMP Cendekia</p>
                            </div>
                        </div>
                        <p class="testimonial-quote">"Fitur E-Learning-nya sangat membantu terutama untuk pembuatan
                            materi dan penilaian otomatis. Menghemat banyak waktu saya."</p>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="testimonial-card">
                        <div class="testimonial-header">
                            <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="Testimonial"
                                class="testimonial-avatar">
                            <div class="testimonial-author">
                                <h5>Siti Rahmawati</h5>
                                <p>Orang Tua Siswa SD Mentari</p>
                            </div>
                        </div>
                        <p class="testimonial-quote">"Portal orang tua memudahkan saya memantau perkembangan anak dan
                            berkomunikasi dengan guru tanpa harus datang ke sekolah."</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing -->
    <section class="pricing" id="pricing">
        <div class="container">
            <div class="section-title">
                <h2>Paket Harga</h2>
                <p>Pilih paket yang sesuai dengan kebutuhan sekolah Anda. Semua paket termasuk update dan support 24/7.
                </p>
            </div>

            <div class="row">
                <div class="col-lg-4">
                    <div class="pricing-card">
                        <h3>Dasar</h3>
                        <div class="price">Rp 1.500.000<span>/bulan</span></div>
                        <ul class="pricing-features list-unstyled">
                            <li><i class="lni lni-checkmark-circle"></i> Manajemen Siswa & Guru</li>
                            <li><i class="lni lni-checkmark-circle"></i> E-Raport Dasar</li>
                            <li><i class="lni lni-checkmark-circle"></i> Portal Orang Tua</li>
                            <li><i class="lni lni-checkmark-circle"></i> Support Email</li>
                            <li><i class="lni lni-close"></i> Fitur AI</li>
                            <li><i class="lni lni-close"></i> E-Learning Premium</li>
                        </ul>
                        <a href="#" class="btn btn-outline-primary w-100">Mulai Sekarang</a>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="pricing-card popular">
                        <div class="popular-badge">POPULAR</div>
                        <h3>Premium</h3>
                        <div class="price">Rp 3.500.000<span>/bulan</span></div>
                        <ul class="pricing-features list-unstyled">
                            <li><i class="lni lni-checkmark-circle"></i> Semua Fitur Dasar</li>
                            <li><i class="lni lni-checkmark-circle"></i> Sistem E-Learning Lengkap</li>
                            <li><i class="lni lni-checkmark-circle"></i> Ujian Online & Proctoring</li>
                            <li><i class="lni lni-checkmark-circle"></i> AI Learning Assistant</li>
                            <li><i class="lni lni-checkmark-circle"></i> Support Prioritas</li>
                            <li><i class="lni lni-checkmark-circle"></i> Integrasi Payment Gateway</li>
                        </ul>
                        <a href="#" class="btn btn-primary w-100">Mulai Sekarang</a>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="pricing-card">
                        <h3>Enterprise</h3>
                        <div class="price">Custom</div>
                        <ul class="pricing-features list-unstyled">
                            <li><i class="lni lni-checkmark-circle"></i> Semua Fitur Premium</li>
                            <li><i class="lni lni-checkmark-circle"></i> Custom Development</li>
                            <li><i class="lni lni-checkmark-circle"></i> Dedicated Server</li>
                            <li><i class="lni lni-checkmark-circle"></i> Onsite Training</li>
                            <li><i class="lni lni-checkmark-circle"></i> Branding Khusus</li>
                            <li><i class="lni lni-checkmark-circle"></i> Manajer Akun Dedicated</li>
                        </ul>
                        <a href="#" class="btn btn-outline-primary w-100">Hubungi Kami</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta">
        <div class="container">
            <h2>Siap Transformasi Sekolah Anda?</h2>
            <p>Jadwalkan demo gratis sekarang dan lihat bagaimana EduSphere dapat merevolusi manajemen sekolah Anda
                dalam hitungan menit.</p>
            <a href="#" class="btn btn-light btn-lg me-2">Jadwalkan Demo</a>
            <a href="#" class="btn btn-outline-light btn-lg">Hubungi Sales</a>
        </div>
    </section>

    <!-- Footer -->
    @include('frontend.includes.footer')

    <!-- Scripts -->
    @include('frontend.includes.scripts')

</body>

</html>
