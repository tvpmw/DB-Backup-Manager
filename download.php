<?php
if (isset($_GET['file'])) {
    $file = $_GET['file'];

    // Output the file as a download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . basename($file));
    header('Content-Length: ' . filesize($file));
    readfile($file);

    // // Delete the file after download (optional)
    // unlink($file);

    exit;
}
?>
