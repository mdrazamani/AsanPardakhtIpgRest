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

    public function init($transactionId, $amount)
    {
        $this->transactionId = $transactionId;
        $this->amount = $amount;

       

        return $this;
    }

    public function token()
    {
        $callbackUrl = $this->config['callback_url'] . (strpos($this->config['callback_url'], '?') === false ? '?' : '&');
        $data = [
            'serviceTypeId' => 1,
            'merchantConfigurationId' => $this->config['merchantConfigID'],
            'localInvoiceId' => $this->transactionId,
            'amountInRials' => $this->amount,
            'localDate' => (new DateTime('now', new \DateTimeZone('Asia/Tehran')))->format('Ymd His'),
            'callbackURL' => $callbackUrl . http_build_query(['invoice' => $this->transactionId]),
            'paymentId' => 0,
            'additionalData' => '',
        ];

      

        return $this->callAPI('POST', 'v1/Token', $data);
    }

    public function verify($transId)
    {
        $data = [
            'merchantConfigurationId' => $this->config['merchantConfigID'],
            'payGateTranId' => $transId,
        ];

      

        return $this->callAPI('POST', 'v1/Verify', $data);
    }

    public function settlement($transId)
    {
        $data = [
            'merchantConfigurationId' => $this->config['merchantConfigID'],
            'payGateTranId' => $transId
        ];

      

        return $this->callAPI('POST', 'v1/Settlement', $data);
    }


    public function tranResult()
    {
        $res = $this->callAPI('GET','v1/Settlement?'.http_build_query([
                'merchantConfigurationId' => $this->config['merchantConfigID'],
                'localInvoiceId' => $this->transactionId
            ]));
        return ['code' => $res['code'],'content' => json_decode($res['content'],true)];
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
