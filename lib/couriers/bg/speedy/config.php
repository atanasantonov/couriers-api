<?php
/**
 * Speedy API Configuration.
 *
 * @package Easy_Shipping
 */

$config = array(
	'test_url'  => 'https://demo.speedy.bg/api/v1',
	'live_url'  => 'https://api.speedy.bg/v1',
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
	'Calculate' => array(
		'sender' => array(
			'type' => 'array',
			'required' => true,
			'siteId' => array(
				'type' => 'int',
				'required' => false,
			),
			'contactName' => array(
				'type' => 'string',
				'required' => false,
				'max_size' => 100,
			),
			'phone1' => array(
				'type' => 'string',
				'required' => false,
				'max_size' => 20,
			),
			'email' => array(
				'type' => 'string',
				'required' => false,
				'max_size' => 100,
			),
			'address' => array(
				'type' => 'array',
				'required' => true,
				'countryId' => array(
					'type' => 'int',
					'required' => true,
				),
				'siteId' => array(
					'type' => 'int',
					'required' => false,
				),
				'postCode' => array(
					'type' => 'string',
					'required' => false,
					'max_size' => 10,
				),
				'streetName' => array(
					'type' => 'string',
					'required' => false,
					'max_size' => 100,
				),
				'streetNo' => array(
					'type' => 'string',
					'required' => false,
					'max_size' => 20,
				),
			),
		),
		'recipient' => array(
			'type' => 'array',
			'required' => true,
			'contactName' => array(
				'type' => 'string',
				'required' => true,
				'max_size' => 100,
			),
			'phone1' => array(
				'type' => 'string',
				'required' => true,
				'max_size' => 20,
			),
			'email' => array(
				'type' => 'string',
				'required' => false,
				'max_size' => 100,
			),
			'address' => array(
				'type' => 'array',
				'required' => true,
				'countryId' => array(
					'type' => 'int',
					'required' => true,
				),
				'siteId' => array(
					'type' => 'int',
					'required' => false,
				),
				'postCode' => array(
					'type' => 'string',
					'required' => false,
					'max_size' => 10,
				),
				'streetName' => array(
					'type' => 'string',
					'required' => false,
					'max_size' => 100,
				),
				'streetNo' => array(
					'type' => 'string',
					'required' => false,
					'max_size' => 20,
				),
			),
		),
		'service' => array(
			'type' => 'array',
			'required' => true,
			'serviceId' => array(
				'type' => 'int',
				'required' => true,
			),
			'additionalServices' => array(
				'type' => 'array',
				'required' => false,
			),
		),
		'content' => array(
			'type' => 'array',
			'required' => true,
			'parcelsCount' => array(
				'type' => 'int',
				'required' => true,
				'min_size' => 1,
			),
			'totalWeight' => array(
				'type' => 'float',
				'required' => true,
				'min_size' => 0.1,
			),
			'contents' => array(
				'type' => 'string',
				'required' => false,
				'max_size' => 255,
			),
			'package' => array(
				'type' => 'string',
				'required' => false,
				'max_size' => 50,
			),
		),
		'payment' => array(
			'type' => 'array',
			'required' => false,
			'courierServicePayer' => array(
				'type' => 'string',
				'required' => false,
			),
			'declaredValuePayer' => array(
				'type' => 'string',
				'required' => false,
			),
			'packagePayer' => array(
				'type' => 'string',
				'required' => false,
			),
		),
	),
	'Shipment' => array(
		'sender' => array(
			'type' => 'array',
			'required' => true,
			'clientId' => array(
				'type' => 'int',
				'required' => false,
			),
			'contactName' => array(
				'type' => 'string',
				'required' => true,
				'max_size' => 100,
			),
			'phone1' => array(
				'type' => 'string',
				'required' => true,
				'max_size' => 20,
			),
			'email' => array(
				'type' => 'string',
				'required' => false,
				'max_size' => 100,
			),
			'address' => array(
				'type' => 'array',
				'required' => true,
				'countryId' => array(
					'type' => 'int',
					'required' => true,
				),
				'siteId' => array(
					'type' => 'int',
					'required' => false,
				),
				'postCode' => array(
					'type' => 'string',
					'required' => false,
					'max_size' => 10,
				),
				'streetName' => array(
					'type' => 'string',
					'required' => false,
					'max_size' => 100,
				),
				'streetNo' => array(
					'type' => 'string',
					'required' => false,
					'max_size' => 20,
				),
			),
		),
		'recipient' => array(
			'type' => 'array',
			'required' => true,
			'contactName' => array(
				'type' => 'string',
				'required' => true,
				'max_size' => 100,
			),
			'phone1' => array(
				'type' => 'string',
				'required' => true,
				'max_size' => 20,
			),
			'email' => array(
				'type' => 'string',
				'required' => false,
				'max_size' => 100,
			),
			'address' => array(
				'type' => 'array',
				'required' => true,
				'countryId' => array(
					'type' => 'int',
					'required' => true,
				),
				'siteId' => array(
					'type' => 'int',
					'required' => false,
				),
				'postCode' => array(
					'type' => 'string',
					'required' => false,
					'max_size' => 10,
				),
				'streetName' => array(
					'type' => 'string',
					'required' => false,
					'max_size' => 100,
				),
				'streetNo' => array(
					'type' => 'string',
					'required' => false,
					'max_size' => 20,
				),
			),
		),
		'service' => array(
			'type' => 'array',
			'required' => true,
			'serviceId' => array(
				'type' => 'int',
				'required' => true,
			),
			'pickupDate' => array(
				'type' => 'date',
				'required' => false,
			),
			'additionalServices' => array(
				'type' => 'array',
				'required' => false,
			),
		),
		'content' => array(
			'type' => 'array',
			'required' => true,
			'parcelsCount' => array(
				'type' => 'int',
				'required' => true,
				'min_size' => 1,
			),
			'totalWeight' => array(
				'type' => 'float',
				'required' => true,
				'min_size' => 0.1,
			),
			'contents' => array(
				'type' => 'string',
				'required' => false,
				'max_size' => 255,
			),
			'package' => array(
				'type' => 'string',
				'required' => false,
				'max_size' => 50,
			),
			'goodsValue' => array(
				'type' => 'float',
				'required' => false,
				'min_size' => 0,
			),
		),
		'payment' => array(
			'type' => 'array',
			'required' => false,
			'courierServicePayer' => array(
				'type' => 'string',
				'required' => false,
			),
			'declaredValuePayer' => array(
				'type' => 'string',
				'required' => false,
			),
			'packagePayer' => array(
				'type' => 'string',
				'required' => false,
			),
		),
		'ref1' => array(
			'type' => 'string',
			'required' => false,
			'max_size' => 35,
		),
		'ref2' => array(
			'type' => 'string',
			'required' => false,
			'max_size' => 35,
		),
	),
	'Track' => array(
		'parcels' => array(
			'type' => 'array',
			'required' => true,
		),
		'language' => array(
			'type' => 'string',
			'required' => false,
		),
	),
	'ShipmentSearch' => array(
		'clientId' => array(
			'type' => 'int',
			'required' => false,
		),
		'dateFrom' => array(
			'type' => 'date',
			'required' => false,
		),
		'dateTo' => array(
			'type' => 'date',
			'required' => false,
		),
		'ref1' => array(
			'type' => 'string',
			'required' => false,
			'max_size' => 35,
		),
		'ref2' => array(
			'type' => 'string',
			'required' => false,
			'max_size' => 35,
		),
	),
	'GetCountries' => array(),
	'GetSites' => array(
		'countryId' => array(
			'type' => 'int',
			'required' => false,
		),
		'name' => array(
			'type' => 'string',
			'required' => false,
			'max_size' => 100,
		),
	),
	'GetStreets' => array(
		'siteId' => array(
			'type' => 'int',
			'required' => true,
		),
		'name' => array(
			'type' => 'string',
			'required' => false,
			'max_size' => 100,
		),
	),
	'GetQuarters' => array(
		'siteId' => array(
			'type' => 'int',
			'required' => true,
		),
		'name' => array(
			'type' => 'string',
			'required' => false,
			'max_size' => 100,
		),
	),
	'GetOffices' => array(
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
			'max_size' => 100,
		),
	),
	'GetServices' => array(
		'date' => array(
			'type' => 'date',
			'required' => false,
		),
	),
	),
);