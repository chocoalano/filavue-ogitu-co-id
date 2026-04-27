<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create all Ogitu application tables using Laravel Schema Builder.
     *
     * Notes:
     * - The Laravel internal `migrations` table is intentionally not created here.
     * - Foreign keys are added after all tables exist to avoid parent-table ordering issues.
     * - Views and stored procedures are not included because Laravel has no native Schema Builder API for them.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        try {
        Schema::create('article_contents', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->increments('id');
            $table->unsignedInteger('article_id');
            $table->longText('content');
            $table->longText('tags')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique('article_id', 'article_contents_article_id_unique');
            $table->fullText('content', 'article_contents_content_fulltext');
        });

        Schema::create('articles', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->increments('id');
            $table->string('title', 255);
            $table->string('slug', 255);
            $table->string('seo_title', 255)->nullable();
            $table->text('seo_description')->nullable();
            $table->string('image_banner', 255)->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();

            $table->unique('slug', 'articles_slug_unique');
            $table->index(['is_published', 'published_at'], 'articles_is_published_published_at_index');
            $table->fullText(['title', 'seo_title'], 'articles_title_seo_title_fulltext');
        });

        Schema::create('bug_report_attachments', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->unsignedBigInteger('bug_report_id');
            $table->string('file_path', 255)->comment('Path file di storage');
            $table->string('file_name', 255)->comment('Nama asli file yang diupload');
            $table->string('mime_type', 80)->nullable()->comment('MIME type file (misal: image/png, image/jpeg)');
            $table->unsignedInteger('file_size')->nullable()->comment('Ukuran file dalam bytes');
            $table->string('caption', 255)->nullable()->comment('Keterangan singkat tentang screenshot/file');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('bug_report_id', 'bug_report_attachments_bug_report_id_index');
        });

        Schema::create('bug_report_comments', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->unsignedBigInteger('bug_report_id');
            $table->unsignedBigInteger('user_id')->nullable()->comment('User admin yang membuat entri (null = dibuat otomatis sistem)');
            $table->string('type', 30)->default('comment')->comment('Tipe entri: comment | internal_note | handling_step | status_change | assignment_change | category_change | resolution');
            $table->text('body')->comment('Isi komentar, catatan, atau deskripsi langkah penanganan');
            $table->string('old_value', 100)->nullable()->comment('Nilai lama sebelum perubahan (untuk status_change, assignment_change, category_change)');
            $table->string('new_value', 100)->nullable()->comment('Nilai baru setelah perubahan');
            $table->unsignedSmallInteger('step_number')->nullable()->comment('Nomor urut langkah penanganan (untuk tipe handling_step)');
            $table->boolean('is_pinned')->default(false)->comment('Pin komentar penting agar tampil di bagian atas');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('bug_report_id', 'bug_report_comments_bug_report_id_index');
            $table->index(['bug_report_id', 'type'], 'bug_report_comments_bug_report_id_type_index');
            $table->index('type', 'bug_report_comments_type_index');
            $table->index('user_id', 'bug_report_comments_user_id_index');
        });

        Schema::create('bug_reports', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->string('reporter_type', 20)->comment('Tipe pelapor: customer | user | anonymous');
            $table->unsignedBigInteger('reporter_id')->nullable()->comment('ID customer atau user (null jika anonymous)');
            $table->string('reporter_name', 100)->nullable()->comment('Nama pelapor (diisi jika anonymous)');
            $table->string('reporter_email', 150)->nullable()->comment('Email pelapor (diisi jika anonymous)');
            $table->string('title', 255)->comment('Judul singkat bug');
            $table->text('description')->comment('Deskripsi lengkap bug yang dialami');
            $table->text('steps_to_reproduce')->nullable()->comment('Langkah-langkah untuk mereproduksi bug');
            $table->text('expected_behavior')->nullable()->comment('Perilaku yang seharusnya terjadi');
            $table->text('actual_behavior')->nullable()->comment('Perilaku yang sebenarnya terjadi');
            $table->string('platform', 20)->comment('Platform: web | mobile');
            $table->string('source', 30)->comment('Sumber aplikasi: storefront | admin_console');
            $table->string('web_screen', 20)->nullable()->comment('Ukuran layar web: desktop | tablet | smartphone');
            $table->string('mobile_type', 20)->nullable()->comment('Tipe mobile OS: android | ios');
            $table->string('page_url', 500)->nullable()->comment('URL halaman tempat bug ditemukan');
            $table->string('browser', 80)->nullable()->comment('Nama browser (misal: Chrome, Firefox, Safari)');
            $table->string('browser_version', 30)->nullable()->comment('Versi browser');
            $table->string('os', 80)->nullable()->comment('Sistem operasi (misal: Windows 11, macOS 14, Android 14)');
            $table->string('os_version', 30)->nullable()->comment('Versi sistem operasi');
            $table->string('device_model', 100)->nullable()->comment('Model perangkat (khusus mobile, misal: Samsung Galaxy S24)');
            $table->string('app_version', 30)->nullable()->comment('Versi aplikasi saat bug terjadi');
            $table->string('screen_resolution', 20)->nullable()->comment('Resolusi layar (misal: 1920x1080)');
            $table->string('error_category', 30)->nullable()->comment('Kategori akar masalah: human_error | system_error | ui_ux_error | performance_issue | data_error | security_issue | configuration_error | unknown');
            $table->string('severity', 20)->default('medium')->comment('Keparahan: critical | high | medium | low');
            $table->string('priority', 20)->default('medium')->comment('Prioritas: urgent | high | medium | low');
            $table->string('status', 20)->default('open')->comment('Status: open | under_review | confirmed | in_progress | resolved | closed | rejected | duplicate');
            $table->unsignedBigInteger('duplicate_of_id')->nullable()->comment('ID bug_report yang ini merupakan duplikatnya');
            $table->unsignedBigInteger('assigned_to')->nullable()->comment('ID user (admin/developer) yang ditugaskan menangani');
            $table->text('resolution_note')->nullable()->comment('Catatan resolusi atau alasan penolakan');
            $table->timestamp('resolved_at')->nullable()->comment('Waktu bug selesai diperbaiki');
            $table->timestamp('closed_at')->nullable()->comment('Waktu laporan ditutup');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('assigned_to', 'bug_reports_assigned_to_index');
            $table->index('duplicate_of_id', 'bug_reports_duplicate_of_id_foreign');
            $table->index('error_category', 'bug_reports_error_category_index');
            $table->index('platform', 'bug_reports_platform_index');
            $table->index('priority', 'bug_reports_priority_index');
            $table->index('reporter_type', 'bug_reports_reporter_type_index');
            $table->index(['reporter_type', 'reporter_id'], 'bug_reports_reporter_type_reporter_id_index');
            $table->index('severity', 'bug_reports_severity_index');
            $table->index('source', 'bug_reports_source_index');
            $table->index('status', 'bug_reports_status_index');
        });

        Schema::create('cache', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->string('key', 255);
            $table->mediumText('value');
            $table->integer('expiration');

            $table->primary('key');
            $table->index('expiration', 'cache_expiration_index');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->string('key', 255);
            $table->string('owner', 255);
            $table->integer('expiration');

            $table->primary('key');
            $table->index('expiration', 'cache_locks_expiration_index');
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->increments('id');
            $table->unsignedInteger('cart_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('qty');
            $table->decimal('unit_price', 15, 2);
            $table->string('currency', 3)->default('IDR');
            $table->string('product_sku', 255);
            $table->string('product_name', 255);
            $table->decimal('row_total', 15, 2);
            $table->longText('meta_json')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('cart_id', 'cart_items_cart_id_index');
            $table->index('product_id', 'cart_items_product_id_index');
        });

        Schema::create('carts', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->increments('id');
            $table->unsignedInteger('customer_id')->nullable();
            $table->string('session_id', 255)->nullable();
            $table->string('currency', 3)->default('IDR');
            $table->decimal('subtotal_amount', 15, 2)->default('0.00');
            $table->decimal('discount_amount', 15, 2)->default('0.00');
            $table->decimal('shipping_amount', 15, 2)->default('0.00');
            $table->decimal('tax_amount', 15, 2)->default('0.00');
            $table->decimal('grand_total', 15, 2)->default('0.00');
            $table->longText('applied_promos')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('customer_id', 'carts_customer_id_index');
            $table->index('session_id', 'carts_session_id_index');
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('slug', 255);
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('image', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique('slug', 'categories_slug_unique');
            $table->index(['is_active', 'sort_order'], 'categories_is_active_sort_order_index');
            $table->index('parent_id', 'categories_parent_id_foreign');
        });

        Schema::create('commodity_codes', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->string('code', 255);
            $table->string('name', 255);
            $table->boolean('dangerous_good')->default(true);
            $table->boolean('is_quarantine')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique('code', 'commodity_codes_code_unique');
        });

        Schema::create('contents', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('title', 255);
            $table->string('slug', 255);
            $table->longText('content')->nullable();
            $table->string('file', 255)->nullable();
            $table->string('vlink', 500)->nullable();
            $table->string('content_type', 50)->nullable();
            $table->string('thumbnail_url', 255)->nullable();
            $table->unsignedInteger('duration_sec')->nullable();
            $table->string('status', 50)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique('slug', 'contents_slug_unique');
            $table->index(['category_id', 'status'], 'contents_category_id_status_index');
            $table->index('created_by', 'contents_created_by_index');
        });

        Schema::create('contents_category', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name', 255);
            $table->string('slug', 255);
            $table->string('icon_key', 100)->nullable();
            $table->string('accent_hex', 7)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('thumbnail_url', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique('slug', 'contents_category_slug_unique');
            $table->index('parent_id', 'contents_category_parent_id_index');
        });

        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id')->comment('Primary key alamat customer');
            $table->unsignedBigInteger('customer_id')->comment('Relasi ke tabel customers sebagai pemilik alamat');
            $table->string('label', 255)->nullable()->comment('Label alamat, misalnya: Rumah, Kantor, dll');
            $table->boolean('is_default')->default(false)->comment('Menandakan apakah ini alamat utama (default) customer');
            $table->string('recipient_name', 255)->comment('Nama penerima barang pada alamat ini');
            $table->string('recipient_phone', 255)->comment('Nomor telepon penerima pada alamat ini');
            $table->text('address_line1')->comment('Detail utama alamat (jalan, blok, nomor rumah)');
            $table->text('address_line2')->nullable()->comment('Detail tambahan alamat (patokan, gedung, unit, dll)');
            $table->string('province_label', 100)->comment('Nama provinsi sesuai layanan ekspedisi / API');
            $table->integer('province_id')->comment('ID provinsi sesuai referensi / API pihak ketiga');
            $table->string('city_label', 100)->comment('Nama kota/kabupaten sesuai layanan ekspedisi / API');
            $table->integer('city_id')->comment('ID kota/kabupaten sesuai referensi / API pihak ketiga');
            $table->string('district', 255)->nullable();
            $table->string('district_lion', 255)->nullable();
            $table->string('postal_code', 255)->nullable()->comment('Kode pos alamat penerima');
            $table->string('country', 255)->default('Indonesia')->comment('Negara alamat, default Indonesia');
            $table->text('description')->nullable()->comment('Catatan tambahan mengenai alamat ini');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('customer_id', 'customer_addresses_customer_id_index');
        });

        Schema::create('customer_bonus_cashbacks', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->increments('id');
            $table->unsignedInteger('member_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->decimal('amount', 15, 2)->default('0.00');
            $table->decimal('index_value', 15, 2)->nullable();
            $table->tinyInteger('status')->default(0)->comment('0=pending, 1=released');
            $table->text('description')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('member_id', 'customer_bonus_cashbacks_member_id_index');
        });

        Schema::create('customer_bonus_rewards', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->increments('id');
            $table->unsignedInteger('member_id');
            $table->string('reward_type', 255)->nullable()->comment('promotion, lifetime');
            $table->string('reward', 225);
            $table->decimal('bv', 15, 2);
            $table->decimal('amount', 15, 2)->default('0.00');
            $table->decimal('index_value', 15, 2)->nullable();
            $table->tinyInteger('status')->default(0)->comment('0=pending, 1=released');
            $table->text('description')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('member_id', 'customer_bonus_rewards_member_id_index');
        });

        Schema::create('customer_bonus_royalty', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->increments('id')->comment('Primary key bonus retail customer');
            $table->unsignedInteger('member_id')->nullable()->comment('Member penerima bonus retail');
            $table->unsignedInteger('from_member_id')->nullable()->comment('Member yang memicu bonus retail');
            $table->decimal('amount', 15, 2)->default('0.00')->comment('Nominal bonus retail yang diterima');
            $table->decimal('index_value', 15, 2)->default('0.00')->comment('Nilai index/point yang berkaitan dengan bonus retail ini');
            $table->tinyInteger('status')->default(0)->comment('Status bonus retail: 0 = pending, 1 = sudah dibayarkan / dirilis');
            $table->text('description')->nullable()->comment('Catatan tambahan terkait bonus retail');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('from_member_id', 'customer_bonus_retails_from_member_id_index');
            $table->index('member_id', 'customer_bonus_retails_member_id_index');
        });

        Schema::create('customer_bonus_sponsors', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->increments('id')->comment('Primary key bonus sponsor customer');
            $table->unsignedInteger('member_id')->nullable()->comment('Member penerima bonus sponsor');
            $table->unsignedInteger('from_member_id')->nullable()->comment('Member yang direkrut (downline) yang memicu bonus sponsor');
            $table->decimal('amount', 15, 2)->default('0.00')->comment('Nominal bonus sponsor yang diterima');
            $table->decimal('index_value', 15, 2)->default('0.00')->comment('Nilai index/point yang berkaitan dengan bonus sponsor ini');
            $table->tinyInteger('status')->default(0)->comment('Status bonus sponsor: 0 = pending, 1 = sudah dibayarkan / dirilis');
            $table->text('description')->nullable()->comment('Catatan tambahan terkait bonus sponsor');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('from_member_id', 'customer_bonus_sponsors_from_member_id_index');
            $table->index('member_id', 'customer_bonus_sponsors_member_id_index');
        });

        Schema::create('customer_bonuses', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->increments('id')->comment('Primary key bonus customer');
            $table->unsignedInteger('member_id')->nullable()->comment('Member penerima bonus');
            $table->decimal('amount', 15, 2)->default('0.00')->comment('Nominal bonus kotor yang diterima sebelum perhitungan pajak');
            $table->decimal('index_value', 15, 2)->default('0.00')->comment('Nilai index/point yang berkaitan dengan bonus ini');
            $table->decimal('tax_netto', 15, 2)->default('0.00')->comment('Nominal bonus bersih setelah pajak (netto)');
            $table->integer('tax_percent')->default(0)->comment('Persentase pajak yang dikenakan terhadap bonus');
            $table->decimal('tax_value', 15, 2)->default('0.00')->comment('Nominal pajak yang dipotong dari bonus');
            $table->tinyInteger('status')->default(0)->comment('Status bonus: 0 = pending, 1 = sudah dibayarkan / dirilis');
            $table->text('description')->nullable()->comment('Catatan tambahan terkait bonus customer');
            $table->date('date')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('member_id', 'customer_bonuses_member_id_index');
        });

        Schema::create('customer_bv_rewards', function (Blueprint $table) {
            $table->charset = 'utf8mb3';
            $table->collation = 'utf8mb3_general_ci';
            $table->increments('id');
            $table->unsignedInteger('member_id');
            $table->unsignedInteger('reward_id');
            $table->decimal('omzet_left', 15, 2)->default('0.00');
            $table->decimal('omzet_right', 15, 2)->default('0.00');
            $table->boolean('status')->default(false);
            $table->dateTime('created_on');

            $table->index('member_id', 'member_id');
            $table->index('reward_id', 'reward_id');
        });

        Schema::create('customer_content_progress', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->unsignedInteger('customer_id');
            $table->unsignedBigInteger('content_category_id');
            $table->unsignedBigInteger('content_id')->nullable();
            $table->decimal('progress', 5, 4)->default('0.0000');
            $table->unsignedInteger('position_sec')->default(0);
            $table->timestamp('last_watched_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['customer_id', 'content_category_id'], 'customer_content_progress_customer_id_content_category_id_unique');
            $table->index('content_category_id', 'customer_content_progress_content_category_id_foreign');
            $table->index('content_id', 'customer_content_progress_content_id_foreign');
            $table->index(['customer_id', 'last_watched_at'], 'customer_content_progress_customer_id_last_watched_at_index');
        });

        Schema::create('customer_module_progress', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->unsignedInteger('customer_id');
            $table->unsignedBigInteger('content_id');
            $table->boolean('is_completed')->default(false);
            $table->unsignedInteger('position_sec')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['customer_id', 'content_id'], 'customer_module_progress_customer_id_content_id_unique');
            $table->index(['content_id', 'is_completed'], 'customer_module_progress_content_id_is_completed_index');
        });

        Schema::create('customer_network_matrixes', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->increments('id')->comment('Primary key matrix jaringan customer');
            $table->unsignedInteger('member_id')->nullable()->comment('Member yang berada di matrix jaringan');
            $table->unsignedInteger('sponsor_id')->nullable()->comment('Sponsor/introducer yang merekrut member ini');
            $table->tinyInteger('level')->default(1)->comment('Level kedalaman member dari sponsor di matrix jaringan');
            $table->text('description')->nullable()->comment('Catatan tambahan terkait posisi member di matrix');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('member_id', 'customer_network_matrixes_member_id_index');
            $table->index('sponsor_id', 'customer_network_matrixes_sponsor_id_index');
        });

        Schema::create('customer_npwp', function (Blueprint $table) {
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';
            $table->increments('id');
            $table->unsignedInteger('member_id');
            $table->string('nama', 50);
            $table->string('npwp', 50);
            $table->tinyInteger('jk');
            $table->date('npwp_date');
            $table->string('alamat', 255);
            $table->enum('menikah', ['y', 'n'])->default('y')->comment('0Single 1Menikah');
            $table->enum('anak', ['0', '1', '2', '3'])->default(0);
            $table->enum('kerja', ['n', 'y'])->default('n');
            $table->string('office', 50)->default('-');
            $table->dateTime('created');
            $table->string('createdby', 20);
            $table->dateTime('updated');
            $table->string('updatedby', 20);

            $table->index('id', 'id');
            $table->index('member_id', 'member_id');
        });

        Schema::create('customer_package', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->increments('id');
            $table->string('name', 255);
            $table->string('alias', 100);
            $table->decimal('price', 10, 2);
            $table->integer('pv');
            $table->integer('pr');
            $table->decimal('sponsor', 12, 2);
            $table->decimal('discount', 10, 2)->default('0.00');
        });

        Schema::create('customer_password_resets', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->string('email', 255);
            $table->string('token', 255);
            $table->timestamp('created_at')->nullable();

            $table->primary('email');
        });

        Schema::create('customer_point_accounts', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->unsignedInteger('customer_id');
            $table->unsignedBigInteger('current_balance')->default(0);
            $table->unsignedBigInteger('locked_balance')->default(0);
            $table->unsignedBigInteger('lifetime_earned')->default(0);
            $table->unsignedBigInteger('lifetime_spent')->default(0);
            $table->unsignedBigInteger('version')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique('customer_id', 'uq_customer_point_accounts_customer');
        });

        Schema::create('customer_point_ledgers', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->unsignedInteger('customer_id');
            $table->unsignedBigInteger('point_account_id');
            $table->string('entry_type', 20);
            $table->string('source_type', 30);
            $table->unsignedBigInteger('source_id')->nullable();
            $table->bigInteger('delta_points');
            $table->unsignedBigInteger('balance_before');
            $table->unsignedBigInteger('balance_after');
            $table->string('reference_no', 100)->nullable();
            $table->string('idempotency_key', 100)->nullable();
            $table->string('note', 255)->nullable();
            $table->json('meta_json')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique('idempotency_key', 'uq_customer_point_ledgers_idempotency_key');
            $table->unique('reference_no', 'uq_customer_point_ledgers_reference_no');
            $table->index('point_account_id', 'customer_point_ledgers_point_account_id_foreign');
            $table->index('created_by', 'fk_customer_point_ledgers_created_by');
            $table->index(['customer_id', 'created_at'], 'idx_customer_point_ledgers_customer_created');
            $table->index(['source_type', 'source_id'], 'idx_customer_point_ledgers_source');
        });

        Schema::create('customer_pph', function (Blueprint $table) {
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';
            $table->increments('id');
            $table->unsignedInteger('member_id');
            $table->string('nama', 30);
            $table->tinyInteger('jk');
            $table->string('alamat', 255);
            $table->string('npwp', 20);
            $table->enum('krj', ['y', 'n'])->default('n');
            $table->string('kantor', 50)->default('Unknown');
            $table->enum('status', ['0', '1'])->default(0);
            $table->tinyInteger('kid');
            $table->decimal('bonus', 14, 2)->default('0.00');
            $table->date('periode');
            $table->double('ptkp');
            $table->double('pkp');
            $table->double('sum_of_pkp');
            $table->double('sum_of_pkp_temp');
            $table->double('akumulasi_bruto_temp');
            $table->double('akumulasi_ptkp');
            $table->double('akumulasi_bruto');
            $table->double('tarif');
            $table->double('tarif_npwp');
            $table->double('pph21');
            $table->double('buffer');
            $table->dateTime('created');
            $table->string('created_by', 10);

            $table->index('member_id', 'member_id');
        });

        Schema::create('customer_reward_instances', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->unsignedInteger('customer_id');
            $table->unsignedBigInteger('reward_item_id');
            $table->string('source_type', 30);
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedInteger('qty')->default(1);
            $table->string('status', 30)->default('granted');
            $table->boolean('requires_shipping')->default(false);
            $table->unsignedBigInteger('shipping_address_id')->nullable();
            $table->json('address_snapshot_json')->nullable();
            $table->string('fulfillment_ref_no', 100)->nullable();
            $table->string('tracking_no', 100)->nullable();
            $table->dateTime('shipped_at')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->dateTime('used_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->json('reward_snapshot_json')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('reward_item_id', 'customer_reward_instances_reward_item_id_foreign');
            $table->index('shipping_address_id', 'fk_customer_reward_instances_shipping_address');
            $table->index(['customer_id', 'status'], 'idx_customer_reward_instances_customer_status');
            $table->index(['source_type', 'source_id'], 'idx_customer_reward_instances_source');
        });

        Schema::create('customer_vouchers', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->unsignedBigInteger('promotion_id');
            $table->unsignedInteger('customer_id');
            $table->string('code', 50)->comment('Kode unik voucher yang digunakan customer saat checkout');
            $table->enum('discount_type', ['percent', 'fixed'])->default('percent')->comment('percent = persen, fixed = nominal Rupiah');
            $table->decimal('discount_amount', 15, 2)->default('0.00')->comment('Nilai diskon: 0-100 untuk persen, atau nominal Rp untuk fixed');
            $table->decimal('min_spend', 15, 2)->nullable()->comment('Minimal total belanja agar voucher berlaku');
            $table->timestamp('valid_from')->nullable()->comment('Override tanggal mulai (default: promotion.start_at)');
            $table->timestamp('valid_until')->nullable()->comment('Override tanggal berakhir (default: promotion.end_at)');
            $table->boolean('is_active')->default(true);
            $table->timestamp('used_at')->nullable()->comment('Waktu voucher digunakan');
            $table->unsignedBigInteger('used_by_order_id')->nullable();
            $table->text('notes')->nullable()->comment('Catatan dari admin');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique('code', 'customer_vouchers_code_unique');
            $table->index(['code', 'is_active'], 'customer_vouchers_code_is_active_index');
            $table->index(['customer_id', 'is_active'], 'customer_vouchers_customer_id_is_active_index');
            $table->index('promotion_id', 'customer_vouchers_promotion_id_foreign');
            $table->index('used_by_order_id', 'customer_vouchers_used_by_order_id_foreign');
        });

        Schema::create('customer_wallet_transactions', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->increments('id');
            $table->unsignedInteger('customer_id');
            $table->enum('type', ['topup', 'withdrawal', 'bonus', 'purchase', 'refund', 'tax']);
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->string('payment_method', 255)->nullable();
            $table->string('transaction_ref', 255)->nullable();
            $table->string('midtrans_transaction_id', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->boolean('is_system')->nullable()->comment('json pattern');
            $table->string('midtrans_signature_key', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique('transaction_ref', 'customer_wallet_transactions_transaction_ref_unique');
            $table->index('created_at', 'customer_wallet_transactions_created_at_index');
            $table->index(['customer_id', 'status'], 'customer_wallet_transactions_customer_id_status_index');
            $table->index(['customer_id', 'type'], 'customer_wallet_transactions_customer_id_type_index');
        });

        Schema::create('customer_whatsapp_confirmations', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->unsignedInteger('customer_id')->nullable();
            $table->string('phone', 20)->comment('Nomor WA yang telah mengirim pesan ke sistem');
            $table->timestamp('confirmed_at')->comment('Pertama kali customer mengirim pesan ke sistem');
            $table->timestamp('last_received_at')->comment('Terakhir kali pesan diterima dari nomor ini');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique('phone', 'customer_whatsapp_confirmations_phone_unique');
            $table->index('customer_id', 'customer_whatsapp_confirmations_customer_id_index');
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->increments('id')->comment('Primary key customer');
            $table->unsignedInteger('sponsor_id')->nullable()->comment('sponsor referral');
            $table->string('ref_code', 50)->nullable();
            $table->string('username', 50)->nullable()->comment('username unique');
            $table->string('nik', 50)->nullable();
            $table->string('name', 255)->comment('Nama lengkap customer');
            $table->string('email', 255)->comment('Email untuk login dan komunikasi');
            $table->string('phone', 255)->nullable()->comment('Nomor telepon / WhatsApp customer');
            $table->string('password', 255)->comment('Password yang telah di-hash untuk autentikasi');
            $table->enum('gender', ['male', 'female', 'l', 'p'])->nullable();
            $table->text('alamat')->nullable();
            $table->string('address', 225)->nullable();
            $table->unsignedInteger('city_id')->nullable();
            $table->unsignedInteger('province_id')->nullable();
            $table->timestamp('email_verified_at')->nullable()->comment('Waktu ketika email customer terverifikasi');
            $table->string('ewallet_id', 255)->nullable()->comment('ID unik dompet elektronik customer');
            $table->decimal('ewallet_saldo', 15, 2)->default('0.00')->comment('Saldo dompet elektronik customer');
            $table->decimal('bonus_pending', 15, 2)->default('0.00');
            $table->decimal('bonus_processed', 15, 2)->default('0.00');
            $table->string('bank_name', 100)->nullable()->comment('Nama bank untuk penarikan');
            $table->string('bank_account', 50)->nullable()->comment('Nomor rekening bank');
            $table->text('description')->nullable()->comment('Catatan tambahan mengenai customer');
            $table->unsignedInteger('package_id')->nullable()->comment('paket sesuai total omset member');
            $table->unsignedInteger('sponsor_left')->default(0)->comment('jumlah member yg disponsorin kaki kiri');
            $table->unsignedInteger('pv')->default(0)->comment('jumlah pv untuk pairing dr kaki kiri');
            $table->decimal('omzet', 15, 2)->default('0.00');
            $table->decimal('omzet_group', 15, 2)->default('0.00');
            $table->enum('level', ['associate', 'senior associate', 'executive', 'director'])->nullable()->comment('1 = Level Associate, 2 = Level Senior Associate, 3 = Level Executive, 4 = Director');
            $table->boolean('is_stockist')->default(false);
            $table->string('stockist_kabupaten_id', 10)->nullable();
            $table->string('stockist_kabupaten_name', 255)->nullable();
            $table->unsignedInteger('stockist_province_id')->nullable();
            $table->string('stockist_province_name', 255)->nullable();
            $table->boolean('network_generated')->default(false);
            $table->tinyInteger('status')->default(1)->comment('Status Customer 1=prosepek, 2=pasif, 3=active');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('remember_token', 100)->nullable();

            $table->unique('ewallet_id', 'customers_ewallet_id_unique');
            $table->unique('ref_code', 'customers_ref_code_unique');
            $table->index('is_stockist', 'customers_is_stockist_index');
            $table->index('package_id', 'customers_package_id_foreign');
            $table->index('sponsor_id', 'customers_sponsor_id_index');
            $table->index('stockist_kabupaten_id', 'customers_stockist_kabupaten_id_index');
            $table->index('stockist_province_id', 'customers_stockist_province_id_index');
        });

        Schema::create('customers_rewards', function (Blueprint $table) {
            $table->charset = 'utf8mb3';
            $table->collation = 'utf8mb3_general_ci';
            $table->increments('id');
            $table->unsignedInteger('member_id');
            $table->unsignedInteger('reward_id');
            $table->string('reward', 225);
            $table->decimal('total_bv_achieved', 15, 2)->default('0.00');
            $table->boolean('type');
            $table->boolean('status')->default(false);
            $table->dateTime('created_at')->nullable();
        });

        Schema::create('exports', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->timestamp('completed_at')->nullable();
            $table->string('file_disk', 255);
            $table->string('file_name', 255)->nullable();
            $table->string('exporter', 255);
            $table->unsignedInteger('processed_rows')->default(0);
            $table->unsignedInteger('total_rows');
            $table->unsignedInteger('successful_rows')->default(0);
            $table->unsignedBigInteger('user_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('user_id', 'exports_user_id_foreign');
        });

        Schema::create('failed_import_rows', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->longText('data');
            $table->unsignedBigInteger('import_id');
            $table->text('validation_error')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('import_id', 'failed_import_rows_import_id_foreign');
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->string('uuid', 255);
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();

            $table->unique('uuid', 'failed_jobs_uuid_unique');
        });

        Schema::create('gacha_board_slots', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->unsignedBigInteger('board_id');
            $table->unsignedInteger('slot_no');
            $table->string('balloon_code', 60);
            $table->unsignedInteger('row_no')->nullable();
            $table->unsignedInteger('col_no')->nullable();
            $table->string('balloon_color', 30)->nullable();
            $table->unsignedBigInteger('reward_item_id');
            $table->json('reward_snapshot_json')->nullable();
            $table->string('status', 20)->default('available');
            $table->unsignedInteger('reserved_by_customer_id')->nullable();
            $table->dateTime('reserved_at')->nullable();
            $table->dateTime('reservation_expires_at')->nullable();
            $table->unsignedInteger('popped_by_customer_id')->nullable();
            $table->dateTime('popped_at')->nullable();
            $table->string('checksum_hash', 128)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['board_id', 'balloon_code'], 'uq_gacha_board_slots_board_balloon_code');
            $table->unique(['board_id', 'slot_no'], 'uq_gacha_board_slots_board_slot_no');
            $table->index('reward_item_id', 'gacha_board_slots_reward_item_id_foreign');
            $table->index(['board_id', 'status'], 'idx_gacha_board_slots_board_status');
            $table->index('popped_by_customer_id', 'idx_gacha_board_slots_popped_by_customer');
            $table->index('reserved_by_customer_id', 'idx_gacha_board_slots_reserved_by_customer');
        });

        Schema::create('gacha_boards', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->unsignedBigInteger('campaign_id');
            $table->string('board_code', 60);
            $table->string('title', 150)->nullable();
            $table->unsignedInteger('rows');
            $table->unsignedInteger('cols');
            $table->unsignedInteger('total_slots');
            $table->unsignedInteger('available_slots');
            $table->unsignedInteger('popped_slots')->default(0);
            $table->string('status', 20)->default('draft');
            $table->dateTime('generated_at')->nullable();
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->dateTime('activated_at')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['campaign_id', 'board_code'], 'uq_gacha_boards_campaign_board_code');
            $table->index('generated_by', 'fk_gacha_boards_generated_by');
            $table->index(['campaign_id', 'status'], 'idx_gacha_boards_campaign_status');
        });

        Schema::create('gacha_campaign_reward_rules', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('reward_item_id');
            $table->unsignedInteger('quota_total')->nullable();
            $table->unsignedInteger('quota_per_board')->nullable();
            $table->decimal('weight', 12, 4)->nullable();
            $table->boolean('is_jackpot')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['campaign_id', 'reward_item_id'], 'uq_gacha_campaign_reward_rules_campaign_reward');
            $table->index('reward_item_id', 'gacha_campaign_reward_rules_reward_item_id_foreign');
            $table->index(['campaign_id', 'is_active'], 'idx_gacha_campaign_reward_rules_campaign_active');
        });

        Schema::create('gacha_campaigns', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->string('code', 50);
            $table->string('name', 150);
            $table->string('slug', 180);
            $table->text('description')->nullable();
            $table->string('gacha_model', 30)->default('balloon_pop');
            $table->unsignedBigInteger('point_cost_per_draw');
            $table->unsignedInteger('max_draw_per_customer_per_day')->nullable();
            $table->unsignedInteger('max_draw_per_customer_total')->nullable();
            $table->boolean('requires_manual_pick')->default(true);
            $table->boolean('guaranteed_prize')->default(true);
            $table->string('status', 20)->default('draft');
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->string('banner_image', 255)->nullable();
            $table->json('terms_json')->nullable();
            $table->json('metadata_json')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique('code', 'uq_gacha_campaigns_code');
            $table->unique('slug', 'uq_gacha_campaigns_slug');
            $table->index('created_by', 'fk_gacha_campaigns_created_by');
            $table->index('updated_by', 'fk_gacha_campaigns_updated_by');
            $table->index(['status', 'start_at', 'end_at'], 'idx_gacha_campaigns_status_schedule');
        });

        Schema::create('gacha_draws', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->string('draw_no', 60);
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('board_id');
            $table->unsignedBigInteger('slot_id');
            $table->unsignedInteger('customer_id');
            $table->unsignedBigInteger('point_account_id');
            $table->unsignedBigInteger('point_ledger_id');
            $table->unsignedBigInteger('reward_item_id');
            $table->unsignedBigInteger('reward_instance_id')->nullable();
            $table->unsignedBigInteger('points_spent');
            $table->string('channel', 20)->default('web');
            $table->unsignedBigInteger('handled_by_user_id')->nullable();
            $table->string('status', 20)->default('confirmed');
            $table->string('idempotency_key', 100)->nullable();
            $table->json('result_snapshot_json')->nullable();
            $table->dateTime('drawn_at');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique('draw_no', 'uq_gacha_draws_draw_no');
            $table->unique('slot_id', 'uq_gacha_draws_slot_id');
            $table->unique('idempotency_key', 'uq_gacha_draws_idempotency_key');
            $table->index('handled_by_user_id', 'fk_gacha_draws_handled_by_user');
            $table->index('board_id', 'gacha_draws_board_id_foreign');
            $table->index('point_account_id', 'gacha_draws_point_account_id_foreign');
            $table->index('point_ledger_id', 'gacha_draws_point_ledger_id_foreign');
            $table->index('reward_instance_id', 'gacha_draws_reward_instance_id_foreign');
            $table->index('reward_item_id', 'gacha_draws_reward_item_id_foreign');
            $table->index(['campaign_id', 'status'], 'idx_gacha_draws_campaign_status');
            $table->index(['customer_id', 'drawn_at'], 'idx_gacha_draws_customer_drawn');
        });

        Schema::create('imports', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->timestamp('completed_at')->nullable();
            $table->string('file_name', 255);
            $table->string('file_path', 255);
            $table->string('importer', 255);
            $table->unsignedInteger('processed_rows')->default(0);
            $table->unsignedInteger('total_rows');
            $table->unsignedInteger('successful_rows')->default(0);
            $table->unsignedBigInteger('user_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('user_id', 'imports_user_id_foreign');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->string('id', 255);
            $table->string('name', 255);
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->string('queue', 255);
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');

            $table->index('queue', 'jobs_queue_index');
        });

        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type', 255);
            $table->unsignedBigInteger('model_id');

            $table->primary(['permission_id', 'model_id', 'model_type']);
            $table->index(['model_id', 'model_type'], 'model_has_permissions_model_id_model_type_index');
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->unsignedBigInteger('role_id');
            $table->string('model_type', 255);
            $table->unsignedBigInteger('model_id');

            $table->primary(['role_id', 'model_id', 'model_type']);
            $table->index(['model_id', 'model_type'], 'model_has_roles_model_id_model_type_index');
        });

        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->string('email', 255);
            $table->timestamp('subscribed_at');
            $table->string('ip_address', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique('email', 'newsletter_subscribers_email_unique');
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->char('id', 36);
            $table->string('type', 255);
            $table->string('notifiable_type', 255);
            $table->unsignedBigInteger('notifiable_id');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index(['notifiable_type', 'notifiable_id'], 'notifications_notifiable_type_notifiable_id_index');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id');
            $table->string('name', 255);
            $table->string('sku', 255);
            $table->integer('qty');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default('0.00');
            $table->decimal('row_total', 15, 2);
            $table->integer('weight_gram')->nullable();
            $table->integer('length_mm')->nullable();
            $table->integer('width_mm')->nullable();
            $table->integer('height_mm')->nullable();
            $table->longText('meta_json')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('order_id', 'order_items_order_id_index');
            $table->index('product_id', 'order_items_product_id_foreign');
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->string('order_no', 255);
            $table->unsignedInteger('customer_id');
            $table->string('currency', 3)->default('IDR');
            $table->string('status', 255)->default('pending');
            $table->decimal('subtotal_amount', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default('0.00');
            $table->decimal('shipping_amount', 15, 2)->default('0.00');
            $table->decimal('tax_amount', 15, 2)->default('0.00');
            $table->decimal('grand_total', 15, 2);
            $table->unsignedBigInteger('shipping_address_id')->nullable();
            $table->unsignedBigInteger('billing_address_id')->nullable();
            $table->longText('applied_promos')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('bv_amount', 15, 2)->nullable();
            $table->decimal('sponsor_amount', 15, 2)->nullable();
            $table->decimal('match_amount', 15, 2)->nullable();
            $table->decimal('pairing_amount', 15, 2)->nullable();
            $table->decimal('retail_amount', 15, 2)->default('0.00');
            $table->decimal('cashback_amount', 15, 2)->nullable();
            $table->decimal('stockist_amount', 15, 2)->default('0.00');
            $table->enum('type', ['plana', 'planb'])->default('plana');
            $table->boolean('bonus_generated')->default(false);
            $table->dateTime('processed_at')->nullable();
            $table->timestamp('placed_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique('order_no', 'orders_order_no_unique');
            $table->index('billing_address_id', 'orders_billing_address_id_foreign');
            $table->index('created_at', 'orders_created_at_index');
            $table->index(['customer_id', 'status'], 'orders_customer_id_status_index');
            $table->index('paid_at', 'orders_paid_at_index');
            $table->index('placed_at', 'orders_placed_at_index');
            $table->index('shipping_address_id', 'orders_shipping_address_id_foreign');
            $table->index('status', 'orders_status_index');
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->increments('id');
            $table->string('title', 255);
            $table->string('slug', 255);
            $table->text('content')->nullable();
            $table->longText('blocks')->nullable();
            $table->text('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->boolean('is_published')->default(true);
            $table->string('template', 255)->default('default');
            $table->enum('show_on', ['header_top_bar', 'header_navbar', 'header_bottombar', 'footer_main', 'bottom_main'])->nullable()->default('bottom_main');
            $table->integer('order')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();

            $table->unique('slug', 'pages_slug_unique');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->string('email', 255);
            $table->string('token', 255);
            $table->timestamp('created_at')->nullable();

            $table->primary('email');
        });

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->string('code', 255);
            $table->string('name', 255);
            $table->string('logo', 500)->nullable();
            $table->string('display_name', 120)->nullable();
            $table->boolean('is_active')->default(true);

            $table->unique('code', 'payment_methods_code_unique');
        });

        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->unsignedBigInteger('payment_id');
            $table->string('status', 255);
            $table->decimal('amount', 15, 2);
            $table->longText('raw_json')->nullable();
            $table->timestamp('created_at');

            $table->index('payment_id', 'payment_transactions_payment_id_index');
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('method_id');
            $table->string('status', 255)->default('pending');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('IDR');
            $table->string('provider_txn_id', 255)->nullable();
            $table->longText('metadata_json')->nullable();
            $table->string('transaction_id', 255)->nullable();
            $table->string('signature_key', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('method_id', 'payments_method_id_foreign');
            $table->index(['order_id', 'status'], 'payments_order_id_status_index');
            $table->index(['transaction_id', 'signature_key'], 'transaction_id_signature_key');
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->string('name', 255);
            $table->string('guard_name', 255);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['name', 'guard_name'], 'permissions_name_guard_name_unique');
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->string('tokenable_type', 255);
            $table->unsignedBigInteger('tokenable_id');
            $table->text('name');
            $table->string('token', 64);
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique('token', 'personal_access_tokens_token_unique');
            $table->index('expires_at', 'personal_access_tokens_expires_at_index');
            $table->index(['tokenable_type', 'tokenable_id'], 'personal_access_tokens_tokenable_type_tokenable_id_index');
        });

        Schema::create('point_redemption_items', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->unsignedBigInteger('point_redemption_id');
            $table->unsignedBigInteger('reward_item_id');
            $table->unsignedBigInteger('reward_instance_id')->nullable();
            $table->unsignedInteger('qty');
            $table->unsignedBigInteger('points_cost_each');
            $table->unsignedBigInteger('subtotal_points');
            $table->json('reward_snapshot_json')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('point_redemption_id', 'idx_point_redemption_items_redemption');
            $table->index('reward_instance_id', 'point_redemption_items_reward_instance_id_foreign');
            $table->index('reward_item_id', 'point_redemption_items_reward_item_id_foreign');
        });

        Schema::create('point_redemptions', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->string('redemption_no', 60);
            $table->unsignedInteger('customer_id');
            $table->unsignedBigInteger('point_account_id');
            $table->unsignedBigInteger('point_ledger_id')->nullable();
            $table->unsignedBigInteger('total_points_spent');
            $table->unsignedInteger('total_items')->default(1);
            $table->string('status', 20)->default('pending');
            $table->unsignedBigInteger('shipping_address_id')->nullable();
            $table->json('address_snapshot_json')->nullable();
            $table->string('note', 255)->nullable();
            $table->dateTime('requested_at');
            $table->dateTime('confirmed_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique('redemption_no', 'uq_point_redemptions_redemption_no');
            $table->index('shipping_address_id', 'fk_point_redemptions_shipping_address');
            $table->index(['customer_id', 'requested_at'], 'idx_point_redemptions_customer_requested');
            $table->index('status', 'idx_point_redemptions_status');
            $table->index('point_account_id', 'point_redemptions_point_account_id_foreign');
            $table->index('point_ledger_id', 'point_redemptions_point_ledger_id_foreign');
        });

        Schema::create('pph', function (Blueprint $table) {
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';
            $table->increments('id');
            $table->unsignedInteger('member_id');
            $table->string('nama', 30);
            $table->tinyInteger('jk');
            $table->string('alamat', 255);
            $table->string('npwp', 20);
            $table->enum('krj', ['y', 'n'])->default('n');
            $table->string('kantor', 50)->default('Unknown');
            $table->enum('status', ['0', '1'])->default(0);
            $table->tinyInteger('kid');
            $table->decimal('bonus', 14, 2)->default('0.00');
            $table->date('periode');
            $table->double('ptkp');
            $table->double('pkp');
            $table->double('sum_of_pkp');
            $table->double('sum_of_pkp_temp');
            $table->double('akumulasi_bruto_temp');
            $table->double('akumulasi_ptkp');
            $table->double('akumulasi_bruto');
            $table->double('tarif');
            $table->double('tarif_npwp');
            $table->double('pph21');
            $table->double('buffer');
            $table->dateTime('created');
            $table->string('created_by', 10);

            $table->index('member_id', 'member_id');
        });

        Schema::create('product_categories', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('category_id');

            $table->unique(['product_id', 'category_id'], 'product_categories_product_id_category_id_unique');
            $table->index('category_id', 'product_categories_category_id_foreign');
        });

        Schema::create('product_media', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->unsignedBigInteger('product_id');
            $table->string('url', 255);
            $table->string('type', 255)->default('image');
            $table->string('alt_text', 255)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index(['product_id', 'sort_order'], 'product_media_product_id_sort_order_index');
        });

        Schema::create('product_reviews', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('order_item_id')->nullable();
            $table->unsignedTinyInteger('rating');
            $table->string('title', 255)->nullable();
            $table->text('comment')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_verified_purchase')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('customer_id', 'product_reviews_customer_id_index');
            $table->index('order_item_id', 'product_reviews_order_item_id_foreign');
            $table->index(['product_id', 'is_approved'], 'product_reviews_product_id_is_approved_index');
        });

        Schema::create('products', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->string('commodity_code', 255)->nullable();
            $table->string('sku', 255);
            $table->string('slug', 255);
            $table->string('name', 255);
            $table->text('short_desc')->nullable();
            $table->longText('long_desc')->nullable();
            $table->string('brand', 255)->nullable();
            $table->integer('warranty_months')->nullable();
            $table->decimal('base_price', 15, 2);
            $table->decimal('discount', 10, 2)->default('0.00');
            $table->string('currency', 3)->default('IDR');
            $table->integer('stock')->default(0);
            $table->integer('weight_gram')->nullable();
            $table->integer('length_mm')->nullable();
            $table->integer('width_mm')->nullable();
            $table->integer('height_mm')->nullable();
            $table->decimal('pv', 15, 2)->default('0.00')->comment('Bonus value');
            $table->decimal('b_sponsor', 15, 2)->default('0.00');
            $table->decimal('b_royalty', 15, 2)->default('0.00');
            $table->decimal('b_cashback', 15, 2)->default('0.00');
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique('sku', 'products_sku_unique');
            $table->unique('slug', 'products_slug_unique');
            $table->index(['is_active', 'created_at'], 'products_is_active_created_at_index');
            $table->fullText(['name', 'short_desc'], 'products_name_short_desc_fulltext');
        });

        Schema::create('promotion_products', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->unsignedBigInteger('promotion_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('min_qty')->default(1);
            $table->decimal('discount_value', 15, 2)->nullable();
            $table->decimal('discount_percent', 5, 2)->nullable();
            $table->decimal('bundle_price', 15, 2)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['promotion_id', 'product_id'], 'promotion_products_promotion_id_product_id_unique');
            $table->index('product_id', 'promotion_products_product_id_foreign');
        });

        Schema::create('promotions', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->string('code', 255);
            $table->string('name', 255);
            $table->string('type', 255);
            $table->string('landing_slug', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('image', 255)->nullable();
            $table->timestamp('start_at');
            $table->timestamp('end_at');
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);
            $table->integer('max_redemption')->nullable();
            $table->integer('per_user_limit')->nullable();
            $table->longText('conditions_json')->nullable();
            $table->string('show_on', 255)->nullable();
            $table->text('custom_html')->nullable();
            $table->string('page', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique('code', 'promotions_code_unique');
            $table->index(['is_active', 'start_at', 'end_at'], 'promotions_is_active_start_at_end_at_index');
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('payment_id');
            $table->string('status', 255)->default('pending');
            $table->decimal('amount', 15, 2);
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index(['order_id', 'status'], 'refunds_order_id_status_index');
            $table->index('payment_id', 'refunds_payment_id_foreign');
        });

        Schema::create('return_items', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->unsignedBigInteger('return_id');
            $table->unsignedBigInteger('order_item_id');
            $table->integer('qty');
            $table->text('condition_note')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('order_item_id', 'return_items_order_item_id_foreign');
            $table->index('return_id', 'return_items_return_id_index');
        });

        Schema::create('returns', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id');
            $table->string('status', 255)->default('pending');
            $table->text('reason')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index(['order_id', 'status'], 'returns_order_id_status_index');
        });

        Schema::create('reward_inventory_ledgers', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->unsignedBigInteger('reward_item_id');
            $table->string('movement_type', 30);
            $table->unsignedInteger('qty_in')->default(0);
            $table->unsignedInteger('qty_out')->default(0);
            $table->unsignedInteger('balance_after')->default(0);
            $table->string('source_type', 30)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('note', 255)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('created_by', 'fk_reward_inventory_ledgers_created_by');
            $table->index(['reward_item_id', 'created_at'], 'idx_reward_inventory_ledgers_reward_created');
            $table->index(['source_type', 'source_id'], 'idx_reward_inventory_ledgers_source');
        });

        Schema::create('reward_items', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->string('code', 50);
            $table->string('name', 150);
            $table->string('slug', 180);
            $table->text('description')->nullable();
            $table->string('reward_type', 30);
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('point_cost')->nullable();
            $table->unsignedBigInteger('point_reward_amount')->nullable();
            $table->boolean('requires_shipping')->default(false);
            $table->string('fulfillment_mode', 20)->default('manual');
            $table->string('stock_mode', 20)->default('finite');
            $table->unsignedInteger('stock_qty')->default(0);
            $table->unsignedInteger('stock_reserved')->default(0);
            $table->unsignedInteger('stock_issued')->default(0);
            $table->boolean('is_gacha_enabled')->default(true);
            $table->boolean('is_point_redeemable')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->string('thumbnail', 255)->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique('code', 'uq_reward_items_code');
            $table->unique('slug', 'uq_reward_items_slug');
            $table->index('product_id', 'fk_reward_items_product');
            $table->index(['is_active', 'is_gacha_enabled'], 'idx_reward_items_active_gacha');
            $table->index(['is_active', 'is_point_redeemable'], 'idx_reward_items_active_redeemable');
        });

        Schema::create('rewards', function (Blueprint $table) {
            $table->charset = 'utf8mb3';
            $table->collation = 'utf8mb3_general_ci';
            $table->increments('id');
            $table->string('code', 10)->nullable();
            $table->string('name', 225);
            $table->string('reward', 225)->nullable();
            $table->decimal('value', 15, 2)->default('0.00');
            $table->date('start')->nullable();
            $table->date('end')->nullable();
            $table->decimal('bv', 15, 2)->default('0.00');
            $table->boolean('type')->comment('0: periode, 1: permanen');
            $table->tinyInteger('status');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');

            $table->primary(['permission_id', 'role_id']);
            $table->index('role_id', 'role_has_permissions_role_id_foreign');
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->string('name', 255);
            $table->string('guard_name', 255);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['name', 'guard_name'], 'roles_name_guard_name_unique');
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->string('id', 255);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity');

            $table->index('user_id', 'sessions_user_id_index');
            $table->index('last_activity', 'sessions_last_activity_index');
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->string('key', 255);
            $table->text('value')->nullable();
            $table->string('type', 255)->default('text');
            $table->string('group', 255)->default('general');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique('key', 'settings_key_unique');
        });

        Schema::create('shipment_items', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->unsignedBigInteger('shipment_id');
            $table->unsignedBigInteger('order_item_id');
            $table->integer('qty');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('order_item_id', 'shipment_items_order_item_id_foreign');
            $table->index('shipment_id', 'shipment_items_shipment_id_index');
        });

        Schema::create('shipments', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id');
            $table->string('courier_id', 255)->nullable();
            $table->string('tracking_no', 255)->nullable();
            $table->string('status', 255)->default('pending');
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->decimal('shipping_fee', 15, 2)->default('0.00');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index(['order_id', 'status'], 'shipments_order_id_status_index');
        });

        Schema::create('shipping_targets', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->string('three_lc_code', 255);
            $table->string('country', 255);
            $table->unsignedBigInteger('province_id')->nullable();
            $table->string('province', 255)->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->string('city', 255)->nullable();
            $table->string('district', 255)->nullable();
            $table->string('district_lion', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique('three_lc_code', 'shipping_targets_three_lc_code_unique');
            $table->index('city_id', 'shipping_targets_city_id_index');
            $table->index('province_id', 'shipping_targets_province_id_index');
        });

        Schema::create('tax_report', function (Blueprint $table) {
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';
            $table->increments('id');
            $table->unsignedInteger('member_id');
            $table->date('tgl');
            $table->integer('masapajak');
            $table->integer('tahunpajak');
            $table->integer('pembetulan');
            $table->string('nomorbuktipotong', 100);
            $table->string('npwp', 100);
            $table->string('nik', 50)->nullable();
            $table->string('nama', 100);
            $table->string('alamat', 255)->nullable();
            $table->string('wpluarnegri', 10);
            $table->string('kodenegara', 100);
            $table->string('kodepajak', 100);
            $table->double('jumlahbruto');
            $table->double('jumlahdpp');
            $table->string('tanpanpwp', 10);
            $table->decimal('tarif', 5, 2);
            $table->double('pph21');
            $table->string('npwppemotong', 100);
            $table->string('namapemotong', 100);

            $table->index('member_id', 'member_id');
        });

        Schema::create('users', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->string('name', 255);
            $table->string('email', 255);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 255);
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('role', 50)->nullable();

            $table->unique('email', 'users_email_unique');
        });

        Schema::create('whatsapp_broadcast_recipients', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->unsignedBigInteger('broadcast_id');
            $table->unsignedInteger('customer_id')->nullable();
            $table->string('customer_name', 255)->nullable();
            $table->string('phone', 255);
            $table->string('normalized_phone', 255);
            $table->enum('status', ['queued', 'processing', 'pending', 'sent', 'failed'])->default('queued');
            $table->text('response_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['broadcast_id', 'normalized_phone'], 'wa_broadcast_recipients_unique_phone');
            $table->index('customer_id', 'wa_broadcast_recipients_customer_idx');
            $table->index(['broadcast_id', 'status'], 'wa_broadcast_recipients_status_idx');
        });

        Schema::create('whatsapp_broadcasts', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->string('title', 255);
            $table->text('message')->nullable();
            $table->longText('body_params')->nullable()->comment('Pemetaan variabel template ke kolom customers, e.g. [{value:"full_name",value_text:"customers.name"}]');
            $table->string('template_id', 255)->nullable();
            $table->string('channel_integration_id', 100)->nullable()->comment('Qontak channel integration UUID, null = gunakan default dari config');
            $table->enum('status', ['draft', 'processing', 'sent', 'partial', 'failed'])->default('draft');
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('success_recipients')->default(0);
            $table->unsignedInteger('failed_recipients')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->text('last_error')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index(['status', 'created_at'], 'wa_broadcasts_status_created_idx');
            $table->index('created_by', 'whatsapp_broadcasts_created_by_foreign');
        });

        Schema::create('whatsapp_outbound_logs', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->string('broadcast_id', 100)->nullable()->comment('ID broadcast (lokal integer atau Qontak UUID string)');
            $table->char('qontak_id', 36)->comment('ID message broadcast dari Qontak API');
            $table->string('name', 255)->comment('Nama broadcast di Qontak');
            $table->char('organization_id', 36)->comment('Organization ID di Qontak');
            $table->char('channel_integration_id', 36)->comment('Channel integration ID di Qontak');
            $table->char('contact_list_id', 36)->nullable()->comment('Contact list ID di Qontak, null jika kirim ke satu kontak');
            $table->char('contact_id', 36)->comment('Contact ID penerima di Qontak');
            $table->string('target_channel', 50)->default('wa_cloud')->comment('Channel target, contoh: wa_cloud');
            $table->timestamp('send_at')->nullable()->comment('Waktu pengiriman sesuai Qontak');
            $table->string('execute_status', 50)->default('todo')->comment('Status eksekusi Qontak: todo, done, failed, dll');
            $table->string('execute_type', 50)->default('immediately')->comment('Tipe eksekusi: immediately atau scheduled');
            $table->longText('parameters')->nullable()->comment('Parameter template (header, body, buttons)');
            $table->longText('message_status_count')->nullable()->comment('Jumlah status pesan: failed, delivered, read, pending, sent');
            $table->longText('contact_extra')->nullable()->comment('Nilai variabel template yang dikirim, contoh: full_name, nominal');
            $table->longText('message_template')->nullable()->comment('Snapshot data template Qontak saat pengiriman');
            $table->char('division_id', 36)->nullable()->comment('Division ID di Qontak, opsional');
            $table->char('message_broadcast_plan_id', 36)->nullable()->comment('Plan ID di Qontak jika bagian dari scheduled plan');
            $table->string('message_broadcast_error', 255)->nullable()->comment('Pesan error dari Qontak, n/a jika tidak ada error');
            $table->string('sender_name', 255)->nullable()->comment('Nama pengirim/operator di Qontak');
            $table->string('sender_email', 255)->nullable()->comment('Email pengirim/operator di Qontak');
            $table->string('channel_account_name', 255)->nullable()->comment('Nama akun channel WhatsApp, contoh: Puranusa');
            $table->string('channel_phone_number', 30)->nullable()->comment('Nomor telepon channel WhatsApp gateway');
            $table->timestamp('qontak_created_at')->nullable()->comment('Waktu record dibuat di Qontak');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique('qontak_id', 'whatsapp_outbound_logs_qontak_id_unique');
            $table->index('broadcast_id', 'whatsapp_outbound_logs_broadcast_id_index');
            $table->index('execute_status', 'whatsapp_outbound_logs_execute_status_index');
            $table->index('qontak_created_at', 'whatsapp_outbound_logs_qontak_created_at_index');
        });

        Schema::create('wishlist_items', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->unsignedBigInteger('wishlist_id');
            $table->unsignedBigInteger('product_id');
            $table->string('product_name', 255);
            $table->string('product_sku', 255);
            $table->longText('meta_json')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['wishlist_id', 'product_id'], 'wishlist_items_wishlist_id_product_id_unique');
            $table->index('product_id', 'wishlist_items_product_id_foreign');
            $table->index('wishlist_id', 'wishlist_items_wishlist_id_index');
        });

        Schema::create('wishlists', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->bigIncrements('id');
            $table->unsignedBigInteger('customer_id');
            $table->string('name', 255)->default('Default');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('customer_id', 'wishlists_customer_id_index');
        });

            // Add foreign key constraints after all tables have been created.
        Schema::table('article_contents', function (Blueprint $table) {

            $table->foreign('article_id', 'article_contents_article_id_foreign')->references('id')->on('articles')->cascadeOnDelete()->cascadeOnUpdate();

        });

        Schema::table('bug_report_attachments', function (Blueprint $table) {

            $table->foreign('bug_report_id', 'bug_report_attachments_bug_report_id_foreign')->references('id')->on('bug_reports')->cascadeOnDelete();

        });

        Schema::table('bug_report_comments', function (Blueprint $table) {

            $table->foreign('bug_report_id', 'bug_report_comments_bug_report_id_foreign')->references('id')->on('bug_reports')->cascadeOnDelete();

            $table->foreign('user_id', 'bug_report_comments_user_id_foreign')->references('id')->on('users')->nullOnDelete();

        });

        Schema::table('bug_reports', function (Blueprint $table) {

            $table->foreign('assigned_to', 'bug_reports_assigned_to_foreign')->references('id')->on('users')->nullOnDelete();

            $table->foreign('duplicate_of_id', 'bug_reports_duplicate_of_id_foreign')->references('id')->on('bug_reports')->nullOnDelete();

        });

        Schema::table('cart_items', function (Blueprint $table) {

            $table->foreign('cart_id', 'cart_items_cart_id_foreign')->references('id')->on('carts')->cascadeOnDelete()->cascadeOnUpdate();

        });

        Schema::table('categories', function (Blueprint $table) {

            $table->foreign('parent_id', 'categories_parent_id_foreign')->references('id')->on('categories')->nullOnDelete();

        });

        Schema::table('contents', function (Blueprint $table) {

            $table->foreign('category_id', 'contents_category_id_foreign')->references('id')->on('contents_category')->nullOnDelete();

            $table->foreign('created_by', 'contents_created_by_foreign')->references('id')->on('users')->nullOnDelete();

        });

        Schema::table('contents_category', function (Blueprint $table) {

            $table->foreign('parent_id', 'contents_category_parent_id_foreign')->references('id')->on('contents_category')->nullOnDelete();

        });

        Schema::table('customer_bonus_cashbacks', function (Blueprint $table) {

            $table->foreign('member_id', 'customer_bonus_cashbacks_member_id_foreign')->references('id')->on('customers')->cascadeOnDelete()->cascadeOnUpdate();

        });

        Schema::table('customer_bonus_rewards', function (Blueprint $table) {

            $table->foreign('member_id', 'customer_bonus_rewards_member_id_foreign')->references('id')->on('customers')->cascadeOnDelete()->cascadeOnUpdate();

        });

        Schema::table('customer_bonus_sponsors', function (Blueprint $table) {

            $table->foreign('from_member_id', 'customer_bonus_sponsors_from_member_id_foreign')->references('id')->on('customers')->cascadeOnDelete()->cascadeOnUpdate();

            $table->foreign('member_id', 'customer_bonus_sponsors_member_id_foreign')->references('id')->on('customers')->cascadeOnDelete()->cascadeOnUpdate();

        });

        Schema::table('customer_bonuses', function (Blueprint $table) {

            $table->foreign('member_id', 'customer_bonuses_member_id_foreign')->references('id')->on('customers')->cascadeOnDelete()->cascadeOnUpdate();

        });

        Schema::table('customer_bv_rewards', function (Blueprint $table) {

            $table->foreign('member_id', 'customer_bv_rewards_ibfk_1')->references('id')->on('customers')->cascadeOnDelete()->cascadeOnUpdate();

            $table->foreign('reward_id', 'customer_bv_rewards_ibfk_2')->references('id')->on('rewards')->cascadeOnDelete()->cascadeOnUpdate();

        });

        Schema::table('customer_content_progress', function (Blueprint $table) {

            $table->foreign('content_category_id', 'customer_content_progress_content_category_id_foreign')->references('id')->on('contents_category')->cascadeOnDelete();

            $table->foreign('content_id', 'customer_content_progress_content_id_foreign')->references('id')->on('contents')->nullOnDelete();

            $table->foreign('customer_id', 'customer_content_progress_customer_id_foreign')->references('id')->on('customers')->cascadeOnDelete();

        });

        Schema::table('customer_module_progress', function (Blueprint $table) {

            $table->foreign('content_id', 'customer_module_progress_content_id_foreign')->references('id')->on('contents')->cascadeOnDelete();

            $table->foreign('customer_id', 'customer_module_progress_customer_id_foreign')->references('id')->on('customers')->cascadeOnDelete();

        });

        Schema::table('customer_network_matrixes', function (Blueprint $table) {

            $table->foreign('member_id', 'customer_network_matrixes_member_id_foreign')->references('id')->on('customers')->cascadeOnDelete()->cascadeOnUpdate();

            $table->foreign('sponsor_id', 'customer_network_matrixes_sponsor_id_foreign')->references('id')->on('customers')->cascadeOnDelete()->cascadeOnUpdate();

        });

        Schema::table('customer_point_accounts', function (Blueprint $table) {

            $table->foreign('customer_id', 'fk_customer_point_accounts_customer')->references('id')->on('customers')->cascadeOnDelete();

        });

        Schema::table('customer_point_ledgers', function (Blueprint $table) {

            $table->foreign('point_account_id', 'customer_point_ledgers_point_account_id_foreign')->references('id')->on('customer_point_accounts')->cascadeOnDelete();

            $table->foreign('created_by', 'fk_customer_point_ledgers_created_by')->references('id')->on('users')->nullOnDelete();

            $table->foreign('customer_id', 'fk_customer_point_ledgers_customer')->references('id')->on('customers')->cascadeOnDelete();

        });

        Schema::table('customer_reward_instances', function (Blueprint $table) {

            $table->foreign('reward_item_id', 'customer_reward_instances_reward_item_id_foreign')->references('id')->on('reward_items')->cascadeOnDelete();

            $table->foreign('customer_id', 'fk_customer_reward_instances_customer')->references('id')->on('customers')->cascadeOnDelete();

            $table->foreign('shipping_address_id', 'fk_customer_reward_instances_shipping_address')->references('id')->on('customer_addresses')->nullOnDelete();

        });

        Schema::table('customer_vouchers', function (Blueprint $table) {

            $table->foreign('customer_id', 'customer_vouchers_customer_id_foreign')->references('id')->on('customers')->cascadeOnDelete();

            $table->foreign('promotion_id', 'customer_vouchers_promotion_id_foreign')->references('id')->on('promotions')->cascadeOnDelete();

            $table->foreign('used_by_order_id', 'customer_vouchers_used_by_order_id_foreign')->references('id')->on('orders')->nullOnDelete();

        });

        Schema::table('customer_wallet_transactions', function (Blueprint $table) {

            $table->foreign('customer_id', 'customer_wallet_transactions_customer_id_foreign')->references('id')->on('customers')->cascadeOnDelete()->cascadeOnUpdate();

        });

        Schema::table('customer_whatsapp_confirmations', function (Blueprint $table) {

            $table->foreign('customer_id', 'customer_whatsapp_confirmations_customer_id_foreign')->references('id')->on('customers')->nullOnDelete();

        });

        Schema::table('customers', function (Blueprint $table) {

            $table->foreign('package_id', 'customers_package_id_foreign')->references('id')->on('customer_package')->nullOnDelete()->cascadeOnUpdate();

            $table->foreign('sponsor_id', 'customers_sponsor_id_foreign')->references('id')->on('customers')->nullOnDelete()->cascadeOnUpdate();

        });

        Schema::table('exports', function (Blueprint $table) {

            $table->foreign('user_id', 'exports_user_id_foreign')->references('id')->on('users')->cascadeOnDelete();

        });

        Schema::table('failed_import_rows', function (Blueprint $table) {

            $table->foreign('import_id', 'failed_import_rows_import_id_foreign')->references('id')->on('imports')->cascadeOnDelete();

        });

        Schema::table('gacha_board_slots', function (Blueprint $table) {

            $table->foreign('popped_by_customer_id', 'fk_gacha_board_slots_popped_customer')->references('id')->on('customers')->nullOnDelete();

            $table->foreign('reserved_by_customer_id', 'fk_gacha_board_slots_reserved_customer')->references('id')->on('customers')->nullOnDelete();

            $table->foreign('board_id', 'gacha_board_slots_board_id_foreign')->references('id')->on('gacha_boards')->cascadeOnDelete();

            $table->foreign('reward_item_id', 'gacha_board_slots_reward_item_id_foreign')->references('id')->on('reward_items')->cascadeOnDelete();

        });

        Schema::table('gacha_boards', function (Blueprint $table) {

            $table->foreign('generated_by', 'fk_gacha_boards_generated_by')->references('id')->on('users')->nullOnDelete();

            $table->foreign('campaign_id', 'gacha_boards_campaign_id_foreign')->references('id')->on('gacha_campaigns')->cascadeOnDelete();

        });

        Schema::table('gacha_campaign_reward_rules', function (Blueprint $table) {

            $table->foreign('campaign_id', 'gacha_campaign_reward_rules_campaign_id_foreign')->references('id')->on('gacha_campaigns')->cascadeOnDelete();

            $table->foreign('reward_item_id', 'gacha_campaign_reward_rules_reward_item_id_foreign')->references('id')->on('reward_items')->cascadeOnDelete();

        });

        Schema::table('gacha_campaigns', function (Blueprint $table) {

            $table->foreign('created_by', 'fk_gacha_campaigns_created_by')->references('id')->on('users')->nullOnDelete();

            $table->foreign('updated_by', 'fk_gacha_campaigns_updated_by')->references('id')->on('users')->nullOnDelete();

        });

        Schema::table('gacha_draws', function (Blueprint $table) {

            $table->foreign('customer_id', 'fk_gacha_draws_customer')->references('id')->on('customers')->cascadeOnDelete();

            $table->foreign('handled_by_user_id', 'fk_gacha_draws_handled_by_user')->references('id')->on('users')->nullOnDelete();

            $table->foreign('board_id', 'gacha_draws_board_id_foreign')->references('id')->on('gacha_boards')->cascadeOnDelete();

            $table->foreign('campaign_id', 'gacha_draws_campaign_id_foreign')->references('id')->on('gacha_campaigns')->cascadeOnDelete();

            $table->foreign('point_account_id', 'gacha_draws_point_account_id_foreign')->references('id')->on('customer_point_accounts')->cascadeOnDelete();

            $table->foreign('point_ledger_id', 'gacha_draws_point_ledger_id_foreign')->references('id')->on('customer_point_ledgers')->cascadeOnDelete();

            $table->foreign('reward_instance_id', 'gacha_draws_reward_instance_id_foreign')->references('id')->on('customer_reward_instances')->nullOnDelete();

            $table->foreign('reward_item_id', 'gacha_draws_reward_item_id_foreign')->references('id')->on('reward_items')->cascadeOnDelete();

            $table->foreign('slot_id', 'gacha_draws_slot_id_foreign')->references('id')->on('gacha_board_slots')->cascadeOnDelete();

        });

        Schema::table('imports', function (Blueprint $table) {

            $table->foreign('user_id', 'imports_user_id_foreign')->references('id')->on('users')->cascadeOnDelete();

        });

        Schema::table('model_has_permissions', function (Blueprint $table) {

            $table->foreign('permission_id', 'model_has_permissions_permission_id_foreign')->references('id')->on('permissions')->cascadeOnDelete();

        });

        Schema::table('model_has_roles', function (Blueprint $table) {

            $table->foreign('role_id', 'model_has_roles_role_id_foreign')->references('id')->on('roles')->cascadeOnDelete();

        });

        Schema::table('order_items', function (Blueprint $table) {

            $table->foreign('order_id', 'order_items_order_id_foreign')->references('id')->on('orders')->cascadeOnDelete();

            $table->foreign('product_id', 'order_items_product_id_foreign')->references('id')->on('products');

        });

        Schema::table('orders', function (Blueprint $table) {

            $table->foreign('billing_address_id', 'orders_billing_address_id_foreign')->references('id')->on('customer_addresses')->nullOnDelete();

            $table->foreign('customer_id', 'orders_ibfk_1')->references('id')->on('customers')->cascadeOnDelete()->cascadeOnUpdate();

            $table->foreign('shipping_address_id', 'orders_shipping_address_id_foreign')->references('id')->on('customer_addresses')->nullOnDelete();

        });

        Schema::table('payment_transactions', function (Blueprint $table) {

            $table->foreign('payment_id', 'payment_transactions_payment_id_foreign')->references('id')->on('payments')->cascadeOnDelete();

        });

        Schema::table('payments', function (Blueprint $table) {

            $table->foreign('method_id', 'payments_method_id_foreign')->references('id')->on('payment_methods');

            $table->foreign('order_id', 'payments_order_id_foreign')->references('id')->on('orders')->cascadeOnDelete();

        });

        Schema::table('point_redemption_items', function (Blueprint $table) {

            $table->foreign('point_redemption_id', 'point_redemption_items_point_redemption_id_foreign')->references('id')->on('point_redemptions')->cascadeOnDelete();

            $table->foreign('reward_instance_id', 'point_redemption_items_reward_instance_id_foreign')->references('id')->on('customer_reward_instances')->nullOnDelete();

            $table->foreign('reward_item_id', 'point_redemption_items_reward_item_id_foreign')->references('id')->on('reward_items')->cascadeOnDelete();

        });

        Schema::table('point_redemptions', function (Blueprint $table) {

            $table->foreign('customer_id', 'fk_point_redemptions_customer')->references('id')->on('customers')->cascadeOnDelete();

            $table->foreign('shipping_address_id', 'fk_point_redemptions_shipping_address')->references('id')->on('customer_addresses')->nullOnDelete();

            $table->foreign('point_account_id', 'point_redemptions_point_account_id_foreign')->references('id')->on('customer_point_accounts')->cascadeOnDelete();

            $table->foreign('point_ledger_id', 'point_redemptions_point_ledger_id_foreign')->references('id')->on('customer_point_ledgers')->nullOnDelete();

        });

        Schema::table('product_categories', function (Blueprint $table) {

            $table->foreign('category_id', 'product_categories_category_id_foreign')->references('id')->on('categories')->cascadeOnDelete();

            $table->foreign('product_id', 'product_categories_product_id_foreign')->references('id')->on('products')->cascadeOnDelete();

        });

        Schema::table('product_media', function (Blueprint $table) {

            $table->foreign('product_id', 'product_media_product_id_foreign')->references('id')->on('products')->cascadeOnDelete();

        });

        Schema::table('product_reviews', function (Blueprint $table) {

            $table->foreign('order_item_id', 'product_reviews_order_item_id_foreign')->references('id')->on('order_items')->nullOnDelete();

            $table->foreign('product_id', 'product_reviews_product_id_foreign')->references('id')->on('products')->cascadeOnDelete();

        });

        Schema::table('promotion_products', function (Blueprint $table) {

            $table->foreign('product_id', 'promotion_products_product_id_foreign')->references('id')->on('products')->cascadeOnDelete();

            $table->foreign('promotion_id', 'promotion_products_promotion_id_foreign')->references('id')->on('promotions')->cascadeOnDelete();

        });

        Schema::table('refunds', function (Blueprint $table) {

            $table->foreign('order_id', 'refunds_order_id_foreign')->references('id')->on('orders')->cascadeOnDelete();

            $table->foreign('payment_id', 'refunds_payment_id_foreign')->references('id')->on('payments')->cascadeOnDelete();

        });

        Schema::table('return_items', function (Blueprint $table) {

            $table->foreign('order_item_id', 'return_items_order_item_id_foreign')->references('id')->on('order_items')->cascadeOnDelete();

            $table->foreign('return_id', 'return_items_return_id_foreign')->references('id')->on('returns')->cascadeOnDelete();

        });

        Schema::table('returns', function (Blueprint $table) {

            $table->foreign('order_id', 'returns_order_id_foreign')->references('id')->on('orders')->cascadeOnDelete();

        });

        Schema::table('reward_inventory_ledgers', function (Blueprint $table) {

            $table->foreign('created_by', 'fk_reward_inventory_ledgers_created_by')->references('id')->on('users')->nullOnDelete();

            $table->foreign('reward_item_id', 'reward_inventory_ledgers_reward_item_id_foreign')->references('id')->on('reward_items')->cascadeOnDelete();

        });

        Schema::table('reward_items', function (Blueprint $table) {

            $table->foreign('product_id', 'fk_reward_items_product')->references('id')->on('products')->nullOnDelete();

        });

        Schema::table('role_has_permissions', function (Blueprint $table) {

            $table->foreign('permission_id', 'role_has_permissions_permission_id_foreign')->references('id')->on('permissions')->cascadeOnDelete();

            $table->foreign('role_id', 'role_has_permissions_role_id_foreign')->references('id')->on('roles')->cascadeOnDelete();

        });

        Schema::table('shipment_items', function (Blueprint $table) {

            $table->foreign('order_item_id', 'shipment_items_order_item_id_foreign')->references('id')->on('order_items')->cascadeOnDelete();

            $table->foreign('shipment_id', 'shipment_items_shipment_id_foreign')->references('id')->on('shipments')->cascadeOnDelete();

        });

        Schema::table('shipments', function (Blueprint $table) {

            $table->foreign('order_id', 'shipments_order_id_foreign')->references('id')->on('orders')->cascadeOnDelete();

        });

        Schema::table('tax_report', function (Blueprint $table) {

            $table->foreign('member_id', 'tax_report_ibfk_1')->references('id')->on('customers')->cascadeOnDelete()->cascadeOnUpdate();

        });

        Schema::table('whatsapp_broadcast_recipients', function (Blueprint $table) {

            $table->foreign('broadcast_id', 'wa_broadcast_recipients_broadcast_fk')->references('id')->on('whatsapp_broadcasts')->cascadeOnDelete();

            $table->foreign('customer_id', 'wa_broadcast_recipients_customer_fk')->references('id')->on('customers')->nullOnDelete()->cascadeOnUpdate();

        });

        Schema::table('whatsapp_broadcasts', function (Blueprint $table) {

            $table->foreign('created_by', 'whatsapp_broadcasts_created_by_foreign')->references('id')->on('users')->nullOnDelete();

        });

        Schema::table('wishlist_items', function (Blueprint $table) {

            $table->foreign('product_id', 'wishlist_items_product_id_foreign')->references('id')->on('products')->cascadeOnDelete();

            $table->foreign('wishlist_id', 'wishlist_items_wishlist_id_foreign')->references('id')->on('wishlists')->cascadeOnDelete();

        });
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            Schema::dropIfExists('wishlists');
            Schema::dropIfExists('wishlist_items');
            Schema::dropIfExists('whatsapp_outbound_logs');
            Schema::dropIfExists('whatsapp_broadcasts');
            Schema::dropIfExists('whatsapp_broadcast_recipients');
            Schema::dropIfExists('users');
            Schema::dropIfExists('tax_report');
            Schema::dropIfExists('shipping_targets');
            Schema::dropIfExists('shipments');
            Schema::dropIfExists('shipment_items');
            Schema::dropIfExists('settings');
            Schema::dropIfExists('sessions');
            Schema::dropIfExists('roles');
            Schema::dropIfExists('role_has_permissions');
            Schema::dropIfExists('rewards');
            Schema::dropIfExists('reward_items');
            Schema::dropIfExists('reward_inventory_ledgers');
            Schema::dropIfExists('returns');
            Schema::dropIfExists('return_items');
            Schema::dropIfExists('refunds');
            Schema::dropIfExists('promotions');
            Schema::dropIfExists('promotion_products');
            Schema::dropIfExists('products');
            Schema::dropIfExists('product_reviews');
            Schema::dropIfExists('product_media');
            Schema::dropIfExists('product_categories');
            Schema::dropIfExists('pph');
            Schema::dropIfExists('point_redemptions');
            Schema::dropIfExists('point_redemption_items');
            Schema::dropIfExists('personal_access_tokens');
            Schema::dropIfExists('permissions');
            Schema::dropIfExists('payments');
            Schema::dropIfExists('payment_transactions');
            Schema::dropIfExists('payment_methods');
            Schema::dropIfExists('password_reset_tokens');
            Schema::dropIfExists('pages');
            Schema::dropIfExists('orders');
            Schema::dropIfExists('order_items');
            Schema::dropIfExists('notifications');
            Schema::dropIfExists('newsletter_subscribers');
            Schema::dropIfExists('model_has_roles');
            Schema::dropIfExists('model_has_permissions');
            Schema::dropIfExists('jobs');
            Schema::dropIfExists('job_batches');
            Schema::dropIfExists('imports');
            Schema::dropIfExists('gacha_draws');
            Schema::dropIfExists('gacha_campaigns');
            Schema::dropIfExists('gacha_campaign_reward_rules');
            Schema::dropIfExists('gacha_boards');
            Schema::dropIfExists('gacha_board_slots');
            Schema::dropIfExists('failed_jobs');
            Schema::dropIfExists('failed_import_rows');
            Schema::dropIfExists('exports');
            Schema::dropIfExists('customers_rewards');
            Schema::dropIfExists('customers');
            Schema::dropIfExists('customer_whatsapp_confirmations');
            Schema::dropIfExists('customer_wallet_transactions');
            Schema::dropIfExists('customer_vouchers');
            Schema::dropIfExists('customer_reward_instances');
            Schema::dropIfExists('customer_pph');
            Schema::dropIfExists('customer_point_ledgers');
            Schema::dropIfExists('customer_point_accounts');
            Schema::dropIfExists('customer_password_resets');
            Schema::dropIfExists('customer_package');
            Schema::dropIfExists('customer_npwp');
            Schema::dropIfExists('customer_network_matrixes');
            Schema::dropIfExists('customer_module_progress');
            Schema::dropIfExists('customer_content_progress');
            Schema::dropIfExists('customer_bv_rewards');
            Schema::dropIfExists('customer_bonuses');
            Schema::dropIfExists('customer_bonus_sponsors');
            Schema::dropIfExists('customer_bonus_royalty');
            Schema::dropIfExists('customer_bonus_rewards');
            Schema::dropIfExists('customer_bonus_cashbacks');
            Schema::dropIfExists('customer_addresses');
            Schema::dropIfExists('contents_category');
            Schema::dropIfExists('contents');
            Schema::dropIfExists('commodity_codes');
            Schema::dropIfExists('categories');
            Schema::dropIfExists('carts');
            Schema::dropIfExists('cart_items');
            Schema::dropIfExists('cache_locks');
            Schema::dropIfExists('cache');
            Schema::dropIfExists('bug_reports');
            Schema::dropIfExists('bug_report_comments');
            Schema::dropIfExists('bug_report_attachments');
            Schema::dropIfExists('articles');
            Schema::dropIfExists('article_contents');
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
};
