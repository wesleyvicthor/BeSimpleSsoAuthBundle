<?php

namespace BeSimple\SsoAuthBundle\Sso;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use BeSimple\SsoAuthBundle\Security\Core\Authentication\Token\SsoToken;
use Buzz\Client\ClientInterface;

/**
 * @author: Jean-François Simon <contact@jfsimon.fr>
 */
class Manager
{
    /**
     * @var \BeSimple\SsoAuthBundle\Sso\ServerInterface
     */
    private $server;

    /**
     * @var \BeSimple\SsoAuthBundle\Sso\ProtocolInterface
     */
    private $protocol;

    /**
     * @var \Buzz\Client\ClientInterface
     */
    private $client;

    /**
     * Constructor.
     *
     * @param ServerInterface              $server
     * @param ProtocolInterface            $protocol
     * @param \Buzz\Client\ClientInterface $client
     */
    public function __construct(ServerInterface $server, ProtocolInterface $protocol, ClientInterface $client)
    {
        $this->server   = $server;
        $this->protocol = $protocol;
        $this->client   = $client;
    }

    /**
     * Validates given credentials.
     *
     * @param string $credentials
     *
     * @return ValidationInterface
     */
    public function validateCredentials($credentials)
    {
        $request    = $this->server->buildValidationRequest($credentials);
        $validation = $this->protocol->executeValidation($this->client, $request, $credentials);

        return $validation;
    }

    /**
     * Creates a token from validation.
     *
     * @param ValidationInterface $validation
     *
     * @throws \Symfony\Component\Security\Core\Exception\BadCredentialsException
     *
     * @return \BeSimple\SsoAuthBundle\Security\Core\Authentication\Token\SsoToken
     */
    public function createToken(ValidationInterface $validation)
    {
        if (!$validation->isSuccess()) {
            throw new BadCredentialsException('Authentication has not been validated by SSO provider.');
        }

        $token = new SsoToken($this, $validation->getCredentials(), $validation->getUsername());
        $token->setAttributes($validation->getAttributes());

        return $token;
    }

    /**
     * @return \BeSimple\SsoAuthBundle\Sso\ServerInterface
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @return \BeSimple\SsoAuthBundle\Sso\ProtocolInterface
     */
    public function getProtocol()
    {
        return $this->protocol;
    }
}
