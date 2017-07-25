<?php
namespace Craft;

use Commerce\Interfaces\ShippingMethod;

/**
 * Shipping method model
 *
 * @property string                        $code
 * @property string                        $name
 * 
 * @property Purolator_ShippingRuleModel[]        $rules
 */
class Purolator_ShippingMethodModel extends BaseModel implements ShippingMethod
{
    /**
     * @return string
     */
    public function getType()
    {
        return Craft::t('Custom');
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return null;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getAttribute('name');
    }

    /**
     * @return string
     */
    public function getHandle()
    {
        return $this->getAttribute('code');
    }

    /**
     * @return Purolator_ShippingRuleModel[]
     */
    public function getRules()
    {
        $calculatedAmount = 0;
        $estimatedTransitDays = 0;
        
        if (! is_null($this->getAttribute('code'))){
            $estimate = craft()->purolator_estimate->getEstimate($this->getAttribute('code'));
            if (! is_null($estimate)) {
                $calculatedAmount = $estimate->TotalPrice;
                $estimatedTransitDays = $estimate->EstimatedTransitDays;
            }
        }

        $rulesModel = new Purolator_ShippingRuleModel();
        $rulesModel->setAttributes(array(
            'code'                  => $this->getAttribute('code'),
            'name'                  => $this->getAttribute('name'),
            'calculatedAmount'      => $calculatedAmount,
            'estimatedTransitDays'  => $estimatedTransitDays
        ));
        return array($rulesModel);
    }

    /**
     * @return bool
     */
    public function getIsEnabled()
    {
        return true;
    }

    /**
     * Not applicable since we link to our own.
     *
     * @return string
     */
    public function getCpEditUrl()
    {
        return '';
    }

    protected function defineAttributes()
    {
        return [
            'code' => [AttributeType::String, 'required' => true],
            'name' => [AttributeType::String, 'required' => true]
        ];
    }
}
