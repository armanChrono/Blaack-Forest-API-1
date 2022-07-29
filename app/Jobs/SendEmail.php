<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use Mail;
use Barryvdh\DomPDF\Facade as PDF;


class SendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mail_data;
    protected $type;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($mail_data, $type)
    {
        $this->mail_data = $mail_data;
        $this->type = $type;
    }
    /**
     * Execute the job.
     *
     * @return void
     */
      public function handle()
    {
        $type = $this->type;
        $order = $this->mail_data;
        $input['subject'] = "this is the subject";

             $input['email'] = "arman.chronoinfotech@gmail.com";
            $input['name'] = "arfaaa";
        if($type == 'Submitted'){
            Mail::send('submitted',$order->toArray(), function ($message)  use ($order)
            {
                $message->to($order->email, $order->billing_name)
                    ->subject('Blaack Forest - Order No. #'.$order->order_id.' Submitted');
                $message->from('online@blaackforestcakes.com', 'Blaack Forest');
            });
        }else if($type == 'Confirmed'){
            Mail::send('confirmed',$order->toArray(), function ($message)  use ($order)
            {
                $message->to($order->email, $order->billing_name)
                    ->subject('Blaack Forest - Order No. #'.$order->order_id.' Confirmed');
                $message->from('online@blaackforestcakes.com', 'Blaack Forest');
            });
        }else if($type == 'Delivered'){
            $data["email"] = "arman.chronoinfotech@gmail.com";
            $data["title"] = "sample pdf";
            $data["body"] = "This is Demo";
      
            $pdf = PDF::loadView('invoice-pdf', $order->toArray());
            // Log::info(json_encode($order->toArray()));
            Mail::send('delivered', $order->toArray(), function($message)use($order, $pdf) {
                $message->to($order->email, $order->billing_name)
                        ->subject('Blaack Forest - Order No. #'.$order->order_id.' Delivered')
                        ->attachData($pdf->output(), "invoice.pdf");
            });
      
          
        }else if($type == 'Hold'){
            Mail::send('hold_unhold',$order->toArray(), function ($message)  use ($order)
            {
                $message->to($order->email, $order->billing_name)
                    ->subject('Blaack Forest - Order No. #'.$order->order_id.' Hold');
                $message->from('online@blaackforestcakes.com', 'Blaack Forest');
            });
        }else if($type == 'Un-Hold'){
            Mail::send('hold_unhold',$order->toArray(), function ($message)  use ($order)
            {
                $message->to($order->email, $order->billing_name)
                    ->subject('Blaack Forest - Order No. #'.$order->order_id.' Un-Hold');
                $message->from('online@blaackforestcakes.com', 'Blaack Forest');
            });
        }else if($type == 'Cancelled'){
           
             $pdf = PDF::loadView('credit-memo', $order->toArray());
             Mail::send('cancelled', $order->toArray(), function($message)use( $pdf, $order) {
                $message->to($order->email, $order->billing_name)
                ->subject('Blaack Forest - Order No. #'.$order->order_id.' Cancelled')
                ->attachData($pdf->output(), "credit-memo.pdf");
            });
       

        }else if($type == 'ShopAccepted'){
            Mail::send('readyForDelivery',$order->toArray(), function ($message)  use ($order)
            {
                $message->to($order->email, $order->billing_name)
                    ->subject('Blaack Forest - Order No. #'.$order->order_id);
                $message->from('online@blaackforestcakes.com', 'Blaack Forest');
            });
        }

         
     }
}
