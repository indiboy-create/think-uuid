<?php

return [
    // 默认 UUID 版本，未显式指定版本时使用。
    // 支持：v1（时间 + 节点）、v3（MD5 命名空间）、v4（随机）、v5（SHA1 命名空间）、
    //       v6（可排序时间）、v7（Unix 时间戳排序）、v8（自定义字节）。
    'version' => 'v4',

    // v3 / v5 使用的命名空间 UUID。
    // 可填写 dns、url、oid、x500 四个预定义命名空间之一，也可填写完整的 UUID 字符串。
    'namespace' => 'dns',

    // v1 / v6 使用的节点标识（通常是网卡 MAC 地址），格式如 00:11:22:33:44:55。
    // 留空则自动获取本机节点，自动获取失败时 ramsey/uuid 会随机生成节点。
    'node' => '',

    // v1 / v6 使用的时钟序列，取值范围 0 - 16383。
    // 留空则由 ramsey/uuid 自动生成，多实例部署时可手工指定以避免冲突。
    'clock_seq' => null,

    // generate() 方法的返回格式。
    // object：返回 Ramsey\Uuid\UuidInterface 对象，便于继续调用对象方法；
    // string：返回标准 36 位带连字符字符串，如 9b2c1f0e-...；
    // simple：返回 32 位无连字符的小写字符串，适合存储到 char(32) 字段。
    'return_type' => 'object',
];