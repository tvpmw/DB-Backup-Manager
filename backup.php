<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil nilai dari formulir
    $database = $_POST['database'];
    $backupTime = $_POST['time'];

    // Membuat nama tugas yang unik berdasarkan nama database
    $uniqueTaskName = "BackupJob_" . $database;

    // User credentials untuk rdp / server
    $username = "user_rdp"; // Update this with the correct username
    $password = "password_rdp"; // Update this with the correct password

    // Cek apakah cronjob sudah ada
    $existingTaskCommand = "schtasks /query /tn \"$uniqueTaskName\" /v /fo list";
    exec($existingTaskCommand, $existingTaskOutput, $existingTaskReturnCode);

    if ($existingTaskReturnCode === 0) {
        // Jika sudah ada, beri notifikasi
        echo "Cronjob untuk database $database sudah ada.";
    } else {
        // Jika belum ada, buat cronjob baru
        // Atur cronjob untuk menjalankan skrip backup pada waktu yang ditentukan
        $taskCommand = "C:\\xampp\\php\\php.exe C:\\xampp\\htdocs\\backup\\backup_script_cj.php database=$database";
        $taskArguments = "";
        $taskTrigger = "/SC DAILY /ST $backupTime";
        $taskUser = "/RU $username /RP $password"; // Set the username and password here

        // Use schtasks to create the scheduled task
        $command = "schtasks /create /tn \"$uniqueTaskName\" /tr \"$taskCommand $taskArguments\" $taskTrigger $taskUser";
        exec($command, $output, $returnCode);

        if ($returnCode === 0) {
            // Simpan informasi jadwal ke file log
            $logFilePath = "log/schedule_log.txt"; // Sesuaikan dengan lokasi file log
            $logEntry = "$database | $backupTime\n";
            file_put_contents($logFilePath, $logEntry, FILE_APPEND);

            echo "Penjadwalan untuk backup otomatis database : $database di waktu : $backupTime telah dibuat dengan nama tugas : $uniqueTaskName.";
        } else {
            echo "Penjadwalan gagal dibuat.";
            print_r($output);
        }
    }
} else {
    echo "Kesalahan akses.";
}
?>