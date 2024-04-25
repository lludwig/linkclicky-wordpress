<?php

use Httpful\Request;
use League\Uri\Uri;
use League\Uri\UriModifier;
use League\Uri\Components\Query;
use League\Uri\Components\Domain;
use League\Uri\Components\HierarchicalPath;
use League\Uri\Components\Path;

function acceptable_domain($domain, $testarray) {
	foreach($testarray as $row) {
		if($row == $domain) {
			return(true);
			break;
		}
	}
	return(false);
}

function array_to_http_get($params, $includequestion=true) {
	$query='';
	if ($includequestion == true) {
		$query = '?';
	}
	foreach( $params as $key => $value ) {
		$query .= urlencode($key) . '=' . urlencode($value ?? '') . '&';
	}
	$query = rtrim($query, '& ');
	return($query);
}

function array_to_slash($params) {
	$query='';
	foreach( $params as $key => $value ) {
		if ($value != '' ) {
			$query .= urlencode($key) . '/' . urlencode($value ?? '') . '/';
		}
		else {
			$query .= urlencode($key) . '/';
		}
	}
	$query = rtrim($query, '/ ');
	return($query);
}


function unparse_url($parsed_url) {
	$scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
	$host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
	$port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
	$user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
	$pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
	$pass     = ($user || $pass) ? "$pass@" : '';
	$path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
	$query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
	$fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
	return "$scheme$user$pass$host$port$path$query$fragment";
}

// Woopra ce
function woopra_ce($project, $parameters, $logfile='', $useragent='Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.95 Safari/537.36', $referer='') {
	$url='https://www.woopra.com/track/ce';
	
	// add project and referal to the list
	$parameters += array('project' => $project );
	$parameters += array('referer' => $referer );

	$buildurl=$url.array_to_http_get($parameters);

	$response = \Httpful\Request::get($buildurl)
		->addHeader('Referer',$referer)
		->addHeader('User-Agent',$useragent)
                ->send();

	if ($logfile != '' ) {	
	        file_put_contents($logfile, date('Y-m-d H:i:s').' '.$buildurl."\n", FILE_APPEND);
	}
	return ($response);
}

// Woopra identify
function woopra_identify($project, $parameters, $logfile='') {
        $url='https://www.woopra.com/track/identify';

        // add prooject to the list
        $parameters += array('project' => $project );

        $buildurl=$url.array_to_http_get($parameters);

        $referer=$project;
        $useragent=$_SERVER['HTTP_USER_AGENT'];

        $response = \Httpful\Request::get($buildurl)
                ->addHeader('Referer',$referer)
                ->addHeader('User-Agent',$useragent)
                ->send();

	if ($logfile != '' ) {	
	        file_put_contents($logfile, date('Y-m-d H:i:s').' '.$buildurl."\n", FILE_APPEND);
	}
	return ($response);
}

// Woopra properties
function woopra_properties($project, $key, $value) {
        $url='https://www.woopra.com/rest/3.7/profile';

        // add project to the list
        $parameters = array(
		'project' => $project,
		'key'     => $key,
		'value'   => $value,
	);

        $buildurl=$url.array_to_http_get($parameters);

        $result = \Httpful\Request::get($buildurl)
		->authenticateWith($GLOBALS['woopra_appid'], $GLOBALS['woopra_secret'])
		->expectsJson()
                ->send();

	// setup array to return
	$return = array (
		'pid'        => $result->body->pid,
		'properties' => $result->body->properties,
	);
	return ($return);
}

// Woopra set properties
function woopra_set_visitor_property($project, $pid, $property, $propertyvalue) {
        $url='https://www.woopra.com/rest/3.7/profile/edit';

        // create json
        $json = array(
		'project' => $project,
		'pid'     => $pid,
		'data'    => array (
			$property      => $propertyvalue,
		),
	);

        $response = \Httpful\Request::post($url)

		->authenticateWith($GLOBALS['woopra_appid'], $GLOBALS['woopra_secret'])
		->body(json_encode($json))
                ->send();
	return ($response->body);
}

// get vistor property value based upon pid or email
// returns array ($pid, $value)
function woopra_visitor_property($project=null, $key='pid', $value=null, $searchkey=null) {
	$result = woopra_properties($project, $key, $value);
	foreach ($result['properties'] as $row) {
		if ($row->key == $searchkey) {
			$return = array (
				'pid'   => $result['pid'],
				'value' =>  $row->value,
			);
			return($return);
			break;
		}
	}
	// didn't find what we wanted
	return(null);
}

// increase or decrease LTV based upon $amount
function woopra_change_ltv($host=null, $key='cookie', $value=null , $amount = 0) {
	if ( $amount != 0 ) {
		$result=woopra_visitor_property($host, $key, $value , 'ltv');
		$ltv=floatval(preg_replace('/[^\d.]/', '', $result['value'] ));
		$ltv = $ltv + $amount;
		woopra_set_visitor_property($host, $result['pid'], 'ltv', (string) $ltv);
	}
}

