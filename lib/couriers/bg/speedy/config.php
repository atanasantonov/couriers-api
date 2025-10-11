<?php
/**
 * Speedy API Configuration.
 *
 * @package Easy_Shipping
 */

$config = array(
	'test_url'  => 'https://api.speedy.bg/api/v1',
	'live_url'  => 'https://api.speedy.bg/api/v1',
	'supported_countries' => array(
		'BG' => 'Bulgaria',
		'RO' => 'Romania',
		'GR' => 'Greece',
		'HR' => 'Croatia',
		'SI' => 'Slovenia',
		'HU' => 'Hungary',
		'CZ' => 'Czech Republic',
		'SK' => 'Slovakia',
		'PL' => 'Poland',
	),
	'endpoints' => array(
		// Calculation endpoint
		'calculate' => array(
			'userName' => array(
				'type' => 'string',
				'required' => true,
			),
			'password' => array(
				'type' => 'string',
				'required' => true,
			),
			'language' => array(
				'type' => 'string',
				'required' => false,
			),
			'sender' => array(
				'type' => 'array',
				'required' => false,
			),
			'recipient' => array(
				'type' => 'array',
				'required' => true,
			),
			'service' => array(
				'type' => 'array',
				'required' => true,
			),
			'content' => array(
				'type' => 'array',
				'required' => true,
			),
			'payment' => array(
				'type' => 'array',
				'required' => false,
			),
		),

		// Shipment creation endpoint
		'shipment' => array(
			'userName' => array(
				'type' => 'string',
				'required' => true,
			),
			'password' => array(
				'type' => 'string',
				'required' => true,
			),
			'language' => array(
				'type' => 'string',
				'required' => false,
			),
			'sender' => array(
				'type' => 'array',
				'required' => true,
			),
			'recipient' => array(
				'type' => 'array',
				'required' => true,
			),
			'service' => array(
				'type' => 'array',
				'required' => true,
			),
			'content' => array(
				'type' => 'array',
				'required' => true,
			),
			'payment' => array(
				'type' => 'array',
				'required' => false,
			),
			'ref1' => array(
				'type' => 'string',
				'required' => false,
			),
			'ref2' => array(
				'type' => 'string',
				'required' => false,
			),
		),

		// Tracking endpoint
		'track' => array(
			'userName' => array(
				'type' => 'string',
				'required' => true,
			),
			'password' => array(
				'type' => 'string',
				'required' => true,
			),
			'language' => array(
				'type' => 'string',
				'required' => false,
			),
			'parcels' => array(
				'type' => 'array',
				'required' => true,
			),
		),

		// Location - Countries
		'location/country' => array(
			'userName' => array(
				'type' => 'string',
				'required' => true,
			),
			'password' => array(
				'type' => 'string',
				'required' => true,
			),
			'language' => array(
				'type' => 'string',
				'required' => false,
			),
		),

		// Location - Sites (Cities)
		'location/site' => array(
			'userName' => array(
				'type' => 'string',
				'required' => true,
			),
			'password' => array(
				'type' => 'string',
				'required' => true,
			),
			'language' => array(
				'type' => 'string',
				'required' => false,
			),
			'countryId' => array(
				'type' => 'int',
				'required' => false,
			),
			'name' => array(
				'type' => 'string',
				'required' => false,
			),
		),

		// Location - Offices
		'location/office' => array(
			'userName' => array(
				'type' => 'string',
				'required' => true,
			),
			'password' => array(
				'type' => 'string',
				'required' => true,
			),
			'language' => array(
				'type' => 'string',
				'required' => false,
			),
			'countryId' => array(
				'type' => 'int',
				'required' => false,
			),
			'siteId' => array(
				'type' => 'int',
				'required' => false,
			),
			'name' => array(
				'type' => 'string',
				'required' => false,
			),
		),

		// Location - Streets
		'location/street' => array(
			'userName' => array(
				'type' => 'string',
				'required' => true,
			),
			'password' => array(
				'type' => 'string',
				'required' => true,
			),
			'language' => array(
				'type' => 'string',
				'required' => false,
			),
			'siteId' => array(
				'type' => 'int',
				'required' => true,
			),
			'name' => array(
				'type' => 'string',
				'required' => false,
			),
		),

		// Location - Complexes (Quarters)
		'location/complex' => array(
			'userName' => array(
				'type' => 'string',
				'required' => true,
			),
			'password' => array(
				'type' => 'string',
				'required' => true,
			),
			'language' => array(
				'type' => 'string',
				'required' => false,
			),
			'siteId' => array(
				'type' => 'int',
				'required' => true,
			),
			'name' => array(
				'type' => 'string',
				'required' => false,
			),
		),

		// Services endpoint
		'services' => array(
			'userName' => array(
				'type' => 'string',
				'required' => true,
			),
			'password' => array(
				'type' => 'string',
				'required' => true,
			),
			'language' => array(
				'type' => 'string',
				'required' => false,
			),
			'date' => array(
				'type' => 'date',
				'required' => false,
			),
		),

		// Print endpoint
		'print' => array(
			'userName' => array(
				'type' => 'string',
				'required' => true,
			),
			'password' => array(
				'type' => 'string',
				'required' => true,
			),
			'language' => array(
				'type' => 'string',
				'required' => false,
			),
			'parcels' => array(
				'type' => 'array',
				'required' => true,
			),
			'paperSize' => array(
				'type' => 'string',
				'required' => false,
			),
		),
	),
);