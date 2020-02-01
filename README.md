## 目录结构

```
├─app
│  ├─Customize   自定义框架目录
│  └─Domain      业务领域目录
│     ├─Common   公共域目录
│     │  ├─Enum  			 枚举类目录
│     │  ├─Exception   异常类目录
│     │  ├─Helpers  	 助手类目录
│     │  ├─BaseBroadcastEvent.php  基础队列广播事件
│     │  ├─BaseQueueListener.php 	 基础队列监听器
│     │  └─ErrorCode.php 					 错误码
│     └─ ...     更多业务领域目录
├─init.sh        初始化shell脚本
├─laravel.supervisor.conf    Supervisor配置文件
```