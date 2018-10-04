<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         1.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Authentication\Test\TestCase\Middleware;

use Cake\Http\Response;
use Cake\Http\ServerRequestFactory;
use Phauthentic\Authentication\AuthenticationService;
use Phauthentic\Authentication\AuthenticationServiceProviderInterface;
use Phauthentic\Authentication\Authenticator\AuthenticatorCollection;
use Phauthentic\Authentication\Authenticator\FormAuthenticator;
use Phauthentic\Authentication\Identifier\PasswordIdentifier;
use Phauthentic\Authentication\Identifier\Resolver\OrmResolver;
use Phauthentic\Authentication\Identity\DefaultIdentityFactory;
use Phauthentic\Authentication\Middleware\CakeAuthenticationMiddleware;
use Phauthentic\Authentication\Test\TestCase\CakeAuthenticationTestCase;
use Phauthentic\Authentication\UrlChecker\DefaultUrlChecker;
use Phauthentic\PasswordHasher\DefaultPasswordHasher;

class CakeAuthenticationMiddlewareTest extends CakeAuthenticationTestCase
{

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $authenticators = new AuthenticatorCollection([
            new FormAuthenticator(new PasswordIdentifier(new OrmResolver(), new DefaultPasswordHasher), new DefaultUrlChecker())
        ]);

        $this->service = new AuthenticationService($authenticators, new DefaultIdentityFactory());
        $this->provider = $this->createMock(AuthenticationServiceProviderInterface::class);
        $this->provider->method('getAuthenticationService')->willReturn($this->service);
    }

    public function testSuccessfulAuthentication()
    {
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/testpath'],
            [],
            ['username' => 'mariano', 'password' => 'password']
        );
        $response = new Response();
        $next = function ($request, $response) {
            $service = $request->getAttribute('authentication');
            $this->assertSame($this->service, $service);

            $identity = $request->getAttribute('identity');
            $this->assertEquals($this->service->getIdentity(), $identity);

            return $response;
        };

        $middleware = new CakeAuthenticationMiddleware($this->provider);

        $result = $middleware($request, $response, $next);
        $this->assertSame($response, $result);

        $this->assertTrue($this->service->getResult()->isValid());
    }

    /**
     * test middleware call with custom attributes
     *
     * @return void
     */
    public function testCustomAttributes()
    {
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/testpath'],
            [],
            ['username' => 'mariano', 'password' => 'password']
        );
        $response = new Response();
        $next = function ($request, $response) {
            $service = $request->getAttribute('auth');
            $this->assertSame($this->service, $service);

            $identity = $request->getAttribute('id');
            $this->assertEquals($this->service->getIdentity(), $identity);

            return $response;
        };

        $middleware = new CakeAuthenticationMiddleware($this->provider, [
            'serviceAttribute' => 'auth',
            'identityAttribute' => 'id',
        ]);

        $result = $middleware($request, $response, $next);
        $this->assertSame($response, $result);

        $this->assertTrue($this->service->getResult()->isValid());
    }

    /**
     * testNonSuccessfulAuthentication
     *
     * @return void
     */
    public function testNonSuccessfulAuthentication()
    {
        $request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/testpath'],
            [],
            ['username' => 'invalid', 'password' => 'invalid']
        );
        $response = new Response();

        $next = function ($request, $response) {
            $service = $request->getAttribute('authentication');
            $this->assertSame($this->service, $service);

            $identity = $request->getAttribute('identity');
            $this->assertNull($identity);

            return $response;
        };

        $middleware = new CakeAuthenticationMiddleware($this->provider);

        $result = $middleware($request, $response, $next);
        $this->assertSame($response, $result);

        $this->assertFalse($this->service->getResult()->isValid());
    }
}
