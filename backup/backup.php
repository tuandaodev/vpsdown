<?php 

require_once('../config.php');
require_once('backup.class.php');
require_once('vendor/autoload.php');
require_once('../function.php');

/**
 * Define database parameters here
 */
define("BACKUP_DIR", 'backup-files'); // Comment this line to use same script's directory ('.')
define("TABLES", '*'); // Full backup
//define("TABLES", 'table1, table2, table3'); // Partial backup
define("CHARSET", 'utf8');
define("GZIP_BACKUP_FILE", false); // Set to false if you want plain SQL backup files (not gzipped)
define("DISABLE_FOREIGN_KEY_CHECKS", true); // Set to true if you are having foreign key constraint fails
define("BATCH_SIZE", 1000); // Batch size when selecting rows from database in order to not exhaust system memory
                            // Also number of rows per INSERT statement in backup file

/**
 * Instantiate Backup_Database and perform backup
 */

$client = getClient();
$service = new Google_Service_Drive($client);

// Report all errors
error_reporting(E_ALL);
// Set script max execution time
set_time_limit(900); // 15 minutes

if (php_sapi_name() != "cli") {
    echo '<div style="font-family: monospace;">';
}

$backupDatabase = new Backup_Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, CHARSET);
$result = $backupDatabase->backupTables(TABLES, BACKUP_DIR) ? 'OK' : 'KO';
$backupDatabase->obfPrint('Backup result: ' . $result, 1);

// Use $output variable for further processing, for example to send it by email
$output = $backupDatabase->getOutput();

if (php_sapi_name() != "cli") {
    echo '</div>';
}

$file_name = $backupDatabase->getOutputFileName();
$file_path = $backupDatabase->getOutputFilePath();

$log = $output;

if ($result == "OK") {
    
    $optParams = array(
        'q' => "mimeType='application/vnd.google-apps.folder' and name='" . BACKUP_DIR . "'",
        'fields' => 'files(id, name)'
      );
      $results = $service->files->listFiles($optParams);
      
      $folder_id = '';
      if (count($results->getFiles()) == 0) {
        $log .= "No files found.\n";
    } else {
        foreach ($results->getFiles() as $file) {
            if ($file->getName() == BACKUP_DIR) {
                $folder_id = $file->getId();
                break;
            }
        }
    }
      
    if (!$folder_id) {
        $fileMetadata = new Google_Service_Drive_DriveFile(array(
        'name' => BACKUP_DIR,
        'mimeType' => 'application/vnd.google-apps.folder'));
        $folder = $service->files->create($fileMetadata, array(
            'fields' => 'id'));

        $log .= "<br/>Folder ID: " . $folder->id . "\n";
        
        $folder_id = $folder->id;
    }
    
    $fileMetadata = new Google_Service_Drive_DriveFile(array(
    'name' => $file_name, 'parents' => array($folder_id)));
    $content = file_get_contents($file_path);

    $file = $service->files->create($fileMetadata, array(
        'data' => $content,
        'uploadType' => 'multipart',
        'fields' => 'id'));
    
    $log .= "<br/>Upload Completed. File ID: " . $file->id . "\n";
    
    if ($file->id) {
        unlink($file_path);
        $log .= "<br/>Upload Completed. Deleted on local: " . $file_path . "\n";
    }
} else {
    $log .= "Export fail, don't upload.";
}

echo $log;
write_logs("cron_backup.txt", $log);

