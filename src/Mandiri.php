<?php
namespace MutasiBank;
/**
 * Ambil Data Mutasi dari bank (Mandiri)
 *
 * no long description
 *
 * @author     David Suwandi <davidsuwandi@gmail.com>
 * @copyright  2019 ombapit
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @version    0.1
 */
 
define('MUTASI_PARSER_DEBUG', false);
class Mandiri {
	private $username;
	private $password;
	
	/**
	* The Constructor
	* this class will set variable when initialized
	*
	* @param string $username
	* @param string $password
	*/
	public function __construct($username, $password)
	{
		if( MUTASI_PARSER_DEBUG == true ) error_reporting(E_ALL);
		$this->username = $username;
		$this->password = $password;
	}
	
	/**
	* Execute the CURL and return result
	*
	* @return curl result
	*/
	public function exec()
	{
		return $this->username;
	}
}