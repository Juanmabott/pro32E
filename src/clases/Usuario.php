<?php
use Poo\AccesoDatos;
require 'accesoDatos.php';
require "IBM.php";

class Usuario implements IBM  {
    public int $id;
    public string $nombre;
    public string $correo;
    public string $clave;
    public int $id_perfil;
    public string$perfil;

    public function __construct($id = -1, $nombre = '', $correo = '', $clave = '', $id_perfil = -1, $perfil = '') {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->correo = $correo;
        $this->clave = $clave;
        $this->id_perfil = $id_perfil;
        $this->perfil = $perfil;
    }

    public function ToJSON() {
        $data = [
            
            'nombre' => $this->nombre,
            'correo' => $this->correo,
            'clave' => $this->clave
        ];

        return json_encode($data);
    }
    public function GuardarEnArchivo() {
        $archivoRuta = './archivos/usuarios.json';
        $ret = new stdClass();
        $ret->exito = false;
        $ret->mensaje = "Ocurrió un error al guardar el archivo";
    
        $archivo = fopen($archivoRuta, "a");
    
        if ($archivo) {
            $caracteresEscritos = fwrite($archivo, $this->ToJSON() . "\r\n");
            if ($caracteresEscritos > 0) {
                $ret->exito = true;
                $ret->mensaje = "Éxito al guardar el archivo";
            }
            fclose($archivo);
        } else {
            $ret->mensaje = "Error al abrir el archivo para escritura";
        }
    
        return $ret;
    }
    public static function TraerTodosJSON():array
        {
            $texto="";
            $array_res=array();
            $archivoRuta = './archivos/usuarios.json';
            $archivo = fopen($archivoRuta, "r");

            if($archivo!==false) {
            while(!feof($archivo)){
                $texto.=fgets($archivo);
            }
            fclose($archivo);
            $obj_array=explode("\r\n",$texto);
            foreach($obj_array as $item)
            {
                if($item!=="")
                {
                    $obj=json_decode($item);
                    $nombre = isset($obj->nombre) ? $obj->nombre : '';
                    $correo = isset($obj->correo) ? $obj->correo : '';
                    $clave = isset($obj->clave) ? $obj->clave : '';

                    $usuario = new self(-1, $nombre, $correo, $clave, -1, '');                
                    array_push($array_res,$usuario);
                }
            }
            }

            return $array_res;
        }
    public static function TraerTodos(): array
        {
            $array_res = [];
            $objPdo = AccesoDatos::dameUnObjetoAcceso();

            // Consulta SQL para recuperar usuarios y perfiles
            $sql = "SELECT u.*, p.descripcion AS perfil
                    FROM usuarios u
                    INNER JOIN perfiles p ON u.id_perfil = p.id";

            $query = $objPdo->retornarConsulta($sql);
            $query->execute();

            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $usuario = new self(
                    $row['id'],
                    $row['nombre'],
                    $row['correo'],
                    $row['clave'],
                    $row['id_perfil'],
                    $row['perfil'] // Descripción del perfil
                );

                array_push($array_res, $usuario);
            }

            return $array_res;
        }
    public static function TraerUno($params)
        {
            $correo = $params['correo'] ?? '';
            $clave = $params['clave'] ?? '';

            $objPdo = AccesoDatos::dameUnObjetoAcceso();

            // Consulta SQL para buscar un usuario por correo y clave
            $sql = "SELECT * FROM usuarios WHERE correo = :correo AND clave = :clave";
            $query = $objPdo->retornarConsulta($sql);
            $query->bindValue(":correo", $correo, PDO::PARAM_STR);
            $query->bindValue(":clave", $clave, PDO::PARAM_STR);
            $query->execute();

            $row = $query->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                // Crear objeto Usuario con los datos encontrados
                $usuario = new self(
                    $row['id'],
                    $row['nombre'],
                    $row['correo'],
                    $row['clave'],
                    $row['id_perfil'],
                    $row['id_perfil']
                );

                return $usuario;
            }

            return null; // No se encontró ningún usuario
        }
    
    
        public function Agregar($tabla = "usuarios")
        {
            try {
                $objPdo = AccesoDatos::dameUnObjetoAcceso();
                $query = $objPdo->retornarConsulta("INSERT INTO {$tabla} (nombre, correo, clave, id_perfil) 
                                                    VALUES (:nombre, :correo, :clave, :id_perfil)");
                $query->bindValue(":nombre", $this->nombre, PDO::PARAM_STR);
                $query->bindValue(":correo", $this->correo, PDO::PARAM_STR);
                $query->bindValue(":clave", $this->clave, PDO::PARAM_STR);
                $query->bindValue(":id_perfil", $this->id_perfil, PDO::PARAM_INT); // Asumiendo que id_perfil es un entero
        
                if ($query->execute()) {
                    return true;
                } else {
                    error_log('Error al agregar el registro: ' . print_r($query->errorInfo(), true));
                    return false;
                }
            } catch (PDOException $e) {
                error_log('Error al agregar el registro: ' . $e->getMessage());
                return false;
            }
        }
        
    public function Modificar($tabla = "usuarios"): bool {
        try {
            $sql = "UPDATE {$tabla} SET nombre = :nombre, correo = :correo WHERE id = :id";
            $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
            $consulta = $objetoAccesoDatos->retornarConsulta($sql);
            $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
            $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
            $consulta->bindValue(':correo', $this->correo, PDO::PARAM_STR);
            
            return $consulta->execute();
        } catch (PDOException $e) {
            error_log('Error al modificar el registro: ' . $e->getMessage());
            return false;
        }
    }
    
    

    public static function Eliminar(int $id,$tabla="usuarios"): bool {
        try {
            $sql = "DELETE FROM {$tabla} WHERE id = :id";
            $objetoAccesoDatos = AccesoDatos::dameUnObjetoAcceso();
            $consulta = $objetoAccesoDatos->retornarConsulta($sql);
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            
            return $consulta->execute();
        } catch (PDOException $e) {
            error_log('Error al eliminar el registro: ' . $e->getMessage());
            return false;
        }
    }
    public static function ValidarUsu($correo, $clave)
    {
        


        $objetoAccesoDato = AccesoDatos::DameUnObjetoAcceso();

        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT * FROM usuarios WHERE correo=:correo AND clave=:clave");

        $consulta->bindValue(':correo', $correo, PDO::PARAM_STR);

        $consulta->bindValue(':clave', $clave, PDO::PARAM_STR);
        //echo "\ncorreo: ".$correo;

        //echo "\nclave: ".$clave;

        $consulta->execute();

        $usuario = false;

        if ($consulta->rowCount()>0) {
           // echo "usuario encontrado";
            $usuario= $consulta->fetchObject('Usuario');
        }
       /* else
        {
            echo "\nrow=".$consulta->rowCount();
        }
        */
        return $usuario;
    }
}

