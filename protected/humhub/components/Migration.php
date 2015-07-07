<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

use Yii;

/**
 * Migration is the base class for representing a database migration.
 *
 * @see \yii\db\Migration
 */
class Migration extends \yii\db\Migration
{

    /**
     * Renames a class
     * 
     * This is often required because some classes are also stored in database 
     * e.g. for polymorphic relations. 
     * 
     * This method is also required for 0.20 namespace migration!
     * 
     * @param string $oldClass
     * @param string $newClass
     */
    protected function renameClass($oldClass, $newClass)
    {
        $this->updateSilent('activity', ['object_model' => $newClass], ['object_model' => $oldClass]);
        $this->updateSilent('activity', ['class' => $newClass], ['class' => $oldClass]);
        $this->updateSilent('comment', ['object_model' => $newClass], ['object_model' => $oldClass]);
        $this->updateSilent('content', ['object_model' => $newClass], ['object_model' => $oldClass]);
        $this->updateSilent('file', ['object_model' => $newClass], ['object_model' => $oldClass]);
        $this->updateSilent('like', ['object_model' => $newClass], ['object_model' => $oldClass]);
        $this->updateSilent('notification', ['source_class' => $newClass], ['source_class' => $oldClass]);
        $this->updateSilent('notification', ['obsolete_target_object_model' => $newClass], ['obsolete_target_object_model' => $oldClass]);
        $this->updateSilent('user_mentioning', ['object_model' => $newClass], ['object_model' => $oldClass]);
        $this->updateSilent('user_follow', ['object_model' => $newClass], ['object_model' => $oldClass]);
        $this->updateSilent('wall', ['object_model' => $newClass], ['object_model' => $oldClass]);

        /**
         * Looking up "NewLike" activities with this className 
         * Since 0.20 the className changed to Like 
         */
        $likes = (new \yii\db\Query())->select("activity.*, like.id as likeid")->from('activity')
                        ->leftJoin('like', 'like.object_model=activity.object_model AND like.object_id=activity.object_id')
                        ->where(['class' => 'humhub\modules\like\activities\Liked'])->andWhere('like.id IS NOT NULL')->andWhere('activity.object_model != :likeClass', [':likeClass' => \humhub\modules\like\models\Like::className()])->all();
        foreach ($likes as $like) {
            Yii::$app->db->createCommand()->update('activity', ['object_model' => \humhub\modules\like\models\Like::className(), 'object_id' => $like['likeid']], ['id' => $like['id']])->execute();
        }
    }

    /**
     * Creates and executes an UPDATE SQL statement without any output.
     * The method will properly escape the column names and bind the values to be updated.
     * 
     * @param string $table the table to be updated.
     * @param array $columns the column data (name => value) to be updated.
     * @param array|string $condition the conditions that will be put in the WHERE part. Please
     * refer to [[Query::where()]] on how to specify conditions.
     * @param array $params the parameters to be bound to the query.
     */
    public function updateSilent($table, $columns, $condition = '', $params = array())
    {
        $this->db->createCommand()->update($table, $columns, $condition, $params)->execute();
    }

}
