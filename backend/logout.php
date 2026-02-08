<?php
session_start();
session_unset();
session_destroy();
header("Location: ../Panel.html"); // Redirigir al login
exit;