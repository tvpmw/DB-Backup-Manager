<?php
if (isset($_GET['database'])) {
    $servername = "127.0.0.1";
    $username = "root";
    $password = "";
    $database = $_GET['database'];

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

    // Lokasi penyimpanan backup (saya menggunakan Google Drive FStream dan untuk Drive Letternya saya G:)
    $backupPath = "G:/My Drive/db/" . $backupFileName;

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
    file_put_contents("log/logfile.txt", $logMessage . PHP_EOL, FILE_APPEND);

    // Output the file as a download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . basename($backupFileName));
    header('Content-Length: ' . filesize($backupPath));
    readfile($backupPath);

    // Tutup koneksi database
    $conn->close();
} elseif (isset($_POST['database'])) {
    $servername = "127.0.0.1";
    $username = "root";
    $password = "";
    $database = $_POST['database'];

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
    $backupPath = "G:/My Drive/db/" . $backupFileName;

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
    $logMessage = "{$database} | {$timestamp} | {$backupFileName}";
    file_put_contents("log/logfile.txt", $logMessage . PHP_EOL, FILE_APPEND);

    // Output the file as a download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . basename($backupFileName));
    header('Content-Length: ' . filesize($backupPath));
    readfile($backupPath);

    // Tutup koneksi database
    $conn->close();
} else {
    echo "Parameter database tidak ditemukan.";
}
?>
