<?php
namespace matroskin92\parser\controllers;

// use yii\console\Controller;
use yii\helpers\ArrayHelper;

use matroskin92\parser\models\ProductModel as Product;
use matroskin92\parser\models\ConfigModel as Config;
use matroskin92\parser\models\HtmlModel as Html;

class ProductController {

	public function get($products, $config = array()){

		$model_config = new Config();
		$model_product = new Product();

		if (empty($config)){
			$config = $model_config->getForHref(current($products)['href']);
		}

		foreach($products as $key => $product){

			$html = Html::get($product['href'], $config);

			$products[$key] = $model_product->get(
				$html,
				$config
			);	

			$products[$key]['parent'] = $product['parent'];

		}

		return $products;
	}

	public function getPrice($products, $config = array()){

		$model_config = new Config();
		$model_product = new Product();

		if (empty($config)){
			$config = $model_config->getForHref(current($products)['href']);
		}

		foreach($products as $key => $product){

			$html = Html::get($product['href'], $config);

			if (!empty($html)) {
				$products[$key]['price'] = $model_product->getPrice(
					$html,
					$config['product']
				);	
			}
			
		}

		return $products;
	}
	
}

?>