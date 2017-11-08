<?php namespace Iesod\Database;


interface ModelInterface {
    public static function insert($data,$returnInsertId = true);
    public static function update($data,$id = null);
}