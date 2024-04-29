<?php

require dirname(__DIR__) . '/public/lib/vendor/autoload.php'; 
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv -> load();

define("HOST", $_ENV['DB_HOST']);
define("DBNAME", $_ENV['DB_NAME']);
define("USUARIO", $_ENV['DB_USER']);
define("PASSWORD", $_ENV['DB_PASS']);

class Crud_bd{
    private $conexion;

    public function conexion_bd(){
        try {
            $this->conexion = new PDO(HOST. DBNAME,USUARIO,PASSWORD );
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conexion->exec("SET NAMES UTF8");
            return $this->conexion;
        }
        catch (Exception $e) {
            die("Error:".$e->getMessage());
            echo "Linea del error " . $e->getLine();
        }
    }

    public function mostrar($consultaEscrita, ?array $arrayAsociativo = null){
        try{
            $this->conexion->beginTransaction();
            if($arrayAsociativo == null){
                $sentencia = $this->conexion->query($consultaEscrita);
            }
            else{
                $sentencia = $this->conexion->prepare($consultaEscrita);
                $sentencia->execute($arrayAsociativo);
            }
            $this->conexion->commit();
            $filas=$sentencia->fetchAll();
            $sentencia = null;
            return $filas;
        }
        catch (\Exception $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollback();
                die("Error:" . $e->getMessage());
                echo "Linea del error " . $e->getLine();
            }
            throw $e;
        }
    }

    public function insertar_eliminar_actualizar($consultaEscrita, $arrayAsociativo){
        try{
            $this->conexion->beginTransaction(); 
            if(is_array($consultaEscrita) && is_array($arrayAsociativo)){
                for($i=0; $i<count($consultaEscrita);$i++){
                    $resultados = $this->conexion->prepare($consultaEscrita[$i]);
                    $resultados->execute($arrayAsociativo[$i]);
                }
            }
            else{
                $resultados=$this->conexion->prepare($consultaEscrita);
                $resultados->execute($arrayAsociativo);
            }
            $this->conexion->commit();
            $resultados = null;
            return true;
        }
        catch (\Exception $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollback();

                die("Error:" . $e->getMessage());
                echo "Linea del error " . $e->getLine();
            }
            throw $e;
        }
    }

    public function cerrar_conexion(){
        $this->conexion = null;
    }

}



?>