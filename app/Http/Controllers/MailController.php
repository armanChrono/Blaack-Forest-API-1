<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mail;
use PDF;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Jobs\NotifyByMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Bus\DispatchesJobs;


class MailController extends Controller
{
    public function txt_mail()
    {
        $info = array(
            'name' => "Alex"
        );
        Mail::send(['text' => 'mail'], $info, function ($message)
        {
            $message->to('arman.chronoinfotech@gmail.com', 'W3SCHOOLS')
                ->subject('Basic test eMail from W3schools.');
            $message->from('armanfahim2409@gmail.com', 'Alex');
        });
        echo "Successfully sent the email";
    }
    public function html_mail($order, $type)
    {       
        // // Log::info("TYPE ==> ".$type);
        // $job = (new NotifyByMail($order, $type))
        // ->delay(now()->addSeconds(15)); 

        // dispatch($job);
        $job = (new \App\Jobs\SendEmail($order,$type))
        ->delay(now()->addSeconds(2)); 

dispatch($job);
       
    }
    public function sendMail($order)
    {
     
        
        $job = (new \App\Jobs\SendEmail($order))
                ->delay(now()->addSeconds(2)); 

        dispatch($job);
        
        dd("Job dispatched.");
    }
    

  

}
