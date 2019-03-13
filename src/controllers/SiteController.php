<?php
namespace matroskin92\parser\controllers;

use yii\helpers\ArrayHelper;
use matroskin92\opencart\controllers\ProductController as Product;
use matroskin92\opencart\controllers\CategoryController as Category;
use matroskin92\opencart\controllers\AttributeController as Attribute;
use matroskin92\opencart\controllers\FilterController as Filter;
use matroskin92\opencart\controllers\ManufacturerController as Manufacturer;
use matroskin92\parser\models\UrlModel as Url;

class SiteController {

	public $config = array();
	public $catalog = array();
	public $report = array();

	public function set($catalog, $config){

		$this->config = $config;
		$this->catalog = $catalog;

		$result = array();

		// Заполняем категории
		foreach($this->catalog['categories'] as $key => $category){
			$this->catalog['categories'][$key]['category_id'] = $this->setCategory($key);
		}

		// Заполняем товары
		foreach($this->catalog['products'] as $key => $category){
			$this->catalog['products'][$key]['product_id'] = $this->setProduct($key);
		}

		return $this->report;

	}

	public function setProduct($id){

		$model_product = new Product();
		$model_category = new Category();
		$model_attr = new Attribute();
		$model_filter = new Filter();

		echo "Set Product ".$id."\n";

		// Берем из хранилища нужную категорию 
		$product = $this->catalog['products'][$id];

		$search = $model_product->getByName($product['name']);

		if ( count($search) == 0 ) {

			// Предварительно в категорию добавляем статичных данных
			$product['language_id'] = $this->config['language_id'];
			$product['store_id'] = $this->config['store_id'];

			// Перерабатываем изначальные данные в наш формат
			$product['price'] = preg_replace('~\D+~','',$product['price']);

			// Копируем изображения и изменяем URL
			foreach($product['image'] as $key => $image){
				if (@fopen($image, "r")) {
					$format = pathinfo($image)['extension'];
					$name = Url::transilte($product['name'])."_".$key.'.'.$format;
					copy($image, './web/image/catalog/'.$name);
					$product['image'][$key] = 'catalog/'.$name;
				} else {
					$product['error_images'][$key] = $image;
				}
			}

			// Основная информация о товер
			$product_id = $model_product->set($product);

			// Узнаем действующие ID категорий, которые указаны в качестве родителей
			foreach($product['parent'] as $parent){

				// Добавить проверку на существование значение категории для этого товара

				$model_category->setProduct($product_id, $this->catalog['categories'][$parent]['category_id']);
			}

		} else {
			$product_id = end($search)['product_id'];
		}

		// Атрибуты
		$this->setAttribute($product['attr'], $product_id);

		// Фильтры
		$this->setFilter($product['attr'], $product_id);

		// Производитель
		$this->setManufacture($product['attr'], $product_id);

		// Метрики
		// $this->setMetric($product['attr'], $product_id);

		echo "Success ".$product_id."\n";

		return $product_id;
	}

	public function setMetric($attrs, $product_id){

		$model_product = new Product();

		// Не хочет обновлять информацию. надо вернуться со свежей головой
		// Данные есть и корректно уходят в update

		if ( isset($this->config['metric']) and $this->config['metric'] !== false ) {

			$name_width = $this->config['metric']['width'];
			$name_heigth = $this->config['metric']['heigth'];
			$name_length = $this->config['metric']['length'];
			$name_weight = $this->config['metric']['weight'];

			$attrs = ArrayHelper::index($attrs, 'name');

			$metrics = array(
				'product_id' => $product_id
			);

			if ( isset($attrs[$name_width]) ) {
				$metrics['width'] = (float)$attrs[$name_width]['value'];
			}

			if ( isset($attrs[$name_heigth]) ) {
				$metrics['heigth'] = (float)$attrs[$name_heigth]['value'];
			}

			if ( isset($attrs[$name_length]) ) {
				$metrics['length'] = (float)$attrs[$name_length]['value'];
			}

			if ( isset($attrs[$name_weight]) ) {
				$metrics['weight'] = (float)$attrs[$name_weight]['value'];
			}
			
			$model_product->update($metrics);

		}
	}

	public function setManufacture($attrs, $product_id){

		$model_manufacturer = new Manufacturer();

		if ( $this->config['manufacturer']['attribute'] !== false ) {

			$search = $this->config['manufacturer']['attribute'];

			$attrs = ArrayHelper::index($attrs, 'name');

			if ( isset($attrs[$search]) ) {

				$result = $model_manufacturer->get($attrs[$search]['value']);

				// Производитель найден, берем ID и прикрепляем к товару
				if (!empty($result)){
					$manufacturer_id = $result['manufacturer_id'];

				// Производитель не найден, создаем
				} else {
					$manufacturer_id = $model_manufacturer->set(array(
						'name' => $attrs[$search]['value']
					));
				}

				$model_manufacturer->setProduct($manufacturer_id, $product_id);
			}

		}
	}

	public function setUpc($upc, $product_id){



	}

	public function setFilter($attrs, $product_id){

		$model_filter = new Filter();

		foreach($attrs as $attr){

			// Если входит в список фильтров
			if ( isset($this->config['filter'][$attr['name']]) ) {

				$result = $model_filter->getFilterGroup($attr['name']);
				if ( isset($result) ) {

					$filter_group_id = $result['filter_group_id'];

					$result = $model_filter->getFilter($attr['value'], $filter_group_id);
					if ( isset($result) ) {
						$filter_id = $result['filter_id'];
					} else {
						$filter_id = $model_filter->setFilter($attr['value'], $filter_group_id);
					}

				} else {
					$filter_group_id = $model_filter->setFilterGroup($attr['name']);
					$filter_id = $model_filter->setFilter($filter_group_id, $attr['value']);
				}

				$model_filter->setProduct($product_id, $filter_id);

			}

		}
	}

	public function setAttribute($attrs, $product_id){

		$model_attr = new Attribute();

		foreach($attrs as $attr){

			$result = $model_attr->get($attr['name']);

			if ( count($result) > 0 ) {
				
				$attribute_id = $result['attribute_id'];

			} else {
				
				$attribute_id = $model_attr->set(array(
					'language_id' => 1,
					'attribute_group_id' => $attr['attribute_group_id'],
					'name' => $attr['name'],
				));
				
			}

			// Добавить проверку на существование значение атрибута для этого товара
			$model_attr->setProduct($product_id, $attribute_id, $attr['value']);
		}

	}

	public function setCategory($id){

		$model_category = new Category();

		// Берем из хранилища нужную категорию 
		$category = $this->catalog['categories'][$id];

		$search = $model_category->get($category['name']);

		if ( count($search) == 0 ) {

			// Предварительно в категорию добавляем статичных данных
			$category['language_id'] = $this->config['language_id'];
			$category['store_id'] = $this->config['store_id'];

			// Надо взять parent_id из массива с данными
			if (isset( $this->catalog['categories'][$id]['parent'] )) {
				$parent = $this->catalog['categories'][$id]['parent'];
				$category['parent_id'] = $this->catalog['categories'][$parent]['category_id'];
			} else {
				$category['parent_id'] = 0;
			}

			// Создаем категорию
			$category_id = $model_category->set($category);

			$this->report['categories'][] = $category_id;

		} else {
			$category_id = end($search)['category_id'];
		}	

		return $category_id;

	}
	
}

?>