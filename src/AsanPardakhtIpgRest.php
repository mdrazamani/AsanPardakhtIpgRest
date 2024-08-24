<?php 

namespace mdrazamani\AsanPardakhtIpgRest;

use GuzzleHttp\Client;
use DateTime;

class AsanPardakhtIpgRest
{
    private const URL = 'https://ipgrest.asanpardakht.ir';

    private $client;
    private $config;

    public function __construct(array $config)
    {
        $this->client = new Client(['base_uri' => self::URL]);
        $this->config = $config;
        
        // Log initialization
        if (class_exists('\Illuminate\Support\Facades\Log')) {
            \Illuminate\Support\Facades\Log::info('AsanPardakhtIpgRest initialized with config', $config);
        }
    }

    public function init($invoiceId, $amount)
    {
        $this->invoiceId = $invoiceId;
        $this->amount = $amount;

        // Log init method
        if (class_exists('\Illuminate\Support\Facades\Log')) {
            \Illuminate\Support\Facades\Log::info('Init method called', ['invoiceId' => $invoiceId, 'amount' => $amount]);
        }

        return $this;
    }

    public function token()
    {
        $callbackUrl = $this->config['callback_url'] . (strpos($this->config['callback_url'], '?') === false ? '?' : '&');
        $data = [
            'serviceTypeId' => 1,
            'merchantConfigurationId' => $this->config['merchantConfigID'],
            'localInvoiceId' => $this->invoiceId,
            'amountInRials' => $this->amount,
            'localDate' => (new DateTime('now', new \DateTimeZone('Asia/Tehran')))->format('Ymd His'),
            'callbackURL' => $callbackUrl . http_build_query(['invoice' => $this->invoiceId]),
            'paymentId' => 0,
            'additionalData' => '',
        ];

        // Log token method call
        if (class_exists('\Illuminate\Support\Facades\Log')) {
            \Illuminate\Support\Facades\Log::info('Token method called with data', $data);
        }

        return $this->callAPI('POST', 'v1/Token', $data);
    }

    public function verify($transId)
    {
        $data = [
            'merchantConfigurationId' => $this->config['merchantConfigID'],
            'payGateTranId' => $transId,
        ];

        // Log verify method call
        if (class_exists('\Illuminate\Support\Facades\Log')) {
            \Illuminate\Support\Facades\Log::info('Verify method called with data', $data);
        }

        return $this->callAPI('POST', 'v1/Verify', $data);
    }

    public function settlement($transId)
    {
        $data = [
            'merchantConfigurationId' => $this->config['merchantConfigID'],
            'payGateTranId' => $transId
        ];

        // Log settlement method call
        if (class_exists('\Illuminate\Support\Facades\Log')) {
            \Illuminate\Support\Facades\Log::info('Settlement method called with data', $data);
        }

        return $this->callAPI('POST', 'v1/Settlement', $data);
    }

    public function redirect($token, $mobile = null)
    {
        if (class_exists('\Illuminate\Support\Facades\Log')) {
            \Illuminate\Support\Facades\Log::info('Redirect method called', ['token' => $token, 'mobile' => $mobile]);
        }

        echo '<html><body><script language="javascript" type="text/javascript">
            function postRefId(refIdValue,mobile) {
                var form = document.createElement("form");
                form.setAttribute("method", "POST");
                form.setAttribute("action", "https://asan.shaparak.ir");
                form.setAttribute("target", "_self");
                var hiddenField = document.createElement("input");
                hiddenField.setAttribute("name", "RefId");
                hiddenField.setAttribute("value", refIdValue);
                form.appendChild(hiddenField);
                var mobileField = document.createElement("input");
                mobileField.setAttribute("name", "mobileap");
                mobileField.setAttribute("value", mobile);
                form.appendChild(mobileField);
                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            }
            postRefId("'.$token.'","'.$mobile.'");
        </script></body></html>';
    }

    protected function callAPI($method, $endpoint, $data = [])
    {
        try {
            if (class_exists('\Illuminate\Support\Facades\Log')) {
                \Illuminate\Support\Facades\Log::info('callAPI method called', ['method' => $method, 'endpoint' => $endpoint, 'data' => $data]);
            }

            $response = $this->client->request($method, $endpoint, [
                'json' => $data,
                'headers' => [
                    'Accept' => 'application/json',
                    'Usr' => $this->config['username'],
                    'Pwd' => $this->config['password'],
                ],
            ]);

            if (class_exists('\Illuminate\Support\Facades\Log')) {
                \Illuminate\Support\Facades\Log::info('API response received', ['response_body' => (string) $response->getBody()]);
            }

            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            if (class_exists('\Illuminate\Support\Facades\Log')) {
                \Illuminate\Support\Facades\Log::error('API call failed', ['error' => $e->getMessage(), 'code' => $e->getCode()]);
            }

            return ['error' => $e->getMessage(), 'code' => $e->getCode()];
        }
    }
}
