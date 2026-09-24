<?php
require_once '../auth/auth_check.php';
require_once '../config/database.php';

if (!has_role('superadmin')) {
    header('Location: ../index.php?unauthorized=1');
    exit();
}

$backupDirectory = __DIR__;
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mysqldumpPath = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
    $timestamp = date('Y-m-d_His');
    $backupFilename = 'cams_backup_' . $timestamp . '.sql';
    $backupPath = $backupDirectory . DIRECTORY_SEPARATOR . $backupFilename;

    if (!is_file($mysqldumpPath)) {
        $message = 'mysqldump.exe was not found at ' . $mysqldumpPath . '.';
        $messageType = 'error';
    } else {
        $command = escapeshellarg($mysqldumpPath)
            . ' --host=' . escapeshellarg(CAMS_DB_HOST)
            . ' --user=' . escapeshellarg(CAMS_DB_USERNAME)
            . ' --single-transaction --routines --events --triggers'
            . ' --result-file=' . escapeshellarg($backupPath)
            . ' ' . escapeshellarg(CAMS_DB_NAME)
            . ' 2>&1';

        $output = [];
        $exitCode = 0;
        exec($command, $output, $exitCode);

        if ($exitCode === 0 && is_file($backupPath) && filesize($backupPath) > 0) {
            $message = 'Backup created successfully: ' . $backupFilename;
            $messageType = 'success';
        } else {
            if (is_file($backupPath)) {
                unlink($backupPath);
            }
            $message = 'Backup failed. ' . htmlspecialchars(implode(' ', $output));
            $messageType = 'error';
        }
    }
}

$backups = glob($backupDirectory . DIRECTORY_SEPARATOR . 'cams_backup_*.sql');
usort($backups, SORT_STRING);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Backup</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; color: #222; }
        main { max-width: 700px; margin: 0 auto; }
        .message { padding: 12px; margin: 20px 0; border-radius: 4px; }
        .success { background: #e8f5e9; color: #1b5e20; }
        .error { background: #ffebee; color: #b71c1c; }
        button { padding: 10px 16px; cursor: pointer; }
        li { margin: 8px 0; }
        .note { color: #666; }
    </style>
</head>
<body>
    <main>
        <h1>Database Backup</h1>
        <p>Database: <strong><?php echo htmlspecialchars(CAMS_DB_NAME); ?></strong></p>
        <p class="note">Only superadmin users can create backups. SQL files are saved in this folder.</p>

        <?php if ($message !== ''): ?>
            <div class="message <?php echo htmlspecialchars($messageType); ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <button type="submit">Create SQL Backup</button>
        </form>

        <h2>Existing Backups</h2>
        <?php if (empty($backups)): ?>
            <p>No backups have been created yet.</p>
        <?php else: ?>
            <ul>
                <?php foreach (array_reverse($backups) as $backup): ?>
                    <li><?php echo htmlspecialchars(basename($backup)); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </main>
</body>
</html>
