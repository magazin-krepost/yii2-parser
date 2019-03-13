<?php

namespace matroskin92\parser\models;

use Yii;
use yii\base\Model;
use matroskin92\parser\models\PaginationModel as Pagination;
use matroskin92\parser\models\HtmlModel as Html;

class LinkModel extends Model
{

	public function get($url){

		$html = Html::get($url);

        // Получить так же страницу @page=1, @page=2
        if ( Pagination::is($html) ) {
            
            // Получить последнию страницу страницу
            $url_last_page = Pagination::getLast($html);

            // Циклом пройтись от первой, до последней
            for ( $page = 0; $page <= Url::getQuery($url_last_page, 'page'); $page++ ){

                // Переопределяем HTML на страницу с номером
                $html = $this->getHTML($url."?page=".$page);

                // Получаем ссылки на новые категории только на первой странице
                if ($page == 0)
                    foreach(Category::getUrlAll($html) as $link)
                        if (!in_array($link, $this->category_temp))
                            $this->category_temp[] = $link;

                // Получаем ссылки на товары
                foreach(Product::getUrlAll($html) as $link)
                    if (!in_array($link, $this->product_temp))
                        $this->product_temp[] = $link;

            }

        } else {

            // Получаем ссылки на новые категории
            foreach(Category::getUrlAll($html) as $link)
                if (!in_array($link, $this->category_temp))
                    $this->category_temp[] = $link;

            // Получаем ссылки на товары
            foreach(Product::getUrlAll($html) as $link)
                if (!in_array($link, $this->product_temp))
                    $this->product_temp[] = $link;

        }

        // Переносим страницу в разряд готовых
        $this->category_ready[] = $url;

	}

	public function getCategories($html){

		$categories = array();

		foreach(Category::getUrlAll($html) as $link) {
            if (!in_array($link, $this->category_temp)){
                $this->category_temp[] = $link;
            }
		}

		return $categories;

	}

	public function getProducts($html){
		foreach(Product::getUrlAll($html) as $link){
		 	if (!in_array($link, $this->product_temp)){
		 		 $this->product_temp[] = $link;
		 	}
		}
           
               
	}

} 