<?php
/**
 * Econt API Configuration.
 *
 * @package Easy_Shipping
 */

$config = array(
	'test_url'  => '',
	'live_url'  => '',
	'endpoints' => array(
		'CreateLabel' => array(
			'mode' => array(
				'type' => 'string',
				'required' => false,
				'allowed_values' => array( 'calculate', 'validate', 'create' ),
			),
			'label' => array(
				'type' => 'array',
				'required' => true,
				'senderClient' => array(
					'type' => 'array',
					'required' => true,
					'name' => array(
						'type' => 'string',
						'required' => true,
						'max_size' => 100,
					),
					'nameEn' => array(
						'type' => 'string',
						'required' => false,
						'max_size' => 100,
					),
					'phones' => array(
						'type' => 'array',
						'required' => true,
					),
					'email' => array(
						'type' => 'string',
						'required' => false,
						'max_size' => 100,
					),
				),
				'senderAddress' => array(
					'type' => 'array',
					'required' => false,
					'city' => array(
						'type' => 'array',
						'required' => true,
						'country' => array(
							'type' => 'array',
							'required' => false,
							'code3' => array(
								'type' => 'string',
								'required' => false,
								'max_size' => 3,
							),
						),
						'id' => array(
							'type' => 'int',
							'required' => false,
						),
						'name' => array(
							'type' => 'string',
							'required' => false,
							'max_size' => 100,
						),
						'postCode' => array(
							'type' => 'string',
							'required' => false,
							'max_size' => 10,
						),
					),
					'street' => array(
						'type' => 'string',
						'required' => false,
						'max_size' => 100,
					),
					'num' => array(
						'type' => 'string',
						'required' => false,
						'max_size' => 20,
					),
					'other' => array(
						'type' => 'string',
						'required' => false,
						'max_size' => 255,
					),
					'quarter' => array(
						'type' => 'string',
						'required' => false,
						'max_size' => 100,
					),
				),
				'senderOfficeCode' => array(
					'type' => 'string',
					'required' => false,
					'max_size' => 10,
				),
				'receiverClient' => array(
					'type' => 'array',
					'required' => true,
					'name' => array(
						'type' => 'string',
						'required' => true,
						'max_size' => 100,
					),
					'nameEn' => array(
						'type' => 'string',
						'required' => false,
						'max_size' => 100,
					),
					'phones' => array(
						'type' => 'array',
						'required' => true,
					),
					'email' => array(
						'type' => 'string',
						'required' => false,
						'max_size' => 100,
					),
				),
				'receiverAddress' => array(
					'type' => 'array',
					'required' => false,
					'city' => array(
						'type' => 'array',
						'required' => true,
						'country' => array(
							'type' => 'array',
							'required' => false,
							'code3' => array(
								'type' => 'string',
								'required' => false,
								'max_size' => 3,
							),
						),
						'id' => array(
							'type' => 'int',
							'required' => false,
						),
						'name' => array(
							'type' => 'string',
							'required' => false,
							'max_size' => 100,
						),
						'postCode' => array(
							'type' => 'string',
							'required' => false,
							'max_size' => 10,
						),
					),
					'street' => array(
						'type' => 'string',
						'required' => false,
						'max_size' => 100,
					),
					'num' => array(
						'type' => 'string',
						'required' => false,
						'max_size' => 20,
					),
					'other' => array(
						'type' => 'string',
						'required' => false,
						'max_size' => 255,
					),
					'quarter' => array(
						'type' => 'string',
						'required' => false,
						'max_size' => 100,
					),
				),
				'receiverOfficeCode' => array(
					'type' => 'string',
					'required' => false,
					'max_size' => 10,
				),
				'packCount' => array(
					'type' => 'int',
					'required' => true,
					'min_size' => 1,
				),
				'shipmentType' => array(
					'type' => 'string',
					'required' => true,
					'allowed_values' => array( 'PACK', 'DOCUMENT', 'PALLET', 'CARGO', 'TYRE', 'POSTPAK' ),
				),
				'weight' => array(
					'type' => 'float',
					'required' => true,
					'min_size' => 0.1,
				),
				'sizeUnder60cm' => array(
					'type' => 'bool',
					'required' => false,
				),
				'shipmentDescription' => array(
					'type' => 'string',
					'required' => false,
					'max_size' => 255,
				),
				'orderNumber' => array(
					'type' => 'string',
					'required' => false,
					'max_size' => 50,
				),
				'sendDate' => array(
					'type' => 'date',
					'required' => false,
				),
				'holidayDeliveryDay' => array(
					'type' => 'string',
					'required' => false,
					'allowed_values' => array( 'workday', 'halfworkday', 'holiday' ),
				),
				'keepUpright' => array(
					'type' => 'bool',
					'required' => false,
				),
				'services' => array(
					'type' => 'array',
					'required' => false,
					'cdAmount' => array(
						'type' => 'float',
						'required' => false,
						'min_size' => 0,
					),
					'cdType' => array(
						'type' => 'string',
						'required' => false,
						'allowed_values' => array( 'get', 'give' ),
					),
					'cdCurrency' => array(
						'type' => 'string',
						'required' => false,
						'max_size' => 3,
					),
					'cdPayOptionsCD' => array(
						'type' => 'bool',
						'required' => false,
					),
					'obpaymentAmount' => array(
						'type' => 'float',
						'required' => false,
						'min_size' => 0,
					),
					'declaredValueAmount' => array(
						'type' => 'float',
						'required' => false,
						'min_size' => 0,
					),
					'declaredValueCurrency' => array(
						'type' => 'string',
						'required' => false,
						'max_size' => 3,
					),
					'declaredValueFragile' => array(
						'type' => 'bool',
						'required' => false,
					),
					'invoiceNum' => array(
						'type' => 'string',
						'required' => false,
						'max_size' => 50,
					),
					'invoiceDate' => array(
						'type' => 'date',
						'required' => false,
					),
					'smsNotification' => array(
						'type' => 'bool',
						'required' => false,
					),
					'specialDeliveryId' => array(
						'type' => 'int',
						'required' => false,
					),
				),
				'payAfterAccept' => array(
					'type' => 'bool',
					'required' => false,
				),
				'payAfterTest' => array(
					'type' => 'bool',
					'required' => false,
				),
				'packingListType' => array(
					'type' => 'string',
					'required' => false,
				),
				'instruction' => array(
					'type' => 'array',
					'required' => false,
				),
			),
		),
		'CreateLabels' => array(
			'mode' => array(
				'type' => 'string',
				'required' => false,
				'allowed_values' => array( 'calculate', 'validate', 'create' ),
			),
			'labels' => array(
				'type' => 'array',
				'required' => true,
			),
		),
		'DeleteLabels' => array(
			'shipmentNumbers' => array(
				'type' => 'array',
				'required' => true,
			),
		),
		'RequestCourier' => array(
			'requestTimeFrom' => array(
				'type' => 'string',
				'required' => true,
			),
			'requestTimeTo' => array(
				'type' => 'string',
				'required' => true,
			),
			'shipmentType' => array(
				'type' => 'string',
				'required' => true,
				'allowed_values' => array( 'PACK', 'DOCUMENT', 'PALLET', 'CARGO', 'TYRE', 'POSTPAK' ),
			),
			'shipmentPackCount' => array(
				'type' => 'int',
				'required' => true,
				'min_size' => 1,
			),
			'shipmentWeight' => array(
				'type' => 'float',
				'required' => true,
				'min_size' => 0.1,
			),
			'senderClient' => array(
				'type' => 'array',
				'required' => true,
				'name' => array(
					'type' => 'string',
					'required' => true,
					'max_size' => 100,
				),
				'phones' => array(
					'type' => 'array',
					'required' => true,
				),
			),
			'senderAddress' => array(
				'type' => 'array',
				'required' => false,
				'city' => array(
					'type' => 'array',
					'required' => true,
					'id' => array(
						'type' => 'int',
						'required' => false,
					),
					'name' => array(
						'type' => 'string',
						'required' => false,
						'max_size' => 100,
					),
					'postCode' => array(
						'type' => 'string',
						'required' => false,
						'max_size' => 10,
					),
				),
				'street' => array(
					'type' => 'string',
					'required' => false,
					'max_size' => 100,
				),
				'num' => array(
					'type' => 'string',
					'required' => false,
					'max_size' => 20,
				),
			),
			'senderOfficeCode' => array(
				'type' => 'string',
				'required' => false,
				'max_size' => 10,
			),
		),
		'GetShipmentStatuses' => array(
			'shipmentNumbers' => array(
				'type' => 'array',
				'required' => true,
			),
		),
		'GetCities' => array(
			'countryCode' => array(
				'type' => 'string',
				'required' => false,
				'max_size' => 3,
			),
			'id' => array(
				'type' => 'int',
				'required' => false,
			),
			'idType' => array(
				'type' => 'string',
				'required' => false,
			),
			'name' => array(
				'type' => 'string',
				'required' => false,
				'max_size' => 100,
			),
		),
		'GetOffices' => array(
			'countryCode' => array(
				'type' => 'string',
				'required' => false,
				'max_size' => 3,
			),
			'cityID' => array(
				'type' => 'int',
				'required' => false,
			),
			'name' => array(
				'type' => 'string',
				'required' => false,
				'max_size' => 100,
			),
			'code' => array(
				'type' => 'string',
				'required' => false,
				'max_size' => 10,
			),
		),
		'GetStreets' => array(
			'cityID' => array(
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
			'cityID' => array(
				'type' => 'int',
				'required' => true,
			),
			'name' => array(
				'type' => 'string',
				'required' => false,
				'max_size' => 100,
			),
		),
		'GetCountries' => array(),
	)
); 