function set_cookie($name, $value) {
	setcookie('_uc_'.$name, urlencode($value), strtotime('+30 days'), '/', '.'.$primary_domain, false, false);
}


// debugging code output
function print_debug ($hasharray) {
	print "<pre>";
        print_r($hasharray);
        print "</pre>";
}

function linkclicky_domain_match($domainname, $list) {
	foreach ($list as $row) {
		if ( preg_match('/'.$row.'$/i', $domainname) ) {
			return(true);
		}
	}
	return(false);
}

// detect if it's an Impact link by URI heuristics
function linkclicky_is_impact_link($testuri) {
	if ( preg_match('/^https?:\/\/(.+)\.(.+)\.(.+)\/c\/[0-9]+\/[0-9]+\/[0-9]+(\?.+)?$/', $testuri) ) {
		return(true);
        }
        return(false);
}

// detect if it's a FirstPromoter link by URI heuristics
function linkclicky_is_firstpromoter($uri) {

	$parseduri = Uri::createFromString($uri);
	$query = Query::createFromUri($parseduri);

	// test to see if the URI has the TUNE parameters needed for an affiliate link
	if ($query->get('fp_ref') != null ) {
		return(true);
	}
	return(false);
}

// detect if it's a LinkMink link by URI heuristics
function linkclicky_is_linkmink($uri) {

	$parseduri = Uri::createFromString($uri);
	$query = Query::createFromUri($parseduri);

	// test to see if the URI has the TUNE parameters needed for an affiliate link
	if ($query->get('lmref') != null ) {
		return(true);
	}
	return(false);
}

// detect if it's a Post Affiliate Pro link by URI heuristics
function linkclicky_is_post_affiliate_pro($uri) {

	$parseduri = Uri::createFromString($uri);
	$query = Query::createFromUri($parseduri);

	// test to see if the URI has the parameters needed for an affiliate link
	if ($query->get('aid') != null && $query->get('bid') != null ) {
		return(true);
	}
	return(false);
}

// detect if it's a TUNE link by URI heuristics
function linkclicky_is_tune($uri) {

	$parseduri = Uri::createFromString($uri);
	$query = Query::createFromUri($parseduri);

	// test to see if the URI has the parameters needed for an affiliate link
	if ($query->get('offer_id') != null && $query->get('aff_id') != null ) {
		return(true);
	}
	return(false);
}

// detect if it's a Cake link by URI heuristics
function linkclicky_is_cake($uri) {

	$parseduri = Uri::createFromString($uri);
	$query = Query::createFromUri($parseduri);

	$a=$query->get('a');
	$c=$query->get('c');

	// test to see if the URI has the parameters needed for an affiliate link
	if ($a != null && $c != null && is_numeric($a) && is_numeric($c) ) {
		return(true);
	}
	return(false);
}

// detect if it's a QuinStreet link by URI heuristics
function linkclicky_is_quinstreet($uri) {

	$parseduri = Uri::createFromString($uri);
	$query = Query::createFromRFC3986($parseduri, ';');

	$n = $query->get('n') ?? null;
	$c = $query->get('c') ?? null;
	$x = $query->get('x') ?? null;
	$f = $query->get('f') ?? null;
	$u = $query->get('u') ?? null;
	$z = $query->get('z') ?? null;
	$src = $query->get('src') ?? null;

	// test to see if the URI has the parameters needed for an affiliate link
	if ( !empty($n) && !empty($c) && !empty($x) && !empty($f) && !empty($u) && !empty($z) && !empty($src) ) {
		return( true );
	}
	return( false );
}

// detect if it's a SkimLinks link by URI heuristics
function linkclicky_is_skimlinks($uri) {

	$parseduri = Uri::createFromString($uri);
	$query = Query::createFromUri($parseduri);

	$skimoffer=$query->get('skimoffer');

	// test to see if the URI has the parameters needed for an affiliate link
	if ($skimoffer != null ) {
		return(true);
	}
	return(false);
}

// detect if it's a Tapffiliate link by URI heuristics
function linkclicky_is_tapffiliate($uri) {

	$parseduri = Uri::createFromString($uri);
	$query = Query::createFromUri($parseduri);

	$tap=$query->get('tap_a');

	// test to see if the URI has the parameters needed for an affiliate link
	if ($tap != null ) {
		return(true);
	}
	return(false);
}

