<?php namespace Iesod;

interface AccessPolicyInterface {
    public function testAccess();
    public function registerFail($codeError = null, $strError = null);
    public function beforeSuccess($alerts = []);
    public function afterFail($codeError = null, $strError = null);
}
