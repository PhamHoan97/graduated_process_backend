<?php

namespace Faker\Provider\fr_CH;

class Payment extends \Faker\Provider\Payment
{
    /**
     * International Bank Accounts Number (IBAN)
     * @link http://en.wikipedia.org/wiki/International_Bank_Account_Number
     * @param  string  $prefix      for generating bank account number of a specific bank
     * @param  string  $countryCode ISO 3166-1 alpha-2 country code
     * @param  integer $length      total length without country code and 2 check digits
     * @return string
     */
    public static function bankAccountNumber($prefix = '', $countryCode = 'CH', $length = null)
    {
        return static::iban($countryCode, $prefix, $length);
    }
}
