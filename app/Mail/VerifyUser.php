<?php

namespace App\Mail;

use App\PendingUser;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class VerifyUser extends Mailable
{
    use Queueable, SerializesModels;

    /** @var PendingUser */
    public $pendingUser;

    /**
     * Create a new message instance.
     *
     * @param PendingUser $pendingUser
     */
    public function __construct(PendingUser $pendingUser)
    {
        $this->pendingUser = $pendingUser;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(config('mail.from.address'), config('mail.from.name'))#
            ->to($this->pendingUser->email, $this->pendingUser->username)
            ->subject('Account Bestätigen')
            ->markdown('emails.verify_user')
            ->with(['verificationUrl' => $this->pendingUser->getVerificationUrl()]);
    }
}
