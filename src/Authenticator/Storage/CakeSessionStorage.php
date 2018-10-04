<?php
declare(strict_types=1);

namespace Phauthentic\Authentication\Authenticator\Storage;

use Phauthentic\Authentication\Authenticator\Storage\StorageInterface;
use Cake\Http\Session;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Storage adapter for the CakePHP Session
 */
class CakeSessionStorage implements StorageInterface
{

    /**
     * @var string
     */
    protected $key = 'Auth';

    /**
     * @var string
     */
    protected $attribute = 'session';

    /**
     * Set request attribute name for a session object.
     *
     * @param string $attribute Request attribute name.
     * @return $this
     */
    public function setAttribute(string $attribute): self
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Set session key for stored identity.
     *
     * @param string $key Session key.
     * @return $this
     */
    public function setKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function clear(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $this->getSession($request)->delete($this->key);

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function read(ServerRequestInterface $request)
    {
        return $this->getSession($request)->read($this->key);
    }

    /**
     * {@inheritDoc}
     */
    public function write(ServerRequestInterface $request, ResponseInterface $response, $data): ResponseInterface
    {
        $this->getSession($request)->write($this->key, $data);

        return $response;
    }

    /**
     * Returns session object.
     *
     * @param ServerRequestInterface $request Request.
     * @return Session
     */
    protected function getSession(ServerRequestInterface $request): Session
    {
        return $request->getAttribute($this->attribute);
    }
}
