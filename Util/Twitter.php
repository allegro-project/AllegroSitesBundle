<?php

/*
 * Twitter - Facade class for getting data from twitter using the TwitterAPIExchange
 *
 * (c) Arturo Rodríguez <arturo@fugadigital.com>
 * MIT License
 */

namespace Allegro\SitesBundle\Util;

use Allegro\SitesBundle\Util\TwitterAPIExchange;

/**
 * Twitter : Facade class for getting data from twitter using the TwitterAPIExchange
 * 
 * @see http://github.com/j7mbo/twitter-api-php
 */
class Twitter
{
    protected $settings;
    protected $datetimeZone;
    protected $datetimeFormat;

    public function __construct()
    {
        $this->settings = array(
            'consumer_key' => 'foo',
            'consumer_secret' => 'bar',
            'oauth_access_token' => 'baz',
            'oauth_access_token_secret' => 'qux',
        );
        $this->datetimeZone = 'America/Mexico_City';
        $this->datetimeFormat = 'd M, H:i';
    }

    public function getUserTimeline($user, $maxTwits = 25, $returnOnNull = null)
    {
        $res = $this->getTimeline($user, 'user', $maxTwits);
        return null !== $res ? $res : $returnOnNull;
    }

    public function getHomeTimeline($user, $maxTwits = 25, $returnOnNull = null)
    {
        $res = $this->getTimeline($user, 'home', $maxTwits);
        return null != $res ? $res : $returnOnNull;
    }

    /**
     * Obtains a user's timeline twits
     *
     * @param string $user     The user name
     * @param string $line     'home' | 'user'
     * @param int    $maxTwits Maximum number of twits to request
     *
     * @return mixed null on error and array otherwise
     */
    protected function getTimeline($user, $line, $maxTwits = 25)
    {
        // Set the GET field BEFORE calling buildOauth();
        $requestMethod = 'GET';
        $url = 'https://api.twitter.com/1.1/statuses/' . $line . '_timeline.json';
        $getfield =   '?screen_name=' . $user
                    . '&count=' . $maxTwits
                    . '&include_rts=1';

        $twitter = new TwitterAPIExchange($this->settings);
        $json = $twitter
            ->setGetfield($getfield)
            ->buildOauth($url, $requestMethod)
            ->performRequest();
        $timeline = json_decode($json, true);

        if (isset($timeline['errors'])) {
            return null;
        }

        if (empty($timeline)) {
            return array();
        }

        foreach ($timeline as &$twit) {
            $date = new \DateTime($twit['created_at']);
            $date->setTimezone(new \DateTimeZone($this->datetimeZone));
            $twit['created_at'] = $date->format($this->datetimeFormat) . ' mx';
            $twit['retweet_count'] = +$twit['retweet_count'];
            $twit['favorite_count'] =  +$twit['favorite_count'];
        }

        return $timeline;
    }

    public function getLastTweet() {
        ;
    }
}
