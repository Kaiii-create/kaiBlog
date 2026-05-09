<?php
namespace app\common\model;

use think\Model;

class Message extends Model
{
    protected $table = 'messages';

    /**
     * 创建消息
     */
    public static function send(string $receiverType, int $receiverId, string $type, string $title, string $content = '', array $extra = []): self
    {
        return self::create(array_merge([
            'receiver_type' => $receiverType,
            'receiver_id' => $receiverId,
            'from_type' => $extra['from_type'] ?? 'system',
            'from_id' => $extra['from_id'] ?? null,
            'type' => $type,
            'title' => $title,
            'content' => $content,
            'target_type' => $extra['target_type'] ?? null,
            'target_id' => $extra['target_id'] ?? null,
        ], $extra));
    }

    /**
     * 获取未读数量
     */
    public static function unreadCount(string $receiverType, int $receiverId): int
    {
        return self::where('receiver_type', $receiverType)
            ->where('receiver_id', $receiverId)
            ->where('is_read', 0)
            ->count();
    }

    /**
     * 标记单条已读
     */
    public static function markRead(int $id, string $receiverType, int $receiverId): bool
    {
        $msg = self::where('id', $id)
            ->where('receiver_type', $receiverType)
            ->where('receiver_id', $receiverId)
            ->find();
        if (!$msg) return false;
        $msg->is_read = 1;
        $msg->read_at = date('Y-m-d H:i:s');
        $msg->save();
        return true;
    }

    /**
     * 全部标记已读
     */
    public static function markAllRead(string $receiverType, int $receiverId): int
    {
        return self::where('receiver_type', $receiverType)
            ->where('receiver_id', $receiverId)
            ->where('is_read', 0)
            ->update([
                'is_read' => 1,
                'read_at' => date('Y-m-d H:i:s'),
            ]);
    }
}
