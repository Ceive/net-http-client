<?php
/**
 * Created by Kutuzov Alexey Konstantinovich <lexus.1995@mail.ru>.
 * Author: Kutuzov Alexey Konstantinovich <lexus.1995@mail.ru>
 * Project: Ceive
 * IDE: PhpStorm
 * Date: 19.10.2016
 * Time: 20:26
 */
namespace Ceive\Net\HttpClient\CookiesManager {
	
	use Ceive\Net\HttpFoundation\CookieInterface;
	
	/**
	 * Class MemoryCookiesManager
	 * @package Ceive\Net\HttpClient\CookiesManager
	 */
	class MemoryCookiesManager extends CookiesManager{

		/** @var CookieInterface[]  */
		protected $cookies = [];

		/**
		 * @param $generalDomain
		 * @return CookieInterface[]
		 */
		protected function getDomainCookies($generalDomain){
			return isset($this->cookies[$generalDomain])?$this->cookies[$generalDomain]:[];
		}

		/**
		 * @param CookieInterface $cookie
		 */
		public function storeCookie(CookieInterface $cookie){
			$name = $cookie->getName();
			$general = $this->getGeneralDomain($cookie->getDomain());
			if(!isset($this->cookies[$general])){
				$this->cookies[$general] = [];
			}
			$this->cookies[$general][$name] = $cookie;
		}
	}
}

