<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = $_POST['database'];
    $time = $_POST['time'];

    // Validasi waktu format 24 jam (HH:mm)
    if (!preg_match('/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/', $time)) {
        echo "Format waktu tidak valid. Gunakan format 24 jam (HH:mm).";
        exit;
    }

    // Update schedule dalam file log
    $logFilePath = "log/schedule_log.txt";
    $logContent = file_get_contents($logFilePath);

    // Ubah format log jika database sudah ada
    if (preg_match("/^$database\s*\|\s*\d{2}:\d{2}\s*$/m", $logContent)) {
        $logContent = preg_replace("/^$database\s*\|\s*\d{2}:\d{2}\s*$/m", "$database | $time", $logContent);
        file_put_contents($logFilePath, $logContent);

        // Update juga tugas terjadwal di Task Scheduler
        $uniqueTaskName = "BackupJob_" . $database;
        // Letak Php dan Tempat dijalankan scriptnya
        $taskCommand = "C:\\xampp\\php\\php.exe C:\\xampp\\htdocs\\dbbackup\\backup_script_cj.php database=$database";
        $taskTrigger = "/SC DAILY /ST $time";

        // User credentials for administrator in windows / rdp
        $username = "username_rdp"; // Update this with the correct username
        $password = "password_rdp"; // Update this with the correct password

        // Delete the existing task
        $deleteTaskCommand = "schtasks /delete /tn \"$uniqueTaskName\" /f";
        exec($deleteTaskCommand, $deleteTaskOutput, $deleteTaskReturnCode);

        // Create a new task with the updated time
        $createTaskCommand = "schtasks /create /tn \"$uniqueTaskName\" /tr \"$taskCommand\" $taskTrigger /RU $username /RP $password";
        exec($createTaskCommand, $createTaskOutput, $createTaskReturnCode);

        if ($createTaskReturnCode === 0) {
            echo "Jadwal untuk database $database berhasil diperbarui menjadi $time dan di Task Scheduler.";
        } else {
            echo "Gagal memperbarui tugas terjadwal di Task Scheduler.";
            print_r($createTaskOutput);
        }
    } else {
        echo "Database $database tidak ditemukan dalam log schedule.";
    }
} else {
    echo "Kesalahan akses.";
}
?>
