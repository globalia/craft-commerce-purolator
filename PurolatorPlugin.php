<?php

namespace Craft;

class PurolatorPlugin extends BasePlugin
{
    function getName()
    {
         return 'Purolator';
    }

    function getVersion()
    {
        return '2.0';
    }

    function getDeveloper()
    {
        return 'Globalia';
    }

    function getDeveloperUrl()
    {
        return 'http://www.globalia.ca';
    }

    public function getSettingsHtml()
    {
        return craft()->templates->render('purolator/settings', array(
            'settings' => $this->getSettings()
        ));
    }

    protected function defineSettings()
    {
        return array(
            'developmentKey'                    => array(AttributeType::String),
            'developmentKeyPassword'            => array(AttributeType::String),
            'developmentCourierAccountNumber'   => array(AttributeType::String),
            'productionKey'                     => array(AttributeType::String),
            'productionKeyPassword'             => array(AttributeType::String),
            'courierAccountNumber'              => array(AttributeType::String),
            'testMode'                          => array(AttributeType::Bool),
            'senderPostalCode'                  => array(AttributeType::String)
        );
    }

    public function init()
    {
        require_once CRAFT_PLUGINS_PATH . '/purolator/vendor/autoload.php';
    }

    public function commerce_registerShippingMethods()
    {
        $shippingMethods = craft()->purolator_estimate->shippingMethods;
        $models = array();
        foreach($shippingMethods as $code => $name){
            $methodModel = new Purolator_ShippingMethodModel();
            $methodModel->setAttributes(array(
                'code'  => $code,
                'name'  => $name
            ));
            $models[] = $methodModel;
        }
        return $models;
    }
}
