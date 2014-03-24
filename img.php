<?php
/**
 * Created by PhpStorm.
 * User: bozh
 * Date: 3/17/14
 * Time: 5:11 PM
 */
include_once 'DB.php';
include_once 'fn.php';

$DB = new DB('lenta31');
$article = $DB->findOne('full_articles', array('_id' => (int) get('_id')));
header("Content-type: image/jpeg");
print base64_decode($article['img-data']);