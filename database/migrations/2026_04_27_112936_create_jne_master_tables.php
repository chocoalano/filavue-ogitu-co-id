<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jne_branches', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->string('branch_code', 20);
            $table->string('branch_name', 150);
            $table->timestamps();

            $table->unique('branch_code', 'jne_branches_branch_code_unique');
        });

        Schema::create('jne_origins', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->string('origin_code', 20);
            $table->string('origin_name', 150);
            $table->timestamps();

            $table->unique('origin_code', 'jne_origins_origin_code_unique');
        });

        Schema::create('jne_destinations', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->string('country_name', 100);
            $table->string('province_name', 150);
            $table->string('city_name', 150);
            $table->string('district_name', 150);
            $table->string('subdistrict_name', 150);
            $table->string('zip_code', 10)->nullable();
            $table->string('tariff_code', 20);
            $table->timestamps();

            $table->index('tariff_code', 'jne_destinations_tariff_code_index');
            $table->index('zip_code', 'jne_destinations_zip_code_index');
            $table->index(['province_name', 'city_name', 'district_name'], 'jne_destinations_area_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jne_destinations');
        Schema::dropIfExists('jne_origins');
        Schema::dropIfExists('jne_branches');
    }
};
