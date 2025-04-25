<?php

namespace Sifouneaissa\LarafireNotify\Repositories;

class NotificationRepository implements NotificationRespositoryInterface
{
	public function list(array $data, $user,$forWs = true)
	{
		$collection = config('larafire-notify.resource.collection');
		
		$per_page =  $data['per_page'] ?? config('larafire-notify.per_page');

		$builder = $user->notifications();

		if(!$forWs) return $builder->get();

		return [
			'success' => true,
			'data' => $collection ? $collection::collection($builder->paginate($per_page)) : $builder->paginate($per_page)
		];
	}
}
