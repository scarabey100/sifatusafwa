<?php
/**
 * License
 * @author mnemonic88uk
 * @copyright 2024 mnemonic88uk
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

// modules/mncklevu/controllers/front/proxy.php

if (!defined('_PS_VERSION_')) {
    exit;
}
use MncKlevu\PrestaShop\Adapter\Configuration as MncKlevuConfiguration;
class MncklevuProxyModuleFrontController extends ModuleFrontController
{
    public $ajax = true;
    /**
     * @var MncKlevuConfiguration
     */
    protected $configuration;
    public function initContent()
    {
        parent::initContent();
    }
    public function init()
    { 
        parent::init();
    }
    
    public function postProcess()
    {
 
        $this->configuration = new MncKlevuConfiguration($this->module);
        $api_key = $this->configuration->get(
                MncKlevuConfiguration::KEY_JS_API_KEY,
                $this->context->language->id
        );
        $apiv2CloudSearchUrl = $this->configuration->get(
                MncKlevuConfiguration::KEY_APIV2_CLOUD_SEARCH_URL,
                $this->context->language->id
            );
        $json_data = Tools::file_get_contents('php://input');
      
       
        // Check if we are performing a direct browser test (GET request with ?q=...)
        if (!empty($json_data)) { 

            if (empty($json_data)) {
                $this->sendErrorResponse('No request data received. Send POST with JSON or GET with ?q=QUERY.', 400);
                return;
            }

            // Inject/replace API key in the request
            $data_array = json_decode($json_data, true);
            
            if ($data_array) {
                // Always replace with the real API key (handles dummy keys from JS)
                $data_array['context']['apiKeys'] = [$api_key];
                $json_data = json_encode($data_array);
            }
        }else{
            $this->sendErrorResponse('No request data received. Send POST with JSON.', 400);
            return;
        } 
 
     
        // 2. Define API endpoint
        // Ensure we have a base URL and prefix https:// if missing
        $apiv2CloudSearchUrl = trim($apiv2CloudSearchUrl);
        if (empty($apiv2CloudSearchUrl)) {
            $this->sendErrorResponse('Missing APIV2 Cloud Search URL in configuration.', 500);
            return;
        }

        // If the configured value already includes a scheme (http/https) use as-is, otherwise force https
        if (preg_match('~^https?://~i', $apiv2CloudSearchUrl)) {
            $base = $apiv2CloudSearchUrl;
        } else {
            $base = 'https://' . $apiv2CloudSearchUrl;
        }

        // Remove trailing slash and append endpoint path
        $base = rtrim($base, '/');
        $url = $base . '/cs/v2/search';
 
        // 3. Define the BASE HTTP Headers (using the static list provided)
        session_start(); // Make sure sessions are started

        // Step 1: Define a list of User-Agents from different browsers
        $user_agents = [
            // Chrome - Desktop
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
            "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36",
            // Firefox - Desktop
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0",
            "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:122.0) Gecko/20100101 Firefox/122.0",
            // Safari - Desktop / iOS
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15",
            "Mozilla/5.0 (iPhone; CPU iPhone OS 17_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Mobile/15E148 Safari/604.1",
            "Mozilla/5.0 (iPad; CPU OS 17_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Mobile/15E148 Safari/604.1",
            // Edge - Desktop
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36 Edg/121.0.0.0",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36 Edg/119.0.0.0",
            // Opera - Desktop
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 OPR/90.0.0.0",
            // Android - Mobile Chrome
            "Mozilla/5.0 (Linux; Android 14; SM-S928B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36",
            "Mozilla/5.0 (Linux; Android 13; Pixel 8 Pro) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Mobile Safari/537.36",
            "Mozilla/5.0 (Linux; Android 12; SAMSUNG SM-G998B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.0.0 Mobile Safari/537.36",
            // Android - Firefox Mobile
            "Mozilla/5.0 (Android 14; Mobile; rv:122.0) Gecko/122.0 Firefox/122.0",
            "Mozilla/5.0 (Android 13; Mobile; rv:121.0) Gecko/121.0 Firefox/121.0",
        ];

        // Step 2: Pick a random User-Agent for this session
        if (!isset($_SESSION['random_user_agent'])) {
            $_SESSION['random_user_agent'] = $user_agents[array_rand($user_agents)];
        }

        // Step 3: Build the headers
        $base_headers = [
            "User-Agent: " . $_SESSION['random_user_agent'],
            "Accept: */*",
            "Accept-Language: en-US,en;q=0.5",
            "Accept-Encoding: gzip, deflate, br, zstd",
            "Referer: https://www.sifatusafwa.com/",
            "Content-Type: application/json; charset=UTF-8",
            "x-klevu-api-key: $api_key",
            "x-klevu-integration-type: jsv2",
            "x-klevu-integration-version: 2.13.1",
            "Origin: https://www.sifatusafwa.com",
            "Connection: keep-alive",
            "Sec-Fetch-Dest: empty",
            "Sec-Fetch-Mode: cors",
            "Sec-Fetch-Site: cross-site",
            "Priority: u=4",
            "TE: trailers",
            "Content-Length: " . Tools::strlen($json_data),
        ];


        // 4. Initialize and Configure cURL
        $ch = curl_init($url);
        
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        
        // Use the defined static headers
        curl_setopt($ch, CURLOPT_HTTPHEADER, $base_headers);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        // ESSENTIAL FIX: Auto-decompress Gzip/Deflate/Brotli response from the API
        curl_setopt($ch, CURLOPT_ENCODING, ''); 
        
        // 5. Execute Request
        $response_body = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        
        curl_close($ch);

        // 6. Check for Errors and Send Response

        // Check for cURL (network) errors
        if ($response_body === false) {
            $this->sendErrorResponse('cURL Error: ' . $curl_error, 500);
            return;
        }

        // Check for API errors (4xx or 5xx status codes)
        if ($http_status >= 400) {
            $this->sendErrorResponse('Klevu API Error. Status: ' . $http_status . '. Response: ' . $response_body, $http_status);
            return;
        }
        
        // 7. Send the successful raw response back to the JavaScript client
        // This is the clean, uncompressed JSON response
        header('Content-Type: application/json');
        http_response_code($http_status);
        die($response_body);
    }
    
    /**
     * Helper function to send an error response back to the client.
     */
    protected function sendErrorResponse($message, $http_code = 500)
    {
        // Log the error (optional but recommended)
        // Ensure PrestaShopLogger class is available in your environment
        // if (class_exists('PrestaShopLogger')) {
        //     PrestaShopLogger::addLog('Klevu Proxy Error: ' . $message, 3);
        // }
        
        // Set the appropriate HTTP status code
        http_response_code($http_code);
        
        // Send a simple JSON error response
        header('Content-Type: application/json');
        die(json_encode(['error' => $message]));
    }
}