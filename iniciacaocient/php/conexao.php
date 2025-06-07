<?php
function getConexao() {
    $host = 'localhost';
    $dbname = 'cad';
    $user = 'root';
    $pass = '';
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ];
    return new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass, $options);
}
?>