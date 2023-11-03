<?php

if (class_exists('PDO')) {
	echo "PDO class exists.<br />";
} else {
	echo "PDO class does NOT exist.<br />";
}
	
if (!defined('PDO::ATTR_DRIVER_NAME')) {
	echo 'PDO unavailable<br />';
} else if (defined('PDO::ATTR_DRIVER_NAME')) {
		echo 'PDO available<br />';
}