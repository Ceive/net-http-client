<?php
/**
 * Created by Kutuzov Alexey Konstantinovich <lexus.1995@mail.ru>.
 * Author: Kutuzov Alexey Konstantinovich <lexus.1995@mail.ru>
 * Project: Ceive
 * IDE: PhpStorm
 * Date: 19.10.2016
 * Time: 23:37
 */
namespace Ceive\Net\HttpClient {
	
	use Ceive\Net\Connection\ConnectorInterface;
	use Ceive\Net\Connection\SecureConnector;
	
	/**
	 * Class AgentFrame
	 * @package Ceive\Net\HttpClient
	 */
	class AgentFrame extends AgentDecorator{

		protected $connector;
		
		/**
		 * AgentFrame constructor.
		 * @param Agent $agent
		 * @param ConnectorInterface $connector
		 */
		public function __construct(Agent $agent, ConnectorInterface $connector){
			$this->connector    = $connector;
			$this->agent        = $agent;
		}

		/**
		 * @return ConnectorInterface
		 */
		protected function getConnector(){
			return $this->connector;
		}

		/**
		 * @return SecureConnector
		 */
		protected function getSecureConnector(){
			return parent::getSecureConnector();
		}


	}
}

