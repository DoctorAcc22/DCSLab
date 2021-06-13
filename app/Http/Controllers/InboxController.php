<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use App\Services\InboxService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Vinkla\Hashids\Facades\Hashids;

class InboxController extends Controller
{
    private $userService;
    private $inboxService;
    private $activityLogService;

    public function __construct(InboxService $inboxService, UserService $userService, ActivityLogService $activityLogService)
    {
        $this->middleware('auth');
        $this->userService = $userService;
        $this->inboxService = $inboxService;
        $this->activityLogService = $activityLogService;
    }

    public function index(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());

        return view('inbox.index');
    }

    public function read()
    {
        $usrId = Auth::user()->id;

        $ts = $this->inboxService->getThreads($usrId);

        $tss = $ts->map(function ($item) {
            return [
                'hId' => Hashids::encode($item->id),
                'subject' => $item->subject,
                'body' => $item->latestMessage->body,
                'participants' => $item->participantsString(),
                'created_at' => $item->created_at->diffForHumans(),
                'updated_at' => $item->updated_at->diffForHumans(),
            ];
        });

        return $tss;
    }

    public function getUserList()
    {
        $email = Auth::user()->email;

        $usr = $this->userService->getAllUserExceptMe($email);

        $brief = $usr->map(function ($item, $key) {
            return [
                'hId' => $item->hId,
                'full_name' => empty($item->profile->first_name) ? $item->name  : $item->profile->first_name . $item->profile->last_name,
                'img_path' => $item->profile->img_path,
                'roles' => $item->roles->first()->description
            ];
        });

        return $brief;
    }

    public function show($id)
    {
        $usr = Auth::user()->id;

        $t = $this->inboxService->getThread($id);

        $mm = $t->map(function ($item) use($usr) {
            return [
                'thread_id' => Hashids::encode($item->thread_id),
                'full_name' => $item->user->profile->first_name . $item->user->profile->last_name,
                'img_path' => $item->user->profile->img_path,
                'message' => $item->body,
                'reverse' => $item->user_id == $usr ? true:false,
                'updated_at' => $item->updated_at->diffForHumans(),
            ];
        });

        return $mm;
    }

    public function store(Request $request)
    {
        $request->validate([
            'to' => 'required',
            'subject' => 'required'
        ]);

        $usrId = Auth::user()->id;

        $decryptedTo = [];

        foreach (explode(',',$request['to']) as $s)
        {
            array_push($decryptedTo, Hashids::decode($s)[0]);
        }

        $result = $this->inboxService->store($usrId, $decryptedTo, $request['subject'], $request['message']);

        if ($result == 0) {
            return response()->json([
                'message' => ''
            ],500);
        } else {
            return response()->json([
            'message' => ''
            ],200);
        }
    }

    public function edit()
    {

    }
}
