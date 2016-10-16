<?php

use Filehosting\Entity\File;

$raw = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
$filtered = [2, 6, 8, 12];

$normalized = filterArray($raw, $filtered);

function filterArray($raw, $filtered)
{
    $diff = count($raw) - count($filtered);
    for($i = 0; $i < $diff; $i++) {
        $filtered[] = true;
    }
    return $filtered;
}

var_dump($normalized);