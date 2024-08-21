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
    }

    public function init($invoiceId, $amount)
    {
        $this->invoiceId = $invoiceId;
        $this->amount = $amount;
        return $this;
    }

    public function token()
    {
        $callbackUrl = $this->config['callback_url'] . (strpos($this->config['callback_url'], '?') === false ? '?' : '&');
        return $this->callAPI('POST', 'v1/Token', [
            'serviceTypeId' => 1,
            'merchantConfigurationId' => $this->config['merchantConfigID'],
            'localInvoiceId' => $this->invoiceId,
            'amountInRials' => $this->amount,
            'localDate' => (new DateTime('Asia/Tehran'))->format('Ymd His'),
            'callbackURL' => $callbackUrl . http_build_query(['invoice' => $this->invoiceId]),
            'paymentId' => 0,
            'additionalData' => '',
        ]);
    }

    public function verify($transId)
    {
        return $this->callAPI('POST', 'v1/Verify', [
            'merchantConfigurationId' => $this->config['merchantConfigID'],
            'payGateTranId' => $transId,
        ]);
    }


    public function settlement($transId)
    {
        return $this->callAPI('POST', 'v1/Settlement', [
            'merchantConfigurationId' => $this->config['merchantConfigID'],
            'payGateTranId' => $transId
        ]);
    }

    public function redirect($token, $mobile = null)
    {
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

    // Add the rest of the methods like callAPI, TranResult, etc.

    protected function callAPI($method, $endpoint, $data = [])
    {
        try {
            $response = $this->client->request($method, $endpoint, [
                'json' => $data,
                'headers' => [
                    'Accept' => 'application/json',
                    'Usr' => $this->config['username'],
                    'Pwd' => $this->config['password'],
                ],
            ]);

            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage(), 'code' => $e->getCode()];
        }
    }
}
