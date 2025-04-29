<?php

namespace App\Http\Controllers;

use App\Models\Question; // Поки не використовуємо, але може знадобитись для оновлення
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use App\Models\User; // Потрібно для тимчасового збереження

class MediaUploadController extends Controller
{
    public function upload(Request $request)
    {
        // Збільшуємо ліміт до 5MB (5 * 1024 = 5120 кілобайт)
        // Або встанови інший потрібний розмір в кілобайтах.
        $maxSizeKb = 5120; // 5 MB

        try {
            // Валідація тепер всередині try, щоб краще логувати помилки
            $validatedData = $request->validate([
                'image' => [
                    'required',
                    'image', // Перевіряє, чи це дійсний файл зображення
                    'mimes:jpeg,png,jpg,gif,svg', // Дозволені типи MIME
                    'max:' . $maxSizeKb // Максимальний розмір у кілобайтах
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Логуємо помилку валідації
            Log::warning('Media upload validation failed.', [
                'user_id' => Auth::id(), // Можна додати ID користувача
                'errors' => $e->errors(),
                'file_info' => $request->hasFile('image') ? [
                    'original_name' => $request->file('image')->getClientOriginalName(),
                    'size' => $request->file('image')->getSize(), // Розмір в байтах
                    'mime_type' => $request->file('image')->getMimeType(),
                ] : 'No file uploaded or invalid field name'
            ]);
            // Повертаємо помилку валідації клієнту
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422); // 422 Unprocessable Content
        }


        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if (!method_exists($user, 'addMediaFromRequest')) {
            Log::error('User model does not use HasMedia trait.');
            return response()->json(['message' => 'Server configuration error.'], 500);
        }

        try {
            Log::debug("Attempting to upload media for user {$user->id}");
            $media = $user->addMediaFromRequest('image')
                ->toMediaCollection('temporary_uploads');

            Log::info("Temporary media uploaded by user {$user->id}. Media ID: {$media->id}, File: {$media->file_name}");

            return response()->json([
                'message' => 'File uploaded successfully',
                'url' => $media->getFullUrl(),
                'media_id' => $media->id
            ], 201);

        } catch (FileDoesNotExist $e) {
            Log::error("Upload error (FileDoesNotExist) for user {$user->id}: " . $e->getMessage());
            return response()->json(['message' => 'File does not exist.'], 400);
        } catch (FileIsTooBig $e) {
            // Це виключення може не спрацювати, якщо валідація 'max' вже його перехопила, але залишимо про всяк випадок
            Log::error("Upload error (FileIsTooBig) for user {$user->id}: " . $e->getMessage());
            // Повідомлення з валідації буде більш інформативним
            return response()->json(['message' => "File is too big. Max size: " . ($maxSizeKb / 1024) . "MB."], 413);
        } catch (\Spatie\MediaLibrary\MediaCollections\Exceptions\FileUnacceptableForCollection $e) {
            // Обробка помилки типу файлу (якщо є обмеження в registerMediaCollections)
            Log::error("Upload error (FileUnacceptableForCollection) for user {$user->id}: " . $e->getMessage());
            return response()->json(['message' => 'Invalid file type.'], 415); // 415 Unsupported Media Type
        } catch (\Throwable $e) { // Ловимо будь-який інший Throwable
            Log::error("Generic upload error for user {$user->id}: " . $e->getMessage(), [
                'exception_type' => get_class($e),
                'trace' => $e->getTraceAsString() // Додаємо повний трейс в лог
            ]);
            // Повертаємо загальну помилку, але тепер логи мають дати більше інформації
            return response()->json(['message' => 'Could not upload file.'], 500);
        }
    }
}