// determine affiliate system based upon the URL passed
// return array(system, subid variable name, network or merchant postback/api, if network the name)
function affiliatesystem($uri, $tag) {
	// list of affiliate systems supported
	$system = (object) array (
		'adsbymoney'           => array(
			'name'           => 'adsbymoney',
			'subid1'         => 's1',
			'connection'     => 'api',
		),
		'affiliatewp'      => array( 
			'name'           => 'affiliatewp',
			'subid1'         => 'campaign',
			'connection'     => null,
		),
		'affise'           => array(
			'name'           => 'affise',
			'subid1'         => 'sub1',
			'connection'     => 'api',
		),
		'amazon'           => array(
			'name'           => 'amazon',
			'subid1'         => null,
			'connection'     => null,
		),
		'awin'             => array(
			'name'           => 'awin',
			'subid1'         => 'clickref',
			'connection'     => 'api',
		),
		'bankrate'         => array(
			'name'           => 'bankrate',
			'subid1'         => 'tid',
			'connection'     => 'api',
		),
		'brainstormforce'         => array(
			'name'           => 'brainstormforce',
			'subid1'         => 'campaign',
			'connection'     => null,
		),
		'cake'             => array(
			'name'           => 'cake',
			'subid1'         => 's5',
			'connection'     => 'api',
		),
		'cellxpert'        => array(
			'name'           => 'cellxpert',
			'subid1'         => 'afp',
			'connection'     => null,
		),
		'cj'               => array(
			'name'           => 'cj',
			'subid1'         => 'sid',
			'connection'     => 'api',
		),
		'clickbank'        => array(
			'name'           => 'clickbank',
			'subid1'         => 'tid',
			'connection'     => null,
		),
		'clickfunnels'     => array(
			'name'           => 'clickfunnels',
			'subid1'         => 'aff_sub',
			'connection'     => null,
		),
		'commissionkings'   => array(
			'name'           => 'commissionkings',
			'subid1'         => 's2s.req_id',
			'connection'     => null,
		),
		'commissionsoup'     => array(
			'name'           => 'commissionsoup',
			'subid1'         => 's1',
			'connection'     => 'api',
		),
		'cpc'              => array(
			'name'           => 'cpc',
			'subid1'         => 'cmcpc',
			'connection'     => 'api',
		),
		'csv'              => array(
			'name'           => 'csv',
			'subid1'         => null,
			'connection'     => null,
		),
		'everflow'         => array(
			'name'           => 'everflow',
			'subid1'         => 'sub1',
			'connection'     => 'api',
		),
		'fintelconnect'    => array(
			'name'           => 'fintelconnect',
			'subid1'         => 'acid',
			'connection'     => null,
		),
		'firstpromoter'    => array(
			'name'           => 'firstpromoter',
			'subid1'         => 'fp_sid',
			'connection'     => null,
		),
		'flexoffers'       => array(
			'name'           => 'flexoffers',
			'subid1'         => 'fobs',
			'connection'     => 'api',
		),
		'idevaffiliate'    => array(
			'name'           => 'idevaffiliate',
			'subid1'         => 'tid1',
			'connection'     => null,
		),
		'impact'           => array(
			'name'           => 'impact',
			'subid1'         => 'subId1',
			'connection'     => 'api',
		),
		'itmedia'           => array(
			'name'           => 'itmedia',
			'subid1'         => 'atrk',
			'connection'     => 'api',
		),
		'jvzoo'            => array(
			'name'           => 'jvzoo',
			'subid1'         => 'tid',
			'connection'     => null,
		),
		'kartra'           => array(
			'name'           => 'kartra',
			'subid1'         => 'tracking_id1',
			'connection'     => null,
		),
		'linkconnector'         => array(
			'name'           => 'linkconnector',
			'subid1'         => 'atid',
			'connection'     => 'api',
		),
		'linkmink'         => array(
			'name'           => 'linkmink',
			'subid1'         => 'lm_meta',
			'connection'     => null,
		),
		'linktrust'        => array(
			'name'           => 'linktrust',
			'subid1'         => 'SID',
			'connection'     => 'api',
		),
		'maxbounty'        => array(
			'name'           => 'maxbounty',
			'subid1'         => 's1',
			'connection'     => 'api',
		),
		'netspend'         => array(
			'name'           => 'netspend',
			'subid1'         => 'sub',
			'connection'     => null,
		),
		'partnerize'       => array(
			'name'           => 'partnerize',
			'subid1'         => null,
			'connection'     => 'api'
		),
		'partnerstack'     => array(
			'name'           => 'partnerstack',
			'subid1'         => 'sid',
			'connection'     => 'api',
		),
		'pepperjam'        => array(
			'name'           => 'pepperjam',
			'subid1'         => 'sid',
			'connection'     => null,
		),
		'performcb'        => array(
			'name'           => 'performcb',
			'subid1'         => 'subid1',
			'connection'     => 'api',
		),
		'postaffiliatepro' => array(
			'name'           => 'postaffiliatepro',
			'subid1'         => 'data1',
			'connection'     => null,
		),
		'quinstreet'       => array(
			'name'           => 'quinstreet',
			'subid1'         => 'var2',
			'connection'     => 'api',
		),
		'rakuten'          => array(
			'name'           => 'rakuten',
			'subid1'         => 'u1',
			'connection'     => 'api',
		),
		'refersion'        => array(
			'name'           => 'refersion',
			'subid1'         => 'subid',
			'connection'     => null,
		),
		'revenuenetwork'   => array(
			'name'           => 'revenuenetwork',
			'subid1'         => 's2s.req_id',
			'connection'     => null,
		),
		'samcart'          => array(
			'name'           => 'samcart',
			'subid1'         => 'utm_content',
			'connection'     => null,
		),
		'scaleo'           => array(
			'name'           => 'scaleo',
			'subid1'         => 'sub_id1',
			'connection'     => 'api',
		),
			'shareasale'       => array(
			'name'           => 'shareasale',
			'subid1'         => 'afftrack',
			'connection'     => 'api',
		),
		'shopify'          => array(
			'name'           => 'shopify',
			'subid1'         => 'subid1',
			'connection'     => null,
		),
		'skimlinks'        => array(
			'name'           => 'skimlinks',
			'subid1'         => 'xcust',
			'connection'     => 'api',
		),
		'tapfiliate'       => array(
			'name'           => 'tapfiliate',
			'subid1'         => 'tm_subid1',
			'connection'     => null,
		),
		'thrivecart'       => array(
			'name'           => 'thrivecart',
			'subid1'         => 'ref',
			'connection'     => 'pixel',
		),
		'toponepartners'   => array(
			'name'           => 'toponepartners',
			'subid1'         => 's2s.req_id',
			'connection'     => 'api',
		),
		'tradedoubler'     => array(
			'name'           => 'tradedoubler',
			'subid1'         => 'epi',
			'connection'     => 'api',
		),
		'tune'             => array(
			'name'           => 'tune',
			'subid1'         => 'aff_click_id',
			'connection'     => 'api',
		),
		'webull'             => array(
			'name'           => 'webull',
			'subid1'         => 'irclickid',
			'connection'     => 'postback',
		),
		'manual'           => array(
			'name'           => 'manual',
			'subid1'         => null,
			'connection'     => null,
		),
		'unknown'           => array(
			'name'           => null,
			'subid1'         => null,
			'connection'     => null,
		),
	);

	$amazon_domains = [
		'amzn.to',
		'amazon.in',
		'amazon.ca',
		'amazon.it',
		'amazon.com.au',
		'amazon.com',
		'amazon.de',
		'amazon.co.uk',
		'amazon.fr',
		'amazon.es',
		'amazon.co.jp',
		'amazon.com.be',
		'amazon.com.cn',
		'amazon.eg',
		'amazon.com.mx',
		'amazon.nl',
		'amazon.pl',
		'amazon.sa',
		'amazon.sg',
		'amazon.se',
		'amazon.ae',
	];

	// start with a leading period to match subdomains
	$cj_domains = [
		'www.dpbolvw.net',
		'www.fastclick.com',
		'www.ftjcfx.com',
		'www.awltovhc.com',
		'www.lduhtrp.net',
		'www.yceml.net',
		'www.tqlkg.com',
		'www.apmebf.com',
		'www.afcyhf.com',
		'www.anrdoezrs.net',
		'www.commission-junction.com',
		'www.jdoqocy.com',
		'www.kqzyfj.com',
		'www.tqlkg.com',
		'www.tkqlhce.com',
	];

	$impact_domains = [
		'.8odi.net',
		'.ibfwsl.net',
		'.68w6.net',
		'.zvbf.net',
		'.xrte.net',
		'.wo8g.net',
		'.i3f2.net',
		'.msafflnk.net',
		'.z6rjha.net',
		'.8bx6ag.net',
		'.pxf.io',
		'.evyy.net',
		'.7eer.net',
		'.risj.net',
		'.i679.net',
		'.sjv.io',
		'.5vju.net',
		'.i6xjt2.net',
		'.8bxp97.net',
		'.nvaz.net',
		'.7eqqol.net',
		'www.fubo.tv',
		'.lvuv.net',
		'.zytd7d.net',
		'.attfm2.net',
		'refer.turo.com',
		'.b4th.net',
		'.mw46.net',
		'.4cna.net',
		'.myi4.net',
		'.u4nb.net',
		'www.sastrk.com',
		'.qylf.net',
		'.ykwujd.net',
		'.qkweww.net',
		'linkto.hrblock.com',
		'.qwjcdi.net',
		'.c3me6x.net',
		'.3qcw.net',
		'affiliates.abebooks.com',
		'.blbvux.net',
		'.ubertrk.com',
		'.dqxc7i.net',
		'.yoxl.net',
		'.a4xxmk.net',
		'.ywhcc7.net',
		'goto.discover.com',
		'partner.canva.com',
		'.mno8.net',
		'partners.hostgator.com',
		'partners.inmotionhosting.com',
		'.i201009.net',
		'.eqcm.net',
		'.yaub.net',
		'partners.webhostinghub.com',
		'tracking.maxcdn.com',
		'.i200065.net',
		'.uqhv.net',
		'.a5oq.net',
		'.4drrzr.net',
		'.vdcy.net',
		'.y8uw.net',
		'.57ib.net',
		'goto.target.com',
		'.9pctbx.net',
		'r.kraken.com',
		'.536u.net',
		'.qflm.net',
		'.i315845.net',
		'.jtlo.net',
		'.i358819.net',
		'.mxuy67.net',
		'.3k3q.net',
		'.58dp.net',
		'.4cl7.net',
		'.wvr2.net',
		'.syuh.net',
		'.envato.market',
		'.fdcm73.net',
		'.lightstream.com',
		'.ustnul.net',
		'cash.app',
		'www.baselane.com',
		'www.ysense.com',
		'.3yjf29.net',
		'.ihfo.net',
		'.l43qr8.net',
		'.ifgza3.net',
		'.qvig.net',
		'.o9o4.net',
	];

	$partnerstack_domains = [
		'grsm.io',
		'try.drip.com',
		'get.keap.com',
	];

	$everflow_domains = [
		'.eflow.team',
		'www.pubtrack.co',
	];

	$quinstreet_domains = [
		'o1.qnsr.com',
		'g.gituy.com',
	];

	$ads_by_money_domains = [
		'www.consumersadvocate.org',
		'secure.money.com',
		'secure.goodfinancialcents.com',
	];

	if (isset($uri)) {
		try {
			$parseduri = Uri::createFromString($uri);
			
			$query = Query::createFromUri($parseduri);
			$domain = Domain::createFromUri($parseduri);
			$domainname=$domain->getContent();
		}
		catch (Exception $e) {
			$errormsg=$e->getMessage();
			print "EXCEPTION: ".$e."\n\n";
		}
	}
	// set some fake domain to please the PHP gods
	else {
		$uri='https://dummydomain.com/dummy';
		$domainname='dummydomain.com';
	}

	// Manual override
	if ( $tag == 'manual' ) {
		return($system->{'manual'});
	}
	// CPC (generic for all)
	elseif ( $tag == 'cpc' ) {
		return($system->{'cpc'});
	}
	// generic CSV
	elseif ( $tag == 'csv' ) {
		return($system->{'csv'});
	}
	// Adbloom
	elseif ( preg_match('/trk\.adbloom\.co/i',$domainname) ) {
		return($system->{'tune'});
	}
	// Ads By Money
	elseif ( $tag == 'adsbymoney' ) {
		return($system->{'adsbymoney'});
	}
	elseif ( linkclicky_domain_match($domainname, $ads_by_money_domains) ) {
		return($system->{'adsbymoney'});
	}
	// Brainstorm Force
	elseif ( $tag == 'brainstormforce' ) {
		return($system->{'brainstormforce'});
	}
	// ShareASale
	elseif ( $tag == 'shareasale' ) {
		return($system->{'shareasale'});
	}
	elseif ( preg_match('/shareasale\.com/i',$domainname) ) {
		return($system->{'shareasale'});
	}
	// Clickbank 
	elseif ( $tag == 'clickbank' ) {
		return($system->{'clickbank'});
	}
	elseif (preg_match('/clickbank\.net/i',$domainname) ) {
		return($system->{'clickbank'});
	}
	// CommissionSoup 
	elseif ( $tag == 'commissionsoup' ) {
		return($system->{'commissionsoup'});
	}
	elseif (preg_match('/beemrdwn\.com/i',$domainname) ) {
		return($system->{'commissionsoup'});
	}
	elseif (preg_match('/gdlckjoe\.com/i',$domainname) ) {
		return($system->{'commissionsoup'});
	}
	elseif (preg_match('/bytemgdd\.com/i',$domainname) ) {
		return($system->{'commissionsoup'});
	}
	// FlexOffers 
	elseif ( $tag == 'flexoffers' ) {
		return($system->{'flexoffers'});
	}
	elseif (preg_match('/track\.flexlinkspro\.com/i',$domainname) ) {
		return($system->{'flexoffers'});
	}
	// TUNE 
	elseif ( $tag == 'tune' ) {
		return($system->{'tune'});
	}
	elseif (preg_match('/go2cloud\.org/i',$domainname) ) {
		return($system->{'tune'});
	}
	// Panthera
	elseif (preg_match('/bigcattracks\.com/i',$domainname) ) {
		return($system->{'tune'});
	}
	// OfferJuice
	elseif (preg_match('/surveycheck\.com/i',$domainname) ) {
		return($system->{'tune'});
	}
	// Scaleo 
	elseif ( $tag == 'scaleo' ) {
		return($system->{'scaleo'});
	}
	elseif (preg_match('/scaletrk\.com/i',$domainname) ) {
		return($system->{'scaleo'});
	}
	// Impact 
	elseif ( $tag == 'impact' ) {
		return($system->{'impact'});
	}
	elseif ( linkclicky_domain_match($domainname, $impact_domains)) {
		return($system->{'impact'});
	}
	// ThriveCart 
	elseif ( $tag == 'thrivecart' ) {
		return($system->{'thrivecart'});
	}
	elseif (preg_match('/thrivecart\.com/i',$domainname) ) {
		return($system->{'thrivecart'});
	}
	// iDevAffiliate 
	elseif ( $tag == 'idevaffiliate' ) {
		return($system->{'idevaffiliate'});
	}
	elseif (preg_match('/\/idevaffiliate\.php/i',$uri) ) {
		return($system->{'idevaffiliate'});
	}
	elseif (preg_match('/idevaffiliate\.com/i',$domainname) ) {
		return($system->{'idevaffiliate'});
	}
	// Everflow 
	elseif ( $tag == 'everflow' ) {
		return($system->{'everflow'});
	}
	elseif ( linkclicky_domain_match($domainname, $everflow_domains) ) {
		return($system->{'everflow'});
	}
	// MaxBounty 
	elseif ( $tag == 'maxbounty' ) {
		return($system->{'maxbounty'});
	}
	elseif (preg_match('/afflat3c1\.com/i',$domainname) ) {
		return($system->{'maxbounty'});
	}
	// Rakuten Marketing 
	elseif ( $tag == 'rakuten' ) {
		return($system->{'rakuten'});
	}
	elseif (preg_match('/click\.linksynergy\.com/i',$domainname) ) {
		return($system->{'rakuten'});
	}
	// Commission Kings
	elseif ( $tag == 'commissionkings' ) {
		return($system->{'commissionkings'});
	}
	elseif (preg_match('/record\.commissionkings\.ag/i',$domainname) ) {
		return($system->{'commissionkings'});
	}
	// Top One Partners
	elseif ( $tag == 'toponepartners' ) {
		return($system->{'toponepartners'});
	}
	elseif (preg_match('/record\.toponepartners\.com/i',$domainname) ) {
		return($system->{'toponepartners'});
	}
	// Revenue Network
	elseif ( $tag == 'revenuenetwork' ) {
		return($system->{'revenuenetwork'});
	}
	elseif (preg_match('/record\.revenuenetwork\.com/i',$domainname) ) {
		return($system->{'revenuenetwork'});
	}
	// Amazon Associates
	elseif ( $tag == 'amazon' ) {
		return($system->{'amazon'});
	}
	elseif ( linkclicky_domain_match($domainname, $amazon_domains) ) {
		return($system->{'amazon'});
	}
	// CJ 
	elseif ( $tag == 'cj' ) {
		return($system->{'cj'});
	}
	elseif ( linkclicky_domain_match($domainname, $cj_domains) ) {
		return($system->{'cj'});
	}
	// Awin 
	elseif ( $tag == 'awin' ) {
		return($system->{'awin'});
	}
	elseif (preg_match('/awin1\.com/i',$domainname) ) {
		return($system->{'awin'});
	}
	// Affise 
	elseif ( $tag == 'affise' ) {
		return($system->{'affise'});
	}
	// Link Mink
	elseif ( $tag == 'linkmink' ) {
		return($system->{'linkmink'});
	}
	// Affiliate WP
	elseif ( $tag == 'affiliatewp' ) {
		return($system->{'affiliatewp'});
	}
	// Cake
	elseif ( $tag == 'cake' ) {
		return($system->{'cake'});
	}
	// Cellxpert
	elseif ( $tag == 'cellxpert' ) {
		return($system->{'cellxpert'});
	}
	// ClickFunnels
	elseif ( $tag == 'clickfunnels' ) {
		return($system->{'clickfunnels'});
	}
	// JVZoo
	elseif ( $tag == 'jvzoo' ) {
		return($system->{'jvzoo'});
	}
	// PepperJam
	elseif ( $tag == 'pepperjam' ) {
		return($system->{'pepperjam'});
	}
	// Post Affiliate Pro
	elseif ( $tag == 'postaffiliatepro' ) {
		return($system->{'postaffiliatepro'});
	}
	// Shopify
	elseif ( $tag == 'shopify' ) {
		return($system->{'shopify'});
	}
	// Skimlinks
	elseif ( $tag == 'skimlinks' ) {
		return($system->{'skimlinks'});
	}
	elseif ( preg_match('/go\.skimresources\.com/i',$domainname) ) {
		return($system->{'skimlinks'});
	}
	// LinkConnector
	elseif ( $tag == 'linkconnector' ) {
		return($system->{'linkconnector'});
	}
	elseif ( preg_match('/www\.linkconnector\.com/i',$domainname) ) {
		return($system->{'linkconnector'});
	}
	// Tapfiliate
	elseif ( $tag == 'tapfiliate' ) {
		return($system->{'tapfiliate'});
	}
	// Bankrate
	elseif ( $tag == 'bankrate' ) {
		return($system->{'bankrate'});
	}
	elseif ( preg_match('/oc\.brcclx\.com/i',$domainname) ) {
		return($system->{'bankrate'});
	}
	// Fintel Connect
	elseif ( $tag == 'fintelconnect' ) {
		return($system->{'fintelconnect'});
	}
	elseif ( preg_match('/api\.fintelconnect\.com/i',$domainname) ) {
		return($system->{'fintelconnect'});
	}
	// SamCart
	elseif ( $tag == 'samcart' ) {
		return($system->{'samcart'});
	}
	elseif ( preg_match('/\.samcart\.com\/referral\//i',$uri) ) {
		return($system->{'samcart'});
	}
	// Refersion
	elseif ( $tag == 'refersion' ) {
		return($system->{'refersion'});
	}
	// LinkTrust
	elseif ( $tag == 'linktrust' ) {
		return($system->{'linktrust'});
	}
	// FirstPromoter
	elseif ( $tag == 'firstpromoter' ) {
		return($system->{'firstpromoter'});
	}
	// Kartra
	elseif ( $tag == 'kartra' ) {
		return($system->{'kartra'});
	}
	// Partnerize
	if ( $tag == 'partnerize' ) {
		return($system->{'partnerize'});
	}
	elseif ( preg_match('/prf\.hn/i',$domainname) ) {
		return($system->{'partnerize'});
	}
	// Partnerstack
	elseif ( $tag == 'partnerstack' ) {
		return($system->{'partnerstack'});
	}
	elseif ( linkclicky_domain_match($domainname, $partnerstack_domains) ) {
		return($system->{'partnerstack'});
	}
	// Quinstreet
	elseif ( $tag == 'quinstreet' ) {
		return($system->{'quinstreet'});
	}
	elseif ( linkclicky_domain_match($domainname, $quinstreet_domains) ) {
		return($system->{'quinstreet'});
	}
	// Netspend
	elseif ( $tag == 'netspend' ) {
		return($system->{'netspend'});
	}
	// Tradedoubler
	elseif ( $tag == 'tradedoubler' ) {
		return($system->{'tradedoubler'});
	}
	elseif ( preg_match('/tracker\.tradedoubler\.com|clk\.tradedoubler\.com/i',$domainname) ) {
		return($system->{'tradedoubler'});
	}
	// Perform CB
	elseif ( $tag == 'performcb' ) {
		return($system->{'performcb'});
	}
	elseif ( preg_match('/aicl7\.com/i',$domainname) ) {
		return($system->{'performcb'});
	}
	// Webull 
	elseif ( $tag == 'webull' ) {
		return($system->{'webull'});
	}
	elseif ( preg_match('/webull\.com/i',$domainname) ) {
		return($system->{'webull'});
	}
	// IT Media
	elseif ( $tag == 'itmedia' ) {
		return($system->{'itmedia'});
	}
	// test uri for Impact links
	elseif ( linkclicky_is_impact_link($uri) ) {
		return($system->{'impact'});
	}
	// test uri for TUNE links
	elseif ( linkclicky_is_tune($uri) ) {
		return($system->{'tune'});
	}
	// test uri for TUNE links
	elseif ( linkclicky_is_cake($uri) ) {
		return($system->{'cake'});
	}
	// test uri for LinkMink links
	elseif ( linkclicky_is_linkmink($uri) ) {
		return($system->{'linkmink'});
	}
	// test uri for FirstPromoter links
	elseif ( linkclicky_is_firstpromoter($uri) ) {
		return($system->{'firstpromoter'});
	}
	// test uri for Post Affiliate Pro links
	elseif ( linkclicky_is_post_affiliate_pro($uri) ) {
		return($system->{'postaffiliatepro'});
	}
	// test uri for Tapfiliate
	elseif ( linkclicky_is_tapffiliate($uri) ) {
		return($system->{'tapfiliate'});
	}
	// test uri for SkimLinks
	elseif ( linkclicky_is_skimlinks($uri) ) {
		return($system->{'skimlinks'});
	}
	// test uri for SkimLinks
	elseif ( linkclicky_is_quinstreet($uri) ) {
		return($system->{'quinstreet'});
	}

	// no idea or an affiliate system with no subid option
	return($system->{'unknown'});
}

