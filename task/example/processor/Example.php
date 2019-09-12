<?php
/**
 * Created by PhpStorm.
 * User: Mysic
 * Date: 2018\12\17 0017
 * Time: 17:01
 */

namespace task\example\processor;


use PhpAmqpLib\Message\AMQPMessage;
use core\Processor;
use core\Storage;
use Medoo\Medoo;

class Example extends Processor
{
    use Document;
    public function __construct(Storage $storage, array $config, Medoo $db = null)
    {
        parent::__construct($storage, $config, $db);
    }

    protected function handle(AMQPMessage $message): bool
    {
        $msg = \json_decode($message->body, true);
        if(empty($msg['extra'])) {
            $msg['extra'] = [];
        }
        $doc = $this->getDoc($msg, 'homeworks');
        parent::run($msg['operation'], $doc);
        return true;
    }

    /**
     * 将数据索引到elastic search中
     * @param int    $id  索引到ES中文档的ID
     * @param string $indexName  ES索引的名称
     * @param array  $extra 额外信息
     * @return array
     */
    protected function indexDoc(int $id, string $indexName, array $extra = [])
    {
        $record = $this->db->get('homework',
            ['school_id', 'grade_id', 'exam_group_id', 'teacher_id','subject_id','finish_time',
                'create_time', 'counts', 'student_num'],
            ['id' => $id]
        );
        $schoolInfo = $this->db->get('sys_school',
            ['province', 'city', 'district'],
            ['id' => $record['school_id']]
        );
        $record['city'] = $schoolInfo['city'];
        $record['county'] = $schoolInfo['district'];

        if(empty($record)) {
            return [];
        }
        $doc =  [
            'index' => $indexName,
            'type' => '_doc',
            'id' => $id,
            'body' => [
                'school'        => $record['school_id'],
                'grade'         => $record['grade_id'],
                'class'         => $record['exam_group_id'],
                'city'          => $record['city'],
                'county'        => $record['county'],
                'created_at'    => \date('Ymd', $record['create_time'])
            ]
        ];
        if(!empty($extra)) {
            $doc['body'] = \array_merge($doc['body'], $extra);
        }
        return $doc;
    }
}