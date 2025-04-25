<?php

namespace Sifouneaissa\LarafireNotify\Controllers;

use Validator;
use Illuminate\Http\Request;
use Sifouneaissa\LarafireNotify\Notifications\TestNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use Auth;

class NotificationManagerController extends BaseController
{
	private function currentUser(){
		return Auth::guard(config("larafire-notify.guard"))->user();
	}

	public function subscribe(Request $request)
	{
		$validator = Validator::make($request->all(), [
			config('larafire-notify.fcm_token_param') => ['required', 'string'],
		]);
		
		if ($validator->fails()) {
			return [
				'success' => false,
				'inputs_errors' => $validator->errors()->toArray(),
			];
		}

		$user = $this->currentUser();
		
		$status = false;
		$fcm_token_param = $request->input(config('larafire-notify.fcm_token_param'));

		try {
			$status = $user->subscribeFcm($fcm_token_param);
		} catch (\Throwable $e) {
			$this->LogThisError($e,'Failed to subscribe to firebase group', "fcm-notification-subscribe");
			return [
				'success' => false,
			];
		}


		return [
			'success' => $status,
		];
	}

	public function unsubscribe(Request $request)
	{
		$validator = Validator::make($request->all(), [
			config('larafire-notify.fcm_token_param') => ['required', 'string'],
		]);

		if ($validator->fails()) {
			return [
				'success' => false,
				'inputs_errors' => $validator->errors()->toArray(),
			];
		}

		$fcm_token_param = $request->input(config('larafire-notify.fcm_token_param'));
		
		try {
			$request->user()->unsubscribeFcm($fcm_token_param);
		} catch (\Exception $e) {
			$this->LogThisError($e,'failed to unsubscribe from the group firebase', "fcm-notification-unsubscribe");
			
			return [
				'success' => false,
			];;
		}

		return [
			'success' => true,
		];
	}

	public function testNotification(Request $request){

		$user = $this->currentUser();

		$notification = new TestNotification($request->title,$request->body,$request->topic);

		if ($request->topic) {
			// Use this to send a notification to guest users who are subscribed to the given topic
			Notification::route('firebase-topic', $request->topic)->notify($notification);
		}

		if(!$user && !$request->topic)
			return [
				'success' => false
			];
		if($user)
		$user->notify($notification);


		return [
			'success' => true
		];
	}

	public function subscribeToTopic(Request $request){
		
		$validator = Validator::make($request->all(), [
			'topic' => ['required', 'string'],
			config('larafire-notify.fcm_token_param') => ['required', 'string'],
		]);

		if ($validator->fails()) {
			return [
				'success' => false,
				'inputs_errors' => $validator->errors()->toArray(),
			];
		}

		$user = $this->currentUser();;

		$result = false;

		if($request->topic){
			if(!$user)
				$user = app(config('auth.providers.users.model'));
			
			$result = $user->subscribeToTopic($request->topic,$request->input(config('larafire-notify.fcm_token_param')));
		}

		return [
			'success' => $result
		];
	}

	public function unsubscribeFromTopic(Request $request){

		$validator = Validator::make($request->all(), [
			'topic' => ['required', 'string'],
			config('larafire-notify.fcm_token_param') => ['required', 'string'],
		]);

		if ($validator->fails()) {
			return [
				'success' => false,
				'inputs_errors' => $validator->errors()->toArray(),
			];
		}

		$user = $this->currentUser();;
		
		$result = false;

		if($request->topic){
			if(!$user)
				$user = app(config('auth.providers.users.model'));
				$result = $user->unsubscribeFromTopic($request->topic,$request->input(config('larafire-notify.fcm_token_param')));
		}

		return [
			'success' => $result
		];
	}

	private function LogThisError(
		\Throwable $err,
		$message = null,
		$tag = ''
	): void {
		$tag = strtolower(trim($tag));
		$tag = $tag == '' ? 'general' : $tag;
		$message ??= 'No custom message used';

		Log::error("[$tag] - $message", [
			'error' => [
				'code' => $err->getCode(),
				'file' => $err->getFile(),
				'line' => $err->getLine(),
				'message' => $err->getMessage(),
			],
			'error_string' => $err->__toString(),
		]);
	}
}
