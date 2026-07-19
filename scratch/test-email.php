<?php
use Illuminate\Support\Facades\Mail;

try {
    Mail::raw('Test email from EzPAIzy via SendGrid!', function ($message) {
        $message->to('denhakim233@gmail.com')
                ->subject('EzPAIzy SendGrid Connection Test');
    });
    echo "SUCCESS: Email sent successfully through SendGrid!\n";
} catch (\Exception $e) {
    echo "ERROR: Failed to send email. Details:\n";
    echo $e->getMessage() . "\n";
}
