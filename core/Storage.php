<?php
/**
 * Created by PhpStorm.
 * User: Mysic
 * Date: 2019\1\4 0004
 * Time: 9:42
 */

namespace core;


abstract class Storage
{
    abstract public function add(array $msg);
    abstract public function update(array $msg);
    abstract public function delete(array $msg);
}