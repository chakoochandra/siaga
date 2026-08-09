<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Create_function_WorkingDaysBetween extends CI_Migration
{

	public function up()
	{
		$this->db->query("DROP FUNCTION IF EXISTS WorkingDaysBetween");

		$sql = "
        CREATE FUNCTION WorkingDaysBetween(start_date DATE, end_date DATE)
        RETURNS INT
        READS SQL DATA
        DETERMINISTIC
        BEGIN
            DECLARE total_days INT;
            SELECT COUNT(*) INTO total_days
            FROM (
                SELECT start_date + INTERVAL n DAY AS day
                FROM numbers
                WHERE start_date + INTERVAL n DAY > start_date
                  AND start_date + INTERVAL n DAY <= end_date
            ) AS dates
            WHERE
                DAYOFWEEK(day) NOT IN (1, 7)
                AND NOT EXISTS (
                    SELECT 1 FROM tmst_holiday h
                    WHERE
                        h.tanggal = dates.day
                        OR
                        (YEAR(h.tanggal) = 0 AND MONTH(h.tanggal) = MONTH(dates.day) AND DAY(h.tanggal) = DAY(dates.day))
                );
            RETURN total_days;
        END
    ";

		$this->db->query($sql);
	}

	public function down()
	{
		// $this->db->query("
		// 	DROP FUNCTION IF EXISTS WorkingDaysBetween;
		// ");
	}
}
