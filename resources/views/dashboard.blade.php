<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gym & Meal Tracker Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-900 text-white min-h-screen p-4 md:p-8" x-data="{ editing: null, editData: {}, deleting: null }">
    <div class="max-w-5xl mx-auto">
        <header class="mb-8 border-b border-gray-800 pb-4 flex justify-between items-end">
            <div>
                <h1 class="text-3xl font-bold text-blue-400">Gym & Meal Tracker</h1>
                <p class="text-gray-400 text-lg">Daily Logs for {{ $user->name }}</p>
            </div>
        </header>

        @if(session('success'))
            <div class="bg-green-600 text-white p-3 rounded mb-6 text-center shadow-lg">
                {{ session('success') }}
            </div>
        @endif

        <div class="space-y-12">
            @foreach($allDates as $date)
                <div class="border-l-2 border-gray-700 pl-6 relative">
                    <div class="absolute w-4 h-4 bg-blue-500 rounded-full -left-[9px] top-0"></div>
                    <h2 class="text-2xl font-bold mb-6 text-gray-200">
                        {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}
                        @if(\Carbon\Carbon::parse($date)->isToday())
                            <span class="ml-2 text-xs bg-blue-600 px-2 py-0.5 rounded-full uppercase">Today</span>
                        @endif
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Workouts -->
                        <div class="space-y-4">
                            <h3 class="text-sm font-bold text-blue-400 uppercase tracking-widest">🏋️‍♂️ Workouts</h3>
                            @forelse($workouts->get($date, []) as $workout)
                                <div class="bg-gray-800 p-4 rounded-xl border border-gray-700 hover:border-blue-500 transition-all group relative">
                                    <div class="absolute top-2 right-2 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button @click="editing = 'workout'; editData = {{ json_encode($workout) }}" class="bg-gray-700 hover:bg-blue-600 p-1.5 rounded text-xs">✏️</button>
                                        <button @click="deleting = {type: 'workout', id: {{ $workout->id }}, name: '{{ $workout->exercise }}'}" class="bg-gray-700 hover:bg-red-600 p-1.5 rounded text-xs">🗑️</button>
                                    </div>
                                    <h4 class="font-bold text-lg capitalize">{{ $workout->exercise }}</h4>
                                    <p class="text-gray-400">{{ $workout->sets }}x{{ $workout->reps }} @ {{ $workout->weight }}kg</p>
                                </div>
                            @empty
                                <p class="text-gray-600 italic text-sm">No workout logged.</p>
                            @endforelse
                        </div>

                        <!-- Meals -->
                        <div class="space-y-4">
                            <h3 class="text-sm font-bold text-green-400 uppercase tracking-widest">🥗 Meals</h3>
                            @forelse($meals->get($date, []) as $meal)
                                <div class="bg-gray-800 p-4 rounded-xl border border-gray-700 hover:border-green-500 transition-all group relative">
                                    <div class="absolute top-2 right-2 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button @click="editing = 'meal'; editData = {{ json_encode($meal) }}" class="bg-gray-700 hover:bg-green-600 p-1.5 rounded text-xs">✏️</button>
                                        <button @click="deleting = {type: 'meal', id: {{ $meal->id }}, name: '{{ $meal->description }}'}" class="bg-gray-700 hover:bg-red-600 p-1.5 rounded text-xs">🗑️</button>
                                    </div>
                                    <h4 class="font-bold text-lg capitalize">{{ $meal->description }}</h4>
                                    <div class="mt-2 flex flex-wrap gap-2 text-[10px] font-bold uppercase">
                                        <span class="bg-green-900/30 text-green-400 px-2 py-0.5 rounded">{{ $meal->calories }} kcal</span>
                                        <span class="bg-blue-900/30 text-blue-400 px-2 py-0.5 rounded">P: {{ $meal->protein }}g</span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-600 italic text-sm">No meal logged.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Edit Modal -->
    <div x-show="editing" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4" x-cloak>
        <div @click.away="editing = null" class="bg-gray-800 border border-gray-700 w-full max-w-md rounded-2xl p-6 shadow-2xl">
            <h3 class="text-xl font-bold mb-4" x-text="editing === 'workout' ? 'Edit Workout' : 'Edit Meal'"></h3>
            <form :action="'/dashboard/update/' + editing + '/' + editData.id" method="POST" class="space-y-4">
                @csrf
                <template x-if="editing === 'workout'">
                    <div class="space-y-4">
                        <input name="exercise" x-model="editData.exercise" class="w-full bg-gray-900 border border-gray-700 rounded p-2 outline-none">
                        <div class="grid grid-cols-3 gap-2">
                            <input type="number" name="sets" x-model="editData.sets" placeholder="Sets" class="bg-gray-900 border border-gray-700 rounded p-2 outline-none">
                            <input type="number" name="reps" x-model="editData.reps" placeholder="Reps" class="bg-gray-900 border border-gray-700 rounded p-2 outline-none">
                            <input type="number" step="0.5" name="weight" x-model="editData.weight" placeholder="kg" class="bg-gray-900 border border-gray-700 rounded p-2 outline-none">
                        </div>
                    </div>
                </template>
                <template x-if="editing === 'meal'">
                    <div class="space-y-4">
                        <input name="description" x-model="editData.description" class="w-full bg-gray-900 border border-gray-700 rounded p-2 outline-none">
                        <div class="grid grid-cols-2 gap-2">
                            <input type="number" name="calories" x-model="editData.calories" placeholder="kcal" class="bg-gray-900 border border-gray-700 rounded p-2 outline-none">
                            <input type="number" name="protein" x-model="editData.protein" placeholder="protein" class="bg-gray-900 border border-gray-700 rounded p-2 outline-none">
                        </div>
                    </div>
                </template>
                <div class="flex gap-2 pt-4">
                    <button type="button" @click="editing = null" class="flex-1 bg-gray-700 p-2 rounded">Cancel</button>
                    <button type="submit" class="flex-1 bg-blue-600 p-2 rounded">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="deleting" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4" x-cloak>
        <div @click.away="deleting = null" class="bg-gray-800 border border-gray-700 w-full max-w-sm rounded-2xl p-6 shadow-2xl text-center">
            <div class="text-4xl mb-4">⚠️</div>
            <h3 class="text-xl font-bold mb-2">Delete Log?</h3>
            <p class="text-gray-400 mb-6 italic" x-text="'Are you sure you want to delete ' + deleting?.name + '?'"></p>
            <form :action="'/dashboard/delete/' + deleting?.type + '/' + deleting?.id" method="POST">
                @csrf
                @method('DELETE')
                <div class="flex gap-2">
                    <button type="button" @click="deleting = null" class="flex-1 bg-gray-700 p-2 rounded">Keep it</button>
                    <button type="submit" class="flex-1 bg-red-600 p-2 rounded">Yes, Delete</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</body>
</html>
