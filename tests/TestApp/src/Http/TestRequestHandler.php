<?php
declare(strict_types = 1);

namespace TestApp\Http;

use Cake\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TestRequestHandler implements RequestHandlerInterface {

	/**
	 * @var \Closure
	 */
	public $callable;

	/**
	 * @param callable|null $callable
	 * @param \Psr\Http\Message\ResponseInterface|null $response
	 */
	public function __construct(?callable $callable = null, ?ResponseInterface $response = null) {
		if ($response === null) {
			$response = new Response();
		}

		$this->callable = $callable ?: function (ServerRequestInterface $request) use ($response) {
			return $response;
		};
	}

	/**
	 * @param \Psr\Http\Message\ServerRequestInterface $request
	 *
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	public function handle(ServerRequestInterface $request): ResponseInterface {
		return ($this->callable)($request);
	}

}