// add subid if not setup so LinkClicky can track conversions
// if already added no changes will be made
function addsubid($uri, $subid1) {
	// what to replace or look for so LinkClicky passes the correct tracking info
	$cmsubidvar='[trackid]';

	$parseduri = Uri::createFromString($uri);
	$query = Query::createFromUri($parseduri);

	// if doesn't match or exist install our variable	
	if ($query->get($subid1) != $cmsubidvar ) {
		$parseduri=UriModifier::mergeQuery($parseduri, "${subid1}=${cmsubidvar}");
		$uri=$parseduri->__toString();
		// url decode what was encoded previous
		$uri=str_replace(urlencode($cmsubidvar),$cmsubidvar,$uri);
	}
	return($uri);
}

// get subid from a uri and return the value
function getsubid($uri, $subid) {
	$parseduri = Uri::createFromString($uri);
	$query = Query::createFromUri($parseduri);
	return($query->get($subid));
}

// add conversionsid postback URL
function addconversionid($uri, $conversionid) {
	$uri=str_replace('[CONVERSIONID]', $conversionid, $uri);
	return($uri);
}

// add subid if not setup so LinkClicky can track conversions
// if already added no changes will be made
function addfintelsubid($uri) {
	// what to replace or look for so LinkClicky passes the correct tracking info
	$cmsubidvar='[trackid]';
	// replace the c-whatever with LinkClicky's variable
	$uri=preg_replace('/\/a-(\d+)b-(\d+)?(c-.+)$/', 'a-${1}b-${2}c-'.$cmsubidvar, $uri);
	return($uri);
}

