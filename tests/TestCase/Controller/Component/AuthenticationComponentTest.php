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
namespace Phauthentic\Authentication\Test\TestCase\Identifier;

use ArrayObject;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Event\EventList;
use Cake\Event\EventManager;
use Cake\Http\Response;
use Cake\Http\ServerRequestFactory;
use Exception;
use Phauthentic\Authentication\AuthenticationService;
use Phauthentic\Authentication\Authenticator\AuthenticatorCollection;
use Phauthentic\Authentication\Authenticator\Exception\UnauthenticatedException;
use Phauthentic\Authentication\Authenticator\FormAuthenticator;
use Phauthentic\Authentication\Controller\Component\AuthenticationComponent;
use Phauthentic\Authentication\Identifier\PasswordIdentifier;
use Phauthentic\Authentication\Identifier\Resolver\OrmResolver;
use Phauthentic\Authentication\Identity\DefaultIdentityFactory;
use Phauthentic\Authentication\Identity\Identity;
use Phauthentic\Authentication\Identity\IdentityInterface;
use Phauthentic\Authentication\Test\TestCase\CakeAuthenticationTestCase;
use Phauthentic\Authentication\UrlChecker\DefaultUrlChecker;
use Phauthentic\PasswordHasher\DefaultPasswordHasher;
use RuntimeException;
use stdClass;

/**
 * Authentication component test.
 */
class AuthenticationComponentTest extends CakeAuthenticationTestCase
{

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        parent::setUp();

        $authenticators = new AuthenticatorCollection([
            new FormAuthenticator(new PasswordIdentifier(new OrmResolver(), new DefaultPasswordHasher), new DefaultUrlChecker())
        ]);

        $this->identityData = new ArrayObject([
            'username' => 'florian',
            'profession' => 'developer',
        ]);
        $this->identity = new Identity($this->identityData);
        $this->service = new AuthenticationService($authenticators, new DefaultIdentityFactory());

        $this->request = ServerRequestFactory::fromGlobals(
            ['REQUEST_URI' => '/'],
            [],
            ['username' => 'mariano', 'password' => 'password']
        );

