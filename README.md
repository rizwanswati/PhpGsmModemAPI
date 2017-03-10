# PhpGsmModemAPI
This API is written on top of php_dio.dll for direct access to GMS modem. Tested on windows 8/8.1/10
Thanks to [maryo](https://github.com/maryo/php-5.5-windows-extensions/blob/master/php_dio-0.0.7-5.5-vc11-x86/php_dio_nts.dll) for php_dio.dll libraries.

# Usage Details
Follow the below instructions to get going with this API

## php_dio.dll installation
Open up the ext folder in you php folder and paste the php_dio.dll file there
Open up the php.ini and look for extensions. and paste following line below the last 'extension' line.
  
  ``
  extension=php_dio.dll
  ``
  now restart the xampp server and you are good to go.
  
## Send SMS
You can use the following code to send the text message

```php
$smsobject = new gsm_send_sms('COM8',921600); //COM Port and Baud Rate. you can check these credentials in Modem Settings.
$smsobject->init();
$check = $smsobject->send($telno,$message);
```
## Receive Message
You Can Read the message from GSM modem using following code.

```php
$smsobject = new gsm_send_sms('COM8',921600); //COM Port and Baud Rate. you can check these credentials in Modem Settings.
$smsobject->init();
$smsobject->receive_message();
```

# More Info
For more information about GSM Modem and AT Commands visit [SMS Solutions](http://www.smssolutions.net/tutorials/gsm/receivesmsat/)
