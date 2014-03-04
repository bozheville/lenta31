<?php
include_once 'DB.php';
include_once 'simple_html_dom.php';
include_once 'fn.php';
$_POST = json_decode(file_get_contents("php://input"), true);

class Api
{
    private $db = null;

    public function __construct()
    {
        $this->db = new DB('lenta31');
    }

    public function add($link, $num)
    {
        $data = array();
        $data['_id'] = (int) $num;
        $data['link'] = $link;
        $page = str_get_html(file_get_contents($link));
        $data['title'] = $page->find('h1.b-topic__title', 0)->innertext;
        if(!$data['title']){
            $data['title'] = $page->find('div.b-topic__title', 0)->find('h1.title',0)->innertext;
            $data['description'] = $page->find('div.b-topic__title', 0)->find('h2.rightcol',0)->innertext;
            $modifier = 'data-image';
            $imglnk = json_decode($page->find('img.item', 0)->$modifier, true);
            $imglnk = $imglnk['url'];
        } else{
            $data['description'] = $page->find('h2.b-topic__rightcol', 0)->innertext;
            $imglnk = $page->find('img.g-picture', 0)->src;
        }
        $data['date'] = $page->find('.g-date', 0)->innertext;

        if(!is_dir('img')){
            mkdir('img');
        }
        $extension = preg_replace('#^.+(\.[^\.]+)$#', '$1', $imglnk);
        $imgname = 'img/' . $data['_id'] . $extension;
        file_put_contents($imgname, file_get_contents($imglnk));
        $data['img'] = 'http://lenta31.grybov.com/' . $imgname;
        $data['ts'] = time();
        $this->db->insert('articles', $data);
        echo json_encode($data);
    }

    public function getAll()
    {
        echo json_encode($this->db->find('articles', array(), 0, 0, array('_id' => -1), false));
    }
}

$Api = new Api();

if ($_GET['add']) {
    $Api->add($_GET['add'], $_GET['num']);
} elseif($_GET['get'] == 'all'){
    $Api->getAll();
}