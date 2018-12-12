<?php
require_once(__DIR__.'/log4php/main/php/Logger.php');
define('Webpay_ROOT', dirname(__DIR__));

class LogHandler {

    private $logFile;
    private $logDir;
    private $logURL;

    public function __construct($ecommerce = 'opencart', $days = 7, $weight = '2MB') {

        $this->logFile = null;
        $this->logDir = null;
        $this->logURL = null;
        $this->lockfile = Webpay_ROOT."/set_logs_activate.lock";
        $dia = date('Y-m-d');
        $this->confdays = $days;
        $this->confweight = $weight;

        $this->logDir = DIR_IMAGE."logs/Transbank_webpay";
        $this->logFile = "{$this->logDir}/log_transbank_{$ecommerce}_{$dia}.log";
        $this->logURL = str_replace($_SERVER['DOCUMENT_ROOT'], "", $this->logDir);

        $configuration =   array(
            'appenders' => array(
                'default' => array(
                    'class' => 'LoggerAppenderRollingFile',
                    'layout' => array(
                        'class' => 'LoggerLayoutPattern',
                        'params' => array(
                            'conversionPattern' => '[%date{Y-m-d H:i:s}] [%-5level] %msg%n',
                        )
                    ),
                    'params' => array(
                        'file' => $this->logFile,
                        'maxFileSize' => $this->confweight,
                        'maxBackupIndex' => 10,
                    ),
                ),
            ),
            'rootLogger' => array(
                'appenders' => array('default'),
            )
        );

        Logger::configure($configuration);
        $this->logger = Logger::getLogger('main');
    }

    private function formatBytes($path) {
        $bytes = sprintf('%u', filesize($path));
        if ($bytes > 0) {
            $unit = intval(log($bytes, 1024));
            $units = array('B', 'KB', 'MB', 'GB');
            if (array_key_exists($unit, $units) === true) {
                return sprintf('%d %s', $bytes / pow(1024, $unit), $units[$unit]);
            }
        }
        return $bytes;
    }

    private function getIsLogDir() {
        if (! file_exists($this->logDir)) {
            return false;
        } else {
            return true;
        }
    }

    private function setMakeLogDir() {
        if ($this->getIsLogDir() === false) {
            $oldmask = umask(0);
            mkdir($this->logDir, 0777, true);
            $ts= date('YmdHis');
            $message = "[TEST][{$ts}][Este es un log de pruebas inicial, se crea automaticamente al instalar el plugin, se eliminará automaticamente]";
            $this->logger->info($message);
            umask($oldmask);
        }
    }

    private function setparamsconf($days, $weight) {
        if (file_exists($this->lockfile)) {
            $file = fopen($this->lockfile, "w") or die("No se puede truncar archivo");
            if (! is_numeric($days) or $days == null or $days == '' or $days === false) {
                $days = 7;
            }
            $txt = "{$days}\n";
            fwrite($file, $txt);
            $txt = "{$weight}\n";
            fwrite($file, $txt);
            fclose($file);
            chmod($this->lockfile, 0600);
        }
    }

    private function setLockFile() {
        if (! file_exists($this->lockfile)) {
            $file = fopen($this->lockfile, "w") or die("No se puede crear archivo de bloqueo");
            if (! is_numeric($this->confdays) or $this->confdays == null or $this->confdays == '' or $this->confdays === false) {
                $this->confdays = $days;
            }
            $txt = "{$this->confdays}\n";
            fwrite($file, $txt);
            $txt = "{$this->confweight}\n";
            fwrite($file, $txt);
            fclose($file);
            chmod($this->lockfile, 0600);
            return true;
        } else {
            return false;
        }
    }

    public function getValidateLockFile() {
        if (! file_exists($this->lockfile)) {
            $result = array(
                'status' => false,
                'lock_file' => basename($this->lockfile),
                'max_logs_days' => '7',
                'max_log_weight' => '2'
            );
        } else {
            $lines = file($this->lockfile);
            $this->confdays = trim(preg_replace('/\s\s+/', ' ', $lines[0]));
            $this->confweight = trim(preg_replace('/\s\s+/', ' ', $lines[1]));
            $result = array(
                'status' => true,
                'lock_file' => basename($this->lockfile),
                'max_logs_days' => $this->confdays,
                'max_log_weight' => $this->confweight
            );
        }
        return $result;
    }

    private function delLockFile() {
        if (file_exists($this->lockfile)) {
            unlink($this->lockfile);
        }
    }

