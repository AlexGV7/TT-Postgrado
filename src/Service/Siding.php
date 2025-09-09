<?php
namespace App\Service;

use App\Entity\Research;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class Siding
{
    private $SOAP;

    public function __construct(ParameterBagInterface $params) {
        
        $uri = $params->get('wsdl.uri');
        $authentication = $params->get('wsdl.auth');
        $login = $params->get('wsdl.login');
        $password = $params->get('wsdl.password');
        $cache = $params->get('wsdl.cache');
        
        $params = array(
            'authentication'        => $authentication,
            'login'                 => $login,
            'password'              => $password,
            'cache_wsdl'            => $cache
        );
                
        $this->SOAP = new \SoapClient($uri, $params);
    }

    /*
    Valida si el par login/pass suministrado es válido para ingresar al SIDING. Parameters: string $login   Login a validar. string $password   Pass a validar. Returns: integer    RUN del usuario asociado, si el par login/pass suministrado es válido para ingresar al SIDING, o 0 si el usuario no existe o el par login/pass no es válido.
    */
    public function authenticate($login, $password) {
        try {
            $data = $this->SOAP->autenticar($login, $password);
        }
        catch(\SoapFault $soapFault) {
            //var_dump($soapFault); die();
            return 0;
        }
        
        return $data;
    }
    
    /*
    Retorna la información del usuario con el RUN dado. Parameters: integer $run    RUN del usuario consultado. Returns: mixed[]    Arreglo con información del usuario con el RUN dado. Throws: SoapFault  Si el RUN dado no corresponde a un usuario válido.
    */
    public function getUserData($rut) {        
        try {
            $data = $this->SOAP->get_datos($rut);
        } catch(\Exception $e) {
            return 0;
        }
        
        if(!$data->run) {
            return 0;
        }
                        
        $image_path = "https://intrawww.ing.puc.cl/siding/datos/fotos/publicas/";
        $first = explode(",",$data->telefonos);

        $nameText = $data->nombre;
        $nameText = mb_convert_encoding($nameText, 'ISO-8859-1', 'UTF-8');

        $lastnameText = $data->apellido_paterno;
        $lastnameText = mb_convert_encoding($lastnameText, 'ISO-8859-1', 'UTF-8');

        $secondLastnameText = $data->apellido_materno;
        $secondLastnameText = mb_convert_encoding($secondLastnameText, 'ISO-8859-1', 'UTF-8');

        $user = array(
            'rut' => $data->run,
            'name' => ucwords(mb_strtolower($nameText)),
            'lastname' => ucwords(mb_strtolower($lastnameText)),
            'secondLastname' => ucwords(mb_strtolower($secondLastnameText)),
            'rol' => $data->rol,
            'department' => $data->departamentos,
            'photo' => $image_path . $data->foto_publica,
            'mail' => $data->email,
            'phoneNumber' => $first[0],
            'sexo' => $data->sexo
        );
        
        return $user;
    }

    public function isRegularStudent($rut) {        
        try {
            $data = $this->SOAP->es_regular_pregrado($rut);
        }
        catch(\SoapFault $soapFault) {
            return false;
        }

        if(is_null($data))
        {
            return false;
        }
        
        return true;
    }
    
}