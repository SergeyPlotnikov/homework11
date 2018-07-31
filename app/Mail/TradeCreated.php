<?php

namespace App\Mail;

use App\Entity\Trade;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class TradeCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $trade;

    /**
     * Create a new message instance.
     * @param Trade $trade
     * @return void
     */
    public function __construct(Trade $trade)
    {
        $this->trade = $trade;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(['address' => 'serhiiplornikov@ukr.net', 'name' => 'Currency market'])
            ->view('emails.make-trade')
            ->with([
                'amount'=>$this->trade->amount,
                'lot_id'=>$this->trade->lot_id
            ])
            ->subject('Success trade');
    }
}
