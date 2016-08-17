日志工具
========================


版本
====

- v0.1.0 王艳星 创建

用法
========

## 简介

 1. 日志分级：debug info notice warn error
 2. 归类：按子系统（分组，根据对应的域得到分组）来集中日志，方便查看跟踪
 3. 归档：按天，按日志等级来文件归档

## 使用方法

```
Logger::debug($msg);
```

## 日志文件结构
    
位于项目目录下
```
    |-- Logs
        |-- yourdomain
            |-- 20160810
                    ├── 16_08_10_debug.log
                    └── 16_08_10_info.log
....         
```