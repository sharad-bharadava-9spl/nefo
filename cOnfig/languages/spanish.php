<?php

/* 
------------------
Language: English
------------------
*/

$lang = array();

$selectStrings = "SELECT string_slug, string_es FROM language_strings order by id ASC";

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
	$string_es = $row['string_es'];
	$lang[$string_slug] = $string_es;
}
