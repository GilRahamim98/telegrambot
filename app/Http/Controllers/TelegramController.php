<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Models\User;
use App\Models\Workout;
use App\Models\Meal;
use App\Services\AiParsingService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TelegramController extends Controller
{
    protected $aiService;

    public function __construct(AiParsingService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function handle(Request $request)
    {
        try {
            $update = Telegram::commandsHandler(true);
            
            $message = $update->getMessage();
            if (!$message) {
                return response()->json(['status' => 'success']);
            }

            $chatId = $message->getChat()->getId();
            $text = $message->getText();

            // Find or create user
            $user = User::firstOrCreate(
                ['telegram_id' => $chatId],
                [
                    'name' => $message->getFrom()->getFirstName() . ' ' . $message->getFrom()->getLastName(),
                    'email' => $chatId . '@telegram.com', // Placeholder email
                    'password' => bcrypt(str()->random(16))
                ]
            );

            // If it's a command, it's already handled by commandsHandler if registered
            // But we can also handle manual checks here if needed
            if (str_starts_with($text, '/')) {
                $this->handleCommands($text, $chatId, $user);
                return response()->json(['status' => 'success']);
            }

            // NLP Parsing
            $parsed = $this->aiService->parse($text);

            if (!$parsed || !isset($parsed['type'])) {
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => "סליחה, לא הבנתי. נסה משהו כמו '3 סטים של 10 סקוואט' או 'סלט עוף 500 קלוריות'."
                ]);
                return response()->json(['status' => 'success']);
            }

            if ($parsed['type'] === 'workout') {
                $workout = $user->workouts()->create(array_merge($parsed['data'], ['date' => Carbon::today()]));
                $response = "✅ אימון נשמר: {$workout->exercise} ({$workout->sets} סטים של {$workout->reps} ב-{$workout->weight} ק״ג)";
            } elseif ($parsed['type'] === 'meal') {
                $meal = $user->meals()->create(array_merge($parsed['data'], ['date' => Carbon::today()]));
                $response = "🥗 ארוחה נשמרה: {$meal->description} ({$meal->calories} קלוריות, {$meal->protein} גרם חלבון)";
            } else {
                $response = "הממ, אני לא בטוח מה לעשות עם הנתונים האלו.";
            }

            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $response
            ]);

        } catch (\Exception $e) {
            Log::error('Telegram Handler Error: ' . $e->getMessage());
        }

        return response()->json(['status' => 'success']);
    }

    protected function handleCommands($text, $chatId, $user)
    {
        $command = explode(' ', $text)[0];

        switch ($command) {
            case '/start':
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => "ברוך הבא למעקב האימונים והתזונה שלך! 🏋️‍♂️🥗\n\nפשוט תכתוב מה עשית (למשל: '3 סטים של 10 סקוואט עם 60 קילו') או מה אכלת, ואני כבר אדאג לשמור הכל.\n\nפקודות:\n/stats - סיכום יומי\n/dashboard - לינק לדאשבורד האישי שלך"
                ]);
                break;

            case '/stats':
                $todayWorkouts = $user->workouts()->whereDate('date', Carbon::today())->get();
                $todayMeals = $user->meals()->whereDate('date', Carbon::today())->get();

                $stats = "📊 סיכום להיום:\n\n";
                $stats .= "💪 אימונים:\n";
                if ($todayWorkouts->isEmpty()) {
                    $stats .= "- אין אימונים עדיין.\n";
                } else {
                    foreach ($todayWorkouts as $w) {
                        $stats .= "- {$w->exercise}: {$w->sets}x{$w->reps} ב-{$w->weight} ק״ג\n";
                    }
                }

                $totalCalories = $todayMeals->sum('calories');
                $totalProtein = $todayMeals->sum('protein');
                $stats .= "\n🥗 תזונה:\n";
                $stats .= "- סך הכל קלוריות: {$totalCalories} קלוריות\n";
                $stats .= "- סך הכל חלבון: {$totalProtein} גרם\n";

                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => $stats
                ]);
                break;

            case '/dashboard':
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => "🔗 הדאשבורד האישי שלך:\n" . config('app.url') . "/dashboard?user=" . base64_encode($user->telegram_id)
                ]);
                break;
        }
    }
}
