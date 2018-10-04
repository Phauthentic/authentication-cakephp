<?php
declare(strict_types=1);
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
namespace Phauthentic\Authentication\Middleware;

use Cake\Core\InstanceConfigTrait;
use Phauthentic\Authentication\AuthenticationServiceProviderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

/**
 * Authentication Middleware
 */
class CakeAuthenticationMiddleware
{
    use InstanceConfigTrait;

    /**
     * Configuration options
     *
     * - `serviceAttribute` - The request attribute to store the service in.
     * - `identityAttribute` - The request attribute to store the identity in.
     *   parameter with the previously blocked URL.
     */
    protected $_defaultConfig = [
        'serviceAttribute' => 'authentication',
        'identityAttribute' => 'identity',
    ];

    /**
     * @var AuthenticationServiceProviderInterface
     */
    protected $provider;

    /**
     * Constructor.
     *
     * @param AuthenticationServiceProviderInterface $provider Provider.
     * @param array $config Config.
     */
    public function __construct(AuthenticationServiceProviderInterface $provider, array $config = [])
    {
        $this->provider = $provider;
        $this->setConfig($config);
    }

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
        $service = $this->provider->getAuthenticationService($request);
        $request = $this->addAttribute($request, $this->getConfig('serviceAttribute'), $service);

        $service->authenticate($request);

        $identity = $service->getIdentity();
        $request = $this->addAttribute($request, $this->getConfig('identityAttribute'), $identity);

        $response = $next($request, $response);
        $result = $service->persistIdentity($request, $response);

        return $result->getResponse();
    }

    /**
     * Adds an attribute to the request and returns a modified request.
     *
     * @param ServerRequestInterface $request Request.
     * @param string $name Attribute name.
     * @param mixed $value Attribute value.
     * @return ServerRequestInterface
     * @throws RuntimeException When attribute is present.
     */
    protected function addAttribute(ServerRequestInterface $request, string $name, $value): ServerRequestInterface
    {
        if ($request->getAttribute($name)) {
            $message = sprintf('Request attribute `%s` already exists.', $name);
            throw new RuntimeException($message);
        }

        return $request->withAttribute($name, $value);
    }

}
