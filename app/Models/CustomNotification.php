<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomNotification extends Model
{
    protected $fillable = [
        'sender_type',
        'sender_id',
        'recipient_type',
        'recipient_ids',
        'title',
        'message',
        'type',
        'action_url',
        'action_text',
        'scheduled_at',
        'sent_at',
        'recipients_count',
        'read_count',
    ];

    protected $casts = [
        'recipient_ids' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    /**
     * Check if notification has been sent
     */
    public function isSent()
    {
        return !is_null($this->sent_at);
    }

    /**
     * Check if notification is scheduled
     */
    public function isScheduled()
    {
        return !is_null($this->scheduled_at) && is_null($this->sent_at);
    }
}
