<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = $_POST['database'];

    // Membuat nama tugas yang unik berdasarkan nama database
    $uniqueTaskName = "BackupJob_" . $database;

    // Hapus jadwal menggunakan schtasks
    $deleteCommand = "schtasks /delete /tn \"$uniqueTaskName\" /f";
    exec($deleteCommand, $deleteOutput, $deleteReturnCode);

    if ($deleteReturnCode === 0) {
        // Hapus entri dari file log
        $logFilePath = "log/schedule_log.txt";
        $logContent = file_get_contents($logFilePath);

        // Hapus baris yang sesuai dengan database yang dihapus
        $logContent = preg_replace("/^$database\s*\|\s*\d{2}:\d{2}\s*$/m", "", $logContent);

        file_put_contents($logFilePath, $logContent);
        echo "Jadwal untuk database $database berhasil dihapus.";
    } else {
        echo "Gagal menghapus jadwal.";
        print_r($deleteOutput);
    }
} else {
    echo "Kesalahan akses.";
}
?>
