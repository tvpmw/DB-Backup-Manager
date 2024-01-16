<?php
// Function to backup the database
function backupDatabase($database)
{
    $servername = "127.0.0.1";
    $username = "root";
    $password = "";

    // Membuat koneksi ke database
    $conn = new mysqli($servername, $username, $password, $database);
    // Memeriksa koneksi
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

     // Tanggal dan waktu saat ini
     $timestamp = date("Y-m-d H:i:s");

     // Nama file backup
     $backupFileName = "backup_{$database}_" . date("Ymd_His") . ".sql";

    // Lokasi penyimpanan backup
    $backupPath = "G:\\My Drive\\db\\" . $backupFileName;
    $logPath = "C:\\xampp\\htdocs\\backup\\log\\logfile.txt";

    // Get list of tables in the database
    $result = $conn->query("SHOW TABLES");
    $tables = array();
    while ($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }

    // Open the backup file for writing
    $backupFile = fopen($backupPath, 'w');

    // Loop through each table and fetch SQL statements
    foreach ($tables as $table) {
        // Fetch CREATE TABLE statement
        $createTableResult = $conn->query("SHOW CREATE TABLE {$table}");
        $createTableRow = $createTableResult->fetch_assoc();
        $createTableStatement = $createTableRow['Create Table'];

        // Write CREATE TABLE statement to the backup file
        fwrite($backupFile, "-- Table structure for table `{$table}`\n");
        fwrite($backupFile, $createTableStatement . ";\n\n");

        // Fetch INSERT statements for data
        $selectDataResult = $conn->query("SELECT * FROM {$table}");
        while ($rowData = $selectDataResult->fetch_assoc()) {
            $insertStatement = "INSERT INTO `{$table}` VALUES (";
            $values = array_map([$conn, 'real_escape_string'], $rowData);
            $insertStatement .= "'" . implode("', '", $values) . "');";
            fwrite($backupFile, $insertStatement . "\n");
        }

        fwrite($backupFile, "\n");
    }

    // Close the backup file
    fclose($backupFile);

    // Catat ke dalam log
    $logMessage = "{$database} | {$timestamp} | {$backupFileName}\n";
    file_put_contents($logPath, $logMessage, FILE_APPEND);

    // Tutup koneksi database
    $conn->close();

    return $backupFileName;
}

// Check if running from the command line
if (php_sapi_name() === 'cli') {
    // Check if the required command line argument is provided
    if ($argc > 1) {
        // Parse the command line arguments
        parse_str(implode('&', array_slice($argv, 1)), $_GET);

        // Check if the 'database' parameter is set
        if (isset($_GET['database'])) {
            $database = $_GET['database'];
            backupDatabase($database);
            echo "Database backup completed for $database." . PHP_EOL;
        } else {
            echo "Parameter 'database' is missing." . PHP_EOL;
        }
    } else {
        echo "Command line arguments are missing." . PHP_EOL;
    }
} else {
    echo "This script is intended to be run from the command line." . PHP_EOL;
}
?>