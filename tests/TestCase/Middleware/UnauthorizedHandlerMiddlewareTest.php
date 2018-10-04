<?php
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
namespace Authentication\Test\TestCase\Middleware;

use Cake\TestSuite\TestCase;
use Phauthentic\Authentication\Authenticator\Exception\UnauthorizedException;
use Phauthentic\Authentication\Middleware\UnauthorizedHandlerMiddleware;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;

class UnauthorizedHandlerMiddlewareTest extends TestCase
{
    public function testHandle()
    {
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/testpath'],
            [],
            ['username' => 'mariano', 'password' => 'password']
        );
        $response = new Response();
        $next = function ($request, $response) {
            throw new UnauthorizedException([
                'Foo' => 'Bar',
            ], 'Authentication required.');
        };

        $middleware = new UnauthorizedHandlerMiddleware();

        $result = $middleware($request, $response, $next);

        $this->assertEquals('Bar', $result->getHeaderLine('Foo'));
        $this->assertEquals('Authentication required.', $result->getBody());
        $this->assertEquals(401, $result->getStatusCode());
    }
}
