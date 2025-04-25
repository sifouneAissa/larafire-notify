<?php

namespace Sifouneaissa\LarafireNotify\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Sifouneaissa\LarafireNotify\Repositories\NotificationRespositoryInterface;
use Illuminate\Http\Request;

class NotificationController extends BaseController
{
    //
    private $notificationRepository;

    public function __construct(NotificationRespositoryInterface $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    public function index(Request $request){
        return $this->notificationRepository->list($request->all(),$request->user());
    }
}
