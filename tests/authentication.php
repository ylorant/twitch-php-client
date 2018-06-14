<?php

use TwitchClient\API\Auth\Authentication;
use TwitchClient\Authentication\DefaultTokenProvider;
use League\CLImate\CLImate;

require __DIR__. '/../vendor/autoload.php';

// Creating output helper
$climate = new CLImate();
$climate->out('Authentication test for Twitch PHP client');

// Reading the PHPUnit configuration file to get the clientID / clientSecret / Redirect URI for the app
$phpUnitXML = new SimpleXMLElement(__DIR__ . "/../phpunit.xml", 0, true);
$parameters = [];

foreach($phpUnitXML->php->const as $declaration) {
    $name = (string) $declaration->attributes()->name;
    $value = (string) $declaration->attributes()->value;
    
    $parameters[$name] = $value;
}

// Initializing the TokenProvider
$tokenProvider = new DefaultTokenProvider($parameters['CLIENT_ID'], $parameters['CLIENT_SECRET']);
$redirectUri = $parameters['REDIRECT_URI'];

$climate->br()->out("For which scopes would you like to create the token ?");
$climate->out("Enter a space-separated list of scopes:");

$input = $climate->input(">>>");
$scopes = $input->prompt();

$scopes = explode(" ", $scopes);

// Getting auth URL and showing it
$authenticationAPI = new Authentication($tokenProvider);
$url = $authenticationAPI->getAuthorizeURL($redirectUri, $scopes);

$climate->br()->out('Open this URL in your browser:');
$climate->out($url);
$climate->br()->out("Once you've accepted the app, you'll be redirected to a localhost page with a code in the URL.");
$climate->out("Paste it on the following prompt to continue.");

// Asking for the auth reply code
$climate->br();
$input = $climate->input(">>>");
$code = $input->prompt();

// Fetching the actual token
$climate->out("Trying to get a token from this code...");
$token = $authenticationAPI->getAccessToken($code, $redirectUri);

if($token) {
    $climate->out("Token fetch successful!");
    $climate->br()->out("Access token: ". $token['token']);
    $climate->out("Refresh token: ". $token['refresh']);
} else {
    $climate->out("Error while fetching token!");
}