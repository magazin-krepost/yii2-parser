<?php

namespace matroskin92\parser\models;

use Yii;
use yii\base\Model;
use keltstr\simplehtmldom\SimpleHTMLDom as SHD;

class HtmlModel extends Model
{

    public function get($url, $config)
    {

    	if (!is_array($config['auth'])) {

    		return SHD::str_get_html(file_get_contents($url));
    	} else {

    		$auth = $config['auth'];

	        $ch = curl_init();
	        curl_setopt($ch, CURLOPT_HEADER, false);
	        curl_setopt($ch, CURLOPT_NOBODY, false);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	        curl_setopt($ch, CURLOPT_COOKIEJAR, './web/cookie/'.$auth['cookie'].'.txt');
	        curl_setopt($ch, CURLOPT_COOKIE, "cookiename=0");
	        curl_setopt($ch, CURLOPT_USERAGENT,"Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.7.12) Gecko/20050915 Firefox/1.0.7");
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	        curl_setopt($ch, CURLOPT_REFERER, $_SERVER['REQUEST_URI']);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
	        
	        // Если нет cookie-файла
	        if ( @fopen('./web/cookie/'.$auth['cookie'].'.txt', "r") ) {
	        	curl_setopt($ch, CURLOPT_URL, $auth['link']);
	        	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	       		curl_setopt($ch, CURLOPT_POST, 1);
	        	curl_setopt($ch, CURLOPT_POSTFIELDS, $auth['post']);
	        	$result = curl_exec($ch);
	        }

	        // Откуда брать контент
	        curl_setopt($ch, CURLOPT_URL, $url);

	        // Берем контент
	        $html = curl_exec($ch);
	        
	        // Закрываем
	        curl_close($ch);

	        return SHD::str_get_html($html);

    	}

        
    }

} 