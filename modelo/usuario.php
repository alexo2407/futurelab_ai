<?php 

 include_once "modelo/conexion.php";


class UsuariosModel

{

/******************************** */
    // MOSTRAR TODOS LOS USUARIOS
    /********************************* */
    public static function mostrarUsuariosModels()
    {

        //instanciamos la BD

        $tabla = "usuarios";

        $dataBase = new Conexion();
        $db = $dataBase->conectar();

        //preparamos la consulta

        $consulta = $db->prepare("SELECT id,nombre, email, password, rol_id, fecha_creacion from $tabla");

        //ejecutamos la consulta

        $consulta->execute();

        $repuesta = $consulta->fetchAll(PDO::FETCH_OBJ);

        return $repuesta;

        //limpiar consulta
        $consulta = null;
    }

}