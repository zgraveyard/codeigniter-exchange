<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * CodeIgniter Exchange Class
 *
 *
 * @package        	CodeIgniter
 * @subpackage    	Libraries
 * @category    	Libraries
 * @author        	Mhd Zaher Ghaibeh
 * @license         http://philsturgeon.co.uk/code/dbad-license
 * @link			http://creativewebgroup-sy.com
 */

class Exchange
{
	protected $open_exchange_url = NULL;
	protected $file_name = NULL;
	protected $exchange_rates = NULL;
	protected $currency_name = NULL;

	public function __construct()
	{
		$this->open_exchange_url = 'http://openexchangerates.org/';
		$this->file_name = 'latest.json';
		$this->_init();
	}

	private function _init()
	{
		$ch = curl_init($this->open_exchange_url . $this->file_name);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		// Get the data:
		if(curl_exec($ch) !== FALSE)
		{
			$json = curl_exec($ch);
			// Decode JSON response:
			$this->exchange_rates = json_decode($json);
		}
		curl_close($ch);
	}

	/*
	*	Convert Function
	*	@param $amount: number 
	*	@param $from : string
	*	@param $to : string
	*	@param $fraction : int
	*	@return : object
	*/
	public function convert($from = null , $to = null,$amount = 1 ,$fraction = 3)
	{
		if(is_null($from) OR is_null($to) OR is_null($this->exchange_rates))
		{
			return FALSE;
		}

		$t = (array) $this->exchange_rates->rates;
		// var_dump($t);
		if(!array_key_exists($from,$t) OR !array_key_exists($to,$t)) return FALSE;
		$value = $amount * (($this->exchange_rates->rates->{$to}/$this->exchange_rates->rates->{$from}));
		$moneyParts = explode('.',$value);

		$exchange = array(
			'value'=> $moneyParts[0].'.'.substr($moneyParts[1],0,(int)$fraction),
			'date' => date('h:i jS F, Y', $this->exchange_rates->timestamp),
			'from_code' => $from,
			'from_name' => $this->get_currency_name($from),
			'to_code' => $to,
			'to_name' => $this->get_currency_name($to),
			'amount' => $amount
		);
		
		return (object)$exchange;
	}

	/*
	*	get rates Function
	*	@param $base: string
	*	@return : object
	*/

	public function get_rates($base = 'USD')
	{
		if(is_null($this->exchange_rates))
		{
			return FALSE;
		}
		
		// if the base is not equal to USD
		if($base !== 'USD')
		{
			foreach($this->exchange_rates->rates as $key => $value)
			{
				$this->exchange_rates->rates->{$key} = $base_value / $value ;
			}
		}

		return $this->exchange_rates->rates;
	}


	/*
	*	get currency name Function
	*	@param $currency_code: string (optional)
	*	@return : array / string
	*/

	public function get_currency_name($currency_code = NULL)
	{
		$this->file_name = 'currencies.json';
		$ch = curl_init($this->open_exchange_url . $this->file_name);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		// Get the data:
		if(curl_exec($ch) !== FALSE)
		{
			$json = curl_exec($ch);
			// Decode JSON response:
			$this->currency_name = (array)json_decode($json);
		}
		curl_close($ch);
		
		if(!is_null($currency_code) && array_key_exists($currency_code, $this->currency_name))
		{
			$this->currency_name =  $this->currency_name[$currency_code];
		}
		return $this->currency_name;
	}
}

/* End of file Exchange.php */