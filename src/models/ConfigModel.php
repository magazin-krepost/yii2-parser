<?php

namespace matroskin92\parser\models;

use Yii;
use yii\base\Model;
use matroskin92\parser\models\UrlModel as Url;

class ConfigModel extends Model
{

    public function get($name = 'start'){

        return json_decode(file_get_contents('./web/config/'.$name.'.json'), true);
    }

    public function getForHref($href){

    	$domain = Url::getDomain($href);

    	return (array)json_decode(file_get_contents('./web/config/'.$domain.'.json'), true);
    }

} 