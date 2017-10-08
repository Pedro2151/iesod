<?php namespace Iesod;

interface RequestInterface {
    public function __construct();
    public function get($name = null, $default = null, $returnValue = false);
    public function post($name = null, $default = null, $returnValue = false);
    public function file($name = null, $returnValue = false);
    public function all($returnValue = false);
}
