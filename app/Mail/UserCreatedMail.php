<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;
    public $roleName;

    public function __construct(User $user, $password, $roleName)
    {
        $this->user = $user;
        $this->password = $password;
        $this->roleName = $roleName;
    }

    public function build()
    {
        return $this->subject('Welcome to ' . config('app.name') . ' - Your Account Details')
            ->view('emails.user-created');
    }
}
