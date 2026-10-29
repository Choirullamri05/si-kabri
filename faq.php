<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex align-items-center mb-4">
        <i class="fas fa-question-circle fa-2x text-primary mr-3"></i>
        <h3 class="mb-0 text-gray-800">FAQ & Panduan Penggunaan SI-KABRI</h3>
    </div>

    <!-- FAQ Card -->
    <div class="card shadow mb-4">
        <div class="card-body">

            <!-- FAQ Accordion -->
            <div class="accordion" id="faqAccordion">

                <!-- FAQ Item 1 -->
                <div class="card border-0 mb-2">
                    <div class="card-header bg-light" id="faq1">
                        <h5 class="mb-0">
                            <button class="btn btn-link text-left w-100 text-decoration-none text-gray-800 collapsed" type="button" data-toggle="collapse" data-target="#collapse1" aria-expanded="false" aria-controls="collapse1">
                                <i class="fas fa-info-circle text-primary mr-2"></i>
                                <strong>1. Apa itu SI-KABRI?</strong>
                            </button>
                        </h5>
                    </div>
                    <div id="collapse1" class="collapse" aria-labelledby="faq1" data-parent="#faqAccordion">
                        <div class="card-body text-gray-700">
                            <p class="mb-0">SI-KABRI adalah <strong>Sistem Informasi Kliping Berita</strong> yang melakukan klasifikasi otomatis terhadap berita <span class="badge badge-success">Prioritas</span> dan <span class="badge badge-secondary">Non Prioritas</span> menggunakan algoritma <em>Naive Bayes</em>.</p>
                        </div>
                    </div>
                </div>

                <!-- FAQ Item 2 -->
                <div class="card border-0 mb-2">
                    <div class="card-header bg-light" id="faq2">
                        <h5 class="mb-0">
                            <button class="btn btn-link text-left w-100 text-decoration-none text-gray-800 collapsed" type="button" data-toggle="collapse" data-target="#collapse2" aria-expanded="false" aria-controls="collapse2">
                                <i class="fas fa-plus-circle text-primary mr-2"></i>
                                <strong>2. Bagaimana cara menambahkan berita?</strong>
                            </button>
                        </h5>
                    </div>
                    <div id="collapse2" class="collapse" aria-labelledby="faq2" data-parent="#faqAccordion">
                        <div class="card-body text-gray-700">
                            <ol class="mb-0">
                                <li>Masuk ke menu <strong>Menu Input → Tambah Berita</strong></li>
                                <li>Isi formulir dengan informasi berita yang diperlukan</li>
                                <li>Sistem akan otomatis menentukan kategori berita</li>
                                <li>Klik tombol <strong>Simpan</strong> untuk menyimpan data</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- FAQ Item 3 -->
                <div class="card border-0 mb-2">
                    <div class="card-header bg-light" id="faq3">
                        <h5 class="mb-0">
                            <button class="btn btn-link text-left w-100 text-decoration-none text-gray-800 collapsed" type="button" data-toggle="collapse" data-target="#collapse3" aria-expanded="false" aria-controls="collapse3">
                                <i class="fas fa-edit text-primary mr-2"></i>
                                <strong>3. Bagaimana jika sistem salah prediksi?</strong>
                            </button>
                        </h5>
                    </div>
                    <div id="collapse3" class="collapse" aria-labelledby="faq3" data-parent="#faqAccordion">
                        <div class="card-body text-gray-700">
                            <p>Anda dapat memperbaiki prediksi dengan langkah berikut:</p>
                            <ol class="mb-2">
                                <li>Buka halaman <strong>Edit Data</strong> pada berita tersebut</li>
                                <li>Centang opsi <strong>"Jadikan Data Latih"</strong></li>
                                <li>Klik tombol <strong>Simpan</strong></li>
                            </ol>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-lightbulb mr-2"></i>
                                Sistem akan belajar dari koreksi Anda dan meningkatkan akurasi prediksi berikutnya.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FAQ Item 4 -->
                <div class="card border-0 mb-2">
                    <div class="card-header bg-light" id="faq4">
                        <h5 class="mb-0">
                            <button class="btn btn-link text-left w-100 text-decoration-none text-gray-800 collapsed" type="button"
                                data-toggle="collapse" data-target="#collapse4" aria-expanded="false" aria-controls="collapse4">
                                <i class="fas fa-chart-line text-primary mr-2"></i>
                                <strong>4. Seberapa akurat hasil klasifikasi sistem?</strong>
                            </button>
                        </h5>
                    </div>
                    <div id="collapse4" class="collapse" aria-labelledby="faq4" data-parent="#faqAccordion">
                        <div class="card-body text-gray-700">
                            <p class="mb-2">Saat pengujian, model klasifikasi mencapai tingkat akurasi sebesar <strong>85,71%</strong>.</p>
                            <div class="alert alert-success mb-0">
                                <i class="fas fa-trophy mr-2"></i>
                                Angka ini dapat meningkat seiring bertambahnya data latih yang digunakan.
                            </div>
                        </div>
                    </div>
                </div>


                <!-- FAQ Item 5 -->
                <div class="card border-0 mb-2">
                    <div class="card-header bg-light" id="faq5">
                        <h5 class="mb-0">
                            <button class="btn btn-link text-left w-100 text-decoration-none text-gray-800 collapsed" type="button" data-toggle="collapse" data-target="#collapse5" aria-expanded="false" aria-controls="collapse5">
                                <i class="fas fa-file-excel text-primary mr-2"></i>
                                <strong>5. Bagaimana cara export laporan?</strong>
                            </button>
                        </h5>
                    </div>
                    <div id="collapse5" class="collapse" aria-labelledby="faq5" data-parent="#faqAccordion">
                        <div class="card-body text-gray-700">
                            <ol class="mb-0">
                                <li>Pilih menu <strong>Laporan Data</strong></li>
                                <li>Atur filter (tanggal, kategori, dll.) sesuai kebutuhan</li>
                                <li>Klik tombol <strong><i class="fa fa-file-excel"></i> Export Excel</strong></li>
                                <li>File akan otomatis terunduh dalam format Excel (.xlsx)</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- FAQ Item 6 -->
                <div class="card border-0 mb-2">
                    <div class="card-header bg-light" id="faq6">
                        <h5 class="mb-0">
                            <button class="btn btn-link text-left w-100 text-decoration-none text-gray-800 collapsed" type="button" data-toggle="collapse" data-target="#collapse6" aria-expanded="false" aria-controls="collapse6">
                                <i class="fas fa-users text-primary mr-2"></i>
                                <strong>6. Siapa yang bisa mengakses sistem?</strong>
                            </button>
                        </h5>
                    </div>
                    <div id="collapse6" class="collapse" aria-labelledby="faq6" data-parent="#faqAccordion">
                        <div class="card-body text-gray-700">
                            <p class="mb-2">Sistem SI-KABRI dapat diakses oleh:</p>
                            <ul class="mb-0">
                                <li><strong>Admin:</strong> Akses penuh ke semua fitur dan pengaturan sistem</li>
                                <li><strong>Staf Pengelola Media:</strong> Dapat menambah, edit, dan mengelola berita</li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>