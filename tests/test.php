<?php 
require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload
use MutasiBank\Mandiri;
$mb = new Mandiri('username','password');//username&password
$html = $mb->getMutasiRekening('2019-01-01','2019-01-10');//date_start,date_end
print_r($html);
