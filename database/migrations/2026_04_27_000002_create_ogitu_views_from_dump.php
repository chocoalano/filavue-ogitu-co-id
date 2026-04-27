<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS `vw_customer_bonus_pph21`');
        DB::unprepared(<<<'OGITU_VIEW_SQL_1'
CREATE OR REPLACE VIEW `vw_customer_bonus_pph21` AS select `cb`.`id` AS `id`,`cb`.`date` AS `tanggal`,`c`.`username` AS `username`,`c`.`name` AS `name`,`c`.`email` AS `email`,`c`.`phone` AS `no_telepon`,`cnpwp`.`npwp` AS `npwp`,year(`cb`.`date`) AS `tahun_pajak`,`c`.`nik` AS `nik`,`c`.`name` AS `fullname`,`c`.`address` AS `alamat`,`cb`.`amount` AS `jumlah_bruto`,`cb`.`tax_percent` AS `tarif`,`cb`.`tax_value` AS `pph21` from ((`customer_bonuses` `cb` join `customers` `c` on((`cb`.`member_id` = `c`.`id`))) left join `customer_npwp` `cnpwp` on((`cnpwp`.`member_id` = `c`.`id`)));
OGITU_VIEW_SQL_1);
        DB::statement('DROP VIEW IF EXISTS `vw_customer_tax_report`');
        DB::unprepared(<<<'OGITU_VIEW_SQL_2'
CREATE OR REPLACE VIEW `vw_customer_tax_report` AS select `tr`.`id` AS `id`,`tr`.`tgl` AS `tanggal`,`c`.`username` AS `username`,`c`.`name` AS `fullname`,`c`.`email` AS `email`,`c`.`phone` AS `no_telepon`,`tr`.`masapajak` AS `masapajak`,`tr`.`tahunpajak` AS `tahunpajak`,`tr`.`pembetulan` AS `pembetulan`,`tr`.`nomorbuktipotong` AS `nomorbuktipotong`,`tr`.`npwp` AS `npwp`,`c`.`nik` AS `nik`,`c`.`name` AS `name`,`c`.`address` AS `address`,`tr`.`wpluarnegri` AS `wpluarnegri`,`tr`.`kodenegara` AS `kodenegara`,`tr`.`kodepajak` AS `kodepajak`,`tr`.`jumlahbruto` AS `jumlahbruto`,`tr`.`jumlahdpp` AS `jumlahdpp`,`tr`.`tanpanpwp` AS `tanpanpwp`,`tr`.`tarif` AS `tarif`,`tr`.`pph21` AS `pph21`,`tr`.`npwppemotong` AS `npwppemotong`,`tr`.`namapemotong` AS `namapemotong` from (`tax_report` `tr` join `customers` `c` on((`tr`.`member_id` = `c`.`id`)));
OGITU_VIEW_SQL_2);
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS `vw_customer_tax_report`');
        DB::statement('DROP VIEW IF EXISTS `vw_customer_bonus_pph21`');
    }
};
