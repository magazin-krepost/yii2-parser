<?php
namespace matroskin92\parser\controllers;

use yii\helpers\ArrayHelper;
use matroskin92\parser\models\HtmlModel as Html;
use matroskin92\parser\models\PaginationModel as Pagination;

class CatalogController {

	public $key = 1;
	public $queue = array();
	public $categories = array();

	public $products = array();

	public function get($config){

		// Первоначально заносим в очередь, с нулевым родителем!
		$this->queue[] = array(
			'name' => $config['start']['name'],
			'href' => $config['domain'].$config['start']['href'],
			'parent' => 0
		);	

		while( !empty($this->queue) ){

			// Получаем жертву из очереди
			$item = array_shift($this->queue);

			// Добавляем текущую жертву в список категорий и получаем её ID 
			$parent = $this->setCategory($item);

			// Получаем HTML-страницы
			$html = Html::get($item['href'], $config);

			// Получаем ссылки категорий на этой странице
			$this->getCategory($html, $config, $parent);

			// Получаем товары
			if (Pagination::is($html, $config['pagination'])) {

				$pages = Pagination::getLast($html, $config['pagination']);
				for( $i=1; $i<=$pages; $i++ ){
					$html = Html::get($item['href']."?page=".$i, $config);
					$this->getProduct($html, $config['catalog'], $parent);
				}

			} else {
				$this->getProduct($html, $config, $parent);				
			}

			unset($parent);
			unset($item);
			unset($pages);
			unset($html);

		}

		return array(
			'categories' => $this->categories,
			'products' => $this->products,
		);

	}

	public function setCategory($category){
		$key = $this->key;
		$this->categories[$key] = $category;
		$this->key++;
		return $key;
	}

	public function setProduct($product){

		$key = $product['name'];

		// Проверка, нет ли уже товара с подобным названием
		if (!isset($this->products[$key])) {
			$this->products[$key] = $product;
		} else {
			$this->products[$key]['parent'][] = $product['parent'][0];
		}
		
	}

    public function getCategory($html, $config, $parent){

		foreach($html->find($config['catalog']['category']) as $key => $category) {

        	$result = array();

        	$result['name'] = trim($category->find($config['catalog']['category_name'], 0)->plaintext); 
        	$result['href'] = trim($category->find($config['catalog']['category_link'], 0)->href); 

        	if (strpos($result['href'], $config['domain']) === false) {
        		$result['href'] = $config['domain'].$result['href'];
        	}
            
            $result['parent'] = $parent; 

            $this->queue[] = $result;

        }



	}

	public function getProduct($html, $config, $parent){

        foreach($html->find($config['catalog']['product']) as $key => $product) {

        	$result = array();
            $result['name'] = trim($product->find($config['catalog']['product_name'], 0)->plaintext); 
            $result['href'] = trim($product->find($config['catalog']['product_link'], 0)->href); 
            $result['parent'] = array($parent); 

            if (strpos($result['href'], $config['domain']) === false and strlen($result['href']) > 0) {
        		$result['href'] = $config['domain'].$result['href'];
        	}

        	if (strlen($result['href']) > 0){
        		$this->setProduct($result);
        	}


        }

	}
	
}

?>