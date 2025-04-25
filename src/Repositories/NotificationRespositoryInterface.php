<?php
namespace Sifouneaissa\LarafireNotify\Repositories;

interface NotificationRespositoryInterface
{
	public function list(array $data,$user,$forWs = true);
}
