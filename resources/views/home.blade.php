<!-- filepath: /home/anone/Projects/Laravel/hyper-web-scholl/resources/views/home.blade.php -->
@extends('admin.layouts.app')
@section('title', 'Dashboard')
@section('content')
    <section class="section">
        <div class="container-fluid">
            <!-- ========== title-wrapper start ========== -->
            <div class="title-wrapper pt-30">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="title">
                            <h2>Dashboard</h2>
                        </div>
                    </div>
                    <!-- end col -->
                    <div class="col-md-6">
                        <div class="breadcrumb-wrapper">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item">
                                        <a href="#0">Dashboard</a>
                                    </li>
                                    <li class="breadcrumb-item active" aria-current="page">
                                        Sekolah
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                    <!-- end col -->
                </div>
                <!-- end row -->
            </div>
            <!-- ========== title-wrapper end ========== -->
            <div class="row">
                <div class="col-xl-3 col-lg-4 col-sm-6">
                    <div class="icon-card mb-30">
                        <div class="icon purple">
                            <i class="lni lni-users"></i>
                        </div>
                        <div class="content">
                            <h6 class="mb-10">Total Siswa</h6>
                            <h3 class="text-bold mb-10">1,234</h3>
                            <p class="text-sm text-success">
                                <i class="lni lni-arrow-up"></i> +3.00%
                                <span class="text-gray">(30 hari terakhir)</span>
                            </p>
                        </div>
                    </div>
                    <!-- End Icon Card -->
                </div>
                <!-- End Col -->
                <div class="col-xl-3 col-lg-4 col-sm-6">
                    <div class="icon-card mb-30">
                        <div class="icon success">
                            <i class="lni lni-graduation"></i>
                        </div>
                        <div class="content">
                            <h6 class="mb-10">Total Guru</h6>
                            <h3 class="text-bold mb-10">56</h3>
                            <p class="text-sm text-success">
                                <i class="lni lni-arrow-up"></i> +1.50%
                                <span class="text-gray">Bulan ini</span>
                            </p>
                        </div>
                    </div>
                    <!-- End Icon Card -->
                </div>
                <!-- End Col -->
                <div class="col-xl-3 col-lg-4 col-sm-6">
                    <div class="icon-card mb-30">
                        <div class="icon primary">
                            <i class="lni lni-book"></i>
                        </div>
                        <div class="content">
                            <h6 class="mb-10">Total Kelas</h6>
                            <h3 class="text-bold mb-10">18</h3>
                            <p class="text-sm text-success">
                                <i class="lni lni-arrow-up"></i> +2.00%
                                <span class="text-gray">Tahun ini</span>
                            </p>
                        </div>
                    </div>
                    <!-- End Icon Card -->
                </div>
                <!-- End Col -->
                <div class="col-xl-3 col-lg-4 col-sm-6">
                    <div class="icon-card mb-30">
                        <div class="icon orange">
                            <i class="lni lni-pencil-alt"></i>
                        </div>
                        <div class="content">
                            <h6 class="mb-10">Total Mata Pelajaran</h6>
                            <h3 class="text-bold mb-10">45</h3>
                            <p class="text-sm text-success">
                                <i class="lni lni-arrow-up"></i> +4.00%
                                <span class="text-gray">Tahun ini</span>
                            </p>
                        </div>
                    </div>
                    <!-- End Icon Card -->
                </div>
                <!-- End Col -->
            </div>
            <!-- End Row -->
        </div>
        <!-- end container -->
    </section>
@endsection