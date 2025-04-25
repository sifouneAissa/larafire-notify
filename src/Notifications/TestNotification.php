<?php

namespace Sifouneaissa\LarafireNotify\Notifications;

use Sifouneaissa\LarafireNotify\Channels\PushToUserChannel;
use Illuminate\Notifications\Notification;

class TestNotification extends Notification
{
	// use Queueable;

    const ACTION = "test_notification";
	private $title;
	private $body;
	/**
	 * Create a new notification instance.
	 *
	 * @return void
	 */
	public function __construct($title,$body,public $topic = null)
	{
		$this->title = $title ?? "Test notification";
		$this->body = $body ?? "Test notification body";
	}

	/**
	 * Get the notification's delivery channels.
	 *
	 * @param  mixed  $notifiable
	 * @return array
	 */
	public function via($notifiable)
	{
		return [PushToUserChannel::class];
	}

	public function toPush($notifiable)
	{
		$baseNotification = app('baseNotification');

        $builders['notification'] = [
            'title' => $this->title,
            'body' =>  $this->body,
        ];

        $builders['data'] = [
            'action' => self::ACTION,
        ];

		if(!$this->topic)
        return $baseNotification->toPush($builders,$notifiable->fcm_notification_key);

        return $baseNotification->toPushAsTopic($builders,$this->topic);
  
	}
}
