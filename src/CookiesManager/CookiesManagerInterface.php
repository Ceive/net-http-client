<?php
/**
 * Created by Kutuzov Alexey Konstantinovich <lexus.1995@mail.ru>.
 * Author: Kutuzov Alexey Konstantinovich <lexus.1995@mail.ru>
 * Project: Ceive
 * IDE: PhpStorm
 * Date: 19.10.2016
 * Time: 20:03
 */
namespace Ceive\Net\HttpClient\CookiesManager {
	
	use Ceive\Net\HttpClient\Request;
	use Ceive\Net\HttpFoundation\Cookie;
	use Ceive\Net\HttpFoundation\CookieInterface;
	
	/**
	 * Interface CookiesManagerInterface
	 * @package Ceive\Net\HttpClient
	 */
	interface CookiesManagerInterface{
		
		/**
		 * @param Request $request
		 * @return Cookie[]
		 */
		public function matchSuitable(Request $request);

		/**
		 * @param CookieInterface $cookie
		 */
		public function storeCookie(CookieInterface $cookie);

	}
}

