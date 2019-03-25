<?php
/**
 * Created by PhpStorm.
 * User: Mysic
 * Date: 2019\1\7 0007
 * Time: 19:15
 */

namespace Task\BI\Processor;


trait Document
{
    abstract protected function indexDoc($id, string $indexName, array $extra = [], string $table = '');

    protected function getDoc(array $msg, string $indexName)
    {
        $doc = [];
        if(!$msg['table']) {
            $msg['table'] = '';
        }
        switch($msg['operation']) {
            case 'add':
                $doc = $this->indexDoc($msg['id'],  $indexName, $msg['extra'], $msg['table']);
                break;
            case 'update':
                $doc = $this->updateDoc($msg['id'], $indexName, $msg['extra'], $msg['table']);
                break;
            case 'updateField':
                $doc = $this->updateFieldDoc($msg['id'], $indexName, $msg['extra']);
                break;
            case 'delete':
                $doc = $this->deleteDoc($msg['id'], $indexName);
                break;
            default:
                break;
        }
        return $doc;
    }

    protected function updateFieldDoc($id, string $indexName, array $extra = [])
    {
        $doc = [
            'index' => $indexName,
            'type' => '_doc',
            'id' => $id,
            'body' => [
                'doc' => $extra
            ]
        ];
        return $doc;
    }

    protected function updateDoc($id, string $indexName, array $extra = [], string $table = '')
    {
        $doc = $this->indexDoc($id, $indexName, $extra, $table);
        $tmp = $doc['body'];
        $extra = \array_merge($tmp, $extra);
        $doc = [
            'index' => $indexName,
            'type' => '_doc',
            'id' => $id,
            'body' => [
                'doc' => $extra,
                'doc_as_upsert' => true
            ]
        ];
        return $doc;
    }

    protected function deleteDoc($id, string $indexName)
    {
        $doc = [
            'index' => $indexName,
            'type' => '_doc',
            'id' => $id
        ];
        return $doc;
    }
}