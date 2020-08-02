### Laravel Vue Admin

基于Laravel6.0构建，前端代码移步[laravel-vue-admin-frontend](https://github.com/jassue/laravel-vue-admin-frontend).

### 目录结构

```
├─app
│  ├─Customize   自定义框架目录
│  └─Domain      业务领域目录
│     ├─Common   公共域目录
│     └─ ...     更多业务领域目录
├─laravel.supervisor.conf    Supervisor配置文件
```

### 安装

1、安装代码及相关依赖

```
git clone https://github.com/jassue/laravel-vue-admin-api.git
composer install
```

2、环境变量配置
```
cp .env.example .env
php artisan key:generate
```

3、执行数据库迁移、初始jwt密钥

```
php artisan migrate --seed
php artisan jwt:secret
```
