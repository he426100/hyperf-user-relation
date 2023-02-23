<?php

declare(strict_types=1);
/**
 * This file is part of he426100/hyperf-user-relation.
 *
 * @link     https://github.com/he426100/hyperf-user-relation
 * @contact  mrpzx001@gmail.com
 * @license  https://github.com/he426100/hyperf-user-relation/blob/master/LICENSE
 */
namespace He426100;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
            ],
            'commands' => [
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                    'class_map' => [
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'user-relation',
                    'description' => 'user-relation-config',
                    'source' => __DIR__ . '/../publish/user_relation.php',
                    'destination' => BASE_PATH . '/config/autoload/user_relation.php',
                ],
            ],
        ];
    }
}
