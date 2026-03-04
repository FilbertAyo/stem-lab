<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestMailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test {email : The recipient email address}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email to verify SMTP/mail configuration';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error("Invalid email address: {$email}");

            return self::FAILURE;
        }

        $this->info("Sending test email to {$email}...");

        try {
            Mail::raw(
                "This is a test email from " . config('app.name') . ".\n\nIf you received this, your mail configuration is working correctly.\n\nSent at: " . now()->toDateTimeString(),
                function ($message) use ($email) {
                    $message->to($email)
                        ->subject('[' . config('app.name') . '] Test email');
                }
            );

            $this->info('Test email sent successfully.');
            $this->comment('Check the inbox (and spam folder) for: ' . $email);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed to send test email.');
            $this->error($e->getMessage());
            $this->newLine();
            $this->comment('Check MAIL_* in .env, then run: php artisan config:clear && php artisan config:cache');

            return self::FAILURE;
        }
    }
}
