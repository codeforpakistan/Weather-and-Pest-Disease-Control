<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\PlantDiary;
use Illuminate\Http\Request;
use App\Models\PlantIdentification;
use App\Http\Controllers\Controller;

class SearchDataController extends Controller
{
    public function searchUser(Request $request)
    {
        $data = $request->input('search');

        //start query
        $query = User::query();

        // start filter
        if ($data) {
            $query->where('first_name', 'LIKE', "%$data%")
                ->orWhere('last_name', 'LIKE', "%$data%");
        }

        $userLists = $query->orderBy('created_at', 'desc')->get();
        $searchRoute = route('users.search');

        return view('admin.userList', compact(['userLists', 'data', 'searchRoute']));
    }

    public function searchDiary(Request $request)
    {

        // dd($request->all());
        $data = $request->input('search');
        $user = auth()->user();

        // start query
        $query = PlantDiary::with(['plantType', 'user']);

        // if not admin only show regular or his own plant diaries

        if (!$user->is_admin == 1) {
            $query->where('user_id', $user->id);
        }

        // start filter
        if ($data) {
            $query->whereHas('plantType', function ($q) use ($data) {
                $q->where('type', 'LIKE', "%$data%");
            })
                ->orWhereHas('user', function ($subq) use ($data) {
                    $subq->where('first_name', 'LIKE', "%$data%");
                });
        }

        $plantDiaries = $query->orderBy('diary_at', 'desc')->get();
        $searchRoute = route('plant_diaries.search');

        return view('admin.plant_diaries.index', compact(['plantDiaries', 'data', 'searchRoute']));
    }

    public function searchIdentification(Request $request)
    {
        $data = $request->input('search');
        $user = auth()->user();

        // start query
        $query = PlantIdentification::with('plantCategory', 'user');

        // if not admin only show regular or his own plant histories
        if (!$user->is_admin == 1) {
            $query->where('user_id', $user->id);
        }

        // start filter
        if ($data) {
            $query->whereHas('plantCategory', function ($q) use ($data) {
                $q->where('category', 'LIKE', "%$data%");
            })
                ->orWhereHas('user', function ($subq) use ($data) {
                    $subq->where('first_name', 'LIKE', "%$data%");
                });
        }

        $plantIdentifications = $query->orderBy('identified_at', 'desc')->get();
        $searchRoute = route('plant_identifications.search');

        return view('admin.plant_identifications.index', compact(['plantIdentifications', 'data', 'searchRoute']));
    }
}
