<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PasswordResetController extends Controller
{
    /**
     * Відправити токен для відновлення пароля (в лог або на пошту)
     */
    public function sendResetToken(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $email = $request->email;
        $token = Str::random(60);

        // Зберігаємо або оновлюємо токен відновлення
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'email' => $email,
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // Оскільки в .env налаштований MAIL_MAILER=log, ми логуємо токен
        // Це дозволяє легко знайти його під час розробки або в системних логах.
        Log::info("Password reset token generated for email: {$email}. Token: {$token}");

        // В реальному середовищі тут відправлявся б Email.
        // Ми повертаємо успішну відповідь.
        return response()->json([
            'message' => 'Token generated and sent to logs/email.',
            'debug_token' => $token // Для локального тестування повертаємо токен відразу, щоб спростити розробку
        ], 200);
    }

    /**
     * Скинути пароль за допомогою токена
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$record) {
            return response()->json(['message' => 'Недійсний токен або email.'], 400);
        }

        // Перевіряємо токен
        if (!Hash::check($request->token, $record->token)) {
            return response()->json(['message' => 'Невірний токен відновлення.'], 400);
        }

        // Оновлюємо пароль користувача
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->password = Hash::make($request->password);
            $user->save();

            // Видаляємо використаний токен
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            Log::info("Password successfully reset for user: {$request->email}");
            return response()->json(['message' => 'Пароль успішно змінено.'], 200);
        }

        return response()->json(['message' => 'Користувача не знайдено.'], 404);
    }
}
