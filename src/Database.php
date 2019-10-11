<?php

namespace Globalis\WP\Datanova;

class Database
{
    const MYSQL_TABLE = 'datanova_laposte_hexasmal';
    const CSV_URL     = 'https://datanova.laposte.fr/explore/dataset/laposte_hexasmal/download/?format=csv';

    private static $instance = null;

    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function hooks()
    {
        add_action('init', [$this, 'scheduleUpdate']);
        add_action('datanova\trigger_update_database', [$this, 'update']);
    }

    public function scheduleUpdate()
    {
        if (!wp_next_scheduled('datanova\trigger_update_database')) {
            $date = new \DateTime('today 03:30', new \DateTimeZone(get_option('timezone_string')));
            if ($date < new \DateTime('now', new \DateTimeZone(get_option('timezone_string')))) {
                 $date->modify('+1 day');
            }
            wp_schedule_single_event($date->getTimestamp(), 'datanova\trigger_update_database');
        }
    }

    public function getTable()
    {
        global $wpdb;
        return $wpdb->prefix . static::MYSQL_TABLE;
    }

    public function tableExists()
    {
        global $wpdb;

        $query = "SELECT IF (EXISTS (SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '%s' AND TABLE_NAME = '%s'), true, false)";

        return (bool) $wpdb->get_var(sprintf($query, esc_sql(DB_NAME), esc_sql($this->getTable())));
    }

    public function tableCreate()
    {
        global $wpdb;

        $query = sprintf("CREATE TABLE `%s` (
			`ID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			`code_postal` varchar(10) CHARACTER SET utf8mb4 NOT NULL,
			`code_insee` varchar(10) CHARACTER SET utf8mb4 DEFAULT NULL,
			`ville` text COLLATE utf8mb4_unicode_ci NOT NULL,
            PRIMARY KEY (ID),
            INDEX code_postal (code_postal),
            INDEX code_insee (code_insee)
        ) %s;", $this->getTable(), $wpdb->get_charset_collate());

        $wpdb->query($query);
    }

    public function update()
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '512M');
        set_time_limit(10*MINUTE_IN_SECONDS);

        if (!$this->tableExists()) {
            $this->tableCreate();
        }

        if (($fp = fopen($this->getCsvfile(), "r")) !== false) {
            global $wpdb;

            $headers = fgetcsv($fp, 500, ";");

            $values = [];

            while (($row = fgetcsv($fp, 500, ";")) !== false) {
                $row = $wpdb->prepare("('%s', '%s', '%s')", $row[2], $row[0], $row[1]);

                if (!in_array($row, $values)) {
                    $values[] = $row;
                }
            }

            fclose($fp);
            $this->deleteCsvFile();

            if (count($values) < 10000) {
                return;
            }

            $wpdb->query("START TRANSACTION");
            $wpdb->query("DELETE FROM `" . $this->getTable() . "`");
            $wpdb->query(sprintf("INSERT INTO `" . $this->getTable() . "` (`code_postal`, `code_insee`, `ville`) VALUES %s", implode(',', $values)));
            $wpdb->query("COMMIT");
        }
    }

    protected function getCsvfile()
    {
        $http_response = wp_remote_get(self::CSV_URL, ['timeout' => 120, 'sslverify' => false]);

        if (200 !== wp_remote_retrieve_response_code($http_response)) {
            return false;
        }

        $this->filename = tempnam('/tmp', 'datanova_laposte_hexasmal_csv_' . date('YmdHis') . '_');
        file_put_contents($this->filename, $http_response['body']);
        return $this->filename;
    }

    protected function deleteCsvFile()
    {
        if (isset($this->filename)) {
            unlink($this->filename);
            unset($this->filename);
        }
    }
}
