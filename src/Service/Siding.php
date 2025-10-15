<?php

namespace App\Service;

use App\Entity\Research;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class Siding
{
    private \SoapClient $SOAP;
    private string $uri;
    private string $authentication;
    private string $login;
    private string $password;
    private string $cache;

    public function __construct(ParameterBagInterface $params)
    {
        // Leer parámetros desde services.yaml o .env
        $this->uri            = $params->get('wsdl.uri');
        $this->authentication = $params->get('wsdl.auth');
    } // ✅ Cierre correcto sin punto y coma
}
