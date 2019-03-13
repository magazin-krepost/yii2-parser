<?php

namespace matroskin92\parser\models;

use Yii;
use yii\db\ActiveRecord;

class DbParser extends ActiveRecord
{
	public static function tableName()
    {
        return '{{1_parser}}';
    }

    public function get($product_id)
	{
		return $this->find()->where(['product_id' => $product_id])->asArray()->one();
	}

	public function getPricesForDomain($domain = '', $limit = 1, $offset = 0)
    {
        return $this->find()->select(['product_id', 'price'])->where(['like', 'price', $domain])->asArray()->offset($offset)->limit($limit)->all();
    }


    public function getParserCount($user)
    {

        $data = array();

        $data['today'] = $this->find()
                                ->where(['staff' => $user])
                                ->andWhere(['>', 'date', \date('Y-m-d 00-00-00')])
                                ->count();
        $data['all'] = $this->find()->where(['staff' => $user])->count();

        return $data;
    }


    public function setParserItem($id, $data)
    {
        $request = $this->findOne($id);

        if (empty($request)) {
            $request = new $this;
            $request->product_id = $id;
        }

        $request->price = json_encode($data['price']);
        $request->content = $data['content'];
        $request->name_new = $data['name_new'];
        $request->staff = Yii::$app->user->identity->username;
        $request->date = \date('Y-m-d H-i-s');
        return $request->save();
    }

} 