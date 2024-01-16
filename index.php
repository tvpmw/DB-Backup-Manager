<?php
session_start();

// Check if the user is authenticated
if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
    // Redirect to the login page if not authenticated
    header("Location: login.php");
    exit;
}
function getAutomaticBackupSchedule() {
    // Replace this with the actual path to your log file
    $logFilePath = "log/schedule_log.txt";
    
    $scheduleEntries = array();

    // Read the log file content
    if (file_exists($logFilePath)) {
        $logContent = file_get_contents($logFilePath);

        // Explode the log content into lines
        $logLines = explode("\n", $logContent);

        // Process each line
        foreach ($logLines as $logLine) {
            // Explode the line into database and backup time
            $logData = explode("|", $logLine);
            
            // Check if indices exist before accessing them
            $database = isset($logData[0]) ? trim($logData[0]) : "";
            $backupTime = isset($logData[1]) ? trim($logData[1]) : "";

            // Add entry to the schedule
            if (!empty($database) && !empty($backupTime)) {
                $scheduleEntries[] = array('database' => $database, 'backupTime' => $backupTime);
            }
        }
    }

    return $scheduleEntries;
}
?>
<!DOCTYPE HTML>
<html>
    <head>
        <title>Backup Database v2</title>
        <meta charset="utf-8" />
        <meta owner="thomas vincent">
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
        <link rel="stylesheet" href="assets/css/main.css" />
        <noscript><link rel="stylesheet" href="assets/css/noscript.css" /></noscript>
        <style>
            /* Add this style for the modal overlay */
.modal {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 1; /* Sit on top */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    overflow: auto; /* Enable scroll if needed */
    background-color: rgba(0, 0, 0, 0.4); /* Black background with opacity */
}

/* Style for the modal content */
.modal-content {
    background-color: #fefefe;
    margin: 10% auto; /* 10% from the top and centered */
    padding: 20px;
    border: 1px solid #888;
    width: 80%; /* Could be more or less, depending on screen size */
}

/* Close button style */
.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}
</style>
<script>
           function typeWriterEffect(element, text, speed, callback) {
    let i = 0;
    const typewriter = setInterval(function () {
        if (i < text.length) {
            element.innerHTML += text.charAt(i);
            i++;
        } else {
            clearInterval(typewriter);
            if (callback) {
                callback(); // Call the callback function after typing is complete
            }
        }
    }, speed);
}
function backupAutomatic() {
    // Disable the button to prevent multiple clicks
    var backupAutoButton = document.getElementById('backupAutoButton');
    backupAutoButton.disabled = true;

    // Display the loading indicator with typing effect
    var loadingIndicatorAuto = document.getElementById('loadingIndicatorAuto');
    loadingIndicatorAuto.style.display = 'inline-block';

    // Typing effect for the loading message
    typeWriterEffect(loadingIndicatorAuto, "Proses penjadwalan backup otomatis sedang dijalankan ...", 50, function () {
        // Send an AJAX request to backup.php
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'backup.php', true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4) {
                // Re-enable the button and hide the loading indicator
                backupAutoButton.disabled = false;
                loadingIndicatorAuto.style.display = 'none';

                if (xhr.status == 200) {
                    // Update the loading message to indicate the backup is complete
                    var backupproses = document.getElementById(`backupproses`);
                    backupproses.innerHTML = xhr.responseText;
                    location.reload();
                } else {
                    // Handle errors here
                    console.error('Error in backup.php: ' + xhr.statusText);
                }
            }
        };

        // You can send additional data if needed
        var data = 'database=' + encodeURIComponent(document.getElementById('database').value) +
                   '&time=' + encodeURIComponent(document.getElementById('time').value);
        xhr.send(data);
    });
}
function backupDatabase(databaseName) {
    // Disable the button to prevent multiple clicks
    var backupButton = document.getElementById(`backupButton_${databaseName}`);
    backupButton.disabled = true;

    // Display the loading indicator with typing effect
    var loadingIndicator = document.getElementById(`loadingIndicator_${databaseName}`);
    loadingIndicator.style.display = 'inline-block';

    // Typing effect for the loading message
    typeWriterEffect(loadingIndicator, "Proses backup sedang berlangsung mohon ditunggu ...", 50, function () {
        // Send an AJAX request to backup_script.php
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "backup_script.php", true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.responseType = 'blob';  // Set the response type to blob

        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4) {
                // Re-enable the button and hide the loading indicator
                backupButton.disabled = false;
                loadingIndicator.style.display = 'none';

                if (xhr.status == 200) {
                    // Create a Blob from the response
                    var blob = new Blob([xhr.response], { type: 'application/octet-stream' });

                    // Create a link element to trigger the download
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = `backup_${databaseName}_${new Date().toISOString().replace(/[:.]/g, "_")}.sql`;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                    // Update the loading message to indicate the backup is complete
                    var backupStatus = document.getElementById(`backupStatus_${databaseName}`);
                    backupStatus.innerHTML = "Proses backup selesai, mohon tunggu sebentar akan download otomatis ....";
                } else {
                    // Handle errors here
                    console.error("Error in backup: " + xhr.statusText);
                }
            }
        };
        xhr.send("database=" + databaseName);
    });
}

