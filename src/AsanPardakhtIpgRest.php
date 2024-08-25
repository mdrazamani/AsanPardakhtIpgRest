<?php 

namespace mdrazamani\AsanPardakhtIpgRest;

use GuzzleHttp\Client;
use DateTime;
use JsonSerializable;

class AsanPardakhtIpgRest implements JsonSerializable
{
    private const URL = 'https://ipgrest.asanpardakht.ir';

    private $client;
    private $config;
    private $transactionId;
    private $amount;

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

        return $this->callAPI('POST', 'v1/Token', ['json' => $data]);
    }

    public function verify($transId)
    {
        $data = [
            'merchantConfigurationId' => $this->config['merchantConfigID'],
            'payGateTranId' => $transId,
        ];

        return $this->callAPI('POST', 'v1/Verify', ['json' => $data]);
    }

    public function settlement($transId)
    {
        $data = [
            'merchantConfigurationId' => $this->config['merchantConfigID'],
            'payGateTranId' => $transId,
        ];

        return $this->callAPI('POST', 'v1/Settlement', ['json' => $data]);
    }

    // public function tranResult()
    // {
    //     try {
    //         $res = $this->callAPI('GET', 'v1/TranResult', [
    //             'query' => [
    //                 'merchantConfigurationId' => $this->config['merchantConfigID'],
    //                 'localInvoiceId' => $this->transactionId
    //             ]
    //         ]);
    
    //         error_log('Raw tranResult API response: ' . print_r($res, true));
    
    //         if (is_array($res) && isset($res['code']) && isset($res['content'])) {
    //             return [
    //                 'code' => $res['code'],
    //                 'content' => json_decode($res['content'], true)
    //             ];
    //         } else {
    //             error_log('Invalid API response format: ' . print_r($res, true));
    //             throw new \Exception('Invalid API response format');
    //         }
    //     } catch (\Exception $e) {
    //         error_log('Error in tranResult: ' . $e->getMessage());
    //         error_log('Stack trace: ' . $e->getTraceAsString());
    
    //         return [
    //             'code' => 500,
    //             'error' => $e->getMessage()
    //         ];
    //     }
    // }


    public function tranResult()
    {
        try {
            
            $url = self::URL . '/v1/TranResult?' . http_build_query([
                'merchantConfigurationId' => $this->config['merchantConfigID'],
                'localInvoiceId' => $this->transactionId
            ]);

    
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'Usr: ' . $this->config['username'],
                'Pwd: ' . $this->config['password']
            ]);

            $response = curl_exec($curl);
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            
            if (curl_errno($curl)) {
                return [
                    'code' => curl_errno($curl),
                    'error' => curl_error($curl)
                ];
            }

            if ($httpcode == 200) {
                return [
                    'code' => 200,
                    'content' => json_decode($response, true)
                ];
            } else {
                return [
                    'code' => $httpcode,
                    'error' => 'Unexpected HTTP code received'
                ];
            }
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'error' => $e->getMessage()
            ];
        }
    }



    public function redirect($token, $mobile = null)
    {
        echo '<html><body><script language="javascript" type="text/javascript">
            function postRefId(refIdValue, mobile) {
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

    protected function callAPI($method, $endpoint, $options = [])
    {
        try {
            $headers = [
                'Accept' => 'application/json',
                'Usr' => $this->config['username'],
                'Pwd' => $this->config['password'],
            ];

            $requestOptions = ['headers' => $headers];

            if ($method === 'GET' && isset($options['query'])) {
                $requestOptions['query'] = $options['query'];
            } elseif ($method !== 'GET' && isset($options['json'])) {
                $requestOptions['json'] = $options['json'];
            }

            $response = $this->client->request($method, $endpoint, $requestOptions);

            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage(), 'code' => $e->getCode()];
        }
    }

    // Implementing the JsonSerializable interface to control JSON serialization
    public function jsonSerialize(): mixed
    {
        return [
            'transactionId' => $this->transactionId,
            'amount' => $this->amount,
            'config' => $this->config,
        ];
    }

}
