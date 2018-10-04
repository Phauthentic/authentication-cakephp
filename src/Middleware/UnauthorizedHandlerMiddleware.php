<?php
declare(strict_types=1);
/**
 * Copyright (c) Phauthentic (https://github.com/Phauthentic)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Phauthentic (https://github.com/Phauthentic)
 * @link          https://github.com/Phauthentic
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Phauthentic\Authentication\Middleware;

use Phauthentic\Authentication\Authenticator\Exception\UnauthorizedException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Stream;

class UnauthorizedHandlerMiddleware
{

    /**
     * Callable implementation for the middleware stack.
     *
     * @param ServerRequestInterface $request The request.
     * @param ResponseInterface $response The response.
     * @param callable $next The next middleware to call.
     * @return ResponseInterface A response.
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
    {
        try {
            return $next($request, $response);
        } catch (UnauthorizedException $e) {
            return $this->createUnauthorizedResponse($e, $response);
        }

        return $response;
    }

    /**
     * Creates an unauthorized response.
     *
     * @param UnauthorizedException $e Exception.
     * @param ResponseInterface $response The response.
     * @return ResponseInterface
     */
    protected function createUnauthorizedResponse(UnauthorizedException $e, ResponseInterface $response): ResponseInterface
    {
        $body = new Stream('php://memory', 'rw');
        $body->write($e->getBody());
        $response = $response
            ->withStatus($e->getCode())
            ->withBody($body);

        foreach ($e->getHeaders() as $header => $value) {
            $response = $response->withHeader($header, $value);
        }

        return $response;
    }
}
