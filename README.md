# Twitch PHP Client
[![FOSSA Status](https://app.fossa.io/api/projects/git%2Bgithub.com%2Fylorant%2Ftwitch-php-client.svg?type=shield)](https://app.fossa.io/projects/git%2Bgithub.com%2Fylorant%2Ftwitch-php-client?ref=badge_shield)


This library is a PHP client for the Twitch APIs. It will allow you to send requests to Twitch easily, using
authentication or not, with a simple design that allows embedding into services. It also relies on as few PHP
dependencies as possible, it only needs the cURL extension to be installed, and depends on `psr/log` for its
LoggerInterface.

Compatibility : PHP 7.0+

*Note: this library is still quite incomplete API-wise. Those will be added progressively. You can still add 
functionality quite easily though by adding to the already existing endpoints or by creating missing endpoints. 
If then you want to contribute it back to the project, just do a pull request :)*

## Install

Using composer:

```
composer install ylorant/twitch-php-client
```

## Basic usage (using Kraken)

For more info about token providers and authentication, see the next section.

```php
<?php
use TwitchClient\API\Kraken\Kraken;
use TwitchClient\Authentication\DefaultTokenProvider;

// Create a default token provider
$tokenProvider = new DefaultTokenProvider('client_id', 'client_secret');

// Create the client for the specific API we want to query, here it's Kraken
$kraken = new Kraken($tokenProvider);

// Fetch info for an user, for example
$userInfo = $kraken->users->info('esamarathon');
```

## Authentication: getting tokens

For some write operations, Twitch requires you to use an access token linked to the entity you want to edit. For this
purpose, there's [an API](https://dev.twitch.tv/docs/authentication/) available to request access tokens to specific
user accounts (using the "Authorize app" page you might have seen already) with multiple authentication workflows. 

This library provides a way to fetch tokens using the OAuth Authorization code workflow. Here is a sample code that
would work to fetch an access token. You can see another example in the file `tests/authentication.php`.

```php
<?php
use TwitchClient\API\Auth\Authentication;
use TwitchClient\Authentication\DefaultTokenProvider;

// Create the token provider using the client ID and secret.
$tokenProvider = new DefaultTokenProvider('client_id', 'client_secret');
$redirectURI = 'http://localhost/'; // The redirect URI configured in the app settings on Twitch.
                                    // Here we'll suppose that we're on a single page that handles both.

$authAPI = new Authentication($tokenProvider);

// If the call has a GET parameter named 'code', then we're on the redirect URI
if (!empty($_GET['code'])) {
    // Getting the access token, the API requiring to send 
    $accessToken = $authAPI->getAccessToken($_GET['code'], $redirectURI);

    var_dump($accessToken); // Dumping it for example
} else {
    // Get an authorize URL with some scope (here the one to allow the app to change the stream title and game)
    $authorizeURL = $authAPI->getAuthorizeURL($redirectURI, ['channel_editor']);
    
    // Redirect the user to the authorize page
    header('Location: '. $authorizeURL);
}

```

## The TokenProvider interface

Twitch API relies on user and app authentication to allow access to its API, so to account for that, this library
uses a simple interface called the TokenProvider (at `TwitchClient\Authentication\TokenProvider`). This interface 
describes how the library will retrieve app and user authentication.

You can choose to implement your own token provider by creating an object implementing the `TokenProvider` interface,
in case you need to store specifically your user tokens, or, if you use a more basic workflow that needs to set
the tokens once, you can use the `DefaultTokenProvider` to implement a standard token provider functionality to the
client.

## Testing

Before testing, you need to install development dependencies :

```
composer install --dev
```

This will install phpunit, Faker and Monolog to ensure debbuging logs, fake data and the testing framework itself.

To test the app, duplicate and rename the `phpunit.xml.dist` file and rename it to `phpunit.xml`, then fill out in it
the info relative to the app credentials (you can use the `tests/authentication.php` file to generate an access token).
Once this is done, you can start tests using the `phpunit` command.

## License
[![FOSSA Status](https://app.fossa.io/api/projects/git%2Bgithub.com%2Fylorant%2Ftwitch-php-client.svg?type=large)](https://app.fossa.io/projects/git%2Bgithub.com%2Fylorant%2Ftwitch-php-client?ref=badge_large)