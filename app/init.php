<?php
/* Пути к основным конфиг-файлам */
define('CONFIG_FILE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/config.json');
define('LOOKUP_FILE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/lookup.dat');

require_once __DIR__ . '/functions.php';

/**
 * Простенький автолоадер классов, не кидайтесь камнями
 *
 * @author u_mulder <m264695502@gmail.com>
 */
spl_autoload_register(function ($class_name) {
    $classes = [
        'RedmineApi' => 'redmineapi.php',
        'EvoApi' => 'evoapi.php',
        'BaseApi' => 'baseapi.php',
        'DbFactory' => 'dbf.php',
        'PDOWrapper' => 'dbf.php',
        'SqliteWrapper' => 'dbf.php',
    ];
    if (isset($classes[$class_name])) {
        require_once __DIR__ . '/' . $classes[$class_name];
    } else {
        throw new Exception('Path to class "' . $class_name . '" cannot be resolved');
    }
});
