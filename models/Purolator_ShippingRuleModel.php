<?php
namespace Craft;

/**
 * Shipping rule model
 *
 * @property float                         $calculatedAmount
 * @property string                        $code
 * @property int                           $estimatedTransitDays
 * @property string                        $name
 *
 * @property Purolator_ShippingMethodRecord $method
 */
class Purolator_ShippingRuleModel extends BaseModel implements \Commerce\Interfaces\ShippingRule
{   
    /**
     * Hard coded rule handle
     *
     * @return string
     */
    public function getHandle()
    {
        return 'purolatorRule';
    }

    /**
     * @return bool
     */
    public function getIsEnabled()
    {
        return true;
    }

    /**
     * @return Commerce_CountryModel|null
     */
    public function getCountry()
    {
        return craft()->commerce_countries->getCountryById($this->countryId);
    }

    /**
     * @return Commerce_StateModel|null
     */
    public function getState()
    {
        return craft()->commerce_states->getStateById($this->stateId);
    }

    /**
     * @param Commerce_OrderModel $order
     * @return bool
     */
    public function matchOrder(Commerce_OrderModel $order)
    {
        return $this->getBaseRate() ? true : false;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->getAttributes();
    }

    /**
     * @return float
     */
    public function getPercentageRate()
    {
        return 0;
    }

    /**
     * @return float
     */
    public function getPerItemRate()
    {
        return 0;
    }

    /**
     * @return float
     */
    public function getWeightRate()
    {
        return 0;
    }

    /**
     * @return float
     */
    public function getBaseRate()
    {
        return $this->getAttribute('calculatedAmount');
    }

    /**
     * @return float
     */
    public function getMaxRate()
    {
        return 0;
    }

    /**
     * @return float
     */
    public function getMinRate()
    {
        return 0;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->getAttribute('name');
    }

    protected function defineAttributes()
    {
        return [
            'code' => [AttributeType::String, 'required' => true],
            'name' => [AttributeType::String, 'required' => true],
            'calculatedAmount' => [
                AttributeType::Number,
                'required' => true,
                'default'  => 0,
                'decimals' => 2
            ],
            'estimatedTransitDays' => [
                AttributeType::Number,
                'required' => true,
                'default'  => 1
            ]
        ];
    }
}
