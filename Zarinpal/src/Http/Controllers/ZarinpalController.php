<?php

namespace Webkul\Zarinpal\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Webkul\Checkout\Facades\Cart;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\InvoiceRepository;
use Webkul\Sales\Repositories\OrderItemRepository;
use Webkul\Sales\Repositories\RefundRepository;
use Webkul\Sales\Transformers\OrderResource;
use Webkul\Zarinpal\Models\Zarinpal;
use Webkul\Zarinpal\Repositories\ZarinpalRepository;
use Redirect;
use Illuminate\Support\Facades\Log;

class ZarinpalController extends Controller
{

    const PAYMENT_OK = 'OK';

    const DESCRIPTION_CONTENT = 'User-';

    protected $_config;

    protected $orderRepository;

    protected $invoiceRepository;

    protected $orderItemRepository;

    protected $refundRepository;

    protected $zarinpalRepository;

    protected $client;

    protected $merchantId = null;

    protected $apiBaseUrl = null;

    protected $requestUrl = null;

    protected $sandboxBaseUrl = null;

    protected $redirectUrl = null;

    protected $callbackUrl = null;

    protected $verifyUrl = null;

    protected $active = false;

    protected $isSandboxEnable = false;

    protected $baseUrl = null;


    public function __construct(
        OrderRepository $orderRepository,
        InvoiceRepository $invoiceRepository,
        OrderItemRepository $orderItemRepository,
        RefundRepository $refundRepository,
        ZarinpalRepository $zarinpalRepository
    ) {
        $this->orderRepository     = $orderRepository;
        $this->invoiceRepository   = $invoiceRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->refundRepository    = $refundRepository;
        $this->zarinpalRepository  = $zarinpalRepository;
        $this->apiBaseUrl          = core()->getConfigData('sales.payment_methods.zarinpal.api_base_url');
        $this->sandboxBaseUrl      = core()->getConfigData('sales.payment_methods.zarinpal.sandbox_base_url');
        $this->active              = core()->getConfigData('sales.payment_methods.zarinpal.active');
        $this->isSandboxEnable     = core()->getConfigData('sales.payment_methods.zarinpal.sandbox');
        $this->merchantId          = core()->getConfigData('sales.payment_methods.zarinpal.merchant_id');
        $this->baseUrl             = $this->setBaseUrl();
        $this->requestUrl          = $this->setFullUrl(core()->getConfigData('sales.payment_methods.zarinpal.request_url'));
        $this->verifyUrl           = $this->setFullUrl(core()->getConfigData('sales.payment_methods.zarinpal.verify_url'));
        $this->redirectUrl         = core()->getConfigData('sales.payment_methods.zarinpal.redirect_url');
        $this->callbackUrl         = core()->getConfigData('sales.payment_methods.zarinpal.callback_url');

        $this->client = HTTP::withHeaders([
            'headers' => ['Content-Type' => 'application/json'],
        ]);

        $this->_config = request('_config');
    }

    public function redirect()
    {
        try {
            $code = $this->getTransactionCode();
            if ($code) {
                $url = sprintf($this->redirectUrl . $code);
                return \Redirect::to($url);
            } else {
                session()->flash('error', trans('zarinpal::app.zarinpal.front.failure-get-code', [], 'fa'));
                return redirect()->back();
            }
        } catch (\Exception $e) {
            $error    = explode(PHP_EOL, preg_replace("/[^a-zA-Z0-9\s]/", "", $e->getMessage()));
            $errorMsg = current($error) . '.' . next($error);
            session()->flash('error', trans($errorMsg));
            return redirect()->back();
        }
    }

    private function getTransactionCode(): string|bool
    {
        $cart                        = Cart::getCart();
        $userEmail                   = $cart->shipping_address->email;
        $sendDataForGetAuthorityCode = [
            "merchant_id"  => $this->merchantId,
            "amount"       => $cart->base_grand_total * 10,
            "callback_url" => $this->callbackUrl,
            "description"  => self::DESCRIPTION_CONTENT . $userEmail,
            "metadata"     => [
                "mobile" => $cart->shipping_address->phone,
                "email"  => $cart->shipping_address->email,
            ],
        ];

        $response = $this->client->post($this->requestUrl, $sendDataForGetAuthorityCode);
        $response = json_decode($response->getBody(), true);

        if (
            is_array($response['data'])
            && $response['data']['code'] === 100
            && $response['data']['message'] === "Success"
        ) {
            $this->setHashedCode($sendDataForGetAuthorityCode);
            return $response['data']['authority'];
        }
        return false;
    }

