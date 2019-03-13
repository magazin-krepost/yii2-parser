<?php
namespace matroskin92\parser\controllers;

use yii\helpers\ArrayHelper;
use matroskin92\parser\models\DbParser as DbParser;

class ParserController {

	public function getPriceAll($domain, $limit, $offset){

		$model_parser = new DbParser();
		return $model_parser->getPricesForDomain($domain, $limit, $offset);

	}
	
}

?>