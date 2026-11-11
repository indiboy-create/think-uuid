## ThinkPHP-PanGu - 盘古ThinkPHP开源索引!
面向 ThinkPHP 的开源工程索引：组件、框架、服务与发布工具，按能力组织，持续整理。
仓库地址：**<https://github.com/indiboy-create/ThinkPHP-PanGu/>**
在线站点：**<https://indiboy-create.github.io/ThinkPHP-PanGu/>**

# think-uuid

基于 [ramsey/uuid](https://github.com/ramsey/uuid) 的 ThinkPHP 8 UUID 生成与解析组件。

支持 UUID v1、v3、v4、v5、v6、v7、v8，已适配 ThinkPHP 8 的服务注册、配置与容器。

## 环境要求

- PHP >= 8.0
- ThinkPHP >= 8.0
- ramsey/uuid >= 4.7

## 安装

```bash
composer require youvision/think-uuid
```

包已通过 `extra.think.services` 声明服务，执行以下命令完成服务发现：

```bash
php think service:discover
```

如果项目未开启自动发现，也可以在 `config/service.php` 中手动注册：

```php
return [
    \YouVision\ThinkUuid\Service::class,
];
```

## 配置

组件默认配置位于 `config/uuid.php`，安装后可将该文件复制到项目 `config/uuid.php` 覆盖默认值：

```php
return [
    // 默认 UUID 版本：v1、v3、v4、v5、v6、v7、v8
    'version' => 'v4',

    // v3 / v5 的命名空间，支持 dns、url、oid、x500 或完整 UUID 字符串
    'namespace' => 'dns',

    // v1 / v6 的节点标识，支持 00:11:22:33:44:55 或十六进制串
    'node' => '',

    // v1 / v6 的时钟序列，取值范围 0 - 16383
    'clock_seq' => null,

    // generate() 返回格式：object、string、simple
    'return_type' => 'object',
];
```

## 使用

### 助手函数

```php
uuid();      // 按配置的默认版本生成，返回字符串
uuid('v4');  // 生成 v4
uuid('v5', null, 'example.com'); // 使用配置的命名空间生成 v5
```

### 门面

```php
use YouVision\ThinkUuid\facade\Uuid;

$uuid = Uuid::v4();              // Ramsey\Uuid\UuidInterface
$id   = Uuid::v7()->toString();  // 36 位字符串
$id   = Uuid::v4()->getHex()->toString(); // 32 位小写十六进制
```

### 容器实例

```php
$uuid = app('uuid');

$uuid->v1();
$uuid->v3(null, 'example.com');
$uuid->v4();
$uuid->v5('url', 'example.com');
$uuid->v6();
$uuid->v7();
$uuid->v8('00112233445566778899aabbccddeeff');
```

### 按配置生成

```php
$uuid = app('uuid');

// 使用配置中的默认版本
$value = $uuid->generate();

// 指定版本生成
$value = $uuid->generate('v7');
$value = $uuid->generate('v5', null, 'example.com');
```

`return_type` 为 `object` 时返回 `Ramsey\Uuid\UuidInterface`，为 `string` 时返回 36 位带连字符字符串，为 `simple` 时返回 32 位无连字符字符串。

### 解析与校验

```php
$uuid = app('uuid');

$uuid->isValid('9b2c1f0e-...');   // bool
$uuid->parse('9b2c1f0e-...');     // UuidInterface
$uuid->parse($uuidObject);        // 原样返回
$uuid->namespace('url');          // 获取预定义命名空间
```

### 数据库主键

```php
use YouVision\ThinkUuid\facade\Uuid;

$id = Uuid::v7()->toString();
```

v7 按时间戳排序，索引友好，适合作为 MySQL、PostgreSQL 等数据库的主键；使用 `char(36)` 存储带连字符字符串，或使用 `binary(16)` 存储 `Uuid::v7()->getBytes()`。

## 版本选择建议

| 版本 | 生成依据 | 适用场景 |
| --- | --- | --- |
| v1 | 时间 + MAC 地址 | 需要时间有序，且可接受暴露节点信息 |
| v3 | MD5(命名空间 + 名称) | 需要由名称稳定生成，安全性要求不高 |
| v4 | 随机数 | 通用场景，使用最广泛 |
| v5 | SHA1(命名空间 + 名称) | 需要由名称稳定生成，安全性高于 v3 |
| v6 | 可排序时间 | 需要时间有序且兼容 v1 结构 |
| v7 | Unix 时间戳 + 随机数 | 数据库主键、日志 ID 等时间有序场景 |
| v8 | 自定义字节 | 需要自定义实现 |

## 测试

```bash
composer install
vendor/bin/phpunit
```

## License

[MIT](LICENSE)