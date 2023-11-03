<?php

/* 
------------------
Language: English
------------------
*/

$lang = array();

$selectStrings = "SELECT string_slug, string_en FROM language_strings order by id ASC";

try
{
	$results = $pdo2->prepare("$selectStrings");
	$results->execute();
}
catch (PDOException $e)
{
		$error = 'Error fetching user: ' . $e->getMessage();
		echo $error;
		exit();
}


while($row = $results->fetch()){
	$string_slug = $row['string_slug'];
	$string_en = $row['string_en'];
	$lang[$string_slug] = $string_en;
}
