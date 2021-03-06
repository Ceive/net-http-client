<?php
/**
 * Created by Kutuzov Alexey Konstantinovich <lexus.1995@mail.ru>.
 * Author: Kutuzov Alexey Konstantinovich <lexus.1995@mail.ru>
 * Project: Ceive
 * IDE: PhpStorm
 * Date: 02.07.2016
 * Time: 18:44
 */
namespace Ceive\Net\HttpClient {
	
	use Ceive\Net\HttpFoundation\Cookie;
	use Ceive\Net\HttpFoundation\RequestInterface;
	use Ceive\Net\HttpFoundation\ResponseInterface;
	use Ceive\Net\HttpFoundation\ResponseOnClientInterface;
	use Ceive\Net\HttpFoundation\ResponseSettableInterface;
	use Ceive\Net\HttpFoundation\ServerInterface;
	use Ceive\Net\Hypertext\Document;
	use Ceive\Net\Hypertext\Document\ReadProcessor;
	use Ceive\Net\Hypertext\Document\WriteProcessor;
	use Ceive\Net\URL;
	use Ceive\Util\Communication\Http;
	
	/**
	 * Class Response
	 * @package Ceive\Net\HttpClient
	 */
	class Response extends Document implements ResponseInterface,ResponseSettableInterface, ResponseOnClientInterface{

		use \Ceive\Util\PropContainer\PropContainerOptionTrait;

		/** @var  Request */
		protected $request;

		/** @var  int */
		protected $code;

		/** @var  string */
		protected $message;

		/** @var  Cookie[]  */
		protected $cookies = [];

		/** @var  bool  */
		protected $reused = false;

		/** @var  bool  */
		protected $loading = false;


		/**
		 * @return Request|RequestInterface
		 */
		public function getRequest(){
			return $this->request;
		}

		/**
		 * @return Server|ServerInterface
		 */
		public function getServer(){
			return $this->request->getServer();
		}

		/**
		 * @param bool|true $loading
		 * @return $this
		 */
		public function setLoading($loading = true){
			$this->loading = $loading;
			return $this;
		}

		/**
		 * @return bool
		 */
		public function isLoading(){
			return $this->loading;
		}

		/**
		 * @param int $code
		 * @return $this
		 */
		public function setCode($code){
			$this->code = $code;
			return $this;
		}

		/**
		 * @return int
		 */
		public function getCode(){
			return $this->code;
		}


		/**
		 * @param string $message
		 * @return $this
		 */
		public function setMessage($message){
			$this->message = $message;
			return $this;
		}

		/**
		 * @return string
		 */
		public function getMessage(){
			return $this->message;
		}

		/**
		 * @return mixed
		 */
		public function getContentType(){
			return $this->getHeader('Content-Type');
		}

		/**
		 * @return mixed
		 */
		public function getContentDisposition(){
			return $this->getHeader('Content-Disposition');
		}

		/**
		 * @return bool
		 */
		public function isRedirect(){
			return isset($this->headers['Location']);
		}

		/**
		 * @param bool $asArray
		 * @return null|string
		 */
		public function getRedirectUrl($asArray = false){
			if(isset($this->headers['Location'])){
				return URL::absoluteUrl($this->getHeader('Location'), [
					'host'      => $this->getServer()->getHost(),
					'scheme'    => $this->getRequest()->getScheme(),
					'path'      => $this->getRequest()->getUri()
				],$asArray);
			}
			return null;
		}

		/**
		 * @return Request
		 */
		public function getRedirectRequest(){
			$url = $this->getRedirectUrl();
			$method = $this->request->getMethod();
			if($this->getCode() === 303){
				$method = 'get';
			}
			return $this->request->getAgent()->createRequest($url, $method);
		}

		/**
		 * @return mixed
		 */
		public function isContentLocated(){
			return isset($this->headers['Content-Location']);
		}

		/**
		 * @param bool $asArray
		 * @return mixed
		 */
		public function getContentLocationUrl($asArray = false){
			if(isset($this->headers['Content-Location'])){
				return URL::absoluteUrl($this->getHeader('Content-Location'), [
					'host'      => $this->getServer()->getHost(),
					'scheme'    => $this->getRequest()->getScheme(),
					'path'      => $this->getRequest()->getUri()
				],$asArray);
			}
			return null;
		}


