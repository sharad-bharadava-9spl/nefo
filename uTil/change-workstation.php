<?php
	session_start();

	unset($_SESSION['workstation']);

	header('Location: ../main.php');