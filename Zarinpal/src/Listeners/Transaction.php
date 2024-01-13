<?php

namespace Webkul\Zarinpal\Listeners;

use Webkul\Sales\Repositories\OrderTransactionRepository;
use Webkul\Zarinpal\Http\Controllers\ZarinpalController;
use Webkul\Zarinpal\Repositories\ZarinpalRepository;

class Transaction
{
    /**
     * Create a new listener instance.
     *
     * @return void
     */
    public function __construct(
        protected ZarinpalRepository $zarinpal,
        protected OrderTransactionRepository $orderTransactionRepository
    ) {
    }

    /**
     * Save the transaction data for online payment.
     *
     * @param \Webkul\Sales\Models\Invoice $invoice
     *
     * @return void
     */
    public function saveTransaction($invoice)
    {
        $data = request()->all();
        if (isset($data['Status']) && $data['Status'] === ZarinpalController::PAYMENT_OK) {
            $transactionDetails = $this->zarinpal->getOrder($invoice->order->id);

            if (
                in_array($transactionDetails->code, [100, 101]) &&
                in_array($transactionDetails->message, ["Verified", "Paid"])
            ) {
                $this->orderTransactionRepository->create([
                    'transaction_id' => $transactionDetails->transaction_id,
                    'status'         => $transactionDetails->status,
                    'type'           => $transactionDetails->fee_type,
                    'amount'         => $transactionDetails->amount,
                    'payment_method' => $invoice->order->payment->method,
                    'order_id'       => $invoice->order->id,
                    'invoice_id'     => $invoice->id,
                    'data'           => json_encode($data),
                ]);
            }
        }

    }
}
