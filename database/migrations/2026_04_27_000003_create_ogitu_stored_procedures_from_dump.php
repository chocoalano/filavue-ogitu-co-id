<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("SET SESSION sql_mode = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");

        // Stored procedure: sp_accumulation_stockist_retail_amount_orders
        DB::statement('DROP PROCEDURE IF EXISTS `sp_accumulation_stockist_retail_amount_orders`');
        DB::unprepared(<<<'OGITU_PROC_SQL_1'
CREATE PROCEDURE `sp_accumulation_stockist_retail_amount_orders`(IN `p_order_id` BIGINT UNSIGNED)
proc:BEGIN
    DECLARE v_order_id BIGINT UNSIGNED;

    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SELECT 0 AS success, 'SQL ERROR: gagal menghitung/update.' AS message, p_order_id AS order_id;
    END;

    START TRANSACTION;

    
    SELECT id INTO v_order_id
    FROM orders
    WHERE id = p_order_id
    FOR UPDATE;

    IF v_order_id IS NULL THEN
        ROLLBACK;
        SELECT 0 AS success, 'Order tidak ditemukan.' AS message, p_order_id AS order_id;
        LEAVE proc;
    END IF;

    
    UPDATE orders o
    LEFT JOIN (
        SELECT
            oi.order_id,
            COALESCE(SUM(oi.qty * COALESCE(p.b_retail, 0)), 0)   AS retail_sum,
            COALESCE(SUM(oi.qty * COALESCE(p.b_stockist, 0)), 0) AS stockist_sum
        FROM order_items oi
        JOIN products p ON p.id = oi.product_id
        WHERE oi.order_id = p_order_id
        GROUP BY oi.order_id
    ) x ON x.order_id = o.id
    SET
        o.retail_amount   = COALESCE(x.retail_sum, 0),
        o.stockist_amount = COALESCE(x.stockist_sum, 0),
        o.updated_at      = NOW()
    WHERE o.id = p_order_id;

    COMMIT;

    
    SELECT
        1 AS success,
        'OK' AS message,
        o.id AS order_id,
        o.retail_amount,
        o.stockist_amount
    FROM orders o
    WHERE o.id = p_order_id;
END;
OGITU_PROC_SQL_1);

        // Stored procedure: sp_approve_withdrawal
        DB::statement('DROP PROCEDURE IF EXISTS `sp_approve_withdrawal`');
        DB::unprepared(<<<'OGITU_PROC_SQL_2'
CREATE PROCEDURE `sp_approve_withdrawal`(IN `p_withdrawal_id` BIGINT UNSIGNED, IN `p_payout_reference` VARCHAR(100), IN `p_payout_status` VARCHAR(50), IN `p_simulated` TINYINT(1), IN `p_midtrans_transaction_id` VARCHAR(100), OUT `o_success` TINYINT(1), OUT `o_message` VARCHAR(255), OUT `o_new_balance` DECIMAL(18,2))
proc:BEGIN
    DECLARE v_customer_id     BIGINT UNSIGNED;
    DECLARE v_type            VARCHAR(50);
    DECLARE v_status          VARCHAR(50);
    DECLARE v_balance_before  DECIMAL(18,2);
    DECLARE v_amount          DECIMAL(18,2);

    
    DECLARE v_notes_json      LONGTEXT;

    DECLARE v_customer_saldo  DECIMAL(18,2);
    DECLARE v_new_balance     DECIMAL(18,2);

    DECLARE v_bank_name       VARCHAR(100);
    DECLARE v_bank_account    VARCHAR(100);
    DECLARE v_bank_holder     VARCHAR(100);

    DECLARE v_not_found       TINYINT(1) DEFAULT 0;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_not_found = 1;

    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET o_success = 0;
        SET o_new_balance = NULL;
        SET o_message = COALESCE(o_message, 'Gagal menyetujui withdrawal');
    END;

    
    SET o_success     = 0;
    SET o_message     = NULL;
    SET o_new_balance = NULL;

    START TRANSACTION;

    
    SET v_not_found = 0;
    SELECT
        cwt.customer_id,
        cwt.type,
        cwt.status,
        cwt.balance_before,
        cwt.amount,
        cwt.notes
    INTO
        v_customer_id,
        v_type,
        v_status,
        v_balance_before,
        v_amount,
        v_notes_json
    FROM customer_wallet_transactions cwt
    WHERE cwt.id = p_withdrawal_id
    FOR UPDATE;

    IF v_not_found = 1 THEN
        ROLLBACK;
        SET o_message = 'Withdrawal tidak ditemukan';
        LEAVE proc;
    END IF;

    IF v_type <> 'withdrawal' OR v_status <> 'pending' THEN
        ROLLBACK;
        SET o_message = 'Withdrawal tidak dapat disetujui';
        LEAVE proc;
    END IF;

    IF v_amount IS NULL OR v_amount <= 0 THEN
        ROLLBACK;
        SET o_message = 'Nominal withdrawal tidak valid';
        LEAVE proc;
    END IF;

    
    IF v_balance_before < v_amount THEN
        ROLLBACK;
        SET o_message = 'Saldo pelanggan tidak mencukupi';
        LEAVE proc;
    END IF;

    
    IF v_notes_json IS NULL OR JSON_VALID(v_notes_json) = 0 THEN
        ROLLBACK;
        SET o_message = 'Informasi rekening bank tidak lengkap';
        LEAVE proc;
    END IF;

    SET v_bank_name    = NULLIF(JSON_UNQUOTE(JSON_EXTRACT(v_notes_json,'$.bank_name')), '');
    SET v_bank_account = NULLIF(JSON_UNQUOTE(JSON_EXTRACT(v_notes_json,'$.bank_account')), '');
    SET v_bank_holder  = NULLIF(JSON_UNQUOTE(JSON_EXTRACT(v_notes_json,'$.bank_holder')), '');

    IF v_bank_name IS NULL OR v_bank_account IS NULL OR v_bank_holder IS NULL THEN
        ROLLBACK;
        SET o_message = 'Informasi rekening bank tidak lengkap';
        LEAVE proc;
    END IF;

    
    SET v_not_found = 0;
    SELECT c.ewallet_saldo
    INTO v_customer_saldo
    FROM customers c
    WHERE c.id = v_customer_id
    FOR UPDATE;

    IF v_not_found = 1 THEN
        ROLLBACK;
        SET o_message = 'Pelanggan tidak ditemukan';
        LEAVE proc;
    END IF;

    
    UPDATE customers
    SET ewallet_saldo = ewallet_saldo - v_amount
    WHERE id = v_customer_id
      AND ewallet_saldo >= v_amount;

    IF ROW_COUNT() <> 1 THEN
        ROLLBACK;
        SET o_message = 'Saldo pelanggan tidak mencukupi';
        LEAVE proc;
    END IF;

    
    SELECT ewallet_saldo
    INTO v_new_balance
    FROM customers
    WHERE id = v_customer_id
    FOR UPDATE;

    
    SET v_notes_json = JSON_SET(
        v_notes_json,
        '$.payout_reference', NULLIF(p_payout_reference,''),
        '$.payout_status', COALESCE(NULLIF(p_payout_status,''), 'unknown'),
        '$.simulated', IFNULL(p_simulated,0),
        '$.processed_at', DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s')
    );

    
    UPDATE customer_wallet_transactions
    SET
        status                  = 'completed',
        balance_after           = v_new_balance,
        completed_at            = NOW(),
        midtrans_transaction_id = NULLIF(p_midtrans_transaction_id,''),
        notes                   = v_notes_json
    WHERE id = p_withdrawal_id
      AND status = 'pending'
      AND type = 'withdrawal';

    IF ROW_COUNT() <> 1 THEN
        
        ROLLBACK;
        SET o_message = 'Gagal mengubah data withdrawal';
        LEAVE proc;
    END IF;

    COMMIT;

    SET o_success     = 1;
    SET o_new_balance = v_new_balance;
    SET o_message     = CONCAT(
        'Withdrawal berhasil disetujui dan dana sedang ditransfer',
        IF(IFNULL(p_simulated,0)=1, ' (Sandbox Mode - Simulasi)', '')
    );
END;
OGITU_PROC_SQL_2);

        // Stored procedure: sp_bonus
        DB::statement('DROP PROCEDURE IF EXISTS `sp_bonus`');
        DB::unprepared(<<<'OGITU_PROC_SQL_3'
CREATE PROCEDURE `sp_bonus`(IN `m_id` INT, IN `in_date` DATE, IN `v_bonus` DECIMAL(15,2))
    MODIFIES SQL DATA
BEGIN

  DECLARE v_id INT DEFAULT NULL;
  DECLARE d_start, d_end DATE;

  SELECT `id` INTO v_id FROM `customer_bonuses` WHERE `member_id`=m_id AND `date`=in_date LIMIT 0,1;
    IF v_id IS NULL THEN
      INSERT INTO customer_bonuses (`member_id`,`amount`,`date`) VALUES (m_id,v_bonus,in_date);
    ELSE
      UPDATE `customer_bonuses` SET `amount`=`amount` + v_bonus WHERE `id`=v_id;
    END IF;
END;
OGITU_PROC_SQL_3);

        // Stored procedure: sp_bonus_cashback
        DB::statement('DROP PROCEDURE IF EXISTS `sp_bonus_cashback`');
        DB::unprepared(<<<'OGITU_PROC_SQL_4'
CREATE PROCEDURE `sp_bonus_cashback`(IN `p_order_id` INT)
BEGIN
    DECLARE v_customer INT;
    DECLARE v_bonus DECIMAL(15,2);

    SELECT customer_id, cashback_amount
    INTO v_customer, v_bonus
    FROM orders
    WHERE id = p_order_id;

    IF v_bonus > 0 THEN
        INSERT INTO customer_bonus_cashbacks
        VALUES (
            NULL, v_customer, p_order_id,
            v_bonus, 0, 0,
            CONCAT('Cashback Plan B - Order#', p_order_id),
            NOW(), NULL
        );
    END IF;

END;
OGITU_PROC_SQL_4);

        // Stored procedure: sp_bonus_engine_run
        DB::statement('DROP PROCEDURE IF EXISTS `sp_bonus_engine_run`');
        DB::unprepared(<<<'OGITU_PROC_SQL_5'
CREATE PROCEDURE `sp_bonus_engine_run`(IN `p_order_id` INT)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    CALL sp_validate_order(p_order_id);
    CALL sp_update_personal_omzet(p_order_id);
    CALL sp_update_group_omzet(p_order_id);

    CALL sp_bonus_transaction(p_order_id);
    CALL sp_finalize_order(p_order_id);

    COMMIT;
END;
OGITU_PROC_SQL_5);

        // Stored procedure: sp_bonus_royalty
        DB::statement('DROP PROCEDURE IF EXISTS `sp_bonus_royalty`');
        DB::unprepared(<<<'OGITU_PROC_SQL_6'
CREATE PROCEDURE `sp_bonus_royalty`(IN `p_order_id` INT)
BEGIN
    DECLARE v_customer INT;
    DECLARE v_sponsor INT;
    DECLARE v_bonus DECIMAL(15,2);

    SELECT o.customer_id, c.sponsor_id, o.royalty_amount
    INTO v_customer, v_sponsor, v_bonus
    FROM orders o
    JOIN customers c ON c.id = o.customer_id
    WHERE o.id = p_order_id;

    IF v_sponsor IS NOT NULL AND v_bonus > 0 THEN
        INSERT INTO customer_bonus_royalty
        VALUES (
            NULL, v_sponsor, v_customer,
            v_bonus, 0, 0,
            CONCAT('Royalty - Order#', p_order_id),
            NOW(), NULL
        );
    END IF;
END;
OGITU_PROC_SQL_6);

        // Stored procedure: sp_bonus_sponsor
        DB::statement('DROP PROCEDURE IF EXISTS `sp_bonus_sponsor`');
        DB::unprepared(<<<'OGITU_PROC_SQL_7'
CREATE PROCEDURE `sp_bonus_sponsor`(IN `p_order_id` INT)
BEGIN
    DECLARE v_customer INT;
    DECLARE v_sponsor INT;
    DECLARE v_bonus DECIMAL(15,2) DEFAULT 0;

    SELECT o.customer_id, c.sponsor_id, o.sponsor_amount
    INTO v_customer, v_sponsor, v_bonus
    FROM orders o
    JOIN customers c ON c.id = o.customer_id
    WHERE o.id = p_order_id;

    IF v_sponsor IS NOT NULL AND v_bonus > 0 THEN
        INSERT INTO customer_bonus_sponsors
        VALUES (
            NULL, v_sponsor, v_customer,
            v_bonus, 0, 0,
            CONCAT('Sponsor - Order#', p_order_id),
            NOW(), NULL
        );
    END IF;
END;
OGITU_PROC_SQL_7);

        // Stored procedure: sp_bonus_transaction
        DB::statement('DROP PROCEDURE IF EXISTS `sp_bonus_transaction`');
        DB::unprepared(<<<'OGITU_PROC_SQL_8'
CREATE PROCEDURE `sp_bonus_transaction`(IN `p_order_id` INT)
BEGIN
    
        CALL sp_bonus_sponsor(p_order_id);
        CALL sp_bonus_royalty(p_order_id);
        
   
END;
OGITU_PROC_SQL_8);

        // Stored procedure: sp_ewallet
        DB::statement('DROP PROCEDURE IF EXISTS `sp_ewallet`');
        DB::unprepared(<<<'OGITU_PROC_SQL_9'
CREATE PROCEDURE `sp_ewallet`(IN `in_date` DATE)
BEGIN

  DECLARE v_id,v_memberid INT DEFAULT NULL;
  DECLARE v_phone,v_uname,v_fname VARCHAR(50);
  DECLARE v_bonus,v_tax,v_bp, v_index,v_idx, v_ewallet, v_balance DECIMAL(15,2) DEFAULT 0;

  DECLARE done BOOLEAN DEFAULT 0;
  DECLARE cursor1 CURSOR FOR
    SELECT bns.id,bns.member_id, bns.amount,bns.index_value,bns.tax_value,m.username,m.name,m.phone,m.ewallet_saldo
      FROM customer_bonuses bns, customers m
      WHERE bns.member_id=m.id AND bns.date= in_date AND bns.status=0;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done :=1;

  START TRANSACTION;

  OPEN cursor1;
loop1:LOOP FETCH cursor1 INTO v_id, v_memberid, v_bonus,v_index, v_tax,v_uname,v_fname,v_phone,v_balance;
    IF done THEN
      CLOSE cursor1;
      LEAVE loop1;
    END IF;

    
    SET v_ewallet = v_bonus - REPLACE(v_tax,'-','');

    UPDATE `customers` SET `ewallet_saldo`=`ewallet_saldo` + v_ewallet WHERE id=v_memberid;

    INSERT INTO customer_wallet_transactions (`customer_id`,`type`,`amount`,`balance_before`,`balance_after`,`status`,`notes`,`completed_at`,`created_at`) 
    VALUES (v_memberid,'bonus',v_bonus,v_balance,v_balance + v_bonus,'completed',concat('Akumulasi komisi tanggal  ',in_date),NOW(),NOW());

    

    INSERT INTO customer_wallet_transactions (`customer_id`,`type`,`amount`,`balance_before`,`balance_after`,`status`,`notes`,`completed_at`,`created_at`) 
    VALUES (v_memberid,'tax',REPLACE(v_tax,'-',''),v_balance + v_bonus,(v_balance + v_bonus) - REPLACE(v_tax,'-',''),'completed',concat('Pajak komisi tanggal  ',in_date),NOW(),NOW());

    

  END LOOP loop1;
  COMMIT;
END;
OGITU_PROC_SQL_9);

        // Stored procedure: sp_finalize_order
        DB::statement('DROP PROCEDURE IF EXISTS `sp_finalize_order`');
        DB::unprepared(<<<'OGITU_PROC_SQL_10'
CREATE PROCEDURE `sp_finalize_order`(IN `p_order_id` INT)
BEGIN
    UPDATE orders
    SET bonus_generated = 1,
        processed_at = NOW()
    WHERE id = p_order_id;
END;
OGITU_PROC_SQL_10);

        // Stored procedure: sp_generate_bonus
        DB::statement('DROP PROCEDURE IF EXISTS `sp_generate_bonus`');
        DB::unprepared(<<<'OGITU_PROC_SQL_11'
CREATE PROCEDURE `sp_generate_bonus`()
BEGIN

  DECLARE l_curdate,in_date,l_tgl1 DATE;
  DECLARE l_month,l_tgl,l_thari INTEGER DEFAULT 0;
  DECLARE d_start, d_end DATE;
  
  START TRANSACTION;
  
    SET l_curdate = CURDATE();
    SELECT DATE_ADD(l_curdate, INTERVAL -1 DAY) INTO in_date;

    SELECT LAST_DAY(in_date - INTERVAL 1 MONTH) + INTERVAL 1 DAY INTO l_tgl1;
    SELECT DAY(l_curdate) INTO l_tgl;
    SELECT DAY(LAST_DAY(CURRENT_DATE)) INTO l_thari;
    
    CALL sp_generate_bonus_sponsor(in_date);
    CALL sp_generate_bonus_royalty(in_date);
    CALL sp_generate_bonus_cashback(in_date);
    
    
    CALL sp_pph21(in_date,in_date);
    CALL sp_ewallet(in_date);
  
  IF DAY(NOW()) = 1 THEN
    SET d_start=last_day(curdate() - interval 2 month) + interval 1 day;
    SET d_end= last_day(curdate() - interval 1 month);
    CALL sp_pph21_report(d_start,d_end);
  END IF;

END;
OGITU_PROC_SQL_11);

        // Stored procedure: sp_generate_bonus_cashback
        DB::statement('DROP PROCEDURE IF EXISTS `sp_generate_bonus_cashback`');
        DB::unprepared(<<<'OGITU_PROC_SQL_12'
CREATE PROCEDURE `sp_generate_bonus_cashback`(IN `in_date` DATE)
    MODIFIES SQL DATA
BEGIN

  DECLARE v_memberid INT DEFAULT 0;
  DECLARE v_bonus DECIMAL(15,2) DEFAULT 0;
  DECLARE v_date DATE;

  DECLARE done BOOLEAN DEFAULT 0;
  DECLARE cursor1 CURSOR FOR
    SELECT `member_id`, SUM(`amount`)AS bns, DATE(`created_at`)
      FROM `customer_bonus_cashbacks`
      
      WHERE `status`=0
      GROUP BY `member_id`;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done :=1;

  START TRANSACTION;
  OPEN cursor1;
loop1:LOOP FETCH cursor1 INTO v_memberid, v_bonus, v_date;
    IF done THEN
      CLOSE cursor1;
      LEAVE loop1;
    END IF;

    call sp_bonus(v_memberid,in_date,v_bonus);
    UPDATE `customer_bonus_cashbacks` SET `status`=1 WHERE `member_id`=v_memberid AND `status`=0;

  END LOOP loop1;
  COMMIT;
END;
OGITU_PROC_SQL_12);

        // Stored procedure: sp_generate_bonus_royalty
        DB::statement('DROP PROCEDURE IF EXISTS `sp_generate_bonus_royalty`');
        DB::unprepared(<<<'OGITU_PROC_SQL_13'
CREATE PROCEDURE `sp_generate_bonus_royalty`(IN `in_date` DATE)
    MODIFIES SQL DATA
BEGIN

  DECLARE v_memberid INT DEFAULT 0;
  DECLARE v_bonus DECIMAL(15,2) DEFAULT 0;
  DECLARE v_date DATE;

  DECLARE done BOOLEAN DEFAULT 0;
  DECLARE cursor1 CURSOR FOR
    SELECT `member_id`, SUM(`amount`)AS bns, DATE(`created_at`)
      FROM `customer_bonus_royalty`
      
      WHERE `status`=0
      GROUP BY `member_id`;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done :=1;

  START TRANSACTION;
  OPEN cursor1;
loop1:LOOP FETCH cursor1 INTO v_memberid, v_bonus, v_date;
    IF done THEN
      CLOSE cursor1;
      LEAVE loop1;
    END IF;

    call sp_bonus(v_memberid,in_date,v_bonus);
    UPDATE `customer_bonus_royalty` SET `status`=1 WHERE `member_id`=v_memberid AND `status`=0;

  END LOOP loop1;
  COMMIT;
END;
OGITU_PROC_SQL_13);

        // Stored procedure: sp_generate_bonus_sponsor
        DB::statement('DROP PROCEDURE IF EXISTS `sp_generate_bonus_sponsor`');
        DB::unprepared(<<<'OGITU_PROC_SQL_14'
CREATE PROCEDURE `sp_generate_bonus_sponsor`(IN `in_date` DATE)
    MODIFIES SQL DATA
BEGIN

  DECLARE v_memberid INT DEFAULT 0;
  DECLARE v_bonus DECIMAL(15,2) DEFAULT 0;
  DECLARE v_date DATE;

  DECLARE done BOOLEAN DEFAULT 0;
  DECLARE cursor1 CURSOR FOR
    SELECT `member_id`, SUM(`amount`)AS bns, DATE(`created_at`)
      FROM `customer_bonus_sponsors`
      
      WHERE `status`=0
      GROUP BY `member_id`;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done :=1;

  START TRANSACTION;
  OPEN cursor1;
loop1:LOOP FETCH cursor1 INTO v_memberid, v_bonus, v_date;
    IF done THEN
      CLOSE cursor1;
      LEAVE loop1;
    END IF;

	call sp_bonus(v_memberid,in_date,v_bonus);
    UPDATE `customer_bonus_sponsors` SET `status`=1 WHERE `member_id`=v_memberid AND `status`=0;

  END LOOP loop1;
  COMMIT;
END;
OGITU_PROC_SQL_14);

        // Stored procedure: sp_generate_group_omzet_sponsor
        DB::statement('DROP PROCEDURE IF EXISTS `sp_generate_group_omzet_sponsor`');
        DB::unprepared(<<<'OGITU_PROC_SQL_15'
CREATE PROCEDURE `sp_generate_group_omzet_sponsor`()
BEGIN

  DECLARE v_memberid INT;
  DECLARE v_subtotal DECIMAL(15,2) DEFAULT 0;

  DECLARE done BOOLEAN DEFAULT 0;
  DECLARE cursor1 CURSOR FOR
    SELECT `customer_id`, `subtotal_amount`
      FROM `orders`
      WHERE `type`='planA' ORDER BY `id` ASC;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done :=1;

  START TRANSACTION;
  OPEN cursor1;
loop1:LOOP FETCH cursor1 INTO v_memberid, v_subtotal;
    IF done THEN
      CLOSE cursor1;
      LEAVE loop1;
    END IF;

    call sp_update_group_omzet_sponsor_manual(v_memberid, v_subtotal);

  END LOOP loop1;
  COMMIT;
END;
OGITU_PROC_SQL_15);

        // Stored procedure: sp_generate_reward_plana
        DB::statement('DROP PROCEDURE IF EXISTS `sp_generate_reward_plana`');
        DB::unprepared(<<<'OGITU_PROC_SQL_16'
CREATE PROCEDURE `sp_generate_reward_plana`(IN `v_mid` INT, IN `v_left` INT, IN `v_right` INT)
BEGIN

  DECLARE v_id INT DEFAULT NULL;

  DECLARE done BOOLEAN DEFAULT 0;
  DECLARE cursor1 CURSOR FOR
    select id from rewards where type=0 AND `start`<=CURRENT_DATE AND `end`>=CURRENT_DATE AND status=1;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done :=1;
  
  START TRANSACTION;

  OPEN cursor1;
loop1:LOOP FETCH cursor1 INTO v_id;
    IF done THEN
      CLOSE cursor1;
      LEAVE loop1;
    END IF;

      CALL sp_generate_reward_plana_detail(v_mid,v_id,v_left,v_right);

  END LOOP loop1;
  COMMIT;
END;
OGITU_PROC_SQL_16);

        // Stored procedure: sp_generate_reward_plana_detail
        DB::statement('DROP PROCEDURE IF EXISTS `sp_generate_reward_plana_detail`');
        DB::unprepared(<<<'OGITU_PROC_SQL_17'
CREATE PROCEDURE `sp_generate_reward_plana_detail`(IN `v_mid` INT, IN `v_rid` INT, IN `vp_left` DECIMAL(15,2), IN `vp_right` DECIMAL(15,2))
BEGIN
      
      DECLARE v_id INT DEFAULT NULL;
      DECLARE v_status INT DEFAULT NULL;

      START TRANSACTION;

      SELECT id,status INTO v_id,v_status FROM `customer_bv_rewards` WHERE member_id=v_mid AND reward_id=v_rid LIMIT 1;
      IF v_id IS NOT NULL AND v_status=0 THEN
        UPDATE customer_bv_rewards SET omzet_left=omzet_left + vp_left,omzet_right=omzet_right + vp_right WHERE id=v_id;
      ELSEIF v_id IS NULL THEN
        INSERT INTO customer_bv_rewards (member_id,reward_id,omzet_left,omzet_right,status,created_on) VALUES (v_mid,v_rid,vp_left,vp_right,0,NOW());
      END IF;

      COMMIT;
END;
OGITU_PROC_SQL_17);

        // Stored procedure: sp_pph21
        DB::statement('DROP PROCEDURE IF EXISTS `sp_pph21`');
        DB::unprepared(<<<'OGITU_PROC_SQL_18'
CREATE PROCEDURE `sp_pph21`(IN `in_tglawal` DATE, IN `in_tglakhir` DATE)
    MODIFIES SQL DATA
BEGIN
  DECLARE l_ptkp_default DOUBLE DEFAULT 3000000; 
  
  DECLARE l_titlebonus INTEGER DEFAULT 99;
  DECLARE v_id, v_memberid,l_countnpwp,l_countpph,l_countpph2,l_countpph3,l_countnpwp2,l_newyear,l_jmlanak INTEGER DEFAULT 0;
  
  DECLARE l_nama, l_kerja, l_office, l_nonpwp, l_menikah,l_menikah2 VARCHAR(50) DEFAULT '';
  DECLARE l_alamat VARCHAR(225) DEFAULT '';
  DECLARE l_bruto,l_ptkp,l_totalpph, v_bruto, akumulasi_t,l_akumulasi_ptkp DOUBLE DEFAULT 0;
  DECLARE l_maxptkp,l_ptkp_nikah,l_ptkp_anak DOUBLE DEFAULT 0;

  DECLARE l_pkp, l_pph21, tarif, tarifnpwp DOUBLE DEFAULT 0;
  DECLARE l_npwpdate DATE;
  DECLARE l_gender integer default 0;
  
  DECLARE done BOOLEAN DEFAULT 0;
  DECLARE cursor1 CURSOR FOR
    SELECT bns.id,bns.member_id,bns.amount
      FROM customer_bonuses bns
      WHERE bns.status=0 AND bns.date BETWEEN in_tglawal AND in_tglakhir;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done :=1;

  START TRANSACTION;
  OPEN cursor1;
loop1:LOOP FETCH cursor1 INTO v_id, v_memberid, v_bruto;
    IF done THEN
      CLOSE cursor1;
      LEAVE loop1;
    END IF;
    SET l_pkp = 0;
    SET l_pph21 = 0;
    SET l_ptkp_anak = 0;
    SET l_ptkp_nikah = 0;

    IF v_bruto > 0 THEN
            SELECT COUNT(id) INTO l_countnpwp FROM customer_npwp WHERE member_id = v_memberid AND DATE(created) <= in_tglakhir;
      IF l_countnpwp > 0 THEN
        SELECT  jk,DATE(created), IFNULL(npwp, '-')AS npwp, alamat, kerja, office, IFNULL(menikah, 'N')AS menikah, IFNULL(anak, 0) AS anak
          INTO l_gender,l_npwpdate, l_nonpwp, l_alamat, l_kerja, l_office, l_menikah, l_jmlanak
        FROM customer_npwp WHERE member_id = v_memberid AND DATE(created) <= in_tglakhir 
        ORDER BY id DESC LIMIT 0,1;
        
        SET tarifnpwp = 100;
        SET l_kerja = 'Y';
        
                IF l_kerja = 'Y' THEN
          SET l_ptkp = 0;
        ELSE
          if l_gender = 1 then             IF l_menikah = 'Y' THEN
              SET l_ptkp_nikah = 250000;
            END IF;
            IF l_jmlanak > 0 THEN
              if l_jmlanak > 3 then 
                set l_jmlanak = 3;
              end if;
              SET l_ptkp_anak = l_jmlanak * 250000;
            END IF;

            SET l_ptkp = l_ptkp_default+l_ptkp_nikah+l_ptkp_anak;
          ELSE
            SET l_ptkp = l_ptkp_default;
          end if;
        END IF;
      ELSE
        SET l_ptkp = 0;
        SET l_nonpwp = '-'; 
        SET l_office = ''; 
        SET l_menikah = 'N';  
        SET l_jmlanak = 0;
        SET tarifnpwp = 100;
        SET l_kerja = 'N';
      END IF;
      
      SELECT COUNT(id) INTO l_countpph FROM customer_pph WHERE member_id=v_memberid AND periode BETWEEN CONCAT(SUBSTR(in_tglakhir,1,7),'-01') AND in_tglakhir;
      IF l_countpph > 0 THEN
        SELECT ROUND(SUM(bonus)/2) INTO l_akumulasi_ptkp FROM customer_pph WHERE member_id=v_memberid AND periode BETWEEN CONCAT(SUBSTR(in_tglakhir,1,7),'-01') AND in_tglakhir;
      ELSE 
        SET l_akumulasi_ptkp = 0;
      END IF;
      
      SET l_bruto = ROUND(v_bruto/2);
      
      IF l_akumulasi_ptkp >= l_ptkp THEN
        SET l_pkp = l_bruto;
        SET l_maxptkp = 0;
        SET l_akumulasi_ptkp = l_ptkp;
      ELSEIF((l_bruto+l_akumulasi_ptkp)-l_ptkp) > 0 THEN
        SET l_pkp = (l_bruto+l_akumulasi_ptkp)-l_ptkp;
        SET l_maxptkp = l_bruto-l_pkp;
        SET l_akumulasi_ptkp = l_ptkp;
      ELSE
        SET l_maxptkp = l_bruto;
        SET l_pkp = 0;
        SET l_akumulasi_ptkp = l_bruto+l_akumulasi_ptkp;
      END IF;
      
      IF l_pkp <= 0 THEN SET l_pkp = 0; END IF;
      
      
      SELECT COUNT(*) INTO l_countpph2 FROM customer_pph WHERE member_id = v_memberid AND MONTH(periode) = MONTH(in_tglakhir) AND YEAR(periode) = YEAR(in_tglakhir);
      IF l_countpph2 > 0 THEN
        IF l_countnpwp > 0 THEN
                    SELECT COUNT(*) INTO l_countpph3 FROM customer_pph WHERE member_id = v_memberid AND SUBSTR(DATE(periode),1,7) BETWEEN SUBSTR(l_npwpdate,1,7) AND SUBSTR(in_tglakhir,1,7);
          IF l_countpph3 > 0 THEN 
            SELECT sum_of_pkp INTO akumulasi_t FROM customer_pph WHERE member_id = v_memberid ORDER BY id DESC LIMIT 0,1;
          ELSE
            SET akumulasi_t = 0;
          END IF;
        ELSE        
          SELECT sum_of_pkp INTO akumulasi_t FROM customer_pph WHERE member_id = v_memberid ORDER BY id DESC LIMIT 0,1;
        END IF;
      ELSE
        SET akumulasi_t = 0;
      END IF;

      IF l_pkp > 0 THEN       
        IF (l_pkp + akumulasi_t) >= 500000000 THEN
          IF akumulasi_t >= 500000000 THEN
            SET tarif = 30; SET l_pph21 = l_pkp * (tarif / 100) * (tarifnpwp / 100);
          ELSE 
            IF akumulasi_t >= 250000000 THEN
              SET tarif = 25; SET l_pph21 = (500000000-akumulasi_t) * (tarif / 100) * (tarifnpwp / 100);
              SET tarif = 30; SET l_pph21 = l_pph21+(((l_pkp + akumulasi_t)-500000000) * (tarif / 100) * (tarifnpwp / 100));
            ELSEIF akumulasi_t >= 60000000 THEN
              SET tarif = 15; SET l_pph21 = (250000000-akumulasi_t) * (tarif / 100) * (tarifnpwp / 100);
              SET tarif = 25; SET l_pph21 = l_pph21+((500000000-250000000) * (tarif / 100) * (tarifnpwp / 100));
              SET tarif = 30; SET l_pph21 = l_pph21+(((l_pkp + akumulasi_t)-500000000) * (tarif / 100) * (tarifnpwp / 100));
            ELSE 
              SET tarif = 5;  SET l_pph21 = (60000000-akumulasi_t) * (tarif / 100) * (tarifnpwp / 100);
              SET tarif = 15; SET l_pph21 = l_pph21+((250000000-60000000) * (tarif / 100) * (tarifnpwp / 100));
              SET tarif = 25; SET l_pph21 = l_pph21+((500000000-250000000) * (tarif / 100) * (tarifnpwp / 100));
              SET tarif = 30; SET l_pph21 = l_pph21+(((l_pkp + akumulasi_t)-500000000) * (tarif / 100) * (tarifnpwp / 100));
            END IF;
          END IF;
        ELSEIF (l_pkp + akumulasi_t) >= 250000000 THEN
          IF akumulasi_t >= 250000000 THEN            
            SET tarif = 25; SET l_pph21 = l_pkp * (tarif / 100) * (tarifnpwp / 100);
          ELSE
            IF akumulasi_t >= 60000000 THEN           
              SET tarif = 15; SET l_pph21 = (250000000-akumulasi_t) * (tarif / 100) * (tarifnpwp / 100);
              SET tarif = 25; SET l_pph21 = l_pph21+(((l_pkp + akumulasi_t)-250000000) * (tarif / 100) * (tarifnpwp / 100));
            ELSE
              SET tarif = 5;  SET l_pph21 = (60000000-akumulasi_t) * (tarif / 100) * (tarifnpwp / 100);
              SET tarif = 15; SET l_pph21 = l_pph21+((250000000-60000000) * (tarif / 100) * (tarifnpwp / 100));
              SET tarif = 25; SET l_pph21 = l_pph21+(((l_pkp + akumulasi_t)-250000000) * (tarif / 100) * (tarifnpwp / 100));
            END IF;
          END IF;
        ELSEIF (l_pkp + akumulasi_t) >= 60000000 THEN
          IF akumulasi_t >= 60000000 THEN           
            SET tarif = 15; SET l_pph21 = l_pkp * (tarif / 100) * (tarifnpwp / 100);
          ELSE
            SET tarif = 5;  SET l_pph21 = (60000000-akumulasi_t) * (tarif / 100) * (tarifnpwp / 100);
            SET tarif = 15; SET l_pph21 = l_pph21+(((l_pkp + akumulasi_t)-60000000) * (tarif / 100) * (tarifnpwp / 100));
          END IF;
        ELSE
          SET tarif = 5;  SET l_pph21 = l_pkp * (tarif / 100) * (tarifnpwp / 100);
        END IF;
        
        SET l_pph21 = ROUND(l_pph21);
        
        IF l_pph21 > 0 THEN
          UPDATE `customer_bonuses` SET `tax_netto`=akumulasi_t,`tax_percent`=(tarif*tarifnpwp)/100, `tax_value`=-ROUND(l_pph21,0) WHERE id=v_id;
        END IF;       
      END IF;
        
      SET akumulasi_t = l_pkp + akumulasi_t;
      
      if l_menikah = 'Y' then
        set l_menikah2='1';
      else
        set l_menikah2='0';
      end if;
      INSERT INTO `customer_pph` 
          ( `id`        ,`member_id`  ,`nama`     , jk, `alamat`    ,`npwp`
            ,`krj`        ,`kantor`   ,`status`   ,`kid`      ,`bonus`
            ,`periode`      ,`ptkp`     ,`pkp`      ,`akumulasi_bruto_temp`
            ,`tarif`    ,`tarif_npwp` ,`pph21`    ,`created`
            ,`created_by`,sum_of_pkp,akumulasi_ptkp,buffer) 
          VALUES
          ( NULL        , v_memberid      , l_nama      , l_gender, l_alamat    , l_nonpwp
            , l_kerja, l_office   , l_menikah2    , l_jmlanak     , v_bruto
            , in_tglakhir   , l_maxptkp     , l_pkp     , akumulasi_t
            , tarif     , tarifnpwp   , l_pph21     , CURDATE()
            , 'automatic',akumulasi_t, l_akumulasi_ptkp, 0);
          
          UPDATE `customer_bonuses` SET `tax_netto`=akumulasi_t WHERE id=v_id;
    END IF;
  END LOOP loop1;
  COMMIT;
END;
OGITU_PROC_SQL_18);

        // Stored procedure: sp_pph21_report
        DB::statement('DROP PROCEDURE IF EXISTS `sp_pph21_report`');
        DB::unprepared(<<<'OGITU_PROC_SQL_19'
CREATE PROCEDURE `sp_pph21_report`(IN `in_tglawal` DATE, IN `in_tglakhir` DATE)
    MODIFIES SQL DATA
BEGIN
  DECLARE l_counter,v_memberid integer default 1;
  DECLARE v_bruto,v_dpp,v_pph21 DOUBLE DEFAULT 0;
  DECLARE v_npwp,v_ktp,v_name,v_tanpanpwp,l_nomorbuktipotong,v_kota,l_nourut VARCHAR(100);
  DECLARE v_tarif DECIMAL(5,2) DEFAULT 0.00;
  DECLARE v_periode DATE;

  DECLARE done BOOLEAN DEFAULT 0;
  DECLARE cursor1 CURSOR FOR SELECT p.member_id,p.periode, if(p.npwp='','000000000000000',p.npwp) as npwp,mm.nik,mm.name,mm.address,sum(round(p.bonus,0)) as bonus,round(sum(round(p.bonus,0))/2,0) as dpp,if(p.npwp='','N','N') as tanpanpwp,p.tarif,sum(round(p.pph21,0))
    FROM pph p
    LEFT JOIN customers mm on p.member_id=mm.id
    WHERE date(p.periode) BETWEEN in_tglawal and in_tglakhir
    GROUP BY p.member_id
    ORDER BY mm.name ASC;

  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done :=1;
  
  START TRANSACTION;

  OPEN cursor1;
loop1:LOOP FETCH cursor1 INTO v_memberid,v_periode,v_npwp,v_ktp,v_name,v_kota,v_bruto,v_dpp,v_tanpanpwp,v_tarif,v_pph21;
    IF done THEN
      CLOSE cursor1;
      LEAVE loop1;
    END IF;
    
    if length(l_counter) = 1 then SET l_nourut = concat('000000',l_counter);
    elseif length(l_counter) = 2 then SET l_nourut = concat('00000',l_counter);
    elseif length(l_counter) = 3 then SET l_nourut = concat('0000',l_counter);
    elseif length(l_counter) = 4 then SET l_nourut = concat('000',l_counter);
    elseif length(l_counter) = 5 then SET l_nourut = concat('00',l_counter);
    elseif length(l_counter) = 6 then SET l_nourut = concat('0',l_counter);
    elseif length(l_counter) >= 7 then SET l_nourut = l_counter; end if;

    SET l_nomorbuktipotong = concat('1.3-',substr(v_periode,6,2),'-',substr(v_periode,3,2),'.',l_nourut);

    INSERT INTO tax_report(member_id,tgl,masapajak,tahunpajak,pembetulan,nomorbuktipotong,npwp,nik,nama,alamat,wpluarnegri,kodenegara,kodepajak,jumlahbruto,jumlahdpp,tanpanpwp,tarif,pph21,npwppemotong,namapemotong)VALUES
          (v_memberid,DATE(in_tglakhir),MONTH(v_periode),YEAR(v_periode),0,l_nomorbuktipotong,v_npwp,v_ktp,v_name,v_kota,'N','','21-100-04',v_bruto,v_dpp,v_tanpanpwp,v_tarif,v_pph21,'000.000','PT. Zenith SInergi Utama');
        
    SET l_counter = l_counter+1;

  END LOOP loop1;

  COMMIT;
END;
OGITU_PROC_SQL_19);

        // Stored procedure: sp_process_omzet_batch
        DB::statement('DROP PROCEDURE IF EXISTS `sp_process_omzet_batch`');
        DB::unprepared(<<<'OGITU_PROC_SQL_20'
CREATE PROCEDURE `sp_process_omzet_batch`()
BEGIN
    DECLARE v_order_id INT; 
    DECLARE done INT DEFAULT FALSE; 

    DECLARE cur_orders CURSOR FOR 
        SELECT o.id 
        FROM orders o
        JOIN customers c ON o.customer_id = c.id
        WHERE o.type = 'planB' 
          AND o.status = 'PAID' 
          AND c.status = 3;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    OPEN cur_orders;

    read_loop: LOOP
        FETCH cur_orders INTO v_order_id;

        IF done THEN
            LEAVE read_loop;
        END IF;

        CALL sp_update_group_omzet_manual(v_order_id);

    END LOOP;

    CLOSE cur_orders;

END;
OGITU_PROC_SQL_20);

        // Stored procedure: sp_registration
        DB::statement('DROP PROCEDURE IF EXISTS `sp_registration`');
        DB::unprepared(<<<'OGITU_PROC_SQL_21'
CREATE PROCEDURE `sp_registration`(IN `p_member_id` INT UNSIGNED)
main: BEGIN
    
    DECLARE v_success TINYINT DEFAULT 0;
    DECLARE v_code    INT DEFAULT 0;
    DECLARE v_message VARCHAR(512) DEFAULT 'OK';

    
    DECLARE v_tx_started TINYINT DEFAULT 0;

    
    DECLARE v_errno    INT DEFAULT 0;
    DECLARE v_sqlstate CHAR(5) DEFAULT '00000';
    DECLARE v_errmsg   TEXT;

    
    DECLARE v_not_found TINYINT DEFAULT 0;

    
    DECLARE v_sponsor_id     INT UNSIGNED;
    DECLARE v_upline_id      INT UNSIGNED;
    DECLARE v_last_position  VARCHAR(10);
    DECLARE v_omzet          DECIMAL(18,2);

    DECLARE v_package_id     INT UNSIGNED;
    DECLARE v_flush_out      INT UNSIGNED;

    DECLARE v_last_upline    INT UNSIGNED;
    DECLARE v_level          INT DEFAULT 1;

    DECLARE v_last_sponsor   INT UNSIGNED;
    DECLARE v_prev_sponsor   INT UNSIGNED;
    DECLARE v_lvl            INT DEFAULT 1;

    DECLARE v_pos_sponsor    VARCHAR(10);

    DECLARE v_cur_member     INT UNSIGNED;
    DECLARE v_cur_upline     INT UNSIGNED;
    DECLARE v_member_pos     VARCHAR(10);

    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_not_found = 1;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1
            v_errno    = MYSQL_ERRNO,
            v_sqlstate = RETURNED_SQLSTATE,
            v_errmsg   = MESSAGE_TEXT;

        IF v_tx_started = 1 THEN
            ROLLBACK;
        END IF;

        SET v_success = 0;
        SET v_code    = IFNULL(v_errno, -1);
        SET v_message = CONCAT(
            'sp_register_test gagal (SQLSTATE ', IFNULL(v_sqlstate,'-----'), '): ',
            IFNULL(v_errmsg,'Unknown error')
        );

        SELECT v_success AS success, v_code AS code, v_message AS message;
    END;

    
    IF p_member_id IS NULL OR p_member_id = 0 THEN
        SET v_success = 0;
        SET v_code    = 400;
        SET v_message = 'member_id tidak valid';
        SELECT v_success AS success, v_code AS code, v_message AS message;
        LEAVE main;
    END IF;

    START TRANSACTION;
    SET v_tx_started = 1;
    
    

    
    SET v_not_found = 0;
    SELECT sponsor_id, upline_id, position, omzet
      INTO v_sponsor_id, v_upline_id, v_last_position, v_omzet
    FROM customers
    WHERE id = p_member_id
    FOR UPDATE;

    IF v_not_found = 1 THEN
        ROLLBACK;
        SET v_tx_started = 0;

        SET v_success = 0;
        SET v_code    = 404;
        SET v_message = CONCAT('Member tidak ditemukan: id=', p_member_id);
        SELECT v_success AS success, v_code AS code, v_message AS message;
        LEAVE main;
    END IF;

    SET v_omzet = IFNULL(v_omzet, 0);

    
    SET v_not_found = 0;
    SELECT id, flush_out
      INTO v_package_id, v_flush_out
    FROM customer_package
    WHERE price <= v_omzet
    ORDER BY price DESC
    LIMIT 1;

    IF v_not_found = 1 THEN
        SET v_package_id = NULL;
    END IF;

    IF v_package_id IS NOT NULL THEN
        UPDATE customers
        SET package_id = v_package_id
        WHERE id = p_member_id;
    END IF;

    
    IF v_upline_id IS NULL OR v_upline_id = 0 THEN
        ROLLBACK;
        SET v_tx_started = 0;

        SET v_success = 0;
        SET v_code    = 422;
        SET v_message = 'Upline tidak valid / belum di-set untuk member ini';
        SELECT v_success AS success, v_code AS code, v_message AS message;
        LEAVE main;
    END IF;

    

    
    SET v_last_upline = v_upline_id;

    WHILE v_last_upline IS NOT NULL AND v_last_upline > 0 DO
        INSERT INTO customer_networks
        VALUES (NULL, p_member_id, v_last_upline, v_last_position, 1, v_level, NULL, NOW(), NULL);

        SET v_level = v_level + 1;

        SET v_not_found = 0;
        SELECT upline_id, position
          INTO v_last_upline, v_last_position
        FROM customers
        WHERE id = v_last_upline;

        IF v_not_found = 1 THEN
            ROLLBACK;
            SET v_tx_started = 0;

            SET v_success = 0;
            SET v_code    = 500;
            SET v_message = 'Integrity error: data upline chain putus (customers tidak ditemukan)';
            SELECT v_success AS success, v_code AS code, v_message AS message;
            LEAVE main;
        END IF;
    END WHILE;

    
    SET v_last_sponsor = IFNULL(v_sponsor_id, 0);

    WHILE v_last_sponsor IS NOT NULL AND v_last_sponsor > 0 DO
        INSERT INTO customer_network_matrixes
        VALUES (NULL, p_member_id, v_last_sponsor, v_lvl, NULL, CURRENT_DATE(), NULL);

        SET v_lvl = v_lvl + 1;
        SET v_prev_sponsor = v_last_sponsor;

        SET v_not_found = 0;
        SELECT sponsor_id
          INTO v_last_sponsor
        FROM customers
        WHERE id = v_prev_sponsor;

        IF v_not_found = 1 THEN
            ROLLBACK;
            SET v_tx_started = 0;

            SET v_success = 0;
            SET v_code    = 500;
            SET v_message = CONCAT('Integrity error: sponsor chain putus (sponsor_id tidak ditemukan untuk id=', v_prev_sponsor, ')');
            SELECT v_success AS success, v_code AS code, v_message AS message;
            LEAVE main;
        END IF;

        SET v_last_sponsor = IFNULL(v_last_sponsor, 0);
    END WHILE;

    
    IF v_sponsor_id IS NOT NULL AND v_sponsor_id > 0 THEN
        SET v_not_found = 0;
        SELECT position
          INTO v_pos_sponsor
        FROM customer_networks
        WHERE member_id = p_member_id
          AND upline_id = v_sponsor_id
        LIMIT 1;

        IF v_not_found = 0 AND v_pos_sponsor IS NOT NULL THEN
            IF v_pos_sponsor = 'left' THEN
                UPDATE customers SET sponsor_left  = sponsor_left  + 1 WHERE id = v_sponsor_id;
            ELSEIF v_pos_sponsor = 'right' THEN
                UPDATE customers SET sponsor_right = sponsor_right + 1 WHERE id = v_sponsor_id;
            END IF;
        END IF;
    END IF;

    
    SET v_cur_member = p_member_id;
    SET v_cur_upline = v_upline_id;

    WHILE v_cur_upline IS NOT NULL AND v_cur_upline > 0 DO
        SET v_not_found = 0;
        SELECT position
          INTO v_member_pos
        FROM customers
        WHERE id = v_cur_member;

        IF v_not_found = 1 THEN
            ROLLBACK;
            SET v_tx_started = 0;

            SET v_success = 0;
            SET v_code    = 500;
            SET v_message = 'Integrity error: member chain putus saat update total_left/total_right';
            SELECT v_success AS success, v_code AS code, v_message AS message;
            LEAVE main;
        END IF;

        IF v_member_pos = 'right' THEN
            UPDATE customers SET total_right = total_right + 1 WHERE id = v_cur_upline;
        ELSE
            UPDATE customers SET total_left  = total_left  + 1 WHERE id = v_cur_upline;
        END IF;

        SET v_cur_member = v_cur_upline;

        SET v_not_found = 0;
        SELECT upline_id
          INTO v_cur_upline
        FROM customers
        WHERE id = v_cur_member;

        IF v_not_found = 1 THEN
            ROLLBACK;
            SET v_tx_started = 0;

            SET v_success = 0;
            SET v_code    = 500;
            SET v_message = 'Integrity error: upline_id parent tidak ditemukan';
            SELECT v_success AS success, v_code AS code, v_message AS message;
            LEAVE main;
        END IF;

        SET v_cur_upline = IFNULL(v_cur_upline, 0);
    END WHILE;
    
    CALL sp_update_group_omzet_placement(p_member_id);
    CALL sp_bonus_sponsor_placement(p_member_id);
    CALL sp_bonus_pairing_placement(p_member_id, 350000,20000);
    CALL sp_bonus_matching_placement(p_member_id);

    COMMIT;
    SET v_tx_started = 0;

    SET v_success = 1;
    SET v_code    = 200;
    SET v_message = CONCAT(
        'Register berhasil. member_id=', p_member_id,
        ', package_id=', IFNULL(v_package_id, 0),
        ', sponsor_id=', IFNULL(v_sponsor_id, 0),
        ', upline_id=', IFNULL(v_upline_id, 0)
    );

    SELECT v_success AS success, v_code AS code, v_message AS message;
END main;
OGITU_PROC_SQL_21);

        // Stored procedure: sp_request_withdrawal
        DB::statement('DROP PROCEDURE IF EXISTS `sp_request_withdrawal`');
        DB::unprepared(<<<'OGITU_PROC_SQL_22'
CREATE PROCEDURE `sp_request_withdrawal`(IN `p_customer_id` BIGINT UNSIGNED, IN `p_amount` DECIMAL(18,2), IN `p_admin_fee` DECIMAL(18,2), OUT `o_success` TINYINT(1), OUT `o_message` VARCHAR(255), OUT `o_withdrawal_id` BIGINT UNSIGNED, OUT `o_transaction_ref` VARCHAR(100), OUT `o_balance_before` DECIMAL(18,2))
proc:BEGIN
    DECLARE v_customer_name   VARCHAR(255);
    DECLARE v_bank_name       VARCHAR(100);
    DECLARE v_bank_account    VARCHAR(100);
    DECLARE v_nik             VARCHAR(50);

    DECLARE v_customer_saldo  DECIMAL(18,2);
    DECLARE v_admin_fee       DECIMAL(18,2);
    DECLARE v_net_amount      DECIMAL(18,2);

    DECLARE v_existing_id     BIGINT UNSIGNED;

    DECLARE v_notes_json      JSON;

    DECLARE v_not_found       TINYINT(1) DEFAULT 0;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_not_found = 1;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET o_success         = 0;
        SET o_withdrawal_id   = NULL;
        SET o_transaction_ref = NULL;
        SET o_balance_before  = NULL;
        SET o_message         = COALESCE(o_message, 'Terjadi kesalahan saat membuat withdrawal');
    END;

    
    SET o_success         = 0;
    SET o_message         = NULL;
    SET o_withdrawal_id   = NULL;
    SET o_transaction_ref = NULL;
    SET o_balance_before  = NULL;

    
    IF p_customer_id IS NULL OR p_customer_id = 0 THEN
        SET o_message = 'Customer ID tidak valid';
        LEAVE proc;
    END IF;

    IF p_amount IS NULL THEN
        SET o_message = 'Nominal penarikan wajib diisi';
        LEAVE proc;
    END IF;

    IF p_amount <= 0 THEN
        SET o_message = 'Nominal penarikan harus lebih besar dari 0';
        LEAVE proc;
    END IF;

    
    IF p_amount < 10000 THEN
        SET o_message = 'Nominal penarikan minimal 50.000';
        LEAVE proc;
    END IF;

    
    SET v_admin_fee = IFNULL(p_admin_fee, 6500);

    IF v_admin_fee < 0 THEN
        SET o_message = 'Biaya admin tidak boleh negatif';
        LEAVE proc;
    END IF;

    
    IF v_admin_fee >= p_amount THEN
        SET o_message = 'Nominal penarikan tidak mencukupi setelah biaya admin (biaya admin >= nominal)';
        LEAVE proc;
    END IF;

    SET v_net_amount = p_amount - v_admin_fee;

    
    START TRANSACTION;

    
    SET v_not_found = 0;
    SELECT
        c.ewallet_saldo,
        c.name,
        c.bank_name,
        c.bank_account,
        c.nik
    INTO
        v_customer_saldo,
        v_customer_name,
        v_bank_name,
        v_bank_account,
        v_nik
    FROM customers c
    WHERE c.id = p_customer_id
    FOR UPDATE;

    IF v_not_found = 1 THEN
        ROLLBACK;
        SET o_message = CONCAT('Pelanggan tidak ditemukan (ID: ', p_customer_id, ')');
        LEAVE proc;
    END IF;

    
    IF v_customer_saldo IS NULL THEN
        ROLLBACK;
        SET o_message = 'Saldo pelanggan tidak tersedia (NULL). Hubungi admin.';
        LEAVE proc;
    END IF;

    IF v_customer_saldo < 0 THEN
        ROLLBACK;
        SET o_message = CONCAT('Saldo pelanggan tidak valid (negatif): ', FORMAT(v_customer_saldo, 2), '. Hubungi admin.');
        LEAVE proc;
    END IF;

    
    SET o_balance_before = v_customer_saldo;

    
    IF v_nik IS NULL OR v_nik = '' THEN
        ROLLBACK;
        SET o_message = 'NIK belum diisi. Lengkapi NIK di profil sebelum melakukan penarikan.';
        LEAVE proc;
    END IF;

    IF v_bank_name IS NULL OR v_bank_name = '' THEN
        ROLLBACK;
        SET o_message = 'Nama bank belum diisi. Lengkapi informasi bank di profil sebelum melakukan penarikan.';
        LEAVE proc;
    END IF;

    IF v_bank_account IS NULL OR v_bank_account = '' THEN
        ROLLBACK;
        SET o_message = 'Nomor rekening bank belum diisi. Lengkapi informasi bank di profil sebelum melakukan penarikan.';
        LEAVE proc;
    END IF;

    IF v_customer_name IS NULL OR v_customer_name = '' THEN
        ROLLBACK;
        SET o_message = 'Nama pemilik rekening belum tersedia. Lengkapi nama Anda di profil.';
        LEAVE proc;
    END IF;

    
    SET v_not_found = 0;
    SELECT id
    INTO v_existing_id
    FROM customer_wallet_transactions
    WHERE customer_id = p_customer_id
      AND type = 'withdrawal'
      AND status = 'pending'
    LIMIT 1
    FOR UPDATE;

    IF v_not_found = 0 THEN
        ROLLBACK;
        SET o_message = CONCAT(
            'Masih ada withdrawal pending (ID: ', v_existing_id, '). ',
            'Silakan tunggu hingga permintaan sebelumnya diproses.'
        );
        LEAVE proc;
    END IF;

    
    IF v_customer_saldo < p_amount THEN
        ROLLBACK;
        SET o_message = CONCAT(
            'Saldo tidak mencukupi. Saldo Anda: ',
            FORMAT(v_customer_saldo, 0),
            ', nominal penarikan: ',
            FORMAT(p_amount, 0),
            ', kurang: ',
            FORMAT((p_amount - v_customer_saldo), 0)
        );
        LEAVE proc;
    END IF;

    
    SET o_transaction_ref = CONCAT(
        'WD-',
        DATE_FORMAT(NOW(), '%Y%m%d%H%i%s'),
        '-',
        UPPER(SUBSTRING(REPLACE(UUID(), '-', ''), 1, 6))
    );

    
    SET v_notes_json = JSON_OBJECT(
        'bank_name', v_bank_name,
        'bank_account', v_bank_account,
        'bank_holder', v_customer_name,
        'gross_amount', p_amount,
        'admin_fee', v_admin_fee,
        'net_amount', v_net_amount,
        'requested_at', DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s')
    );

    
    INSERT INTO customer_wallet_transactions
    (
        customer_id,
        type,
        amount,
        balance_before,
        balance_after,
        status,
        transaction_ref,
        notes,
        created_at,
        updated_at,
        is_system
    )
    VALUES
    (
        p_customer_id,
        'withdrawal',
        p_amount,
        v_customer_saldo,
        v_customer_saldo, 
        'pending',
        o_transaction_ref,
        v_notes_json,
        NOW(),
        NOW(),
        1
    );

    SET o_withdrawal_id = LAST_INSERT_ID();

    COMMIT;

    SET o_success = 1;
    SET o_message = CONCAT(
        'Permintaan penarikan berhasil dibuat (Ref: ', o_transaction_ref, '). ',
        'Akan diproses dalam 1-3 hari kerja.'
    );
END;
OGITU_PROC_SQL_22);

        // Stored procedure: sp_update_group_omzet
        DB::statement('DROP PROCEDURE IF EXISTS `sp_update_group_omzet`');
        DB::unprepared(<<<'OGITU_PROC_SQL_23'
CREATE PROCEDURE `sp_update_group_omzet`(IN `p_order_id` INT)
BEGIN
    DECLARE v_customer, v_sponsor, v_parent, v_parent_sponsor, v_count_direct, v_count_level, v_SET INT DEFAULT 0;
    DECLARE v_subtotal, v_pv, v_omzet_grp DECIMAL(15,2) DEFAULT 0;
    DECLARE v_type, v_new_level, v_last_level, v_level VARCHAR(20);
    DECLARE v_pos VARCHAR(10);

    SELECT o.customer_id, o.subtotal_amount, o.pv_amount, c.sponsor_id
    INTO v_customer, v_subtotal, v_pv, v_sponsor
    FROM orders o
    JOIN customers c ON c.id = o.customer_id
    WHERE o.id = p_order_id;

        WHILE v_sponsor IS NOT NULL DO
            SET v_SET = 0;
            SET v_count_level = 0;

            UPDATE customers SET omzet_group = omzet_group + v_subtotal WHERE id = v_sponsor;

            SELECT sponsor_id, omzet_group, level INTO v_parent_sponsor, v_omzet_grp, v_level 
            FROM customers WHERE id = v_sponsor;

            IF v_level IS NULL AND v_omzet_grp >= 10000000 THEN
                SELECT COUNT(id) INTO v_count_direct FROM customer_network_matrixes WHERE sponsor_id = v_sponsor AND level = 1;
                IF v_count_direct >= 2 THEN
                    UPDATE customers SET level = 'Dropshipper' WHERE id = v_sponsor;
                END IF;
            END IF;

            IF v_level IS NOT NULL THEN
                IF v_level = 'Dropshipper' THEN
                    SET v_last_level='Dropshipper', v_new_level='Reseler', v_SET=1;
                ELSEIF v_level = 'Reseler' THEN
                    SET v_last_level='Reseler', v_new_level='Agent', v_SET=1;
                ELSEIF v_level = 'Agent' THEN
                    SET v_last_level='Agent', v_new_level='Master Agent', v_SET=1;
                END IF;

                IF v_SET = 1 THEN
                    SELECT COUNT(cn.id) INTO v_count_level FROM customer_network_matrixes cn
                    JOIN customers c ON cn.member_id = c.id
                    WHERE cn.sponsor_id = v_sponsor AND cn.level = 1 AND c.level = v_last_level;

                    IF v_count_level >= 2 THEN
                        UPDATE customers SET level = v_new_level WHERE id = v_sponsor;
                    END IF;
                END IF;
            END IF;

            SET v_sponsor = v_parent_sponsor;
        END WHILE;
   
END;
OGITU_PROC_SQL_23);

        // Stored procedure: sp_update_personal_omzet
        DB::statement('DROP PROCEDURE IF EXISTS `sp_update_personal_omzet`');
        DB::unprepared(<<<'OGITU_PROC_SQL_24'
CREATE PROCEDURE `sp_update_personal_omzet`(IN `p_order_id` INT)
BEGIN
    DECLARE v_customer INT;
    DECLARE v_subtotal DECIMAL(15,2) DEFAULT 0;

    SELECT customer_id, subtotal_amount
    INTO v_customer, v_subtotal
    FROM orders
    WHERE id = p_order_id;

    UPDATE customers SET omzet = omzet + v_subtotal WHERE id = v_customer;
   
END;
OGITU_PROC_SQL_24);

        // Stored procedure: sp_validate_order
        DB::statement('DROP PROCEDURE IF EXISTS `sp_validate_order`');
        DB::unprepared(<<<'OGITU_PROC_SQL_25'
CREATE PROCEDURE `sp_validate_order`(IN `p_order_id` INT)
BEGIN
    DECLARE v_done INT;

    SELECT bonus_generated
    INTO v_done
    FROM orders
    WHERE id = p_order_id
    FOR UPDATE;

    IF v_done = 1 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Order already processed';
    END IF;
END;
OGITU_PROC_SQL_25);
    }

    public function down(): void
    {
        DB::statement('DROP PROCEDURE IF EXISTS `sp_validate_order`');
        DB::statement('DROP PROCEDURE IF EXISTS `sp_update_personal_omzet`');
        DB::statement('DROP PROCEDURE IF EXISTS `sp_update_group_omzet`');
        DB::statement('DROP PROCEDURE IF EXISTS `sp_registration`');
        DB::statement('DROP PROCEDURE IF EXISTS `sp_process_omzet_batch`');
        DB::statement('DROP PROCEDURE IF EXISTS `sp_pph21_report`');
        DB::statement('DROP PROCEDURE IF EXISTS `sp_pph21`');
        DB::statement('DROP PROCEDURE IF EXISTS `sp_generate_reward_plana_detail`');
        DB::statement('DROP PROCEDURE IF EXISTS `sp_generate_reward_plana`');
        DB::statement('DROP PROCEDURE IF EXISTS `sp_generate_group_omzet_sponsor`');
        DB::statement('DROP PROCEDURE IF EXISTS `sp_generate_bonus_sponsor`');
        DB::statement('DROP PROCEDURE IF EXISTS `sp_generate_bonus_royalty`');
        DB::statement('DROP PROCEDURE IF EXISTS `sp_generate_bonus_cashback`');
        DB::statement('DROP PROCEDURE IF EXISTS `sp_generate_bonus`');
        DB::statement('DROP PROCEDURE IF EXISTS `sp_finalize_order`');
        DB::statement('DROP PROCEDURE IF EXISTS `sp_ewallet`');
        DB::statement('DROP PROCEDURE IF EXISTS `sp_bonus_transaction`');
        DB::statement('DROP PROCEDURE IF EXISTS `sp_bonus_sponsor`');
        DB::statement('DROP PROCEDURE IF EXISTS `sp_bonus_royalty`');
        DB::statement('DROP PROCEDURE IF EXISTS `sp_bonus_engine_run`');
        DB::statement('DROP PROCEDURE IF EXISTS `sp_bonus_cashback`');
        DB::statement('DROP PROCEDURE IF EXISTS `sp_bonus`');
        DB::statement('DROP PROCEDURE IF EXISTS `sp_approve_withdrawal`');
        DB::statement('DROP PROCEDURE IF EXISTS `sp_accumulation_stockist_retail_amount_orders`');
    }
};
