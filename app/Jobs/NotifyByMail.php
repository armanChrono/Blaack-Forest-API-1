<?php

namespace App\Jobs;

use App\Mail\SendMailable;
use Barryvdh\DomPDF\PDF;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;


class NotifyByMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $order;
    protected $type;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($order, $type)
    {
        $this->order = $order;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info("inside handle");
        $type = $this->type;
        $order = $this->order;
        Log::info("email ==> ".$order->email);
        // if($type == 'Submitted'){
        //     Mail::send('submitted',$order->toArray(), function ($message)  use ($order)
        //     {
        //         $message->to($order->email, $order->billing_name)
        //             ->subject('Blaack Forest - Order No. #'.$order->order_id);
        //         $message->from('online@blaackforestcakes.com', 'Blaack Forest');
        //     });
        // }else if($type == 'Confirmed'){
        //     Mail::send('confirmed',$order->toArray(), function ($message)  use ($order)
        //     {
        //         $message->to($order->email, $order->billing_name)
        //             ->subject('Blaack Forest - Order No. #'.$order->order_id);
        //         $message->from('online@blaackforestcakes.com', 'Blaack Forest');
        //     });
        // }else if($type == 'Delivered'){
        //     $data["email"] = "arman.chronoinfotech@gmail.com";
        //     $data["title"] = "sample pdf";
        //     $data["body"] = "This is Demo";
      
        //     $pdf = PDF::loadView('invoice-pdf', $order->toArray());
        //     // Log::info(json_encode($order->toArray()));
        //     Mail::send('delivered', $order->toArray(), function($message)use($order, $pdf) {
        //         $message->to($order->email, $order->billing_name)
        //                 ->subject('Blaack Forest - Order No. #')
        //                 ->attachData($pdf->output(), "invoice.pdf");
        //     });
      
          
        // }else if($type == 'Hold'){
        //     Mail::send('hold_unhold',$order->toArray(), function ($message)  use ($order)
        //     {
        //         $message->to($order->email, $order->billing_name)
        //             ->subject('Blaack Forest - Order No. #'.$order->order_id);
        //         $message->from('online@blaackforestcakes.com', 'Blaack Forest');
        //     });
        // }else if($type == 'Un-Hold'){
        //     Mail::send('hold_unhold',$order->toArray(), function ($message)  use ($order)
        //     {
        //         $message->to($order->email, $order->billing_name)
        //             ->subject('Blaack Forest - Order No. #'.$order->order_id);
        //         $message->from('online@blaackforestcakes.com', 'Blaack Forest');
        //     });
        // }else if($type == 'Cancelled'){
        //     $data["email"] = "arman.chronoinfotech@gmail.com";
        //     $data["title"] = "sample pdf";
        //     $data["body"] = "This is Demo";
        //      $pdf = PDF::loadView('credit-memo', $order->toArray());
        //      Mail::send('cancelled', $order->toArray(), function($message)use($data, $pdf, $order) {
        //         $message->to($order->email, $order->billing_name)
        //         ->subject('Blaack Forest - Order No. #'.$order->order_id)
        //         ->attachData($pdf->output(), "credit-memo.pdf");
        //     });
       

        // }else if($type == 'ShopAccepted'){
        //     Mail::send('readyForDelivery',$order->toArray(), function ($message)  use ($order)
        //     {
        //         $message->to($order->email, $order->billing_name)
        //             ->subject('Blaack Forest - Order No. #'.$order->order_id);
        //         $message->from('online@blaackforestcakes.com', 'Blaack Forest');
        //     });
        // }
       
        Mail::to('arman.chronoinfotech@gmail.com')->send(new SendMailable());
    }
}
