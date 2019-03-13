<?php

namespace matroskin92\parser\models;

use Yii;
use yii\base\Model;

class UrlModel extends Model
{

	public function getQuery($url, $prm){

		// Потрошим на URL-составляющие
		$url_arr = parse_url($url);

		// Потрошим на GET-параметры
        parse_str($url_arr['query'], $url_query);

        // Отдаем значение параметра
		return $url_query[$prm];
	}

	public function getDomain($url){

		// Потрошим на URL-составляющие
		$url_arr = parse_url($url);

		// Отдаем значение параметра
		return $url_arr['host'];
	}

	public function transilte($value){
		$converter = array(
	        'а' => 'a',   'б' => 'b',   'в' => 'v',
	        'г' => 'g',   'д' => 'd',   'е' => 'e',
	        'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
	        'и' => 'i',   'й' => 'y',   'к' => 'k',
	        'л' => 'l',   'м' => 'm',   'н' => 'n',
	        'о' => 'o',   'п' => 'p',   'р' => 'r',
	        'с' => 's',   'т' => 't',   'у' => 'u',
	        'ф' => 'f',   'х' => 'h',   'ц' => 'c',
	        'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
	        'ь' => '',    'ы' => 'y',   'ъ' => '',
	        'э' => 'e',   'ю' => 'yu',  'я' => 'ya',
	        
	        'А' => 'A',   'Б' => 'B',   'В' => 'V',
	        'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
	        'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
	        'И' => 'I',   'Й' => 'Y',   'К' => 'K',
	        'Л' => 'L',   'М' => 'M',   'Н' => 'N',
	        'О' => 'O',   'П' => 'P',   'Р' => 'R',
	        'С' => 'S',   'Т' => 'T',   'У' => 'U',
	        'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
	        'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
	        'Ь' => '',    'Ы' => 'Y',   'Ъ' => '',
	        'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',

	        '(' => '',     ')' => '',    '/' => '_',  
	    );
	    return str_replace(' ', '_', strtolower(strtr($value, $converter)));
	}

} 