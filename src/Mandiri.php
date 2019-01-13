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
 
define('MANDIRI_PARSER_DEBUG', false);
class Mandiri {
	private $username;
	private $password;

	public $_defaultTargets = [
		'loginUrl' => 'https://ib.bankmandiri.co.id/retail/Login.do?action=form&lang=in_ID',
		'loginAction' => 'https://ib.bankmandiri.co.id/retail/Login.do',
		'loginAction' => 'https://ib.bankmandiri.co.id/retail/Login.do',
		'getAccountID' => 'https://ib.bankmandiri.co.id/retail/TrxHistoryInq.do?action=form',
		'getMutasiUrl' => 'https://ib.bankmandiri.co.id/retail/TrxHistoryInq.do',
		'logoutAction' => 'https://ib.bankmandiri.co.id/retail/Logout.do?action=result'
	];

	protected $isLoggedIn = false;
	
	/**
	* The Constructor
	* this class will set variable when initialized
	*
	* @param string $username
	* @param string $password
	*/
	public function __construct($username, $password)
	{
		if( MANDIRI_PARSER_DEBUG == true ) error_reporting(E_ALL);
		$this->username = $username;
		$this->password = $password;
		$this->agent = 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:10.0.2) Gecko/20100101 Firefox/10.0.2';

		$this->curlHandle = curl_init();
		$this->setupCurl();
		$this->logout();
		$this->login($this->username, $this->password);
	}
	
	/**
	* Execute the CURL and return result
	*
	* @return curl result
	*/
	public function exec()
	{
		$result = curl_exec($this->curlHandle);
		if( MANDIRI_PARSER_DEBUG == true ) {
			$http_code = curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE);
			print_r($result);
			/**
			* Perlu diwapadai jangan melakukan pengecekan dengan interval waktu dibawah 10 menit ! 
			*/
			if($http_code == 302) {
				echo 'HALAMAN DIREDIRECT, harap tunggu beberapa menit ( biasanya 10 Menit! )';
				exit;
			}
		}
		return $result;
	}

	/**
	* Register default CURL parameters
	*/
	protected function setupCurl()
	{
		curl_setopt( $this->curlHandle, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $this->curlHandle, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $this->curlHandle, CURLOPT_COOKIEFILE,'cookie' );
		curl_setopt( $this->curlHandle, CURLOPT_COOKIEJAR, 'cookie' );
		curl_setopt( $this->curlHandle, CURLOPT_USERAGENT, $this->agent);
	}

	/**
	* Set request method on CURL to GET 
	*/
	protected function curlSetGet($urlref=false)
	{
		curl_setopt( $this->curlHandle, CURLOPT_POST, 0 );
		curl_setopt( $this->curlHandle, CURLOPT_HTTPGET, 1 );
	}
	
	/**
	* Set request method on CURL to POST 
	*/
	protected function curlSetPost()
	{
		curl_setopt( $this->curlHandle, CURLOPT_POST, 1 );
		curl_setopt( $this->curlHandle, CURLOPT_HTTPGET, 0 );
	}

	/**
	* Login to Mandiri
	*/
	private function login($username, $password)
	{
		//Just to Get Cookies
		curl_setopt( $this->curlHandle, CURLOPT_URL, $this->_defaultTargets['loginUrl'] );
		curl_setopt( $this->curlHandle, CURLOPT_REFERER, $this->_defaultTargets['logoutAction'] );
		$this->curlSetGet();
		$this->exec();
		
		//Sending Login Info
		$params = array(
			"action=".urlencode("result"),
			"password=".urlencode($password),
			"userID=".urlencode($username),
			"image.x=".urlencode("0"),
			"image.y=".urlencode("0")
		);
		$params = implode( '&', $params );
		$this->curlSetPost();
		curl_setopt( $this->curlHandle, CURLOPT_URL, $this->_defaultTargets['loginAction'] );
		curl_setopt( $this->curlHandle, CURLOPT_REFERER, $this->_defaultTargets['loginUrl'] );
		curl_setopt( $this->curlHandle, CURLOPT_POSTFIELDS, $params );
		$this->exec();
		$this->isLoggedIn = true;
	}

	/**
	* Get mutasi rekening pages
	*
	* @param string $from 'Y-m-d'
	* @param string $to 'Y-m-d'
	* @return string
	*/
	public function getMutasiRekening($from, $to)
	{
		if( !$this->isLoggedIn ) $this->login( $this->username, $this->password );
		
		$this->curlSetGet();
		curl_setopt( $this->curlHandle, CURLOPT_URL, $this->_defaultTargets['getAccountID'] );
		curl_setopt( $this->curlHandle, CURLOPT_REFERER, $this->_defaultTargets['loginAction'] );
		$res_acc_id = $this->exec();
		$accID = strpos($res_acc_id,'name="fromAccountID"');
	    if( $accID  && ($accID=strpos($res_acc_id,' value="',++$accID)) && ($accID=strpos($res_acc_id,' value="',++$accID)) ) {
	        $accID = substr($res_acc_id,$accID+8,15);
	        $accID = rtrim($accID, '"> ');
	    }

	    //bagi tanggal
	    $expl_from = explode("-", $from);
	    $expl_to = explode("-", $to);
		$params = array( 
				'action=result', 
				'fromAccountID='.urlencode($accID),
				'fromDay='.urlencode($expl_from[2]),
				'fromMonth='.urlencode($expl_from[1]),
				'fromYear='.urlencode($expl_from[0]),
				'orderBy='.urlencode("ASC"),
				'searchType='.urlencode("R"),
				'sortType='.urlencode("Date"),
				'toDay='.urlencode($expl_to[2]),
				'toMonth='.urlencode($expl_to[1]),
				'toYear='.urlencode($expl_to[0])
				);
		$params = implode( '&', $params );
		
		curl_setopt( $this->curlHandle, CURLOPT_URL, $this->_defaultTargets['getMutasiUrl'] );
		curl_setopt( $this->curlHandle, CURLOPT_REFERER, $this->_defaultTargets['getAccountID'] );
		curl_setopt( $this->curlHandle, CURLOPT_POSTFIELDS, $params );
		$html = $this->exec();

		$this->logout();
		return $this->getMutasiRekeningTable($html);
	}

	/**
	* Parse the pages on mutasi rekening
	* this method will return only elements on <table> tag that contain only list of transaction
	*
	* @param string $html
	* @return string
	*/
	private function getMutasiRekeningTable($html)
	{
		$iawal    = strpos($html,'<!-- Start of Item List -->');
		$iakir    = strpos($html,'<!-- End of Item List -->');
		if($iawal && $iakir && $iawal<$iakir) {
		    return substr($html,$iawal,$iakir-$iawal);
		} else {
		    return "kosong";
		}
	}

	/**
	* Logout
	* 
	* Logout from Mandiri website
	* Lakukan logout setiap transaksi berakhir!
	*
	* @return string
	*/
	public function logout()
	{
		$this->curlSetGet();
		curl_setopt( $this->curlHandle, CURLOPT_URL, $this->_defaultTargets['logoutAction'] );
		return $this->exec();
	}
}