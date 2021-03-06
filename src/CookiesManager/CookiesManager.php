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
	use Ceive\Net\HttpFoundation\CookieInterface;
	use Ceive\Net\URL;
	
	/**
	 * Class CookiesManager
	 * @package Ceive\Net\HttpClient
	 */
	abstract class CookiesManager implements CookiesManagerInterface{

		/**
		 * @param Request $request
		 * @return CookieInterface[]
		 */
		public function matchSuitable(Request $request){
			$domain = $request->getServer()->getDomain();
			$general = $this->getGeneralDomain($domain);
			$cookies = $this->getDomainCookies($general);
			$a = [];
			foreach($cookies as $cookie){
				$name = $cookie->getName();
				if($request->hasCookie($name)){
					continue;
				}
				if(!$cookie->checkDomain($domain)){
					continue;
				}
				if($cookie->isSecure() && !$request->isSecure()){
					continue;
				}
				if($cookie->isHttpOnly() && $request->hasHeader('Sec-WebSocket-Protocol')){
					continue;
				}
				if(strcmp($cookie->getPath(),$request->getUri()) > 0){
					continue;
				}
				if($cookie->isOverdue()){
					continue;
				}
				$a[] = $cookie;
			}
			return $a;
		}

		/**
		 * @param $domain
		 * @return string
		 */
		protected function getGeneralDomain($domain){
			return URL::getBaseDomain($domain);
		}


		/**
		 * @param $base_domain
		 * @return CookieInterface[]
		 */
		abstract protected function getDomainCookies($base_domain);

	}
}

