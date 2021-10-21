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
     */
    public function index()
    {
        $tasks = $this->user->tasks()->get(['title', 'description'])->toArray();

        return $tasks;
    }
    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $task = $this->user->tasks()->find($id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, task with id ' . $id . ' cannot be found.'
            ], 400);
        }

        return $task;
    }
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        /*
        $this->validate($request, [
            'title' => 'required',
            'description' => 'required',
            'created' => 'required'
        ]);
        */
        $task = new Task();
        $task->title = $request->title;
        $task->description = $request->description;
        $task->created = $request->created;
        $new_date = new \DateTime();
        $this_date = new \DateTime($task->created);
        $interval = date_diff($new_date, $this_date);
        if ($new_date >  $this_date) {
            $text = 'Новая дата больше текушей вам нельзя создвать задачу';
        } elseif ($new_date < $this_date) {
            $text = 'Новая дата мень текушей вам можно создвать задачу';
        }

        if ($interval->d > 7) {
            $text = 'вы указали дату больше недели';
        }
        if ($new_date < $this_date && $interval->d <= 7) {
            if ($this->user->tasks()->save($task))
                return response()->json([
                    'success' => true,
                    'task' => $task,
                    'now_date' => $new_date,
                    'result_if' => $interval,
                ]);
            else
                return response()->json([
                    'success' => false,
                    'message' => 'Sorry, task could not be added.'
                ], 500);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Вы указали дату в прошлом или больше недели',
            ]);
        }

        //если все нормально и запись был сделан удачно то возврошаем true
        return response()->json([
            'success' => $interval,
            'new_date' => $new_date,
            'this_date' => $this_date,
            'text' => $text,

        ]);
        /*
        if ($this->user->tasks()->save($task))
            return response()->json([
                'success' => true,
                'task' => $task,
                'now_date' => $new_date,
                'result_if' => $interval,
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'Sorry, task could not be added.'
            ], 500);
        */
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
                'message' => 'Sorry, task with id ' . $id . ' cannot be found.'
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
                'message' => 'Sorry, task could not be updated.'
            ], 500);
        }
    }
}
