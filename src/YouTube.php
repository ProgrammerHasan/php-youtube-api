<?php
/**
 * Created by PhpStorm
 * User: ProgrammerHasan
 * Date: 10-10-2020
 * Time: 9:16 PM
 */

namespace ProgrammerHasan\YouTube;

class YouTube{
 /**
     * @var string
     */
    protected $youtube_key; // from the config file

    /**
     * @var array
     */
    public $APIs = [
        'categories.list' => 'https://www.googleapis.com/youtube/v3/videoCategories',
        'videos.list' => 'https://www.googleapis.com/youtube/v3/videos',
        'search.list' => 'https://www.googleapis.com/youtube/v3/search',
        'channels.list' => 'https://www.googleapis.com/youtube/v3/channels',
        'playlists.list' => 'https://www.googleapis.com/youtube/v3/playlists',
        'playlistItems.list' => 'https://www.googleapis.com/youtube/v3/playlistItems',
        'activities' => 'https://www.googleapis.com/youtube/v3/activities',
        'commentThreads.list' => 'https://www.googleapis.com/youtube/v3/commentThreads',
    ];

    /**
     * @var array
     */
    public $page_info = [];

    /**
     * @var array
     */
    protected $config = [];

    /**
     * Constructor
     * $youtube = new Youtube(['key' => 'KEY HERE'])
     *
     * @param string $key
     * @param array $config
     * @throws \Exception
     */
    public function __construct($key, $config = [])
    {
        if (is_string($key) && !empty($key)) {
            $this->youtube_key = $key;
        } else {
            throw new \Exception('Google API key is Required, please visit https://console.developers.google.com/');
        }
        $this->config['use-http-host'] = isset($config['use-http-host']) ? $config['use-http-host'] : false;
    }

    /**
     * @param $setting
     * @return Youtube
     */
    public function useHttpHost($setting)
    {
        $this->config['use-http-host'] = !!$setting;

        return $this;
    }

    /**
     * @param $key
     * @return Youtube
     */
    public function setApiKey($key)
    {
        $this->youtube_key = $key;

        return $this;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->youtube_key;
    }

    public static function get_youtube_video_id_from_url($url)
    {
        preg_match('/(http(s|):|)\/\/(www\.|)yout(.*?)\/(embed\/|watch.*?v=|)([a-z_A-Z0-9\-]{11})/i', $url, $results);    return $results[6];
    }

    public static function channel_verify_by_id($channelId)
    {
        $API = env('YOUTUBE_API');
        $url = "https://www.googleapis.com/youtube/v3/channels?part=id&id={$channelId}&key={$API}";
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        $result = json_decode( curl_exec( $ch ) );
        if($result->pageInfo->resultsPerPage != 0)
        {
          return  $result->items[0]->id;
        }
        return  'error';
    }

    public static function get_channel_avatar_url($channelId)
    {
        $API = env('YOUTUBE_API');
        $url = "https://www.googleapis.com/youtube/v3/channels?part=snippet&fields=items%2Fsnippet%2Fthumbnails%2Fdefault&id={$channelId}&key={$API}";
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        $channelOBJ = json_decode( curl_exec( $ch ) );
        $thumbnail_url = $channelOBJ->items[0]->snippet->thumbnails->default->url;
        return $thumbnail_url;
    }

    public static function get_channel_brandingSettings($channelId)
    {
        $API = env('YOUTUBE_API');
        $url = "https://www.googleapis.com/youtube/v3/channels?part=brandingSettings&id={$channelId}&key={$API}";
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        $result = json_decode( curl_exec( $ch ) );
       return $result->items[0];
    }
    public static function get_channel_title($channelId)
    {
      return  self::get_channel_brandingSettings($channelId)->brandingSettings->channel->title;
    }
    public static function get_channel_description($channelId)
    {
        return  self::get_channel_brandingSettings($channelId)->brandingSettings->channel->description??'';
    }
    public static function get_channel_bannerImageUrl($channelId)
    {
        return  self::get_channel_brandingSettings($channelId)->brandingSettings->image->bannerImageUrl;
    }
    public static function get_channel_country($channelId)
    {
        return  self::get_channel_brandingSettings($channelId)->brandingSettings->channel->country;
    }


    public static function get_channel_statistics($channelId)
    {
        $API = env('YOUTUBE_API');
        $url = "https://www.googleapis.com/youtube/v3/channels?part=statistics&id={$channelId}&key={$API}";
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        $result = json_decode( curl_exec( $ch ) );
        return $result->items[0];
    }
    public static function get_viewCount($channelId)
    {
        return  self::get_channel_statistics($channelId)->statistics->viewCount;
    }
    public static function get_commentCount($channelId)
    {
        return  self::get_channel_statistics($channelId)->statistics->commentCount;
    }
    public static function get_subscriberCount($channelId)
    {
        return  self::get_channel_statistics($channelId)->statistics->subscriberCount;
    }
    public static function get_hiddenSubscriberCount($channelId)
    {
        return  self::get_channel_statistics($channelId)->statistics->hiddenSubscriberCount;
    }
    public static function get_videoCount($channelId)
    {
        return  self::get_channel_statistics($channelId)->statistics->videoCount;
    }

    // get channel all videos
    public static function get_channel_videos($channelId)
    {
        $API = env('YOUTUBE_API');
        $url = "https://www.googleapis.com/youtube/v3/search?channelId={$channelId}&order=date&part=snippet&type=video&maxResults=20&key={$API}";
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        $result = json_decode( curl_exec( $ch ) );
        if (!empty($result->items)) {
            return $result;
        }
        return 'error';
//
//        $arr_list = array();
//        $url = "https://www.googleapis.com/youtube/v3/search?channelId=".$channelId."&order=date&part=snippet&type=video&maxResults=10&key=".env('YOUTUBE_API');
//        $arr_list = getYTList($url);
//        if (!empty($arr_list)) {
////            echo '<ul class="video-list">';
////            foreach ($arr_list->items as $yt) {
////                echo "<li>". $yt->snippet->title ." (". $yt->id->videoId .")</li>";
////            }
////            echo '</ul>';
////
////            if (isset($arr_list->nextPageToken)) {
////                echo '<input type="hidden" class="nextpagetoken" value="'. $arr_list->nextPageToken .'" />';
////                echo '<div id="loadmore">Load More</div>';
////            }
//            return $arr_list;
//        }
//        return 'error';
    }


}
