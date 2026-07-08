<?php
/**
 * Copyright 2024 LÍNEA GRÁFICA E.C.E S.L.
 *
 * @author    Línea Gráfica E.C.E. S.L.
 * @copyright Lineagrafica.es - Línea Gráfica E.C.E. S.L. all rights reserved.
 * @license   https://www.apache.org/licenses/LICENSE-2.0
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class LGFreeShippingZonesFreeShippingModuleFrontController extends ModuleFrontController
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->ajax) {
            Tools::redirect('index');
        }
    }

    public function displayAjaxRecalculate()
    {
        $params = [
            'cart' => $this->context->cart,
            'lgfreshippingzones_is_ajax' => true,
        ];

        $response = [
            'html' => $this->module->render($params),
        ];

        self::returnResponse($response);
    }

    /**
     * return de Ajax response
     *
     * @param $response
     * @param int $status_code
     */
    public static function returnResponse($response, $status_code = 200)
    {
        if (!headers_sent()) {
            self::httpResponseCode($status_code);

            header('Content-Type: application/json');
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Cache-Control: post-check=0, pre-check=0', false);
            header('Pragma: no-cache');
        }

        if (!empty($response) || trim($response) != '' || !is_null($response)) {
            exit(self::jsonEncode($response));
        } else {
            exit;
        }
    }

    /**
     * set response code according to php version
     *
     * @param null $code
     * @return int|null
     */
    public static function httpResponseCode($code = null)
    {
        if (!function_exists('http_response_code')) {
            if ($code !== null) {
                switch ($code) {
                    case 100:
                        $text = 'Continue';

                        break;

                    case 101:
                        $text = 'Switching Protocols';

                        break;

                    case 200:
                        $text = 'OK';

                        break;

                    case 201:
                        $text = 'Created';

                        break;

                    case 202:
                        $text = 'Accepted';

                        break;

                    case 203:
                        $text = 'Non-Authoritative Information';

                        break;

                    case 204:
                        $text = 'No Content';

                        break;

                    case 205:
                        $text = 'Reset Content';

                        break;

                    case 206:
                        $text = 'Partial Content';

                        break;

                    case 300:
                        $text = 'Multiple Choices';

                        break;

                    case 301:
                        $text = 'Moved Permanently';

                        break;

                    case 302:
                        $text = 'Moved Temporarily';

                        break;

                    case 303:
                        $text = 'See Other';

                        break;

                    case 304:
                        $text = 'Not Modified';

                        break;

                    case 305:
                        $text = 'Use Proxy';

                        break;

                    case 400:
                        $text = 'Bad Request';

                        break;

                    case 401:
                        $text = 'Unauthorized';

                        break;

                    case 402:
                        $text = 'Payment Required';

                        break;

                    case 403:
                        $text = 'Forbidden';

                        break;

                    case 404:
                        $text = 'Not Found';

                        break;

                    case 405:
                        $text = 'Method Not Allowed';

                        break;

                    case 406:
                        $text = 'Not Acceptable';

                        break;

                    case 407:
                        $text = 'Proxy Authentication Required';

                        break;

                    case 408:
                        $text = 'Request Time-out';

                        break;

                    case 409:
                        $text = 'Conflict';

                        break;

                    case 410:
                        $text = 'Gone';

                        break;

                    case 411:
                        $text = 'Length Required';

                        break;

                    case 412:
                        $text = 'Precondition Failed';

                        break;

                    case 413:
                        $text = 'Request Entity Too Large';

                        break;

                    case 414:
                        $text = 'Request-URI Too Large';

                        break;

                    case 415:
                        $text = 'Unsupported Media Type';

                        break;

                    case 500:
                        $text = 'Internal Server Error';

                        break;

                    case 501:
                        $text = 'Not Implemented';

                        break;

                    case 502:
                        $text = 'Bad Gateway';

                        break;

                    case 503:
                        $text = 'Service Unavailable';

                        break;

                    case 504:
                        $text = 'Gateway Time-out';

                        break;

                    case 505:
                        $text = 'HTTP Version not supported';

                        break;

                    default:
                        $text = 'Unknown http status code "' . htmlentities($code) . '"';

                        break;
                }

                $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');

                header($protocol . ' ' . $code . ' ' . $text);

                $GLOBALS['http_response_code'] = $code;
            } else {
                $code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);
            }

            return $code;
        } else {
            http_response_code($code);
        }
    }

    public static function jsonEncode($data, $options = 0, $depth = 512)
    {
        return method_exists('Tools', 'jsonEncode') ?
            Tools::jsonEncode($data) :
            json_encode($data, $options, $depth);
    }

    public static function jsonDecode($data, $assoc = false, $depth = 512, $options = 0)
    {
        return method_exists('Tools', 'jsonDecode') ?
            Tools::jsonDecode($data, $assoc) :
            json_decode($data, $assoc, $depth, $options);
    }
}
