<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Task;

class TaskController extends Controller
{
    /**
     * @var
     */
    protected $user;

    /**
     * TaskController constructor.
     */
    public function __construct()
    {
        //метод получит токен от объекта запроса и authenticate()метод аутентифицирует пользователя по токену.
        $this->user = JWTAuth::parseToken()->authenticate();
    }

    /**
     * @return mixed
     * Показывает все записи пользователя
     */
    public function index()
    {
        $tasks = $this->user->tasks()->get(['title', 'description', 'created', 'status'])->toArray();

        return $tasks;
    }
    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        //Ищем по id задачу
        $task = $this->user->tasks()->find($id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Извините такой ' . $id . ' не найден.'
            ], 400);
        }

        return $task;
    }
    public function show_date($date1, $date2)
    {
        $dt1 = new \DateTime($date1);
        $dt2 = new \DateTime($date2);
        if ($dt1 < $dt2) {
            $task = Task::where([['created', '>=', $date1], ['created', '<=', $date2]])->get();
            return response()->json([
                'success' => true,
                'task' => $task,
            ]);
        } else {
            //
            return [
                'success' => false,
                'message' => 'Не правильный диапозон.'
            ];
        }
    }
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {

        $this->validate($request, [
            'title' => 'required',
            'description' => 'required',
            'created' => 'required'
        ]);

        $task = new Task();
        $task->title = $request->title;
        $task->description = $request->description;
        $task->created = $request->created;
        $new_date = new \DateTime();
        $this_date = new \DateTime($task->created);
        $interval = date_diff($new_date, $this_date);
        $date = date('Y-m-d');
        $date_created = preg_replace("/\s[0-9]{2}:[0-9]{2}:[0-9]{2}/", '', $task->created);
        $tasks_count = Task::where([['created', 'LIKE', '%' . $date_created . '%'], ['user_id', $this->user->id]])->count();

        //проверяем разнизу времени если дата создание меньше или больше недели то не выполняем сохранение

        if ($new_date < $this_date && $interval->d <= 7) {
            if ($tasks_count <= 4) {
                if ($this->user->tasks()->save($task)) {
                    return response()->json([
                        'success' => true,
                        'task' => $task,
                        'count' => $tasks_count,
                        'user_id' => $this->user->id,
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Запись не возможно добавить.'
                    ], 500);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Задачи превышает 5 записей.'
                ], 500);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Вы указали дату в прошлом или больше недели',
            ], 500);
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $task = $this->user->tasks()->find($id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Задача с ' . $id . ' не возможно найти.'
            ], 400);
        }

        $updated = $task->fill($request->all())->save();

        if ($updated) {
            return response()->json([
                'success' => true
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Запись не возможно обновить'
            ], 500);
        }
    }

    public function destroy($id)
    {
        $task = $this->user->tasks()->find($id);
        if ($task->delete()) {
            return response()->json([
                'success' => true,
                'message' => 'Запись удален'
            ], 200);
        } else {

            return response()->json([
                'success' => false,
                'message' => 'Запись не существует или произошло ошибка'
            ], 400);
        }
    }
}
