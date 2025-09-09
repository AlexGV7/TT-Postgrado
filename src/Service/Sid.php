<?php
namespace App\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Doctrine\ORM\EntityManagerInterface;

class Sid
{
    private $conn;
    private $server;
    private $user;
    private $password;
    private $db;
    private $em;

    public function __construct(ParameterBagInterface $params, EntityManagerInterface $em) {
        $this->server = $params->get('sid.server');
        $this->db = $params->get('sid.db');
        $this->user = $params->get('sid.user');
        $this->password = $params->get('sid.password');
        $this->em = $em;
    }

    public function getAcademicCategoryForMentors() {
        try {
            $conn = new \PDO("mysql:host={$this->server};dbname={$this->db}", $this->user, $this->password);
    
            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    
            // ing_categoria_academica //
            // cod_categoria (letra)
            // nom_categoria (nombre)
            // tipo_categoria (ordinaria/adjunta/null)

            // ing mant profesor //
            // run
            // cod_categoria
            
            $query = "
                SELECT 
                    p.run AS run,
                    c.tipo_categoria AS tipo_categoria
                FROM 
                    ing_profesor p
                LEFT JOIN 
                    ing_categoria_academica c ON p.cod_categoria = c.cod_categoria
            ";
    
            $res = $conn->query($query);
    
            $result = [];
            foreach ($res as $row) {
                $run = $row['run'];
                $tipo = $row['tipo_categoria'] ?? null;
                $result[$run] = strtolower(trim($tipo));
            }
    
        } catch (\PDOException $e) {
            echo "Error: " . $e->getMessage();
            return [];
        }
    
        return $result;
    }
    
}