    private function setLogList() {
        $arr = array_diff(scandir($this->logDir), array('.', '..'));
        foreach ($arr as $key => $value) {
            $oldmask = umask(0);
            chmod($this->logDir."/".$value, 0777);
            $var[] = "<a href='{$this->logURL}/{$value}' download>{$value}</a>";
            umask($oldmask);
        }
        if (isset($var)) {
            $this->logList = $var;
        } else {
            $this->logList = null;
        }
        return $this->logList;
    }

    private function setLastLog() {
        $files = glob($this->logDir."/*.log");
        if (!$files) {
            return array("No existen Logs disponibles");
        }
        $files = array_combine($files, array_map("filemtime", $files));
        arsort($files);
        $this->lastLog = key($files);
        if (isset($this->lastLog)) {
            $var = file_get_contents($this->lastLog);
        } else {
            $var = null;
        }
        $return = array(
            'log_file' => basename($this->lastLog),
            'log_weight' => $this->formatBytes($this->lastLog),
            'log_regs_lines' => count(file($this->lastLog)),
            'log_content' => $var
        );
        return $return;
    }

    private function readLogByFile($filename) {
        $var = file_get_contents($this->logDir."/".$filename);
        $return = array(
            'log_file' => $filename,
            'log_content' => $var
        );
        return $return;
    }

    private function setCountLogByFile($filename) {
        $fp = file($this->logDir."/".$filename);
        $return  = array(
            'log_file' => $filename,
            'lines_regs' => count($fp)
        );
        return $return;
    }

    private function setLastLogCountLines() {
        $lastfile = $this->setLastLog();
        $fp = file($this->logDir."/".$lastfile['log_file']);
        $return  = array(
            'log_file' => basename($lastfile['log_file']),
            'lines_regs' => count($fp)
        );
        return $return;
    }

    private function setLogDir() {
        return $this->logDir;
    }

    private function setLogCount() {
        $count = count($this->setLogList());
        $result = array('log_count' => $count);
        return $result;
    }

    /** Funciones de mantencion de directorio de logs**/
    // limpieza total de directorio

    private function delAllLogs() {
        if (! file_exists($this->logDir)) {
            // echo "error!: no existe directorio de logs";
            exit;
        }
        $files = glob($this->logDir.'/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        return true;
    }

    // mantiene solo los ultimos n dias de logs
    private function digestLogs() {
        if (! file_exists($this->logDir)) {
            // echo "error!: no existe directorio de logs";
            $this->setMakeLogDir();
        }
        $files = glob($this->logDir.'/*', GLOB_ONLYDIR);
        $deletions = array_slice($files, 0, count($files) - $this->confdays);
        foreach ($deletions as $to_delete) {
            array_map('unlink', glob("$to_delete"));
            //$deleted = rmdir($to_delete);
        }
        return true;
    }

    // Obtiene archivo de bloqueo
    public function getLockFile(){
        return json_encode($this->getValidateLockFile());
    }

    // obtiene directorio de log
    public function getLogDir() {
        return json_encode($this->setLogDir());
    }

    // obtiene conteo de logs en logdir definido
    public function getLogCount() {
        return json_encode($this->setLogCount());
    }

    // obtiene listado de logs en logdir
    public function getLogList() {
        return json_encode($this->setLogList());
    }

    // obtiene ultimo log modificado (al crearse con timestamp es tambien el ultimo creado)
    public function getLastLog() {
        return json_encode($this->setLastLog());
    }

    // obtiene conteo de lineas de ultimo log creado
    public function getLastLogCountLines() {
        return json_encode($this->setLastLogCountLines());
    }

    // obtiene log en base a parametro
    public function getLogByFile($filename) {
        return json_encode($this->readLogByFile($filename));
    }

    // obtiene conteo de lineas de log en base a parametro
    public function getCountLogByFile($filename) {
        return json_encode($this->setCountLogByFile($filename));
    }

    public function delLogsFromDir() {
        $this->delAllLogs();
    }

    public function delKeepOnlyLastLogs() {
        $this->digestLogs();
    }

    public function setLockStatus($status = true) {
        if ($status === true) {
            $this->setLockFile();
        } else {
            $this->delLockFile();
        }
    }

    public function getResume() {
        $result = array(
            'config' => $this->getValidateLockFile(),
            'log_dir' => $this->setLogDir(),
            'logs_count' => $this->setLogCount(),
            'logs_list' => $this->setLogList(),
            'last_log' => $this->setLastLog(),
        );
        return json_encode($result);
    }

    public function setnewconfig($days, $weight) {
        $this->setparamsconf($days, $weight);
    }
}
