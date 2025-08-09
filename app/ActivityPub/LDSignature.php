<?php

/*

Adaptado desde:

https://github.com/friendica/friendica/blob/develop/src/Util/LDSignature.php

que a su vez es Ported from Osada: https://framagit.org/macgirvin/osada

*/
 
 
// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace App\ActivityPub;
use Illuminate\Support\Facades\Log;


/**
  * Implements JSON-LD signatures
 *
 * Ported from Osada: https://framagit.org/macgirvin/osada
 * Basado en friendica (buscar url)
 */
class LDSignature
{

	public function __construct()
	{
		require_once app_path('../jsonldsignature/JsonLD.php');
	}
        
	public static function verify(array $data,$pubkey)
	{
		if (!isset($data['signature'])) return false;
		$ohash = self::hash(self::signableOptions($data['signature']));
		$dhash = self::hash(self::signableData($data));
		return openssl_verify($ohash . $dhash, base64_decode($data['signature']['signatureValue']), $pubkey,OPENSSL_ALGO_SHA256);
	}

	public static function sign(array $data, $creator,$privkey): array
	{
		$options = [
			'type'    => 'RsaSignature2017',
			'nonce'   => bin2hex(random_bytes(32)), // 32 bytes = 64 caracteres hexadecimales
			'creator' => $creator,
			'created' => $data["published"]
		];

		$ohash                     = self::hash(self::signableOptions($options));
		$dhash                     = self::hash(self::signableData($data));
		#$options['signatureValue'] = base64_encode(Crypto::rsaSign($ohash . $dhash, $owner['uprvkey']));
		openssl_sign($ohash . $dhash,$signed,$privkey,OPENSSL_ALGO_SHA256);
		if ($signed)
		{
			$options['signatureValue']=base64_encode($signed);
		}

		return array_merge($data, ['signature' => $options]);
	}



	/**
	 * Removes element 'signature' from array
	 *
	 * @param array $data
	 * @return array With no element 'signature'
	 */
	private static function signableData(array $data): array
	{
		unset($data['signature']);
		return $data;
	}

	/**
	 * Removes some elements and adds '@context' to it
	 *
	 * @param array $options
	 * @return array With some removed elements and added '@context' element
	 */
	private static function signableOptions(array $options): array
	{
		$newopts = ['@context' => 'https://w3id.org/identity/v1'];

		unset($options['type']);
		unset($options['id']);
		unset($options['signatureValue']);

		return array_merge($newopts, $options);
	}

	/**
	 * Hashes normalized object
	 *
	 * @param array $obj
	 * @return string SHA256 hash
	 */
	private static function hash($obj): string
	{
		return hash('sha256', \JsonLD::normalize($obj));
	}
}
