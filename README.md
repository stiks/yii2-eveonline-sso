# Yii2 EveOnline SSO OAuth2 client extension

This extension uses standart `yii2-authclient` to allow auth via EveOnline site. Based on `unti1x/yii2-eveonline-sso`

## Dependencies

The only required package is `yiisoft/yii2-authclient` version "2.1+"

## Installation

### Via Composer

Add line `"stiks/yii2-eveonline-sso": "*"` in your `composer.json` into `require`-section
and then update packages

### Via Console

Use following command:

```bash
composer require "stiks/yii2-eveonline-sso" "dev-master"
```

## Usage

Configuration, config/web.php:

```php
'components' => [

    # ...

    'authClientCollection' => [
        'class' => 'yii\authclient\Collection',
        'clients' => [
            'eve-online-sso' => [
                'class' => 'stiks\eveonline_sso\EveOnlineSSO',
                'clientId' => 'Your Client ID',
                'clientSecret' => 'Your Client Secret',
            ],
        ],
    ]

    # ...

]

```

You can also add some fields into `User` model, like `character_id`, `character_name`, `owner_hash`.
The last one is strongly recomended because characters can be transfered to another account and CCP
provides a way to check this with unique code (see SSO manual, "Obtain the character ID" section).


Next register `auth` action in a controller:

```php
    public function actions () {
        return [
            'auth' => [
                'class' => 'yii\authclient\AuthAction',
                'successCallback' => [$this, 'successCallback'],
            ],
        ];
    }
```

And implement `successCallback`-method that should be called on user successfully authorized.

You can use something like this:

```php

    public function successCallback ($client) {
        $attributes = $client->getUserAttributes();

        if (Yii::$app->user->isGuest) {
            # get user by hash
            $user = User::findOne (['owner_hash' => $attributes['CharacterOwnerHash']]);
            if($user) {
                Yii::$app->user->login ($user);

                return $this->->goHome ();
            }

            # new user found
            $user = new User ();
            $user->attributes = [
                  'character_id'   => $attributes['CharacterID'],
                  'character_name' => $attributes['CharacterName'],
                  'owner_hash'     => $attributes['CharacterOwnerHash']
            ];
            if (!$user->save ()) {
                Yii::error (print_r ($user->getErrors (), true));
            }

            Yii::$app->user->login ($user);
        }

        return $this->->goHome ();
    }
```

Example code for views:

```php
<?php use yii\helpers\Url; ?>

<?php if(Yii::$app->user->isGuest): ?>
    <a href="<?= Url::toRoute('site/auth', ['authclient' => 'eve-online-sso']) ?>">
        <img src="https://images.contentful.com/idjq7aai9ylm/18BxKSXCymyqY4QKo8KwKe/c2bdded6118472dd587c8107f24104d7/EVE_SSO_Login_Buttons_Small_White.png?w=195&h=30" alt="SSO auth" />
    </a>
<?php else:?>
    <div class="user-avatar">
        <img src="//image.eveonline.com/Character/<?= Yii::$app->user->identity->character_id ?>_128.jpg" alt="avatar" />
    </div>
    <?= Yii::$app->user->identity->character_name ?>
<?php endif; ?>

```

## Links

 * [Yii2 AuthClient documentation](http://www.yiiframework.com/doc-2.0/ext-authclient-index.html)
 * [EveOnline Single Sign-On manual](http://eveonline-third-party-documentation.readthedocs.io/en/latest/sso/index.html)

## License

CreativeCommons Attribution-ShareAlike 4.0
 ([user friendly](https://creativecommons.org/licenses/by-sa/4.0/), [legal](https://creativecommons.org/licenses/by-sa/4.0/legalcode))