function addpartnerizesubid($uri) {
	$cmsubidvar='[trackid]';
	$parseduri = Uri::createFromString($uri);
	$path = HierarchicalPath::createFromUri($parseduri);
	$count=0;
	$replace=false;
	foreach ($path as $offset => $segment) {
		// if it finds pubref: replace it with ours
		if (str_starts_with($segment, 'pubref:')) {
			$newpath = $path->withSegment($count, 'pubref:'.$cmsubidvar);
			$replace=true;
		}
		$count++;
	}
	// never found it otherwise add to the very end
	if ($replace === false) {
		$newpath = $path->withSegment($count, 'pubref:'.$cmsubidvar);
	}

	$parseduri = UriModifier::removeBasePath($parseduri, $path);
	$parseduri = UriModifier::addBasePath($parseduri, $newpath->__toString());
	$parseduri = UriModifier::removeTrailingSlash($parseduri);
	
       	$uri=$parseduri->__toString();
	$uri=str_replace(urlencode($cmsubidvar),$cmsubidvar,$uri);
	return($uri);
}

// if already added no changes will be made
function addquinstreetsubid($uri) {
	// what to replace or look for so LinkClicky passes the correct tracking info
	$cmsubidvar='[trackid]';
	$subid1='var2';
	
	$parseduri = Uri::createFromString($uri);
	$query = Query::createFromRFC3986($parseduri, ';');

	// if doesn't match or exist install our variable	
	if ($query->get($subid1) != $cmsubidvar ) {
		$parseduri=UriModifier::mergeQuery($parseduri, "${subid1}=${cmsubidvar}");
		$uri=$parseduri->__toString();
		// url decode what was encoded previous
		$uri=str_replace(urlencode($cmsubidvar),$cmsubidvar,$uri);
	}
	return($uri);
}

