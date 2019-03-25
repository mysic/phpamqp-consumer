<?php
/**
 * Created by PhpStorm.
 * User: Mysic
 * Date: 2019\1\4 0004
 * Time: 10:02
 */

namespace task\example\storage;

use core\Storage;
use Elasticsearch\ClientBuilder;

class Elastic extends Storage
{
    protected $handle;
    public function __construct(array $conn)
    {
        $this->handle = ClientBuilder::create()->setHosts($conn)->build();
    }

    public function add(array $doc)
    {
        return $this->handle->index($doc);
    }

    public function update(array $doc)
    {
        return $this->handle->update($doc);
    }

    public function delete(array $doc)
    {
        return $this->handle->delete($doc);
    }
}