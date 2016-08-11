
redis对象
========================


版本
====

- v0.1.0 王艳星 创建

用法
========

## 实例

单例模式

## 使用方法

```
MyRedis::instance()->lpush('test',11);
```
## redis有非常多的操作方法，我们只封装了一部分，也可以直接调用redis自身方法，如下：

```
$redis = MyRedis::instance()->redis();
$redis->lpush('test',1);
```

## 多实例

多实例将在后续添加
