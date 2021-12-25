<?php

namespace backend\modules\smsgo\models\search;

use backend\modules\smsgo\models\SmsHistory;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * SmsHistorySearch represents the model behind the search form of `backend\modules\smsgo\models\SmsHistory`.
 */
class SmsHistorySearch extends SmsHistory
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'status', 'message_id', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['phone', 'message', 'from', 'callback_url', 'status_date'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = SmsHistory::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC
                ]
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'message_id' => $this->message_id,
            'status_date' => $this->status_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'phone', $this->phone])
            ->andFilterWhere(['like', 'message', $this->message])
            ->andFilterWhere(['like', 'from', $this->from])
            ->andFilterWhere(['like', 'callback_url', $this->callback_url]);

        return $dataProvider;
    }
}
