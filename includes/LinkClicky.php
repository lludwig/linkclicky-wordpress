<?php

namespace LinkClickySDK;

use GuzzleHttp\Client;

// LinkClicky API

class LinkClicky {
	protected $Domain;
	protected $apiBase;
	protected $s2surl;
	protected $apiToken;
	protected $filebase = '/linkclicky';
	protected $useragent = 'LinkClickyBot/1.1';
	protected $guzzle;

	public function __construct(string $fqdn, string $apiToken) {
 		$this->Domain = $fqdn;
		$this->apiBase = 'https://'.$fqdn.'/api/';
		$this->s2surl = 'https://'.$fqdn.'/event/';
		$this->apiToken = $apiToken;
		
		// build a new Guzzle
		$this->guzzle = new Client();
	}

	//
	// Links
	//

	public function Links(array $options = []):array {

		$endpoint = $this->apiBase.'links/'.array_to_http_get($options,true);

		try {
			$results = $this->guzzle->request('GET', $endpoint, [
				'headers' => [
					'User-Agent' => $this->useragent,
					'Accept'     => 'application/json',
					'Api-Token'  => $this->apiToken,
				],
			]);

			$json = json_decode( $results->getBody() );

			if ( $results->getStatusCode() == 200 ) {
				return($json->{'result'});
			}
			else {
				print 'ERROR: '.$results->getStatusCode().PHP_EOL;
				return( (array) [] );
			}
		}
		catch ( \Exception $e ) {
			print "ENDPOINT: ". $endpoint . PHP_EOL;
			print 'EXCEPTION: ' .  $e->getMessage() . PHP_EOL;
			print $e->getTraceAsString() . PHP_EOL;
			return( (array) [] );
		}
		return( (array) [] );
	}

	public function LinkUpdate(array $options = []):array {

		$endpoint = $this->apiBase.'linkupdate/'.array_to_http_get($options,true);

		try {
			$results = $this->guzzle->request('GET', $endpoint, [
				'headers' => [
					'User-Agent' => $this->useragent,
					'Accept'     => 'application/json',
					'Api-Token'  => $this->apiToken,
				],
			]);

			$json = json_decode( $results->getBody() );

			if ( $results->getStatusCode() == 200 ) {
				return($json->{'result'});
			}
			else {
				print 'ERROR: '.$results->getStatusCode().PHP_EOL;
				return( (array) [] );
			}
		}
		catch ( \Exception $e ) {
			print "ENDPOINT: ". $endpoint . PHP_EOL;
			print 'EXCEPTION: ' .  $e->getMessage() . PHP_EOL;
			print $e->getTraceAsString() . PHP_EOL;
			return( (array) [] );
		}
		return( (array) [] );
	}

	//
	// Integrations Systems
	//

	public function Integrations(array $options = []):array {

		$endpoint = $this->apiBase.'integrations/'.array_to_http_get($options,true);

		try {
			$results = $this->guzzle->request('GET', $endpoint, [
				'headers' => [
					'User-Agent' => $this->useragent,
					'Accept'     => 'application/json',
					'Api-Token'  => $this->apiToken,
				],
			]);

			$json = json_decode( $results->getBody() );

			if ( $results->getStatusCode() == 200 ) {
				return($json->{'result'});
			}
			else {
				print 'ERROR: '.$results->getStatusCode().PHP_EOL;
				return( (array) [] );
			}
		}
		catch ( \Exception $e ) {
			print 'EXCEPTION: ' .  $e->getMessage() . PHP_EOL;
			print $e->getTraceAsString() . PHP_EOL;
			print "ENDPOINT: ". $endpoint . PHP_EOL;
			return( (array) [] );
		}
		return( (array) [] );
	}

	//
	// Affiliate Systems
	//

	public function AffiliateSystems(array $options = []):array {

		$endpoint = $this->apiBase.'affiliatesystems/'.array_to_http_get($options,true);

		try {
			$results = $this->guzzle->request('GET', $endpoint, [
				'headers' => [
					'User-Agent' => $this->useragent,
					'Accept'     => 'application/json',
					'Api-Token'  => $this->apiToken,
				],
			]);

			$json = json_decode( $results->getBody() );

			if ( $results->getStatusCode() == 200 ) {
				return($json->{'result'});
			}
			else {
				print 'ERROR: '.$results->getStatusCode().PHP_EOL;
				return( (array) [] );
			}
		}
		catch ( \Exception $e ) {
			print "ENDPOINT: ". $endpoint . PHP_EOL;
			print 'EXCEPTION: ' .  $e->getMessage() . PHP_EOL;
			print $e->getTraceAsString() . PHP_EOL;
			return( (array) [] );
		}
		return( (array) [] );
	}

	//
	// Sessions
	//

