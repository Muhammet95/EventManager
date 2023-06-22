<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventController extends ApiController
{
    /**
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        if ($this->unauthorized)
            return response('Unauthorized', 401);

        $events_all = Event::get();
        $events_my = Event::where('creator_id', $this->user->id)->get()->toArray();

        return response(["events_all" => $events_all, "events_my" => $events_my]);
    }

    public function create(Request $request): Response
    {
        try {
            try {
                $request->validate([
                    'title' => 'required|string|max:255',
                    'desc' => 'required|string'
                ]);
            } catch (\Exception $exception) {
                Log::error($exception->getMessage());
                return response(['message' => 'Ошибка валидации заполненных данных'], 500);
            }

            $event = Event::create([
                'Title' => $request->get('title'),
                'Text' => $request->get('desc'),
                'creator_id' => $this->user->id
            ]);

            DB::table('event_users')
                ->insertOrIgnore([
                    'user_id' => $this->user->id,
                    'event_id' => $event->id,
                    'updated_at' => Carbon::now(),
                    'created_at' => Carbon::now()
                ]);

            return response(['message' => 'Событие успешно создано'], 200);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            return response(['message' => 'Ошибка создания события'], 500);
        }
    }

    public function show($id): Response
    {
        try {
            if (empty($id))
                return response(['message' => 'Не указан идентификатор события'], 500);

            $event = Event::with('users')->find($id);

            if (empty($event))
                return response(['message' => 'Не получилось найти событие'], 500);

            return response($event);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            return response(['message' => 'Ошибка поиска события'], 500);
        }
    }

    public function user($login): Response
    {
        try {
            if (empty($login))
                return response(['message' => 'Не указан логин пользователя'], 500);

            $user = User::where('login', $login)->first();

            if (empty($user))
                return response(['message' => 'Не получилось найти данные пользователя'], 500);

            return response([
                'login' => $user->login,
                'name' => $user->name,
                'surname' => $user->surname,
                'born' => $user->born
            ]);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            return response(['message' => 'Ошибка поиска события'], 500);
        }
    }

    public function removeParticipate(Request $request): Response
    {
        try {
            try {
                $request->validate([
                    'event_id' => 'required|int',
                ]);
            } catch (\Exception $exception) {
                Log::error($exception->getMessage());
                return response(['message' => 'Ошибка валидации данных'], 500);
            }

            DB::table('event_users')
                ->where('event_id', $request->get('event_id'))
                ->where('user_id', $this->user->id)
                ->delete();

            return response(['message' => 'Связка участника успешно удалено'], 200);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            return response(['message' => 'Ошибка при удалении участника'], 500);
        }
    }

    public function addParticipate(Request $request): Response
    {
        try {
            try {
                $request->validate([
                    'event_id' => 'required|int',
                ]);
            } catch (\Exception $exception) {
                Log::error($exception->getMessage());
                return response(['message' => 'Ошибка валидации данных'], 500);
            }

            DB::table('event_users')
                ->insertOrIgnore([
                    'event_id' => $request->get('event_id'),
                    'user_id' => $this->user->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

            return response(['message' => 'Участник успешно добавлен'], 200);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            return response(['message' => 'Ошибка при добавлении участника'], 500);
        }
    }
}
