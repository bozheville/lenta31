<?php

function cl($var = "") {
    print_r($var);
    if (!is_array($var)) {
        print_r("\n");
    }
}

function cld($var = "") {
    cl($var);
    die();
}

function p($var = "") {
    print_r("<pre>");
    print_r($var);
    print_r("</pre>");
}

function pd($var = "") {
    p($var);
    die();
}

function get($key) {
    return $_GET[$key];
}

function post($key) {
    return $_POST[$key];
}

function cookie($key, $val = "") {
    if (empty($val)) {
        return $_COOKIE[$key];
    }
    setcookie($key, $val);
}

function redirect($url, $code = '') {
    switch ($code) {
        case 301:
            header("HTTP/1.1 301 Moved Permanently");
            break;
        case 302:
            header("HTTP/1.1 302 Moved Temporarily");
            break;
    }
    preg_match_all('#[\?&]add_to_fav=[a-z]+-([0-9]+)-[0-9]*#ims', $url, $matches);
    if ($matches[1][0]) {
        my_setcookie('add_to_fav', $matches[1][0], 600);
        $url = preg_replace('#[\?&]add_to_fav=[a-z]+-[0-9]+-[0-9]*#ims', '', $url);
    }
    header("location: $url");
    die();
}

/**
 * Генератор случайной строки для пароля или соли.<br/>
 * Сначала выбирается алфавит, из которого будет составлена строка.<br />
 * Затем заданное количество раз добавляется к случайной строке случайные символы из алфавита<br />
 * Полученная строка перемешивается и обрезается до нужной длины<br />
 * Дальше идет валидация. Если валидация не была пройдена, функция вызывается рекурсивно до получения валидной строки.
 * @param int $base_len Длина случайной строки
 * @param int $rounds Количество раундов (для добавления дублирующихся символов)
 * @param string $alphatype Тип выборки алфавита. "luns" = lower+upper+numeric+specials; lu = lower+upper;
 * @return string Возвращает случайную строку, которая обязательно содержит хотя бы один символ каждого установленного в $alphatype типа
 */
function getRandomString($base_len = 20, $rounds = 3, $alphatype = "luns") {
    $true_rounds = $rounds;
    $alphabet = array();
    if (strstr($alphatype, "l")) {
        $alphabet["lower"] = "qwertyuiopasdfghjklzxcvbnm";
    }
    if (strstr($alphatype, "u")) {
        $alphabet["upper"] = "QWERTYUIOPASDFGHJKLZXCVBNM";
    }
    if (strstr($alphatype, "n")) {
        $alphabet["numeric"] = "1234567890";
    }
    if (strstr($alphatype, "s")) {
        $alphabet["specials"] = ",.?;:[]{}!@#$%^&*()_+-=";
    }
    $crypt = "";
    while (--$rounds > 0) {
        $substr = str_split(implode("", $alphabet));
        shuffle($substr);
        $substr = implode("", $substr);
        $substr = substr($substr, rand(0, strlen($substr) - $base_len), $base_len);
        $crypt .= $substr;
    }
    $crypt = str_split($crypt);
    shuffle($crypt);
    $crypt = implode("", $crypt);
    $crypt = substr($crypt, rand(0, strlen($crypt) - $base_len), $base_len);
    if ($base_len >= count(array_keys($alphabet))) {
        $candidate = str_split($crypt);
        foreach ($alphabet as $key => $value) {
            $has[$key] = false;
        }
        foreach ($candidate as $value) {
            foreach ($has as $key => $val) {
                if (strstr($alphabet[$key], $value)) {
                    $has[$key] = true;
                    break;
                }
            }
        }
        foreach ($has as $val) {
            if (empty($val)) {
                $crypt = getRandomString($base_len,$true_rounds, $alphatype);
                break;
            }
        }
    }
    return $crypt;
}

function str_split_utf8($str) {
    $split = 1;
    $array = array();
    for ($i=0; $i < strlen($str); ){
        $value = ord($str[$i]);
        if($value > 127){
            if ($value >= 192 && $value <= 223)      $split = 2;
            elseif ($value >= 224 && $value <= 239)  $split = 3;
            elseif ($value >= 240 && $value <= 247)  $split = 4;
        } else $split = 1;
        $key = NULL;
        for ( $j = 0; $j < $split; $j++, $i++ ) $key .= $str[$i];
        array_push( $array, $key );
    }
    return $array;
}

function transliterate($string, $revert = false) {
    $converter = array(

        'а' => 'a', 'б' => 'b', 'в' => 'v',

        'г' => 'g', 'д' => 'd', 'е' => 'e',

        'ё' => 'e', 'ж' => 'zh', 'з' => 'z',

        'и' => 'i', 'й' => 'y', 'к' => 'k',

        'л' => 'l', 'м' => 'm', 'н' => 'n',

        'о' => 'o', 'п' => 'p', 'р' => 'r',

        'с' => 's', 'т' => 't', 'у' => 'u',

        'ф' => 'f', 'х' => 'h', 'ц' => 'c',

        'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',

        'ь' => '\'', 'ы' => 'y', 'ъ' => '\'',

        'э' => 'e', 'ю' => 'yu', 'я' => 'ya',


        'А' => 'A', 'Б' => 'B', 'В' => 'V',

        'Г' => 'G', 'Д' => 'D', 'Е' => 'E',

        'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z',

        'И' => 'I', 'Й' => 'Y', 'К' => 'K',

        'Л' => 'L', 'М' => 'M', 'Н' => 'N',

        'О' => 'O', 'П' => 'P', 'Р' => 'R',

        'С' => 'S', 'Т' => 'T', 'У' => 'U',

        'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',

        'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch',

        'Ь' => '\'', 'Ы' => 'Y', 'Ъ' => '\'',

        'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',

    );

    $reverted = array();
    foreach ($converter as $key => $val) {
        $reverted[$val] = $key;
    }

    return strtr($string, ($revert ? $reverted : $converter));

}


function getGetClone(){
    $clone = explode("&", $_SERVER["QUERY_STRING"]);
    $return = array();
    foreach($clone as $data){
        $data = explode("=", $data);
        if($data[0] != 'q'){
            $return[$data[0]] = $data[1];
        }
    }
    return $return;
}