    public function callback()
    {
        $authorityCode = request()->input('Authority');
        $status        = request()->input('Status');
        $securityAlert = [];

        if (
            $authorityCode &&
            $status == self::PAYMENT_OK
        ) {
            $cart              = Cart::getCart();
            $verifyTransaction = $this->verify($authorityCode);
            try {
                if (
                    isset($verifyTransaction['data']['code'])
                    && in_array($verifyTransaction['data']['code'], [100, 101])
                    && in_array($verifyTransaction['data']['message'], ["Verified", "Paid"])
                ) {
                    $findTransaction = Zarinpal::where('transaction_id', $verifyTransaction['data']['ref_id'])->first();
                    if ($findTransaction) {
                        session()->flash('error',
                            trans('zarinpal::app.zarinpal.front.duplicate-transaction', [], 'fa'));
                        $securityAlert['data']['message'] = "Security problem in part 2";
                        $securityAlert['data']['ref_id']  = $verifyTransaction['data']['ref_id'];
                        $securityAlert['data']['email']   = $cart->shipping_address->email;
                        Log::warning('security', $securityAlert);
                        return redirect()->route('shop.checkout.onepage.index');
                    }

                    $data = (new OrderResource($cart))->jsonSerialize();

                    $order = $this->orderRepository->create($data);

                    $params = [
                        'code'           => $verifyTransaction['data']['code'],
                        'message'        => $verifyTransaction['data']['message'],
                        'card_hash'      => $verifyTransaction['data']['card_hash'],
                        'card_pan'       => $verifyTransaction['data']['card_pan'],
                        'transaction_id' => $verifyTransaction['data']['ref_id'],
                        'fee_type'       => $verifyTransaction['data']['fee_type'],
                        'fee'            => $verifyTransaction['data']['fee'],
                        'order_id'       => $order->id,
                        'amount'         => $cart->base_grand_total * 10,
                        'status'         => 'success',
                    ];
                    $this->zarinpalRepository->create($params);
                    Cart::deActivateCart();
                    $this->orderRepository->update(['status' => 'processing'], $order->id);
                    if ($order->canInvoice()) {
                        $this->invoiceRepository->create($this->prepareInvoiceData($order));
                    }
                    session()->flash('order_id', $order->id);

                    return redirect()->route('shop.checkout.onepage.success');
                } else {
                    session()->flash('error', trans('zarinpal::app.zarinpal.front.failure-message', [], 'fa'));
                    return redirect()->route('shop.checkout.onepage.index');
                }
            } catch (\Exception $e) {
                Log::warning('security', [$e->getMessage()]);
                session()->flash('error', trans('zarinpal::app.zarinpal.front.failure-message', [], 'fa'));
                return redirect()->route('shop.checkout.onepage.index');
            }

        } else {
            session()->flash('error', trans('zarinpal::app.zarinpal.front.failure-message', [], 'fa'));
            return redirect()->route('shop.checkout.onepage.index');
        }
    }

    protected function verify(string $authorityCode): array
    {
        $cart              = Cart::getCart();

        if(!$cart){
            $response['data']['message']   = "Security problem in part 0";
            $response['data']['authority'] = $authorityCode;
            Log::warning('security', $response);
            return $response;
        }

        $sendDataForVerify = [
            "merchant_id" => $this->merchantId,
            "amount"      => $cart->base_grand_total * 10,
            "authority"   => $authorityCode,
        ];

        if (!$this->checkHashedCode($cart)) {
            $response['data']['message']   = "Security problem in part 1";
            $response['data']['authority'] = $authorityCode;
            $response['data']['email']     = $cart->shipping_address->email;
            Log::warning('security', $response);
            return $response;
        }

        $response = $this->client->post($this->verifyUrl, $sendDataForVerify);
        return json_decode($response->getBody(), true);
    }

    protected function setHashedCode(array $sendDataForGetAuthorityCode): void
    {
        session([
            $sendDataForGetAuthorityCode['metadata']['email'] => sha1(
                $sendDataForGetAuthorityCode['amount'] .
                $sendDataForGetAuthorityCode['description'] .
                date('H')
            ),
        ]);
    }

    protected function checkHashedCode($cart): bool
    {
        $userEmail     = $cart->shipping_address->email;
        $description   = self::DESCRIPTION_CONTENT . $userEmail;
        $amount        = $cart->base_grand_total * 10;
        $getHashedData = Session::get($userEmail);
        $wanted        = sha1($amount . $description . date('H'));
        //Session::forget($userEmail);
        if ($getHashedData === $wanted) {
            return true;
        } else {
            return false;
        }
    }

    protected function prepareInvoiceData($order): array
    {
        $invoiceData = [
            "order_id" => $order->id,
        ];

        foreach ($order->items as $item) {
            $invoiceData['invoice']['items'][$item->id] = $item->qty_to_invoice;
        }

        return $invoiceData;
    }

    protected function setBaseUrl(): string
    {
        if ($this->isSandboxEnable) {
            $this->baseUrl = $this->sandboxBaseUrl;
        } else {
            $this->baseUrl = $this->apiBaseUrl;
        }
        return $this->baseUrl;
    }

    protected function setFullUrl(string $url): string
    {
        return trim($this->baseUrl, '/ ') . '/' . trim($url, '/ ');
    }
}
