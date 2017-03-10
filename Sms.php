<?php
/*Rizwan Ullah
 * The receiving part of this Class is written by Rizwan Ullah ..(Sending was done by someone else already)
 *The receiving is very basic... you can use time evet to trigger receiver multiple times.
 *
 *before using this code i recommend you must visit the following link to grasp the understanding of AT commands
 *which i will be using in this code....http://www.smssolutions.net/tutorials/gsm/receivesmsat/
 *Windows only (tested on XP, and 8.1 with PHP 5.2.6, and PHP 5.6.8)
 *Tested with the EZ863 (Telit GE863) GSM modem and D-LINK USB GSM Modem
 *Requires that PHP has permission to access "COM" system device, and system "mode" command
 *
 * Note : for windows dio.dll library has been used for this library.. it may behave shabby. if so, comment out the line and uncomment the line
 * below the line containing the dio code.. it is used for two reasons.. fast memory access and evade the time out issue.
*/
//Send SMS via serial SMS modem

class gsm_send_sms
{
	private $port = 'COM8';
	private $baud = 921600; //921600 is default
	public $debug = false;
	private $fp;
	private $buffer;
	//Setup COM port

	public function __construct($portNumber,$baudRate)
	{
		$this->port = $portNumber;
		$this->baud = $baudRate;
	}

	public function init()
	{
		$this->debugmsg("Setting up port: \"{$this->port} @ \"{$this->baud}\" baud");
		exec("MODE {$this->port}: BAUD={$this->baud} PARITY=N DATA=8 STOP=1", $output, $retval);
		if ($retval != 0) {
			throw new Exception('Unable to setup COM port, check it is correct');
			$this->close();
		}
		$this->debugmsg(implode("\n", $output));
		$this->debugmsg("Opening port");
		$this->fp = dio_open($this->port, O_RDWR);
//		$this->fp = fopen($this->port . ':', 'r+');
		if (!$this->fp) {
			throw new Exception("Unable to open port \"{$this->port}\"");
		}

		$this->debugmsg("Port opened");
		$this->debugmsg("Checking for responce from modem");

//		fputs($this->fp, "AT\r");   // executing AT command on the port that was strored in $fp
		dio_write($this->fp, "AT\r");

		//Wait for ok
		$status = $this->wait_reply("OK\r\n", 5);
		if (!$status) {
			throw new Exception('Did not receive responce from modem');
		}

		$this->debugmsg('Modem connected');
		
		usleep(200000);//0.2 sec
		
		//Set modem to SMS text mode
		$this->debugmsg('Setting text mode');
//		fputs($this->fp, "AT+CMGF=1\r");
		
		dio_write($this->fp, "AT+CMGF=1\r");
		$status = $this->wait_reply("OK\r\n", 5);

		if (!$status) {
			throw new Exception('Unable to set text mode');
		}

		usleep(200000);//0.2 sec
		$this->debugmsg('Text mode set');
		echo "modem is initialized";

	}

	private function wait_reply($expected_result, $timeout) {

		$this->debugmsg("Waiting {$timeout} seconds for expected result");
		$this->buffer = '';

		//Set timeout
		$timeoutat = time() + $timeout;
		//Loop until timeout reached (or expected result found)
		do {
			$this->debugmsg('Now: ' . time() . ", Timeout at: {$timeoutat}");
			$buffer ="";
			$response="";
			while(1)
			{
				$response.=dio_read($this->fp, 2);
				$lines=preg_replace("/\r+/","",$response);
				$lines=explode("\n",$lines);
				if (in_array("OK",$lines)) break;
				if (in_array("ERROR",$lines)) break;
			}
			
				if (in_array("OK",$lines))
			{
				return true;
			}
			else
			{
				return false;
			}
			
		}
			 while ($timeoutat > time());
    }

	//Print debug messages
	private function debugmsg($message) {

		if ($this->debug == true) {
			$message = preg_replace("%[^\040-\176\n\t]%", '', $message);
			echo $message . "\n";
		}

	}

	//Close port
	public function close(){
		$this->debugmsg('Closing port');
		dio_close($this->fp);
	}

	//Send message
	public function send($tel, $message) {

		//Filter tel
		$tel = preg_replace("%[^0-9\+]%", '', $tel);

		//Filter message text
		$message = preg_replace("%[^\040-\176\r\n\t]%", '', $message);
		$this->debugmsg("Sending message \"{$message}\" to \"{$tel}\"");

		//Start sending of message
		dio_write($this->fp, "AT+CMGS=\"{$tel}\"\r");

		//Send message text
		dio_write($this->fp, $message);

		//Send message finished indicator
		dio_write($this->fp, chr(26));

		//Wait for confirmation
		$status = $this->wait_reply("OK\r\n", 180);
		if (!$status) {
			//throw new Exception('Did not receive confirmation of messgage sent');
			$this->debugmsg('Did not receive confirmation of messgage sent');
			return false;
		}
		$this->debugmsg("Message sent");
		return true;
	}

	function receive_message()
	{
      /*
		fputs($this->fp, "AT+CPMS=?\r");
		$mybuffer = fread($this->fp, 1024);
		echo $mybuffer;
        */
	
		$storage = "SM"; // storage space of SIM.. 
		$alltext = "ALL"; // storage space of SIM and MODEM both.. list all messages..
		$unread = "REC UNREAD"; //to get the messages which have been red
		$delete = "DELL ALL"; // delete all the messages
//		fputs($this->fp, "AT+CPMS=\"{$storage}\"\r");
		dio_write($this->fp, "AT+CPMS=\"{$storage}\"\r");
		$buffer ="";
		$response="";
			while(1)
			{
				usleep(200000); // sleep for 0.2 sec
				$response.=dio_read($this->fp, 2);
				$lines=preg_replace("/\r+/","",$response);
				$lines=explode("\n",$lines);
				if (in_array("OK",$lines)) break;
				if (in_array("ERROR",$lines)) break;
			}
			//print_r($lines);
		dio_write($this->fp, "AT+CMGL=\"{$alltext}\"\r");
		$buffer ="";
			$response="";
			while(1)
			{
				usleep(200000); // sleep for 0.2 sec
				$response.=dio_read($this->fp, 2);
				$lines=preg_replace("/\r+/","",$response);
				$lines=explode("\n",$lines);
				if (in_array("OK",$lines)) break;
				if (in_array("ERROR",$lines)) break;
			}
		$lines = removefirsttwoindex($lines);
		$message = new stringmanipulation();
		$message->ShowMessage($lines);

        /*
		fputs($this->fp, "AT+CMGL=\"{$alltext}\"\r");
		$mybuffer = fread($this->fp, 1024);
		echo $mybuffer;
		*/
	}
}

?>

