<?php

$user = isset($_SESSION['user']) ?? null;  // 0 means no role or default role

?>

<?php if ($user): ?>
    <a href="logout.php">
    Αποσυνδέσου
    </a>


<?php else: ?>
    <a href="login.php">
        Σύνδεση
    </a>
<?php endif; ?>