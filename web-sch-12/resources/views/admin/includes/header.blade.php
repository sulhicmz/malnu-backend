<header class="header bg-white shadow-sm">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-lg-5 col-md-5 col-6">
                <div class="header-left d-flex align-items-center">
                    <div class="menu-toggle-btn mr-15">
                        <button id="menu-toggle" class="main-btn primary-btn btn-hover">
                            <i class="lni lni-chevron-left me-2"></i> Menu
                        </button>
                    </div>
                    <div class="school-branding mr-15">
                        <!--<a href="#">
                            <img src="{{ asset('images/school-logo.png') }}" alt="School Logo" class="logo" style="max-height: 40px;">
                            <span class="school-name fw-500 d-none d-md-inline">{{ config('app.name', 'School System') }}</span>
                        </a>-->
                    </div>
                    <div class="header-search d-none d-md-flex">
                        <form action="#" method="GET">
                            <input type="text" name="query" placeholder="Cari siswa, buku, kursus..." />
                            <button type="submit"><i class="lni lni-search-alt"></i></button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-7 col-md-7 col-6">
                <div class="header-right d-flex justify-content-end align-items-center">
                    <div class="theme-toggle-box ml-15">
                        <button id="theme-toggle" class="border-0 bg-transparent p-2 rounded-circle"
                            title="Toggle Dark Mode">
                            <i class="lni lni-night" id="theme-icon"></i>
                            <span class="visually-hidden">Toggle Dark Mode</span>
                        </button>
                    </div>
                    <div class="language-box ml-15 d-none d-md-flex">
                        <button class="dropdown-toggle bg-transparent border-0" type="button" id="language"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="lni lni-world"></i>
                            <span>{{ strtoupper(app()->getLocale()) }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="language">
                            <li><a href="#">Indonesia</a></li>
                            <li><a href="#">English</a></li>
                            <li><a href="#">العربية</a></li>
                        </ul>
                    </div>
                    <div class="notification-box ml-15 d-none d-md-flex">
                        <button class="dropdown-toggle position-relative" type="button" id="notification"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="lni lni-alarm"></i>
                            @if ($notificationsCount ?? 0 > 0)
                                <span
                                    class="badge bg-danger rounded-circle position-absolute top-0 start-100 translate-middle">{{ $notificationsCount ?? 0 }}</span>
                            @endif
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notification">
                            <li>
                                <a href="#">
                                    <div class="content">
                                        <h6>PPDB: Pendaftaran Baru</h6>
                                        <p>Ada 5 pendaftar baru menunggu verifikasi.</p>
                                        <span>30 menit lalu</span>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <div class="content">
                                        <h6>Hasil UTS Tersedia</h6>
                                        <p>Nilai UTS kelas X telah diunggah.</p>
                                        <span>1 jam lalu</span>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <div class="content">
                                        <h6>Pesan dari Orang Tua</h6>
                                        <p>Orang tua siswa X meminta jadwal konsultasi.</p>
                                        <span>2 jam lalu</span>
                                    </div>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="header-message-box ml-15 d-none d-md-flex">
                        <button class="dropdown-toggle position-relative" type="button" id="message"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="lni lni-envelope"></i>
                            @if ($messagesCount ?? 0 > 0)
                                <span
                                    class="badge bg-primary rounded-circle position-absolute top-0 start-100 translate-middle">{{ $messagesCount ?? 0 }}</span>
                            @endif
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="message">
                            <li>
                                <a href="#">
                                    <div class="content">
                                        <h6>Guru Matematika</h6>
                                        <p>Jadwal remedial minggu ini.</p>
                                        <span>15 menit lalu</span>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    <div class="content">
                                        <h6>Siswa Kelas XI</h6>
                                        <p>Pertanyaan di forum e-learning.</p>
                                        <span>1 jam lalu</span>
                                    </div>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="profile-box ml-15">
                        <button class="dropdown-toggle bg-transparent border-0" type="button" id="profile"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="profile-info d-flex align-items-center">
                                <div class="image">
                                    <img src="{{ asset('backend/assets/images/profile/profile-image.png') }}" alt="Profile"
                                        style="width: 40px; border-radius: 50%;">
                                </div>
                                <div class="info ms-2">
                                    <h6 class="fw-500">{{ Auth::user()->name ?? 'User' }}</h6>
                                    <p>{{ Auth::user()->role ?? 'Admin' }}</p>
                                </div>
                            </div>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profile">
                            <li>
                                <div class="author-info d-flex align-items-center p-2">
                                    <div class="image">
                                        <img src="{{ asset('backend/assets/images/profile/profile-image.png') }}" alt="Profile"
                                            style="width: 50px; border-radius: 50%;">
                                    </div>
                                    <div class="content ms-2">
                                        <h4 class="text-sm">{{ Auth::user()->name ?? 'User' }}</h4>
                                        <a class="text-muted text-xs"
                                            href="#">{{ Auth::user()->email ?? 'email@example.com' }}</a>
                                    </div>
                                </div>
                            </li>
                            <li class="divider"></li>
                            <li><a href="#"><i class="lni lni-user"></i> Lihat Profil</a></li>
                            <li><a href="#"><i class="lni lni-robot"></i> AI Assistant <span
                                        class="badge bg-danger ms-2">PRO</span></a></li>
                            <li><a href="#"><i class="lni lni-cart"></i> Marketplace Sekolah <span
                                        class="badge bg-warning ms-2">Income</span></a></li>
                            <li><a href="#"><i class="lni lni-cog"></i> Pengaturan</a></li>
                            <li class="divider"></li>
                            <li>
                                <a href="#"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="lni lni-exit"></i> Keluar
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                    style="display: none;">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
