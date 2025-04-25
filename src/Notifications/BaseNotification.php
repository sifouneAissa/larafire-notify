<?php

namespace Sifouneaissa\LarafireNotify\Notifications;

use Illuminate\Notifications\Notification;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\Notification as MessagingNotification;

class BaseNotification extends Notification
{

    public function toPush($builders, $notificationKey)
    {
        $castNotification = null;
        $notification = $builders['notification'] ?? null;
        $data = $builders['data'] ?? null;
        $message = null;
        $optionBuilder = isset($builders["option"])
            ? $builders["option"]
            : null;

        if ($data) {
            $data["force_show_in_foreground"] = true;
        }

        if ($notification) {
            $sound = $builders['sound'] ?? 'default';

            $androidConfig = [
                'notification' => array_filter([
                    'sound' => $sound,
                    'channel_id' => $builders['channel_id'] ?? null,
                ]),
            ];
    
            $apnsConfig = [
                'headers' => [],
                'payload' => [
                    'aps' => [
                        'sound' => $sound,
                    ],
                ],
            ];
    
            if ($builders['with_highest_possible_priority'] ?? false) {
                $androidConfig['priority'] = 'high';
                $apnsConfig['headers']['apns-priority'] = '10';
            }

            if (isset($builders['collapse_key']) && is_string($builders['collapse_key'])) {
                $androidConfig['collapse_key'] = $builders['collapse_key'];
                $apnsConfig['headers']['apns-collapse-id'] = $builders['collapse_key'];
            }

            $castNotification = MessagingNotification::create($notification['title'] ?? "", $notification['body'] ?? "", $notification['image'] ?? "");
            $message = CloudMessage::withTarget('token', $notificationKey)
                                ->withNotification($castNotification)
                                ->withAndroidConfig(AndroidConfig::fromArray($androidConfig))
                                ->withApnsConfig(ApnsConfig::fromArray($apnsConfig));
            if ($data){
                $data = array_map(function ($item){
                    if(is_array($item)){
                        return json_encode($item);
                    }
                    else if(is_object($item)){
                        return json_encode($item);
                    }
                    return $item;
                },$data);
                
                $message = $message->withData($data ?? []);
            }
        }

        return $message;
    }


    public function toPushAsTopic($builders, $topic)
    {
        $castNotification = null;
        $notification = $builders['notification'] ?? null;
        $data = $builders['data'] ?? null;
        $message = null;
        $optionBuilder = $builders["option"] ?? null;

        if ($data) {
            $data["force_show_in_foreground"] = true;
        }

        if ($notification) {
            $sound = $builders['sound'] ?? 'default';

            $androidConfig = [
                'notification' => array_filter([
                    'sound' => $sound,
                    'channel_id' => $builders['channel_id'] ?? null,
                ]),
            ];

            $apnsConfig = [
                'headers' => [],
                'payload' => [
                    'aps' => [
                        'sound' => $sound,
                    ],
                ],
            ];

            if ($builders['with_highest_possible_priority'] ?? false) {
                $androidConfig['priority'] = 'high';
                $apnsConfig['headers']['apns-priority'] = '10';
            }

            if (isset($builders['collapse_key']) && is_string($builders['collapse_key'])) {
                $androidConfig['collapse_key'] = $builders['collapse_key'];
                $apnsConfig['headers']['apns-collapse-id'] = $builders['collapse_key'];
            }

            $castNotification = MessagingNotification::create(
                $notification['title'] ?? "",
                $notification['body'] ?? "",
                $notification['image'] ?? ""
            );

            $message = CloudMessage::withTarget('topic', $topic)
                ->withNotification($castNotification)
                ->withAndroidConfig(AndroidConfig::fromArray($androidConfig))
                ->withApnsConfig(ApnsConfig::fromArray($apnsConfig));

            if ($data) {
                $data = array_map(function ($item) {
                    return is_array($item) || is_object($item) ? json_encode($item) : $item;
                }, $data);

                $message = $message->withData($data ?? []);
            }
        }

        return $message;
    }
}
