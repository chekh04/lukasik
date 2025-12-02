<?php
class TrsImport {
    protected $config;
    protected $db;
    protected $logfile;

    public function __construct($registry) {
        $this->config = $registry->get('config');
        $this->db     = $registry->get('db');
        $this->logfile = DIR_STORAGE . 'logs/trs_import.log';
    }

    public function setConfig($config) { $this->config = $config; }

    public function log($msg) {
        $line = '['.date('Y-m-d H:i:s').'] '.$msg . PHP_EOL;
        file_put_contents($this->logfile, $line, FILE_APPEND);
    }

    public function import() {
        $trs_dir = $this->config->get('module_trs_import_pro_trs_dir');
        if (!$trs_dir) { $trs_dir = DIR_APPLICATION . '../trs'; }
        $filename = rtrim($trs_dir,'/').'/TSGoods.trs';
        if (!is_file($filename)) { $this->log('Файл не найден: ' . $filename); return; }
        $this->log('Чтение файла: ' . $filename);
        $rows = 0;
        if (($fh = fopen($filename, 'r')) !== false) {
            $header = fgetcsv($fh, 0, ';');
            while (($r = fgetcsv($fh, 0, ';')) !== false) { $rows++; if ($rows % 1000 === 0) $this->log('...строк обработано: '.$rows); }
            fclose($fh);
        }
        $this->log('ИМПОРТ ЗАВЕРШЕН, строк: '.$rows);
    }
}