function processsubid($uri, $affiliatesys, $subid1) {
	// only run if for tune the addtional link
	if ($affiliatesys == 'tune' ) {
		$uri = addsubid($uri, 'aff_sub5');
	}

	// partnerize needs a 'special' link
	if ($affiliatesys == 'partnerize') {
		return(addpartnerizesubid($uri));
	}
	elseif ($affiliatesys == 'quinstreet') {
		return(addquinstreetsubid($uri));
	}
	elseif ($subid1 != '') {
		return(addsubid($uri, $subid1));
	}
	// just pass the same URL back
	else {
		return($uri);
	}
}

// determine the result and then store to make sure we don't repeat the same transaction
// Requires: Filebase
function linkclicky_postback_results($result, &$filebase) {
	if ( $result->{'code'} == 200 ) {
		print 'Result: Success'."\n";
		$filebase->{'submitted'}=true;
		$filebase->save();
		return(true);
	}
	else {
		$errormsg=$result->headers['Error'];
		print 'Result: Error Uploading - '.$result->{'code'}."\n";
		print 'Error: '.$errormsg."\n";

		// store these errors so not to try again
		if (preg_match('/Can not associate conversion: no hit found/', $errormsg) == true) {
			$filebase->{'submitted'}=true;
			$filebase->save();
		}
		else if (preg_match('/Event trackid not found/', $errormsg) == true) {
			$filebase->{'submitted'}=true;
			$filebase->save();
		}
		else if (preg_match('/Can not associate conversion: invalid S2S identifier/', $errormsg) == true ) {
			$filebase->{'submitted'}=true;
			$lookup->save();
		}
		return(false);
	}
}

function linkclicky_stripdash($row) {
        foreach ($row as $key => $value) {
                if ($value == '-') {
                        $row[$key]='';
                }
        }
        return($row);
}

function google_analytics_extract( $ga ) {
	preg_match('/^(GA\d\.\d)\.(\d+\.\d+)$/', $ga, $matches);

	$version=$matches[1];
	$clientid=$matches[2];

	return( [ $version, $clientid ]);
}

function create_fbc_from_fbclid( $fbclid, $timestamp, $timezone ) {
	$fbcliddate = new DateTime($timestamp, new DateTimeZone( $timezone ) );
	// set timezone of the conversion to what we define
	$fbcliddate->setTimezone(new DateTimeZone('UTC'));
	
	$fbc = 'fb.1.' . $fbcliddate->format( 'U' ) . '.' . $fbclid;

	return( $fbc );
}

function php_binary_mysql_binary($var) {
	if ( $var === false || $var == null) {
		return( 'false' );
	}
	else {
		return ( 'true' );
	}
}