   public function Sessions(string $sessionid):array {

      $options = [
            'sessionid' => $sessionid,
      ];

		$endpoint = $this->apiBase.'sessions/'.array_to_http_get($options,true);

		try {
         $results = $this->guzzle->request('GET', $endpoint, [
				'headers' => [
					'User-Agent' => $this->useragent,
					'Accept'     => 'application/json',
					'Api-Token'  => $this->apiToken,
				],
			]);

			$json = json_decode( $results->getBody() );

			if ( $results->getStatusCode() == 200 ) {
				return($json->{'result'});
			}
			else {
				print 'ERROR: '.$results->getStatusCode().PHP_EOL;
				return( (array) [] );
			}
		}
		catch ( \Exception $e ) {
			print "ENDPOINT: ". $endpoint . PHP_EOL;
			print 'EXCEPTION: ' .  $e->getMessage() . PHP_EOL;
			print $e->getTraceAsString() . PHP_EOL;
			return( (array) [] );
		}
		return( (array) [] );
	}

	//
	// SessionAdd
	//

   public function SessionAdd(string $sessionid, string $ip_address, array $data = [] ):bool {

      $jsonbody = [
            'sessionid'  => $sessionid,
            'ip_address' => $ip_address,
            'data'       => $data,
      ];

		$endpoint = $this->apiBase.'sessions/';

		try {
			$promise = $this->guzzle->requestAsync('POST', $endpoint, [
            'timeout' => 1.0,
				'headers' => [
					'User-Agent' => $this->useragent,
					'Accept'     => 'application/json',
					'Api-Token'  => $this->apiToken,
				],
				'body' => json_encode($jsonbody),
         ]);
         $promise->wait();
      }
		catch ( \Exception $e ) {
		   return( false );
		}
		return( true );
	}

	//
	// Clicks
	//

	public function Clicks(array $options = []):array {

		$endpoint = $this->apiBase.'clicks/'.array_to_http_get($options,true);

		try {
			$results = $this->guzzle->request('GET', $endpoint, [
				'headers' => [
					'User-Agent' => $this->useragent,
					'Accept'     => 'application/json',
					'Api-Token'  => $this->apiToken,
				],
			]);

			$json = json_decode( $results->getBody() );

			if ( $results->getStatusCode() == 200 ) {
				return($json->{'result'});
			}
			else {
				print 'ERROR: '.$results->getStatusCode().PHP_EOL;
				return( (array) [] );
			}
		}
		catch ( \Exception $e ) {
			print "ENDPOINT: ". $endpoint . PHP_EOL;
			print 'EXCEPTION: ' .  $e->getMessage() . PHP_EOL;
			print $e->getTraceAsString() . PHP_EOL;
			return( (array) [] );
		}
		return( (array) [] );
	}

	//
	// Events
	//

	public function Events(array $options = []):array {

		$endpoint = $this->apiBase.'events/'.array_to_http_get($options,true);

		try {
			$results = $this->guzzle->request('GET', $endpoint, [
				'headers' => [
					'User-Agent' => $this->useragent,
					'Accept'     => 'application/json',
					'Api-Token'  => $this->apiToken,
				],
			]);

			$json = json_decode( $results->getBody() );

			if ( $results->getStatusCode() == 200 ) {
				return($json->{'result'});
			}
			else {
				print 'ERROR: '.$results->getStatusCode().PHP_EOL;
				return( (array) [] );
			}
		}
		catch ( \Exception $e ) {
			print "ENDPOINT: ". $endpoint . PHP_EOL;
			print 'EXCEPTION: ' .  $e->getMessage() . PHP_EOL;
			print $e->getTraceAsString() . PHP_EOL;
			return( (array) [] );
		}
		return( (array) [] );
	}

	//
	// Postback
	//
	public function createPostback(array $options = []) {
		// make sure the required fields are set
       	if ( $options['trackid'] != '' && $options['val'] !='' && $options['com'] !='' ) {
			$endpoint = $this->s2surl.array_to_http_get($options,true);
	
			try {
				$results = \Httpful\Request::get($endpoint)
					->addHeader( 'User-Agent', $this->useragent )
					->send();	
			}
			catch ( \Exception $e ) {
				print "ENDPOINT: ". $endpoint . PHP_EOL;
				print 'EXCEPTION: ' .  $e->getMessage() . PHP_EOL;
				print $e->getTraceAsString() . PHP_EOL;
			}
		
			return($results);
		}
		else {
				return("ERROR: Cannot create postback. Required parameters are missing.");
		}
	}

	//
	// Summary
	//

	public function Summary(array $options = []):array {

		$endpoint = $this->apiBase.'summary/'.array_to_http_get($options,true);

		try {
			$results = $this->guzzle->request('GET', $endpoint, [
				'headers' => [
					'User-Agent' => $this->useragent,
					'Accept'     => 'application/json',
					'Api-Token'  => $this->apiToken,
				],
			]);

			$json = json_decode( $results->getBody() );

			if ( $results->getStatusCode() == 200 ) {
				return($json->{'result'});
			}
			else {
				print 'ERROR: '.$results->getStatusCode().PHP_EOL;
				return( (array) [] );
			}
		}
		catch ( \Exception $e ) {
			print "ENDPOINT: ". $endpoint . PHP_EOL;
			print 'EXCEPTION: ' .  $e->getMessage() . PHP_EOL;
			print $e->getTraceAsString() . PHP_EOL;
			return( (array) [] );
		}
		return( (array) [] );
	}
}
