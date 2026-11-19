<?php

namespace App\Providers;

use App\Models\TransactionNotification;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::toMailUsing(function ($notifiable, string $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new MailMessage)
                ->subject('Globaltronics WMS - Password Reset Authorization')
                ->greeting('Hello '.$notifiable->name.',')
                ->line('You are receiving this email because a password reset request was initiated for your Globaltronics Warehouse Management System account.')
                ->action('Reset Account Password', $url)
                ->line('This password reset link will expire in '.config('auth.passwords.users.expire', 60).' minutes.')
                ->line('If you did not request a password reset, no further action is required.')
                ->salutation("Best regards,\nGlobaltronics Security Operations");
        });

        View::composer('*', function ($view) {
            if (Auth::check()) {
                try {
                    $userId = Auth::id();
                    $notifications = TransactionNotification::where(function ($q) use ($userId) {
                        $q->whereNull('user_id')->orWhere('user_id', $userId);
                    })
                        ->latest()
                        ->take(15)
                        ->get();

                    $unreadCount = TransactionNotification::where(function ($q) use ($userId) {
                        $q->whereNull('user_id')->orWhere('user_id', $userId);
                    })
                        ->where('is_read', false)
                        ->count();

                    $threshold = now()->subMinutes(5)->timestamp;
                    $onlineUsersCount = max(1, (int) \Illuminate\Support\Facades\DB::table('sessions')
                        ->whereNotNull('user_id')
                        ->where('last_activity', '>=', $threshold)
                        ->distinct('user_id')
                        ->count('user_id'));

                    $view->with([
                        'headerNotifications' => $notifications,
                        'unreadNotificationCount' => $unreadCount,
                        'onlineUsersCount' => $onlineUsersCount,
                    ]);
                } catch (\Throwable $e) {
                    $view->with([
                        'headerNotifications' => collect(),
                        'unreadNotificationCount' => 0,
                        'onlineUsersCount' => 1,
                    ]);
                }
            } else {
                $view->with([
                    'headerNotifications' => collect(),
                    'unreadNotificationCount' => 0,
                    'onlineUsersCount' => 0,
                ]);
            }
        });
    }
}
