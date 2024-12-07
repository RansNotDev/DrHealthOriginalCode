<?php
session_start();
if (isset($_POST['active_tab'])) {
    $_SESSION['active_tab'] = $_POST['active_tab'];
}
?>
