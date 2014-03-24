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
            $data['announce'] = $page->find('p.b-topic__announce', 0)->innertext;
            $data['author'] = $page->find('p.b-topic__content__author', 0)->innertext;
            $body = array();
            //
            foreach($page->find('div[itemprop=articleBody]',0)->find('p') as $p){
                if ($p->hasAttribute('class') && (strstr($p->getAttribute('class'), 'b-topic__announce') || strstr($p->getAttribute('class'), 'b-topic__content__author'))){

                } else{
                    $class = '';
                    if($p->hasAttribute('class')){
                        $class=$p->class;
                    }
                    $body[] = '<p'.($class?' class="'.$class.'"':'').'>'.$p->innertext.'</p>';
                }

            }
            $data['body'] = implode('', $body);
            $imglnk = $page->find('img.g-picture', 0)->src;
        }
        $data['date'] = $page->find('.g-date', 0)->innertext;
        if(!is_dir('img')){
            mkdir('img');
        }
        $extension = preg_replace('#^.+(\.[^\.]+)$#', '$1', $imglnk);
        $imgname = 'img/' . $data['_id'] . $extension;
        $data['img_src'] = 'http://lenta31.grybov.com/' . $imgname;
        $data['img_data'] = base64_encode(file_get_contents($imglnk));
        $data['ts'] = time();
        $this->db->insert('full_articles', $data);
        echo json_encode(array('status' => 'added'));
    }

    public function getAll() {
        $fields = array('_id' => true, 'img_src' => true, 'title' => true, 'description' => true, 'date' => true);
        echo json_encode($this->db->find('full_articles', array(), 0, 0, array('_id' => -1), false, $fields));
    }

    public function getArticle($id) {
        $article = $this->db->findOne('full_articles', array('_id' => (int) $id));
        if($article){
            echo json_encode($article);
        }
    }

    public function reparse(){
        $articles = $this->db->find('articles');
        foreach($articles as $k => $article){
            $this->add($article['link'], $k);
        }
    }
}

$Api = new Api();

if ($_GET['add']) {
    $Api->add($_GET['add'], $_GET['num']);
} elseif($_GET['get'] == 'all'){
    $Api->getAll();
} elseif($_GET['get'] > 0){
    $Api->getArticle(get('get'));
} elseif(isset($_GET['reparse'])){
    $Api->reparse();
}