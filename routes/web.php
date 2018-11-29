<?php

use App\Task;
use Illuminate\Http\Request;

// Show Tasks
Route::get('/', function () {
    $tasks = Cache::rememberForever('all_tasks', function () {
        return Task::orderBy('created_at', 'asc')->get();
    });

    $stats = Cache::getMemcached()->getStats();

    return view('tasks', [
        'tasks' => $tasks,
        'stats' => array_pop($stats)
    ]);
});

// Add New Task
Route::post('/task', function (Request $request) {
    // Validate input
    $validator = Validator::make($request->all(), [
        'name' => 'required|max:255',
    ]);

    if ($validator->fails()) {
        return redirect('/')
            ->withInput()
            ->withErrors($validator);
    }

    // Create task
    $task = new Task;
    $task->name = $request->name;
    $task->save();

    Cache::forget('all_tasks');

    return redirect('/');
});

// Delete Task
Route::delete('/task/{task}', function (Task $task) {
  $task->delete();

  Cache::forget('all_tasks');

  return redirect('/');
});
