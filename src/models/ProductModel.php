<?php

namespace matroskin92\parser\models;

use Yii;
use yii\base\Model;

class ProductModel extends Model
{

	public function get($html, $config){

		$result = array();

		$result['name'] 	= $this->getTitle($html, $config['product']);
		$result['price'] 	= $this->getPrice($html, $config['product']);
		$result['attr'] 	= $this->getAttr($html, $config['attribute']);
		$result['desc'] 	= $this->getDescription($html, $config['product']);
		$result['image'] 	= $this->getImages($html, $config['product']);

		return $result;

	}

	public function getTitle($html, $config){



		$result = strip_tags(trim($html->find($config['title'], 0)->plaintext));
		return $result;
	}

	public function getPrice($html, $config){


		// Сначала берем все значение селектора
		$result = $html->find($config['price'], 0)->plaintext;

		// Убираем все, кроме цифр, точек и запятой
		$result = preg_replace('/[^\d.,]/', '', $result);

		// После находим значение до точки (если она есть)
		if ( stripos($result, ',') !== false ) {
			return strstr($result, ',', true);

		} elseif ( stripos($result, '.') !== false ) {
			return strstr($result, '.', true);

		} else {
			return $result;
		}
	}


	public function getAttr($html, $config){

		$attr = array();

		foreach($html->find($config['attr']) as $key => $item){
			$attr[$key]['name'] = trim($item->find($config['attr_name'], 0)->plaintext);
			$attr[$key]['value'] = trim($item->find($config['attr_value'], 0)->plaintext);
			$attr[$key]['attribute_group_id'] = $config['group_id'];
		}
	    
	    return $attr;
	}

	public function getDescription($html, $config){

		return trim($html->find($config['desc'], 0)->plaintext);

	}

	public function getImages($html, $config){

		$images = array();

		foreach($html->find($config['images']) as $key => $item){
			$images[] = $item->find('img', 0)->src;
		}
	    
	    return $images;
	}

} 