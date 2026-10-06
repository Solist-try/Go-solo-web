<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Go Solo setup</title>
  <link rel="stylesheet" href="<?= htmlspecialchars(($GLOBALS['base_path'] ?? '') . '/assets/css/site.css', ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
  <main class="frame section">
    <h1>Go Solo needs a database first.</h1>
    <p>Copy <code>config.example.php</code> to <code>config.php</code> and add the MySQL name, user, and password from your hosting panel.</p>
    <p>In phpMyAdmin, import <code>sql/install.sql</code>. Then reload this page.</p>
    <p>The first steward signs in as <strong>marge@gosolo.co.network</strong>. The starting password is in INSTALL.md. Change it after you come in.</p>
  </main>
</body>
</html>
