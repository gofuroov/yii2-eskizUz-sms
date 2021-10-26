<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px" alt="Yii LOGO">
    </a>
    <h1 align="center">Yii2 sms manager for Eskiz.uz</h1>
    <br>
</p>

## O'rnatish

1. *backend/modules* papkasiga *smsgo* papkasini joylashtirasiz. 
2. *backend/config/main.php* fayliga quyidagi kodni joylashtirasiz:
    ```php
   return [
   ...
   'modules' => [
        'smsgo' => [
            'class' => backend\modules\smsgo\Smsgo::class,
        ],
    ],
   ...
   ];
   ```
3. *backend/config/main.php* (Backend da ishlatish uchun) yoki *common/config/main.php* (Umumiy proectda ishlatish uchun) quyidagi qatorni qo'shasiz:
   ```php
   return [
   ...
   'components' => [
        ...
       'sms' => [
            'class' => backend\modules\smsgo\models\Sms::class
        ],
        ...
   ],
   ...
   ];
   ```
4. Migratsiya uchun *console/config/main.php* fayliga quyidagi qatorni qo'shasiz:
   ```php
   return [
   ...
   'controllerMap' => [
        'smsgo' => [
            'class' => "backend\modules\smsgo\controllers\MigrateController",
        ]
    ],
   ...
   ];
   ```
   
## Ishga tushurish

Migratsiyani amalga oshirish uchun:
```
php yii smsgo
```
Migratsiyani bekor qilish uchun:
```
php yii smsgo/down all
```

## Ishlatish
```php
Yii::$app->sms->send(string $phone, string $message, int $user_id = null, string $from = self::MAIN_NUMBER)
```
yoki
```php
(new \backend\modules\smsgo\models\Sms())->send(string $phone, string $message, int $user_id = null, string $from = self::MAIN_NUMBER)
```

>  Created by: Olimjon Gofurov! :wink: