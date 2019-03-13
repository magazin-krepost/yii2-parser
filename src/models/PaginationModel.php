<?php

namespace matroskin92\parser\models;

use Yii;
use yii\base\Model;

use matroskin92\parser\models\HtmlModel as Html;
use matroskin92\parser\models\UrlModel as Url;

class PaginationModel extends Model
{

	public function is($html, $config){

		if ( $html->find($config['pagination']) != NULL ) {
			return true;
		} else {
			return false;
		}

	}

	public function getLast($html, $config){
		$last = end($html->find($config['pagination_item']));
		return Url::getQuery($last->children(0)->href, 'page');
	}

} 