<?php
/**
 * Created by Kutuzov Alexey Konstantinovich <lexus.1995@mail.ru>.
 * Author: Kutuzov Alexey Konstantinovich <lexus.1995@mail.ru>
 * Project: Ceive
 * IDE: PhpStorm
 * Date: 18.10.2016
 * Time: 16:15
 */
namespace Ceive\Net\HttpClient\CacheManager {
	
	use Ceive\Net\HttpClient\Request;
	
	/**
	 * Interface CacheResourceInterface
	 * @package Ceive\Net\HttpClient\CacheManager\CacheManager
	 */
	interface CacheResourceInterface{


		/**
		 * @param array $vary
		 * @return $this
		 */
		public function setVary(array $vary = null);

		/**
		 * @param Request $request
		 * @return string
		 */
		public function makeSignature(Request $request);

		/**
		 * @param string $vary_signature
		 * @return CacheEntry
		 */
		public function getEntry($vary_signature);

		/**
		 * @return CacheEntry
		 */
		public function createEntry();

		/**
		 * @param $vary_signature
		 * @param CacheEntry $entry
		 * @return $this
		 */
		public function updateEntry($vary_signature, CacheEntry $entry);

		/**
		 * @return bool
		 */
		public function isDirty();

	}
}

