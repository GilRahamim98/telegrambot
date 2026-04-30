<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $telegramId = base64_decode($request->query('user'));
        $user = User::where('telegram_id', $telegramId)->firstOrFail();

        $workouts = $user->workouts()->orderBy('date', 'desc')->get()->groupBy('date');
        $meals = $user->meals()->orderBy('date', 'desc')->get()->groupBy('date');

        // Combine all dates for a unified timeline
        $allDates = $workouts->keys()->concat($meals->keys())->unique()->sortDesc();

        return view('dashboard', compact('user', 'workouts', 'meals', 'allDates'));
    }

    public function update(Request $request, $type, $id)
    {
        $data = $request->validate([
            'exercise' => 'nullable|string',
            'weight' => 'nullable|numeric',
            'reps' => 'nullable|integer',
            'sets' => 'nullable|integer',
            'description' => 'nullable|string',
            'calories' => 'nullable|numeric',
            'protein' => 'nullable|numeric',
            'carbs' => 'nullable|numeric',
            'fats' => 'nullable|numeric',
        ]);

        if ($type === 'workout') {
            \App\Models\Workout::findOrFail($id)->update($data);
        } else {
            \App\Models\Meal::findOrFail($id)->update($data);
        }

        return back()->with('success', 'Updated successfully!');
    }

    public function destroy($type, $id)
    {
        if ($type === 'workout') {
            \App\Models\Workout::findOrFail($id)->delete();
        } else {
            \App\Models\Meal::findOrFail($id)->delete();
        }

        return back()->with('success', 'Deleted successfully!');
    }
}