document.addEventListener("DOMContentLoaded", function () {
    const emptyMessage = document.getElementById("emptyMessage");
    if (emptyMessage) {
        typeWriterEffect(emptyMessage, "Data Riwayat Backup Masih Kosong ...", 50);
    }
});
document.addEventListener("DOMContentLoaded", function () {
    const emptyMessage1 = document.getElementById("emptyMessage1");
    if (emptyMessage1) {
        typeWriterEffect(emptyMessage1, "Data Riwayat Backup Masih Kosong ...", 50);
    }
});
document.addEventListener("DOMContentLoaded", function () {
    const emptyMessage2 = document.getElementById("emptyMessage2");
    if (emptyMessage2) {
        typeWriterEffect(emptyMessage2, "Data Riwayat Backup Masih Kosong ...", 50);
    }
});

        </script>
    </head>
    <body class="is-preload">

        <!-- Wrapper-->
        <div id="wrapper">

            <!-- Nav -->
            <nav id="nav">
                <a href="#" class="icon solid fa-database"><span>Backup</span></a>
                <a href="#work" class="icon solid fa-history"><span>History</span></a>
                <a href="#auto" class="icon solid fa-clock"><span>Automatic</span></a>
                <a href="logout.php" class="icon solid fa-lock"><span>Log Out</span></a>
            </nav>

            <!-- Main -->
            <div id="main">
                <!-- Me -->
                <article id="home" class="panel intro">
                    <header>
                        <p>
                            <?php
                            // Menampilkan daftar database
                            $servername = "127.0.0.1";
                            $username = "root";
                            $password = "30287y";

                            // Membuat koneksi
                            $conn = new mysqli($servername, $username, $password);

                            // Memeriksa koneksi
                            if ($conn->connect_error) {
                                die("Koneksi gagal: " . $conn->connect_error);
                            }

                            // Menjalankan query untuk menampilkan database
                            $query = "SHOW DATABASES";
                            $result = $conn->query($query);

                            // Memeriksa hasil query
                            if ($result) {
                                echo "<h2><center>- Backup Database -</center></h2>";
                                echo "<center><div class='database-columns'>";
                                echo "<span style='font-weight: bold; color: black; font-size: medium'>[ #Information# ]<br>Tombol backup dinonaktifkan karena update fitur backup otomatis</span>";
								
								// Function to get the last backup time from the log file
							function getLastBackupTimeFromLog($databaseName) {
								// Replace this with the actual path to your log file
								$logFilePath = "log/logfile.txt";

								// Read the log file content
								if (file_exists($logFilePath)) {
									$logContent = file_get_contents($logFilePath);

									// Create a pattern to match the last backup entry for the given database
									$pattern = "/{$databaseName} \| (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}|\d{4}-\d{2}-\d{2}_\d{2}:\d{2}:\d{2}) \|/";

									// Match all occurrences of the pattern
									preg_match_all($pattern, $logContent, $matches);

									// Check if there are any matches
									if (isset($matches[1]) && is_array($matches[1]) && count($matches[1]) > 0) {
										// Extract the last match (last backup time)
										$lastBackupTime = end($matches[1]);
									} else {
										echo "Error: No matching backup entry found for database {$databaseName}.";
										// Return a default value
										$lastBackupTime = "Not available";
									}

									return $lastBackupTime;
								} else {
									echo "Error: Riwayat Backup belum ada ...";
									// Return a default value
									return "Tidak tersedia";
								}
							}								
                                // Menampilkan nama-nama database dengan tombol backup
                                while ($row = $result->fetch_assoc()) {
                                    $databaseName = $row['Database'];

                                    // Check if the database name is 'database1' or 'database2' or delete it so list all database
                                    if ($databaseName == 'database' || $databaseName == 'database2') {
                                        $lastBackupTime = getLastBackupTimeFromLog($databaseName);
										echo "<div class='database-column'>";
										echo "<span style='font-weight: bold; color: black; font-size: medium'>{$databaseName} (Backup Terakhir : {$lastBackupTime}) </span><div>";
                                        // Call the JavaScript function on button click
                                        echo "<button id='backupButton_{$databaseName}' disabled class='btn' style='background-color: #d3d3d3; color: #808080;' onclick='backupDatabase(\"{$databaseName}\")'>Backup Now →</button></div>";
                                        // Create a div to display the backup status
                                        echo "<div id='backupStatus_{$databaseName}' style='font-weight: bold; color: black; font-size: small;'></div>";
                                        // Create a loading indicator
                                        echo "<div id='loadingIndicator_{$databaseName}' style='font-weight: bold; color: black; display: none; font-size: small;'>Loading... </div>";
										echo "------------------------";
                                        echo "</div>";
                                    }
                                }
                                echo "</div></center>";

                                // Menutup hasil query
                                $result->close();
                            } else {
                                echo "Error: " . $conn->error;
                            }

                            // Menutup koneksi
                            $conn->close();
                            ?>
                        </p>
                    </header>
                    <a href="#work" class="jumplink pic">
                        <span class="arrow icon solid fa-chevron-right"><span>See my work</span></span>
                        <img src="assets/css/images/backup.jpg" alt="" />
                    </a>
                </article>

                <!-- Work -->
                <article id="work" class="panel">
                    <header>
                        <center><h2>-History Backup -</h2></center>
                    </header>
                    <p>
					<?php
