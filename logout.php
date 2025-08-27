<?php
session_start();
session_unset();
session_destroy();

// MUDANÇA AQUI: Redireciona para a nova página de login
header("Location: index.php");
exit();
?>