        $this->response = new Response();
    }

    /**
     * testGetAuthenticationService
     *
     * @return void
     */
    public function testGetAuthenticationService()
    {
        $request = $this->request->withAttribute('authentication', $this->service);
        $controller = new Controller($request, $this->response);
        $registry = new ComponentRegistry($controller);
        $component = new AuthenticationComponent($registry);
        $result = $component->getAuthenticationService();
        $this->assertSame($this->service, $result);
    }

    /**
     * testGetAuthenticationServiceCustomAttribute
     *
     * @return void
     */
    public function testGetAuthenticationServiceCustomAttribute()
    {
        $request = $this->request->withAttribute('customService', $this->service);
        $controller = new Controller($request, $this->response);
        $registry = new ComponentRegistry($controller);
        $component = new AuthenticationComponent($registry, [
            'serviceAttribute' => 'customService'
        ]);
        $result = $component->getAuthenticationService();
        $this->assertSame($this->service, $result);
    }

    /**
     * testGetAuthenticationServiceMissingServiceAttribute
     *
     * @expectedException Exception
     * @expectedExceptionMessage The request object does not contain the required `authentication` attribute
     * @return void
     */
    public function testGetAuthenticationServiceMissingServiceAttribute()
    {
        $controller = new Controller($this->request, $this->response);
        $registry = new ComponentRegistry($controller);
        $component = new AuthenticationComponent($registry);
        $component->getAuthenticationService();
    }

    /**
     * testGetAuthenticationServiceInvalidServiceObject
     *
     * @expectedException Exception
     * @expectedExceptionMessage Authentication service does not implement Phauthentic\Authentication\AuthenticationServiceInterface
     * @return void
     */
    public function testGetAuthenticationServiceInvalidServiceObject()
    {
        $request = $this->request->withAttribute('authentication', new stdClass());
        $controller = new Controller($request, $this->response);
        $registry = new ComponentRegistry($controller);
        $component = new AuthenticationComponent($registry);
        $component->getAuthenticationService();
    }

    /**
     * testGetIdentity
     *
     * @eturn void
     */
    public function testGetIdentity()
    {
        $request = $this->request
            ->withAttribute('identity', $this->identity)
            ->withAttribute('authentication', $this->service);

        $controller = new Controller($request, $this->response);
        $registry = new ComponentRegistry($controller);
        $component = new AuthenticationComponent($registry);

        $result = $component->getIdentity();
        $this->assertInstanceOf(IdentityInterface::class, $result);
        $this->assertEquals('florian', $result->username);
    }

    /**
     * testGetIdentity with custom attribute
     *
     * @eturn void
     */
    public function testGetIdentityWithCustomAttribute()
    {
        $this->request = $this->request->withAttribute('customIdentity', $this->identity);
        $this->request = $this->request->withAttribute('authentication', $this->service);

        $controller = new Controller($this->request, $this->response);
        $registry = new ComponentRegistry($controller);
        $component = new AuthenticationComponent($registry, [
            'identityAttribute' => 'customIdentity'
        ]);

        $result = $component->getIdentity();
        $this->assertInstanceOf(IdentityInterface::class, $result);
        $this->assertEquals('florian', $result->username);
    }

    /**
     * testGetIdentity
     *
     * @eturn void
     */
    public function testSetIdentity()
    {
        $request = $this->request->withAttribute('authentication', $this->service);

        $controller = new Controller($request, $this->response);
        $registry = new ComponentRegistry($controller);
        $component = new AuthenticationComponent($registry);

        $component->setIdentity($this->identity);
        $result = $component->getIdentity();
        $this->assertSame($this->identity, $result);
    }

    /**
     * testGetIdentity
     *
     * @eturn void
     */
    public function testGetIdentityData()
    {
        $request = $this->request
            ->withAttribute('identity', $this->identity)
            ->withAttribute('authentication', $this->service);

        $controller = new Controller($request, $this->response);
        $registry = new ComponentRegistry($controller);
        $component = new AuthenticationComponent($registry);

        $result = $component->getIdentityData('profession');
        $this->assertEquals('developer', $result);
    }

    /**
     * testGetMissingIdentityData
     *
     * @eturn void
     * @expectedException RuntimeException
     * @expectedExceptionMessage The identity has not been found.
     */
    public function testGetMissingIdentityData()
    {
        $request = $this->request->withAttribute('authentication', $this->service);

        $controller = new Controller($request, $this->response);
        $registry = new ComponentRegistry($controller);
        $component = new AuthenticationComponent($registry);

        $component->getIdentityData('profession');
    }

    /**
     * testGetResult
     *
     * @return void
     */
    public function testGetResult()
    {
        $request = $this->request
            ->withAttribute('identity', $this->identity)
            ->withAttribute('authentication', $this->service);

        $controller = new Controller($request, $this->response);
        $registry = new ComponentRegistry($controller);
        $component = new AuthenticationComponent($registry);
        $this->assertNull($component->getResult());
    }

    /**
     * testLogout
     *
     * @return void
     */
    public function testLogout()
    {
        EventManager::instance()->setEventList(new EventList);

        $request = $this->request
            ->withAttribute('identity', $this->identity)
            ->withAttribute('authentication', $this->service);

        $controller = new Controller($request, $this->response);
        $registry = new ComponentRegistry($controller);
        $component = new AuthenticationComponent($registry);

        $this->assertEquals('florian', $controller->request->getAttribute('identity')->username);
        $component->logout();
        $this->assertNull($controller->request->getAttribute('identity'));
        $this->assertEventFired('Authentication.logout');
    }

    /**
     * testAfterIdentifyEvent
     *
     * @return void
     */
    public function testAfterIdentifyEvent()
    {
        EventManager::instance()->setEventList(new EventList);

        $this->service->authenticate(
            $this->request,
            $this->response
        );

        $request = $this->request
            ->withAttribute('identity', $this->identity)
            ->withAttribute('authentication', $this->service);

        $controller = new Controller($request, $this->response);
        $controller->loadComponent('Phauthentic/Authentication.Authentication');
        $controller->startupProcess();

        $this->assertEventFiredWith('Authentication.afterIdentify', 'identity', $this->identity);
        $this->assertEventFiredWith('Authentication.afterIdentify', 'service', $this->service);
        $this->assertEventFiredWith('Authentication.afterIdentify', 'provider', $this->service->getSuccessfulAuthenticator());
    }

    /**
     * test unauthenticated actions methods
     *
     * @return void
     */
    public function testUnauthenticatedActions()
    {
        $request = $this->request
            ->withParam('action', 'view')
            ->withAttribute('authentication', $this->service);

        $controller = new Controller($request, $this->response);
        $controller->loadComponent('Phauthentic/Authentication.Authentication');

        $controller->Authentication->allowUnauthenticated(['view']);
        $this->assertSame(['view'], $controller->Authentication->getUnauthenticatedActions());

        $controller->Authentication->allowUnauthenticated(['add', 'delete']);
        $this->assertSame(['add', 'delete'], $controller->Authentication->getUnauthenticatedActions());

        $controller->Authentication->addUnauthenticatedActions(['index']);
        $this->assertSame(['add', 'delete', 'index'], $controller->Authentication->getUnauthenticatedActions());

        $controller->Authentication->addUnauthenticatedActions(['index', 'view']);
        $this->assertSame(
            ['add', 'delete', 'index', 'view'],
            $controller->Authentication->getUnauthenticatedActions(),
            'Should contain unique set.'
        );
    }

    /**
     * test unauthenticated actions ok
     *
     * @return void
     */
    public function testUnauthenticatedActionsOk()
    {
        $request = $this->request
            ->withParam('action', 'view')
            ->withAttribute('authentication', $this->service);

        $controller = new Controller($request, $this->response);
        $controller->loadComponent('Phauthentic/Authentication.Authentication');

        $controller->Authentication->allowUnauthenticated(['view']);
        $controller->startupProcess();
        $this->assertTrue(true, 'No exception should be raised');
    }

    /**
     * test unauthenticated actions mismatched action
     *
     * @return void
     */
    public function testUnauthenticatedActionsMismatchAction()
    {
        $request = $this->request
            ->withParam('action', 'view')
            ->withAttribute('authentication', $this->service);

        $controller = new Controller($request, $this->response);
        $controller->loadComponent('Phauthentic/Authentication.Authentication');

        $this->expectException(UnauthenticatedException::class);
//        $this->expectExceptionCode(401);
        $controller->Authentication->allowUnauthenticated(['index', 'add']);
        $controller->startupProcess();
    }

    /**
     * test unauthenticated actions ok
     *
     * @return void
     */
    public function testUnauthenticatedActionsNoActionsFails()
    {
        $request = $this->request
            ->withParam('action', 'view')
            ->withAttribute('authentication', $this->service);

        $controller = new Controller($request, $this->response);
        $controller->loadComponent('Phauthentic/Authentication.Authentication');

        $this->expectException(UnauthenticatedException::class);
//        $this->expectExceptionCode(401);
        $controller->startupProcess();
    }

    /**
     * test disabling requireidentity via settings
     *
     * @return void
     */
    public function testUnauthenticatedActionsDisabledOptions()
    {
        $request = $this->request
            ->withParam('action', 'view')
            ->withAttribute('authentication', $this->service);

        $controller = new Controller($request, $this->response);
        $controller->loadComponent('Phauthentic/Authentication.Authentication', [
            'requireIdentity' => false
        ]);

        // Mismatched actions would normally cause an error.
        $controller->Authentication->allowUnauthenticated(['index', 'add']);
        $controller->startupProcess();
        $this->assertTrue(true, 'No exception should be raised as require identity is off.');
    }
}