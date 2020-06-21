## 目录结构

```
├─app
│  ├─Customize   自定义框架目录
│  └─Domain      业务领域目录
│     ├─Common   公共域目录
│     │  ├─Enum         枚举类目录
│     │  ├─Exception    异常类目录
│     │  ├─Helpers      助手类目录
│     │  ├─BaseBroadcastEvent.php  基础队列广播事件
│     │  ├─BaseQueueListener.php   基础队列监听器
│     │  └─ErrorCode.php           错误码
│     └─ ...     更多业务领域目录
├─crontab.sh        任务调度
├─echo-server.nginx.conf     Socket.IO服务器重定向配置文件
├─laravel.supervisor.conf    Supervisor配置文件
```

## 安装

1、安装相关依赖

```
composer install
```

2、环境变量配置
```
cp .env.example .env
php artisan key:generate
```

3、执行数据库迁移

```
php artisan migrate --seed
```

4、初始化jwt密钥

```
php artisan jwt:secret
```
