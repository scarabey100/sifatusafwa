<?php
/**
 * 2007-2017 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2017 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

require_once _PS_MODULE_DIR_ . 'bmsorderpreparation/classes/tpl/ShippingLabelTemplate.php';
class SwissPost
{

    public function submitShipment($order)
    {
        $order_id = $order->id;
        $this->generateBarcode($order_id);

        //change order status to shipped
        $status = Configuration::get("BMS_OP_STATUT_FOR_SHIPPED");
        if ($status)
        {
            $order->current_state = $status;
            $order->save();

            $history = new OrderHistory();
            $history->id_order = (int) $order->id;
            $history->changeIdOrderState($status, (int) $order->id);
            $history->save();
        }

        //change preparation status to packed


    }

    public function exportLabel($orders)
    {
        foreach($orders as $order)
        {
            $order_id = $order->id;
            $shipping_no = $order->getWsShippingNumber();

            $this->generatePDF(
                'Barcode_'.$order_id.'_'.$shipping_no,
                _PS_MODULE_DIR_.'swisspostbarcodegenerator/barcodes/output_Barcode_'.$shipping_no.'_'.$order_id.'.png',
                $shipping_no,
                $order_id
            );
        }
    }

    /**
     * Generate the label PDF
     *
     * @param $title
     * @param $label
     * @param $tnumber
     * @param $order_id
     * @return mixed
     */
    private function generatePDF($title, $label, $tnumber, $order_id)
    {
        ob_start();
        //Change the PDF page configuration relative to the page size chosen in the configuration

        $printSize = Configuration::get('SWISSPOST_BARCODE_GENERATOR_PRINT_SIZE');


        $orientation = ($printSize == 'A5' || $printSize == 'A6' || $printSize == 'A7') ? 'L' : 'P';


        $page_setup = array(
            'orientation' => $orientation,
            'page_format' => $printSize,
        );

        //create new pdf object with custom page parameters
        $pdf = new TCPDF($page_setup['orientation'], 'mm', $page_setup['page_format'], true, 'UTF-8', false);

        //set pdf title and desctiption with invoice details to easily find them again if lost somewhere in the computer
        $pdf->SetTitle($title);
        $pdf->SetSubject('Barcode invoice for Order id '.$order_id.' tracking number '.$tnumber);

        //set header and footer to false and margin to 0 to have exactly the print size needed for the barcode
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(0, 0, 0);

        //Create the page
        $pdf->AddPage();

        //no new page on overflow (if the image is slighty bigger)
        $pdf->SetAutoPageBreak(false, 0);

        //If user wants a A4 with details of the order
        if (Configuration::get('SWISSPOST_BARCODE_GENERATOR_PRINT_SIZE') == 'A4WithInfos') {
            $content = $this->getHtmlWithInfos($order_id);
            $pdf->Image($label, 62, 0, 148, 105, 'PNG', '', '', false, 300, '', false, false, 0, 'LT', false, false);
            $pdf->WriteHtml($content, true, false, true, false, '');
        } else if (Configuration::get('SWISSPOST_BARCODE_GENERATOR_PRINT_SIZE') == 'A7') {
            //put the image at coordinates (8;0) and exactly the size of an A7 at 300 dpi
            $pdf->Image($label, 2, 0, 105, 74, 'PNG', '', '', false, 300, '', false, false, 0, 'LT', false, false);
        } else if (Configuration::get('SWISSPOST_BARCODE_GENERATOR_PRINT_SIZE') == 'A6') {
            //put the image at coordinates (8;0) and exactly the size of an A6 at 300 dpi
            $pdf->Image($label, 4, 0, 148, 105, 'PNG', '', '', false, 300, '', false, false, 0, 'LT', false, false);
        } else {
            //put the image at coordinates (8;0) and exactly the size of an A5 at 300 dpi
            $pdf->Image($label, 8, 0, 210, 148, 'PNG', '', '', false, 300, '', false, false, 0, 'LT', false, false);
        }

        //output the pdf with the instruction to download and open it
        return $pdf->Output($title.'.pdf', 'D');

        //clear cached mem to avoid TCPDF known dual-data-output error
        ob_end_clean();
    }

    /**
     * Send shipment request
     *
     * @param $id_order
     * @return mixed
     */
    private function generateBarcode($id_order, $order_delivery = 'PRI', $order_delivery_option = '', $order_handling_option = '')
    {

        //generate order object to interact with it
        $order = new Order((int) $id_order);

        //generate customer object in order to interact with it
        $id_customer = $order->id_customer;
        $customer = new Customer((int) $id_customer);
        $customer_address = new Address($order->id_address_delivery);

        //Create shop var to access shop contact info
        $shop = array(
            'name' => Configuration::get('PS_SHOP_NAME'),
            'address' => Configuration::get('PS_SHOP_ADDR1'),
            'city' => Configuration::get('PS_SHOP_CITY'),
            'zip' => Configuration::get('PS_SHOP_CODE'),
            'country' => Configuration::get('PS_SHOP_COUNTRY'),
        );

        // SOAP Configuration
        $SOAP_wsdl_file_path = _PS_MODULE_DIR_.'swisspostbarcodegenerator/controllers/admin/barcode_v2_3.wsdl';
        $SOAP_config = array(
            // Webservice Endpoint URL
            'location' => 'https://wsbc.post.ch/wsbc/barcode/v2_3',
            // Webservice Barcode Login
            'login' => Configuration::get('SWISSPOST_BARCODE_GENERATOR_ACCOUNT_USERNAME'),
            'password' => htmlspecialchars_decode(Configuration::get('SWISSPOST_BARCODE_GENERATOR_ACCOUNT_PASSWORD')),
        );

        // SOAP Connection
        try {
            $SOAP_Client = new SoapClient($SOAP_wsdl_file_path, $SOAP_config);
        } catch (SoapFault $fault) {
            die('<pre>Error in SOAP Initialization: '.$fault->__toString().'</pre>');
        }

        //make an array of the delivery option to send it correctly in the przl
        $order_delivery = explode('-', $order_delivery);

        //constructing the array for przl
        $przl = array();
        foreach ($order_delivery as $value) {
            $przl[] = $value;
        }

        //assign only if options were chosen
        if (!empty($order_delivery_option)) {
            //merge each of the options with the main przl array
            foreach ($order_delivery_option as $value) {
                if (!empty($value)) {
                    $przl[] = $value;
                }
            }
        }

        //assign only if options were chosen the handling options needs to be last
        $phone_display = false;
        if (!empty($order_handling_option)) {
            foreach ($order_handling_option as $value) {
                if (!empty($value)) {
                    $przl[] = $value;
                    if ($value == 'ZAW3213') {
                        $phone_display = true;
                    }
                }
            }
        }

        //assign recipient array
        $recipient = array(
            'Name1' => $customer_address->firstname.' '.$customer_address->lastname,
            'Name2' => $customer_address->company,
            'Street' => $customer_address->address1,
            'ZIP' => $customer_address->postcode,
            'City' => $customer_address->city,
            'EMail' => $customer->email,
        );

        //add adress suffix value only if there is an adress2 field not empty
        if (!empty($customer_address->address2)) {
            $recipient['AddressSuffix'] = $customer_address->address2;
        }

        //add phone value only if handling option ZAW3213 is selected
        if ($phone_display == true) {
            $recipient['Phone'] = $customer_address->phone;
        }

        $LabelLayout = 'A5';
        if (Configuration::get('SWISSPOST_BARCODE_GENERATOR_PRINT_SIZE') == 'A4WithInfos') {
            $LabelLayout = 'A6';
        }

        if (Configuration::get('SWISSPOST_BARCODE_GENERATOR_PRINT_SIZE') == 'A6') {
            $LabelLayout = 'A6';
        }

        if (Configuration::get('SWISSPOST_BARCODE_GENERATOR_PRINT_SIZE') == 'A7') {
            $LabelLayout = 'A7';
        }

        $shopName = Tools::substr($shop['name'], 0, 25);

        if (Tools::strlen($shop['name']) > 25 && Tools::strlen($shop['url'] <= 25)) {
            $shopName = $shop['url'];
        }

        //building the request with the required parameters
        $generateLabelRequest = array(
            'Language' => Context::getContext()->language->iso_code,
            'Envelope' => array(
                'LabelDefinition' => array(
                    'LabelLayout' => $LabelLayout,
                    'PrintAddresses' => 'RecipientAndCustomer',
                    'ImageFileType' => 'PNG',
                    'ImageResolution' => 300,
                    'PrintPreview' => false,
                ),
                'FileInfos' => array(
                    'FrankingLicense' => Configuration::get('SWISSPOST_BARCODE_GENERATOR_ACCOUNT_LICENSE_NUMBER'),
                    'PpFranking' => false,
                    'Customer' => array(
                        'Name1' => $shopName,
                        'Street' => $shop['address'],
                        'ZIP' => $shop['zip'],
                        'City' => $shop['city'],
                        'DomicilePostOffice' => $shop['ZIP'] + ' ' + $shop['city'],
                    ),
                    'CustomerSystem' => 'Prestashop Swiss Post Barcode Generator',
                ),
                'Data' => array(
                    'Provider' => array(
                        'Sending' => array(
                            'Item' => array(
                                array(
                                    'Recipient' => $recipient,
                                    'Attributes' => array(
                                        'PRZL' => $przl,
                                        'Amount' => ($order->total_products_wt + $order->total_shipping) - $order->total_discounts,
                                        'ProClima' => Configuration::get('SWISSPOST_BARCODE_GENERATOR_PROCLIMA'),
                                    ),

                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );


        //Send the request to the webservice server
        $response = null;
        try {
            $response = $SOAP_Client->GenerateLabel($generateLabelRequest);
        }
        catch (SoapFault $fault) {
            echo '<p><strong>' . $this->l('Please check your credentials') . '</strong></p>';
            echo '<p>'.$this->l('The username and password that you set in the module settings must not be the same as the ones that you use to login to Swiss Post\'s website.').'</p>';
            echo '<p>'.$this->l('The username should have 9 characters and the password should have 12 characters and are provided by Swiss Post for the use of their barcode webservice only.').'</p>';
            echo '<p>'.$this->l('For more information, please refer to the documentation provided with the module').'</p>';
            echo '<p>'.$this->l('If the problem persists, please contact us through PrestaShop addons with the following information:').'</p>';
            echo '<pre>Error in generateBarcode: '.$fault->__toString().'</pre>';

            die();
        }

        //the method can generate multiple barcodes (not used in this case) keep the foreach if we use it in the future
        foreach ($response->Envelope->Data->Provider->Sending as $item) {
            if (isset($item->Errors) && $item->Errors != null) {
                $errorMessages = '';
                $delimiter = '';
                foreach ($item->Errors as $error) {
                    $errorMessages .= $delimiter.$error->Message;
                    $delimiter = ',';
                }
                //In case of error throw error and die
                die('<pre>Error: '.$errorMessages.'</pre>');
            } else {
                //output and save the image to the desired folder
                $identCode = $item->IdentCode;
                $labelBinaryData = $item->Label;
                $filename = 'output_Barcode_'.$identCode.'_'.$id_order.'.png';
                $filepath = _PS_MODULE_DIR_.'swisspostbarcodegenerator/barcodes/'.$filename;
                file_put_contents($filepath, $labelBinaryData);

                //Set the  order status to the one chosen in the configuration
                /*
                $history = new OrderHistory();
                $history->id_order = (int) $order->id;
                $history->changeIdOrderState(Configuration::get('SWISSPOST_BARCODE_GENERATOR_STATUS_SET'), (int) ($order->id));
                */

                //Set shipping number to order carrier
                $order->setWsShippingNumber($identCode);

                //check if user wants to override the main mail method
                if (Configuration::get('SWISSPOST_BARCODE_GENERATOR_EMAIL_MODE') == 1) {
                    //add the statut to the order history
                    //$history->add(true);

                    $this->sendCustomEmail(Configuration::get('SWISSPOST_BARCODE_GENERATOR_ACCOUNT_EMAIL_CONTENT_'.$order->id_lang), $identCode, $order, $customer, $customer_address);
                } else {
                    //add the statut to the order detail page and send the default email
                    //$history->addWithemail(true);
                }

            }
        }
    }


}