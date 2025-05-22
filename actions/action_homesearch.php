<?php
session_start();

$search = trim($_GET['search'] ?? $_POST['search'] ?? '');

if ($search !== '') {
    header('Location: ../pages/gigs.php?search=' . urlencode($search));
} else {
    header('Location: ../pages/gigs.php');
}
exit();
?>