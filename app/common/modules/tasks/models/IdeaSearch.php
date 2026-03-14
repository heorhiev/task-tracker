<?php

namespace common\modules\tasks\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class IdeaSearch extends Idea
{
    public function rules(): array
    {
        return [
            [['id', 'project_id'], 'integer'],
            [['title', 'status'], 'safe'],
        ];
    }

    public function scenarios(): array
    {
        return Model::scenarios();
    }

    public function search(array $params): ActiveDataProvider
    {
        $query = Idea::find()->with('project');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 10],
            'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'status' => $this->status,
            'project_id' => $this->project_id,
        ]);

        $query->andFilterWhere(['ilike', 'title', $this->title]);

        return $dataProvider;
    }
}
