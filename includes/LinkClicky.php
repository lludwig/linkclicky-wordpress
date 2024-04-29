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
	protected $useragent = 'LinkClickyBot/1.0';
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

	public function Links(array $options = []) {

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
				return( [] );
			}
		}
		catch ( \Exception $e ) {
			print "ENDPOINT: ". $endpoint . PHP_EOL;
			print 'EXCEPTION: ' .  $e->getMessage() . PHP_EOL;
			print $e->getTraceAsString() . PHP_EOL;
			return( [] );
		}
		return( [] );
	}

	public function LinkUpdate(array $options = []) {

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
				return( [] );
			}
		}
		catch ( \Exception $e ) {
			print "ENDPOINT: ". $endpoint . PHP_EOL;
			print 'EXCEPTION: ' .  $e->getMessage() . PHP_EOL;
			print $e->getTraceAsString() . PHP_EOL;
			return( [] );
		}
		return( [] );
	}

	//
	// Integrations Systems
	//

	public function Integrations(array $options = []) {

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
				return( [] );
			}
		}
		catch ( \Exception $e ) {
			print 'EXCEPTION: ' .  $e->getMessage() . PHP_EOL;
			print $e->getTraceAsString() . PHP_EOL;
			print "ENDPOINT: ". $endpoint . PHP_EOL;
			return( [] );
		}
		return( [] );
	}

	//
	// Affiliate Systems
	//

	public function AffiliateSystems(array $options = []) {

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
				return( [] );
			}
		}
		catch ( \Exception $e ) {
			print "ENDPOINT: ". $endpoint . PHP_EOL;
			print 'EXCEPTION: ' .  $e->getMessage() . PHP_EOL;
			print $e->getTraceAsString() . PHP_EOL;
			return( [] );
		}
		return( [] );
	}

	//
	// Sessions
	//

   public function Sessions(string $sessionid) {

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
				return( [] );
			}
		}
		catch ( \Exception $e ) {
			print "ENDPOINT: ". $endpoint . PHP_EOL;
			print 'EXCEPTION: ' .  $e->getMessage() . PHP_EOL;
			print $e->getTraceAsString() . PHP_EOL;
			return( [] );
		}
		return( [] );
	}

	//
	// SessionAdd
	//

   public function SessionAdd(string $sessionid, string $ip_address, array $data = [] ) {

      $options = [
            'sessionid'  => $sessionid,
            'ip_address' => $ip_address,
            'data'       => json_encode($data) ?? null,
      ];

		$endpoint = $this->apiBase.'sessionadd/'.array_to_http_get($options,true);

		try {
			$promise = $this->guzzle->requestAsync('GET', $endpoint, [
            'timeout' => 0.1,
				'headers' => [
					'User-Agent' => $this->useragent,
					'Accept'     => 'application/json',
					'Api-Token'  => $this->apiToken,
				],
         ]);
         $promise->wait();
      }
      // don't error 
		catch ( \Exception $e ) {
		   return( true );
		}
		return( true );
	}

	//
	// Clicks
	//

	public function Clicks(array $options = []) {

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
				return( [] );
			}
		}
		catch ( \Exception $e ) {
			print "ENDPOINT: ". $endpoint . PHP_EOL;
			print 'EXCEPTION: ' .  $e->getMessage() . PHP_EOL;
			print $e->getTraceAsString() . PHP_EOL;
			return( [] );
		}
		return( [] );
	}

	//
	// Events
	//

	public function Events(array $options = []) {

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
				return( [] );
			}
		}
		catch ( \Exception $e ) {
			print "ENDPOINT: ". $endpoint . PHP_EOL;
			print 'EXCEPTION: ' .  $e->getMessage() . PHP_EOL;
			print $e->getTraceAsString() . PHP_EOL;
			return( [] );
		}
		return( [] );
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

	public function Summary(array $options = []) {

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
				return( [] );
			}
		}
		catch ( \Exception $e ) {
			print "ENDPOINT: ". $endpoint . PHP_EOL;
			print 'EXCEPTION: ' .  $e->getMessage() . PHP_EOL;
			print $e->getTraceAsString() . PHP_EOL;
			return( [] );
		}
		return( [] );
	}
}
