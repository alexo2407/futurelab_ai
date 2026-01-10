<?php 

class EnlaceBackendModel
{

    public static function enlaceBackendModel($link)
    {

        //lista blanca de url
        if($link == "inicio" ||
        $link == "articulos"||
        $link == "crearArticulo"||
        $link == "editarArticulo"||
        $link == "usuarios" ||
        $link == "crearUsuario"||
        $link == "editarUsuario"||
        $link == "comentarios" ||
        $link == "editarComentario" ||
        $link == "salir")
        {
            $modulo = "vista/modulos/".$link.".php";
        }
        else {
            $modulo = "vista/modulos/inicio.php";
        }

        return $modulo;
    }
}