		/**
		 * @param RequestInterface $request
		 * @return mixed
		 */
		public function setRequest(RequestInterface $request){
			$this->request = $request;
			return $this;
		}

		/**
		 * @param array|string $key
		 * @param $value
		 * @param int $expires
		 * @param string $path
		 * @param null $secure
		 * @param null $httpOnly
		 * @param null $domain
		 * @return mixed
		 */
		public function setCookie(
			$key, $value = null, $expires = null, $path = null, $secure = null, $httpOnly = null, $domain = null
		){
			if(is_array($key)){
				$key = array_replace([
					'key'       => null,
					'value'     => null,
					'domain'     => null,
					'expires'   => null,
					'path'      => null,
					'secure'    => null,
					'httpOnly'  => null,
				],$key);
				extract($key,EXTR_OVERWRITE);
			}

			if(!$domain){
				$domain = $this->request->getServer()->getDomain();
			}

			$cookie = new Cookie();
			$cookie->setName($key);
			$cookie->setValue($value);
			$cookie->setExpires($expires);
			$cookie->setPath($path);
			$cookie->setSecure($secure);
			$cookie->setHttpOnly($httpOnly);
			$cookie->setDomain($domain);
			$this->cookies[$key] = $cookie;
		}


		/**
		 * @param $key
		 * @return Cookie
		 */
		public function getCookie($key){
			return isset($this->cookies[$key])?$this->cookies[$key]:null;
		}

		/**
		 * @return Cookie[]
		 */
		public function getCookies(){
			return $this->cookies;
		}

		/**
		 * @param $type
		 * @return mixed
		 */
		public function setContentType($type){
			$this->setHeader('Content-Type', $type);
			return $this;
		}

		/**
		 * @param $disposition
		 * @return mixed
		 */
		public function setContentDisposition($disposition){
			$this->setHeader('Content-Disposition', $disposition);
			return $this;
		}


		/**
		 * @param WriteProcessor $writer
		 */
		public function beforeWrite(WriteProcessor $writer){
			/** Meta Header Write */
			$writer->write("HTTP/1.1 {$this->code} {$this->message}\r\n");
		}


		/**
		 * @param Document\ReadProcessor $reader
		 * @return mixed
		 */
		public function onHeadersRead(Document\ReadProcessor $reader){
			$collection = $this->getHeaderCollection('Set-Cookie');
			foreach($collection as $cookieString){
				$cookie = Http::parseCookieString($cookieString);
				$this->setCookie($cookie);
			}

			/** @var Agent $browser */
			$request = $this->request;
			$agent = $request->getAgent();
			$agent->onResponseHeadersLoaded($this,$request);
			$agent->updateCacheHeaders($this,$request);
		}

		/**
		 * @param ReadProcessor $reader
		 */
		public function afterRead(ReadProcessor $reader){
			/** Accept response and pass stream */
			$request = $this->request;

			$request->getServer()->onResponse($this, $request);
			$agent = $request->getAgent();
			$agent->onResponse($this, $request);
			$agent->updateCache($this, $request);

		}

		/**
		 * @param ReadProcessor $writer
		 * @return mixed|void
		 */
		public function continueRead(ReadProcessor $writer){
			$this->loading = false;
		}


		/**
		 * @param $data
		 * @param $readingIndex
		 * @return mixed|void
		 */
		public function beforeHeaderRead($data, $readingIndex){
			/** Meta Header Read */
			if($readingIndex===0){
				list($protocol, $code, $message) = explode(' ',$data,3);
				$this->code = intval($code);
				$this->message = $message;
				return false;
			}
			return null;
		}

		/**
		 * @return string
		 * @throws \Exception
		 */
		public function render(){
			$this->cacheable = true;
			return parent::render();
		}

		/**
		 * @param Response $response
		 * @param bool $onValidate
		 * @return $this
		 */
		public function reuse(Response $response, $onValidate = false){
			if($onValidate){
				$content = $response->getContent();
				$this->setContent($content);
				$this->setContentType($response->getContentType());
				$this->setHeader('Content-Length', $response->getHeader('Content-Length'));
			}else{
				$this->setCode($response->getCode());
				$this->setMessage($response->getMessage());
				$this->setHeaders($response->getHeaders());
				$this->setContent($response->getContent());
			}
			$this->reused = true;
			return $this;
		}

		/**
		 * @return bool
		 */
		public function isReused(){
			return $this->reused;
		}

	}
}

