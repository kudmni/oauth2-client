<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User;
use League\OAuth2\Client\Token\AccessToken;

class Microsoft extends AbstractProvider
{
    public $scopes = array('wl.basic', 'wl.emails');
    public $responseType = 'json';

    public function urlAuthorize()
    {
        return 'https://oauth.live.com/authorize';
    }

    public function urlAccessToken()
    {
        return 'https://oauth.live.com/token';
    }

    public function urlUserDetails(AccessToken $token)
    {
        return 'https://apis.live.net/v5.0/me?access_token='.$token;
    }

    public function userDetails($response, AccessToken $token)
    {
        $client = $this->getHttpClient();
        $client->setBaseUrl('https://apis.live.net/v5.0/' . $response->id . '/picture');
        $request = $client->get()->send();
        $info = $request->getInfo();
        $imageUrl = $info['url'];

        $user = new User;

        $email = (isset($response->emails->preferred)) ? $response->emails->preferred : null;

        $user->exchangeArray(array(
            'uid' => $response->id,
            'name' => $response->name,
            'firstName' => $response->first_name,
            'lastName' => $response->last_name,
            'email' => $email,
            'imageUrl' => $imageUrl,
            'urls' => $response->link . '/cid-' . $response->id,
        ));

        return $user;
    }

    public function userUid($response, AccessToken $token)
    {
        return $response->id;
    }

    public function userEmail($response, AccessToken $token)
    {
        return isset($response->emails->preferred) && $response->emails->preferred
            ? $response->emails->preferred
            : null;
    }

    public function userScreenName($response, AccessToken $token)
    {
        return array($response->first_name, $response->last_name);
    }
}
