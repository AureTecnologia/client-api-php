<?php

namespace Aure;

use Exception;

class AureClientAPI {

	public $baseURL;
	public $apiVersion;
	public $customer;
	public $form;

	private $data = array();

	/**
	 * AureClientAPI constructor.
	 *
	 * @param $customer
	 * @param $form
	 * @param string $baseURL
	 * @param string $apiVersion
	 *
	 * @throws Exception
	 */
	public function __construct( $customer, $form, $apiVersion = 'v1', $baseURL = 'https://api.aure.com.br' ) {

		if ( empty( $customer ) ) {
			throw new Exception( "Inform customer as the first argument." );
		}

		if ( empty( $form ) ) {
			throw new Exception( "Inform form as the second argument." );
		}

		if ( empty( $baseURL ) ) {
			throw new Exception( "BaseURL not can not be empty." );
		}

		if ( empty( $apiVersion ) ) {
			throw new Exception( "ApiVersion not can not be empty." );
		}


		$this->baseURL    = $baseURL;
		$this->apiVersion = $apiVersion;
		$this->customer   = $customer;
		$this->form       = $form;
	}

	/**
	 * Get URL for request
	 *
	 * @param string $type
	 *
	 * @return string
	 * @throws Exception
	 */
	protected function _getURL( $type = 'capture' ) {

		if ( empty( $type ) ) {
			throw new Exception( "Method not can not be empty." );
		}

		switch ( $type ) {
			case 'capture':
			default:
				return $this->baseURL . '/' . $this->apiVersion . '/capture/' . $this->customer . '/' . $this->form;
		}
	}

	protected function _request( $method = 'post', $url, $data = array(), $referer ) {

		if ( empty( $method ) ) {
			throw new Exception( "Method not can not be empty." );
		}

		if ( empty( $url ) ) {
			throw new Exception( "URL not can not be empty." );
		}

		$method = strtoupper( $method );

		if ( function_exists( 'curl_init' ) ) {

			$curlDefaultOpts = [
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_URL            => $url,
				CURLOPT_USERAGENT      => 'AureClientAPI cURL Request',
				CURLOPT_REFERER        => $referer,
			];


			switch ( $method ) {
				case 'POST':
					$curlDefaultOpts[ CURLOPT_POST ]       = 1;
					$curlDefaultOpts[ CURLOPT_POSTFIELDS ] = http_build_query( $data );
					break;
				default:
					break;
			}

			$curl = curl_init();

			curl_setopt_array( $curl, $curlDefaultOpts );

			$resp = curl_exec( $curl );

			curl_close( $curl );

			return ! ( $resp === false );

		} else if ( function_exists( 'fsockopen' ) ) {

			$URLParts = parse_url( $url );

			$fp = fsockopen( $URLParts['host'], isset( $URLParts['port'] ) ? $URLParts['port'] : 80, $errno, $errstr,
				30 );

			$data = http_build_query( $data );

			$out = $method . " " . $URLParts['path'] . " HTTP/1.1\r\n";
			$out .= "Host: " . $URLParts['host'] . "\r\n";
			$out .= "Content-Type: application/json\r\n";
			$out .= "Content-Length: " . strlen( $data ) . "\r\n";
			$out .= "Referer: " . $referer . "\r\n";
			$out .= "User-Agent: AureClientAPI fsockopen Request\r\n";
			$out .= "Connection: Close\r\n\r\n";
			$out .= $data;

			$written = fwrite( $fp, $out );
			fclose( $fp );

			return ! ( $written === false );

		} else {

			throw new Exception( "The host do not support external calls." );

		}

	}

	/**
	 * Set data to send to Aure
	 *
	 * @param $data
	 */
	public function setData( $data ) {
		$this->data = $data;
	}

	public function sendNewLead( $email, $data = array(), $referer ) {

		if ( empty( $email ) ) {
			throw new Exception( "Inform at least the lead email as the first argument." );
		}

		$data          = array_merge( $data, $this->data );
		$data['email'] = $email;


		return $this->_request( "POST", $this->_getURL( 'capture' ), $data, $referer );

	}


}