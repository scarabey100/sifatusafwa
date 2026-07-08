<?php
/**
 * License
 * @author mnemonic88uk
 * @copyright 2024 mnemonic88uk
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace MncKlevu\Klevu;

class Client
{
    /**
     * @var string
     */
    protected $restAuthKey;

    /**
     * @param string $restAuthKey
     */
    public function __construct($restAuthKey)
    {
        $this->restAuthKey = $restAuthKey;
    }

    /**
     * @param string $xml
     *
     * @return array|false
     */
    protected function convertXmlToArray($xml)
    {
        if ($object = @simplexml_load_string($xml)) {
            return (array)@json_decode(json_encode((array)$object), true);
        }

        return false;
    }

    /**
     * @param array $data
     * @param array $information
     *
     * @return Response
     */
    protected function buildResponse(array $data, array $information)
    {
        $response = new Response();

        if ($data) {
            if (isset($data['status'])) {
                $response->setStatus($data['status']);
                unset($data['status']);
            }

            if ($response->getStatus() === Response::STATUS_ERROR) {
                if (isset($data['msg'])) {
                    $response->setError($data['msg']);
                    unset($data['msg']);
                } else {
                    $response->setError('Unknown error.');
                }
            }

            $response->setData($data);
        }
        
        if (isset($information['http_code'])) {
            $response->setCode($information['http_code']);
        }

        return $response;
    }

    /**
     * @param string $action
     * @param string $xml
     *
     * @return Response
     */
    protected function execute($action, $xml = '')
    {
        $handle = curl_init();

        $httpHeader = [
            'Authorization: ' . $this->restAuthKey,
            'Content-Type: application/xml',
        ];

        curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($handle, CURLOPT_POSTFIELDS, (string)$xml);
        curl_setopt($handle, CURLOPT_HTTPHEADER, $httpHeader);
        curl_setopt($handle, CURLOPT_URL, 'http://rest.klevu.com/rest/service/' . $action);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, 0);

        $result = curl_exec($handle);
        $information = curl_getinfo($handle);

        curl_close($handle);

        $data = $this->convertXmlToArray($result, true);
        if (!is_array($data)) {
            $data = ['data' => $result];
        }

        return $this->buildResponse($data, $information);
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return (string)$this->execute('startSession')->getValue('sessionId');
    }

    /**
     * @param RequestData $data
     *
     * @return Response|null
     */
    public function updateRecords(RequestData $data)
    {
        return $this->execute('updateRecords', $data->getXml());
    }

    /**
     * @param RequestData $data
     *
     * @return Response|null
     */
    public function deleteRecords(RequestData $data)
    {
        return $this->execute('deleteRecords', $data->getXml());
    }
}
