<?php
namespace Craft;

/*********************************************************************************

  Written By  : Client Services, Purolator Courier Ltd.
                webservices@purolator.com

  Adapted By  : Globalia

  Requires    : PHP SOAP extension enabled
              : Local copy of the Estimating Service WSDL
              : Valid Development/Production Key and Password

*********************************************************************************/ 

//Define the Production (or development) Key and Password
define("PUROLATOR_KEY", craft()->purolator_settings->getKey());
define("PUROLATOR_PASS", craft()->purolator_settings->getKeyPassword());

//Define the Billing account and the account that is registered with PWS
define("BILLING_ACCOUNT", craft()->purolator_settings->getCourierAccountNumber());
define("REGISTERED_ACCOUNT", craft()->purolator_settings->getCourierAccountNumber());

class Purolator_EstimateService extends BaseApplicationComponent
{
    private $estimate = null;

    public $shippingMethods = array(
        'PurolatorExpress'          => 'Purolator Express',
        'PurolatorExpress9AM'       => 'Purolator Express 9 AM',
        'PurolatorExpress1030AM'    => 'Purolator Express 10:30 AM',
        'PurolatorExpressEvening'   => 'Purolator Express Evening',
        'PurolatorGround'           => 'Purolator Ground',
        'PurolatorGround9AM'        => 'Purolator Ground 9 AM',
        'PurolatorGround1030AM'     => 'Purolator Ground 10:30 AM'
    );

    function createPWSSOAPClient()
    {
        $location = craft()->purolator_settings->getSettings()->testMode ?
                    "https://devwebservices.purolator.com/PWS/V1/Estimating/EstimatingService.asmx" :
                    "https://webservices.purolator.com/PWS/V1/Estimating/EstimatingService.asmx";

        $endpoint = craft()->purolator_settings->getSettings()->testMode ?
                    "https://devwebservices.purolator.com/EWS/V2/Estimating/EstimatingService.asmx?wsdl" :
                    "https://webservices.purolator.com/EWS/V2/Estimating/EstimatingService.asmx?wsdl";

        /** Purpose : Creates a SOAP Client in Non-WSDL mode with the appropriate authentication and
          *           header information
        **/
        //Set the parameters for the Non-WSDL mode SOAP communication with your Development/Production credentials
        $client = new \SoapClient($endpoint,
                                  array	(
                                          'trace'		=>	true,
                                          'location'	=>	$location,
                                          'uri'			=>	"http://purolator.com/pws/datatypes/v1",
                                          'login'		=>	PUROLATOR_KEY,
                                          'password'	=>	PUROLATOR_PASS
                                        )
                                );

        //Define the SOAP Envelope Headers
        $headers[] = new \SoapHeader ( 'http://purolator.com/pws/datatypes/v2',
                                      'RequestContext',
                                      array (
                                              'Version'           =>  '2.0',
                                              'Language'          =>  substr(craft()->locale->id, 0, 2),
                                              'GroupID'           =>  'xxx',
                                              'RequestReference'  =>  'Rating Example'
                                            )
                                    );
        //Apply the SOAP Header to your client
        $client->__setSoapHeaders($headers);

        return $client;
    }
    
    public function getQuickEstimate()
    {
        if (! is_null($this->estimate)) {
            return $this->estimate;
        }

        $client = $this->createPWSSOAPClient();
        $cart = craft()->commerce_cart->getCart();
        if (!$cart || !$cart->shippingAddress) {
            return;
        }

        $totalWeight = 0;
        foreach ($cart->lineItems as $lineItem) {
            $totalWeight += $lineItem->weight * $lineItem->qty;
        }

        $request = new \stdClass;

        //Populate the Billing Account Number
        $request->BillingAccountNumber = BILLING_ACCOUNT;

        //Populate the Origin Information
        $request->SenderPostalCode = craft()->purolator_settings->getSenderPostalCode();

        //Populate the Desination Information
        $request->ReceiverAddress = new \stdClass;
        $request->ReceiverAddress->City = $cart->shippingAddress->city;
        $request->ReceiverAddress->Province = $cart->shippingAddress->state->abbreviation;
        $request->ReceiverAddress->Country = $cart->shippingAddress->country->iso;
        $request->ReceiverAddress->PostalCode = $cart->shippingAddress->zipCode;

         //Populate the Package Information
        $request->PackageType = "CustomerPackaging";

        //Populate the Shipment Weight
        $request->TotalWeight = new \stdClass;
        $weight = craft()->commerce_settings->getOption('weightUnits') == 'g' ? $totalWeight / 1000 : $totalWeight;
        $request->TotalWeight->Value = $weight < 1 ? 1 : floor($weight);
        $request->TotalWeight->WeightUnit = craft()->commerce_settings->getOption('weightUnits') == 'g' ? 'kg' : craft()->commerce_settings->getOption('weightUnits');

        //Execute the request and return the response
        $response = $client->GetQuickEstimate($request);

        if (! is_null($response->ShipmentEstimates)) {
            $estimate = [];
            foreach ($response->ShipmentEstimates->ShipmentEstimate as $shipmentEstimate){
                $estimate[$shipmentEstimate->ServiceID] = $shipmentEstimate;
            }
            return $this->estimate = $estimate;
        }

        if (! empty($response->ResponseInformation->Errors)) {
            craft()->userSession->setFlash('purolator', $response->ResponseInformation->Errors->Error->Description);
        }

        return $this->estimate;
    }

    public function getEstimate($code)
    {
        $estimate = $this->getQuickEstimate();
        return isset($estimate[$code]) ? $estimate[$code] : null;
    }
}
