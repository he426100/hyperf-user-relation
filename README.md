# hyperf-user-relation

受[jtar-hyperf-user-node](https://github.com/taobali32/jtar-hyperf-user-node)启发，把自己常用的用户关系实现发出来

## Installation

- Request

```bash
composer require he426100/hyperf-user-relation
```

- Publish

```bash
php bin/hyperf.php vendor:publish he426100/hyperf-user-relation
```


用户关系表参考  
```
CREATE TABLE `user_relation`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '父级ID',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '会员ID',
  `level` int(11) UNSIGNED NOT NULL DEFAULT 1 COMMENT '关系等级',
  `create_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `parent_user_index`(`parent_id`, `user_id`) USING BTREE,
  UNIQUE INDEX `user_level_index`(`user_id`, `level`) USING BTREE,
  INDEX `rds_idx_0`(`user_id`, `parent_id`, `level`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = COMPACT;
```