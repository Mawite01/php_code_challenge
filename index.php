<?php

declare(strict_types=1);

include 'src/FinalResult.php';

$result = new FinalResult('tests/support/data_sample.csv');

print_r($result->results());

?>


