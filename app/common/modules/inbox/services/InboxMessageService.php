<?php

namespace common\modules\inbox\services;

use common\modules\fileManager\models\StoredFile;
use common\modules\inbox\models\InboxMessage;
use common\modules\inbox\models\InboxMessageAttachment;
use Yii;
use yii\db\Exception;

class InboxMessageService
{
    public function create(array $messageAttributes): ?InboxMessage
    {
        $message = new InboxMessage();
        $message->setAttributes($messageAttributes, false);

        return $message->save() ? $message : null;
    }

    public function createWithAttachment(array $messageAttributes, StoredFile $storedFile, string $role): ?InboxMessage
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $message = $this->create($messageAttributes);
            if ($message === null) {
                $transaction->rollBack();

                return null;
            }

            $attachment = new InboxMessageAttachment();
            if ($this->populateAndSaveAttachment($attachment, $message, $storedFile, $role) === null) {
                $transaction->rollBack();

                return null;
            }

            $transaction->commit();

            return $message;
        } catch (\Throwable $exception) {
            $transaction->rollBack();

            throw new Exception('Failed to create inbox message with attachment.', 0, $exception);
        }
    }

    /**
     * @return InboxMessage[]
     */
    public function getPendingMessages(int $limit = 50, ?string $messageType = null, ?string $source = null): array
    {
        $query = InboxMessage::find()
            ->with(['attachments', 'attachments.storedFile'])
            ->where(['status' => InboxMessage::STATUS_PENDING])
            ->orderBy(['received_at' => SORT_ASC, 'id' => SORT_ASC])
            ->limit($limit);

        if ($messageType !== null) {
            $query->andWhere(['message_type' => $messageType]);
        }

        if ($source !== null) {
            $query->andWhere(['source' => $source]);
        }

        return $query->all();
    }

    public function attachFile(InboxMessage $message, StoredFile $storedFile, string $role): ?InboxMessageAttachment
    {
        $attachment = new InboxMessageAttachment();

        return $this->populateAndSaveAttachment($attachment, $message, $storedFile, $role);
    }

    private function populateAndSaveAttachment(
        InboxMessageAttachment $attachment,
        InboxMessage $message,
        StoredFile $storedFile,
        string $role
    ): ?InboxMessageAttachment
    {
        $attachment->setAttributes([
            'inbox_message_id' => $message->id,
            'stored_file_id' => $storedFile->id,
            'role' => $role,
        ], false);

        return $attachment->save() ? $attachment : null;
    }
}
