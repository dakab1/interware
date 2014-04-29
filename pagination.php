<?php
$ItemsPerPage = 50;
extract ($_GET);

if (!isset($page)) {
    $page = 1;// default to page 1
} elseif($page<1) {
    $page=1;  	 
}

$limit = "LIMIT " . (($page-1) * $ItemsPerPage) . "," . $ItemsPerPage;

?>