// Waktu backup terakhir
$lastBackupLog = "log/logfile.txt"; // Sesuaikan dengan lokasi file log
if (file_exists($lastBackupLog)) {
    $logLines = file($lastBackupLog, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if (empty($logLines)) {
        echo "<p id='emptyMessage1'></p>";
    } else {
        // Number of rows per page
        $rowsPerPage = 10;

        // Current page number, default to 1 if not set
        $currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;

        // Total number of pages
        $totalPages = ceil(count($logLines) / $rowsPerPage);

        // Calculate the starting index for the current page
        $startIndex = ($currentPage - 1) * $rowsPerPage;

        // Get the rows for the current page
        $rowsForPage = array_slice($logLines, $startIndex, $rowsPerPage);

        echo "<table style='border-collapse: collapse; width: 100%; border: 1px solid black; color: black;' border='1'>
                <tr>
                    <th style='border: 1px solid black; text-align: left; padding: 8px;'>No</th>
                    <th style='border: 1px solid black; text-align: left; padding: 8px;'>Database</th>
                    <th style='border: 1px solid black; text-align: left; padding: 8px;'>Waktu Backup</th>
                    <th style='border: 1px solid black; text-align: left; padding: 8px;'>File Backup</th>
                </tr>";

                $counter = ($currentPage - 1) * $rowsPerPage + 1;

                foreach ($rowsForPage as $logLine) {
                    // Parsing data dari baris log
                    $logData = explode("|", $logLine);
                
                    // Check if indices exist before accessing them
                    $database = isset($logData[0]) ? trim($logData[0]) : "";
                    $timestamp = isset($logData[1]) ? trim($logData[1]) : "";
                    $backupFileName = isset($logData[2]) ? trim($logData[2]) : "";
                
                    // Menampilkan data dalam tabel
                    echo "<tr>
                            <td style='border: 1px solid black; text-align: left; padding: 8px; font-size: medium; color: black !important;'><span style='color: black;'>{$counter}</span></td>
                            <td style='border: 1px solid black; text-align: left; padding: 8px; font-size: medium; color: black !important;'>{$database}</td>
                            <td style='border: 1px solid black; text-align: left; padding: 8px; font-size: medium; color: black !important;'>{$timestamp}</td>
                            <td style='border: 1px solid black; text-align: left; padding: 8px; font-size: medium;'><a href='db/{$backupFileName}' style='color: blue !important;' download>→ Click To Download ←</a></td>
                        </tr>";
                
                    $counter++;
                }
                
                echo "</table>";
                
                // Display pagination links
                echo "<div style='text-align: center; margin-top: 20px;'>";
                for ($i = 1; $i <= $totalPages; $i++) {
                    $activeClass = ($i == $currentPage) ? 'active' : '';
                    $url = ($i == 1) ? "index.php#work" : "index.php?page={$i}#work";
                    echo "<a class='{$activeClass}' href='{$url}'> {$i}</a>";
                }
                echo "</div>";                
    }
} else {
    echo "<p>Belum ada backup yang dilakukan.</p>";
}
?>
                    </p>
                </article>
               
                     <!-- Auto -->
<article id="auto" class="panel">
    <header>
        <h2>Automatic System Backup</h2>
        <small>*Schedule Backup Cronjob tiap Database hanya bisa 1x</small>
    </header>
    <form>
    <div>
        <div class="row">
            <div class="col-6 col-12-medium">
                <label for="database">Pilih Database:</label>
                <select name="database" id="database">
                    <!-- Gantilah opsi-opsi ini dengan daftar nama database yang tersedia -->
                    <option value="database1">Database database1</option>
                    <option value="database2">Database database2</option>
                    <!-- Tambahkan opsi lain sesuai kebutuhan -->
                </select>
            </div>
            <div class="col-6 col-12-medium">
                <label for="time">Select Time (24-hour format):</label>
                <input type="text" name="time" id="time" required>
            </div>
                <div>
                    <input type="submit" id="backupAutoButton" class="btn" onclick="backupAutomatic()" value="Atur Backup Otomatis">
                </div>
                <!-- Loading indicator for automatic backup -->
                <div id="loadingIndicatorAuto" style="display: none; font-weight: bold; color: black; font-size: small;">Loading... </div>
                <div id='backupproses' style='font-weight: bold; color: black; font-size: small;'></div>
            </div></div
                    </form>
                    <hr>
                      <!-- Tambahkan kode ini untuk menampilkan daftar jadwal backup otomatis -->
                      <div id="automaticBackupSchedule" class="panel">
    <header>
        <h3>Daftar Otomatisasi :</h3>
    </header>
    <ul>
    <?php
$automaticBackupSchedule = getAutomaticBackupSchedule();

if (!empty($automaticBackupSchedule)) {
    // Display the schedule list
    foreach ($automaticBackupSchedule as $scheduleEntry) {
        echo "<li><strong>Database:</strong> " . $scheduleEntry['database'] . "<br>";
        echo "<strong>Backup Time:</strong> " . $scheduleEntry['backupTime'] . "<br>";

        // Display action buttons only if the schedule is not empty
        if (!empty($scheduleEntry['database'])) {
            echo "<strong>Action : </strong>";
            echo "<button onclick='showEditModal(\"{$scheduleEntry['database']}\", \"{$scheduleEntry['backupTime']}\')'>Ubah Jadwal</button> | ";
            echo "<button onclick='confirmDeleteSchedule(\"{$scheduleEntry['database']}\")'>Hapus Jadwal</button> ";
        }

        echo "</li>";
    }

    // Display the modal
    echo '<!-- Modal untuk formulir edit/delete -->
    <div id="editDeleteModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Ubah Jadwal ' . $scheduleEntry['database'] . '</h2>
            <form id="editDeleteForm">
                <label for="editTime">Edit Backup Time (24-hour format):</label>
                <input type="text" name="editTime" id="editTime" required>
                <input type="hidden" name="editDatabase" id="editDatabase">
                <button type="button" onclick="editSchedule()">Ubah Jadwal</button>
                <!-- <button type="button" onclick="confirmDeleteSchedule()">Delete</button> -->
            </form>
        </div>
    </div>';
} else {
    // Hide the entire section when the schedule is empty
    echo 'Jadwal saat ini belum ada / kosong / belum tersedia ...';
}
?>
                </article>
            </div>
            <!-- Footer -->
            <div id="footer">
                <ul class="copyright">
                    <li>&copy; 2023.</li><li>♥ Coded: <a href="https://www.facebook.com/ThomsVnct/">Thomas Vincent</a></li>
                </ul>
            </div>
        </div>

        <!-- Scripts -->
        <!-- Include flatpickr library -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
function showEditModal(database, backupTime) {
    document.getElementById('editTime').value = backupTime;
    document.getElementById('editDatabase').value = database;
    document.getElementById('editDeleteModal').style.display = 'block';
    document.querySelector('.modal').style.display = 'block'; // Display the overlay
}

function closeModal() {
    document.getElementById('editDeleteModal').style.display = 'none';
    document.querySelector('.modal').style.display = 'none'; // Hide the overlay
}

// Fungsi untuk mengirim permintaan edit schedule ke server
function editSchedule() {
    var editDatabase = document.getElementById('editDatabase').value;
    var editTime = document.getElementById('editTime').value;

    // Kirim permintaan edit schedule ke server menggunakan AJAX
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'edit_schedule.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4) {
            // Handle respons dari server
            console.log(xhr.responseText);
            // Tutup modal setelah berhasil diedit
            closeModal();
            
            // Reload the page after closing the modal
            location.reload();
        }
    };
    xhr.send('database=' + encodeURIComponent(editDatabase) + '&time=' + encodeURIComponent(editTime));
}

// Fungsi untuk mengonfirmasi penghapusan schedule
function confirmDeleteSchedule(database) {
    var confirmDelete = confirm("Apakah Anda yakin ingin menghapus schedule ini?");
    if (confirmDelete) {
        deleteSchedule(database);
    }
}

// Fungsi untuk mengirim permintaan delete schedule ke server
function deleteSchedule(database) {
    // Kirim permintaan delete schedule ke server menggunakan AJAX
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'delete_schedule.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            // Handle respons dari server (jika diperlukan)
            console.log(xhr.responseText);
            // Tutup modal setelah berhasil dihapus
            closeModal();
            // Reload the page after closing the modal
            location.reload();
        }
    };
    xhr.send('database=' + encodeURIComponent(database));
}

</script>
<script>
flatpickr("#time", {
    enableTime: true,
    noCalendar: true,
    dateFormat: "H:i",
    time_24hr: true,
});
</script>
        <script src="assets/js/jquery.min.js"></script>
        <script src="assets/js/browser.min.js"></script>
        <script src="assets/js/breakpoints.min.js"></script>
        <script src="assets/js/util.js"></script>
        <script src="assets/js/main.js"></script>
        
    </body>
</html>
