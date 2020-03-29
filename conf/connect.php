<?php
/*
* @Author: Ricardo Órfão
* @Date:   2014-05-04 13:22:09
* @Last Modified by:   Ricardo Órfão
* @Last Modified time: 2014-08-18 13:05:40
*/

$host = 'localhost';
$dbname = 'projcont';
$dbUser = 'root';
//$dbPass = 'T4h6m3YuniurhCDfHGE9VYBQmQMszt8x';// no server
$dbPass = '';//no localhost
$charset = 'utf8';

try {
    $connection = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $dbUser, $dbPass);// uso do mysql
//    $connection = new PDO("mysql:host=$host;port=3307;dbname=$dbname;charset=$charset", $dbUser, $dbPass);//uso do mariadb
    $connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo $e->getMessage();
    file_put_contents('PDOErrors.txt', $e->getMessage(), FILE_APPEND);
}