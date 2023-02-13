<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $user;
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        $html_content = EmailTemplate::where([
            "type" => "email_verification_mail",
            "is_active" => 1

        ])->first()->template();


        $html_content =  str_replace("{{dynamic-username}}", $this->user->first_Name, $html_content );

        $html_content =  str_replace("{{dynamic-verify-link}}", (env('APP_URL').'/activate/'.$this->user->email_verify_token),
         $html_content );


        return $this->view('email.dynamic_mail',["html_content"=>$html_content]);
    }
}
