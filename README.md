# phpamqp-consumer
针对RabbitMQ写的消费者包

### 目录结构
```
/
core/ 包核心目录
    Db.php           Mysql数据源连接器
    Dispatcher.php   任务调度
    MqConnector.php  RabbitMQ 连接器
    Processor.php    父级消息处理器
    Storage.php      父级数据存储器
task/ 任务目录
    project_1/  项目1的消费业务代码 
    project_n/  项目n的消费业务代码  
         config/ 配置文件 
         processor/ 每个队列所对应的消费处理器(消费者) 
         storage/ 数据存储对象实例

run.php    cli入口文件
       
```
            
### 任务示例

#### 示例说明
- 本示例中数据存储涉及到MYSQL，ELASTIC。MYSQL是数据源，ELASTIC是数据目标存储
- 队列中的消息保存的是MYSQL中的`id`，以及消息`extra`额外信息
- 数据源不是必须的。可以将要写入目标数据存储的完整信息都写入队列，读取时直接获取的就是详细信息。根据自身业务来决定。

-业务流程：
1. 从队列中读取消息中的id和extra。通过id到数据源存储中读取数据的详细信息 
2. 将详细信息以及额外信息处理后存入数据存储器中

#### 示例文件说明
```       
config/ 配置文件
    db.php             数据源连接器配置文件
    messageQueue.php   消息队列配置文件
    storage.php        数据存储配置文件
processor/ 每个队列所对应的消费者处理逻辑
    Document.php     生成CURD ES文档的方法集
    Example.php      消息处理器示例
storage/ 数据存储对象实例
    Elastic.php     存储器实例
```

### 运行示例
在命令行中执行：
```
php run.php project_name processor_name storage_name
```
project_name    项目名称 (task下的项目目录名)  
processor_name  消费处理器名称  
storage_name    存储名称