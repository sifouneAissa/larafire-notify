<?php

namespace Sifouneaissa\LarafireNotify\Traits;

use Kreait\Laravel\Firebase\Facades\Firebase;

trait UserFcm
{


    public function subscribeFcm($registration_id)
    {
        $firebaseAuth = app('firebaseAuth');
        $fcm_notification_key = $firebaseAuth->addToOrCreateGroup($this->fcm_notification_key_name, [
            $registration_id
        ], $this->fcm_notification_key);
        
        if ($fcm_notification_key) {
            $this->fcm_notification_key = $fcm_notification_key;
            $this->save();
        }

        return $fcm_notification_key != null;
    }

    public function unsubscribeFcm($registration_id)
    {

        $firebaseAuth = app('firebaseAuth');

        $fcm_notification_key = $firebaseAuth->deleteFromGroup($this->fcm_notification_key_name, [
            $registration_id
        ], $this->fcm_notification_key);

        $this->fcm_notification_key = $fcm_notification_key;

        $this->save();

        return $fcm_notification_key != null;
    }

      /**
     * Subscribe user to a Firebase topic
     */
    public function subscribeToTopic(string $topic,$registration_id)
    {

        try {
            $messaging = Firebase::messaging();
            $result = $messaging->subscribeToTopic($topic, [$registration_id]);
            $result = $result[$topic][$registration_id] === 'OK';
            return $result;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Unsubscribe user from a Firebase topic
     */
    public function unsubscribeFromTopic(string $topic,$registration_id)
    {
        try {
            $messaging = Firebase::messaging();
            $result = $messaging->unsubscribeFromTopic($topic, [$registration_id]);
            $result = $result[$topic][$registration_id] === 'OK';
            return $result;
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
