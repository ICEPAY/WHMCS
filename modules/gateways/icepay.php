<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Define module related meta data.
 *
 * Values returned here are used to determine module related capabilities and
 * settings.
 *
 * @see http://docs.whmcs.com/Gateway_Module_Meta_Data_Parameters
 *
 * @return array
 */
function icepay_MetaData()
{
    return array(
        'DisplayName' => 'Icepay',
        'APIVersion' => '1.1', // Use API Version 1.1
        'DisableLocalCredtCardInput' => true,
        'TokenisedStorage' => false,
    );
}

/**
 * Define gateway configuration options.
 *
 * The fields you define here determine the configuration options that are
 * presented to administrator users when activating and configuring your
 * payment gateway module for use.
 *
 * Supported field types include:
 * * text
 * * password
 * * yesno
 * * dropdown
 * * radio
 * * textarea
 *
 * Examples of each field type and their possible configuration parameters are
 * provided in the sample function below.
 *
 * @return array
 */
function icepay_config()
{
    return array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'Icepay',
        ),

        'merchantID' => array(
            'FriendlyName' => 'Merchant ID',
            'Type' => 'text',
            'Size' => '30',
            'Default' => '',
            'Description' => 'Your Icepay merchant ID',
        ),

        'secretCode' => array(
            'FriendlyName' => 'Secret Code',
            'Type' => 'text',
            'Size' => '50',
            'Default' => '',
            'Description' => 'Your Icepay secret key',
        ),
    );
}

/**
 * Payment link.
 *
 * Required by third party payment gateway modules only.
 *
 * Defines the HTML output displayed on an invoice. Typically consists of an
 * HTML form that will take the user to the payment gateway endpoint.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see http://docs.whmcs.com/Payment_Gateway_Module_Parameters
 *
 * @return string
 */
function icepay_link($params)
{
    // Gateway Configuration Parameters
    $merchantID = $params['merchantID'];
    $secretCode = $params['secretCode'];

    // Invoice Parameters
    $invoiceId = $params['invoiceid'];
    $description = $params["description"];
    $amount = $params['amount'];
    $currencyCode = $params['currency'];

    // Client Parameters
    $firstname = $params['clientdetails']['firstname'];
    $lastname = $params['clientdetails']['lastname'];
    $email = $params['clientdetails']['email'];
    $address1 = $params['clientdetails']['address1'];
    $address2 = $params['clientdetails']['address2'];
    $city = $params['clientdetails']['city'];
    $state = $params['clientdetails']['state'];
    $postcode = $params['clientdetails']['postcode'];
    $country = $params['clientdetails']['country'];
    $phone = $params['clientdetails']['phonenumber'];

    // System Parameters
    $companyName = $params['companyname'];
    $systemUrl = $params['systemurl'];
    $returnUrl = $params['returnurl'];
    $langPayNow = $params['langpaynow'];
    $moduleDisplayName = $params['name'];
    $moduleName = $params['paymentmethod'];
    $whmcsVersion = $params['whmcsVersion'];

    $url = 'https://www.demopaymentgateway.com/do.payment';

    $postfields = array();
    $postfields['username'] = $username;
    $postfields['invoice_id'] = $invoiceId;
    $postfields['description'] = $description;
    $postfields['amount'] = $amount;
    $postfields['currency'] = $currencyCode;
    $postfields['first_name'] = $firstname;
    $postfields['last_name'] = $lastname;
    $postfields['email'] = $email;
    $postfields['address1'] = $address1;
    $postfields['address2'] = $address2;
    $postfields['city'] = $city;
    $postfields['state'] = $state;
    $postfields['postcode'] = $postcode;
    $postfields['country'] = $country;
    $postfields['phone'] = $phone;
    $postfields['callback_url'] = $systemUrl . '/modules/gateways/callback/' . $moduleName . '.php';
    $postfields['return_url'] = $returnUrl;

    // Include Icepay API
    require_once 'icepay/api/icepay_api_basic.php';

    try
    {
        // Create new payment object
        $paymentObj = new Icepay_PaymentObject();
        $paymentObj->setAmount(($amount * 100))
            ->setCountry($params['clientdetails']['country'])
            ->setLanguage($params['clientdetails']['country'])
            ->setReference($invoiceId)
            ->setDescription($description)
            ->setCurrency($currencyCode)
            ->setOrderID(); // not used in WHMCS

        $basicmode = Icepay_Basicmode::getInstance();
        $basicmode->setMerchantID($merchantID)
            ->setSecretCode($secretCode)
            ->setSuccessURL($returnUrl)
            ->setErrorURL($returnUrl)
            ->validatePayment($paymentObj);
    } 
    catch (Exception $e)
    {
        echo($e->getMessage());
    }

    $htmlOutput = '<form method="post" action="' . $basicmode->getURL() . '">';
    foreach ($postfields as $k => $v) {
        $htmlOutput .= '<input type="hidden" name="' . $k . '" value="' . urlencode($v) . '" />';
    }
    $htmlOutput .= '<input type="submit" value="' . $langPayNow . '" />';
    $htmlOutput .= '</form>';

    return $htmlOutput;
}