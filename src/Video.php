<?php


namespace FireCentaur;


use FireCentaur\Response\Details;
use FireCentaur\Response\Formats;
use FireCentaur\Response\Captions;
use Illuminate\Support\Facades\Log;

class Video
{
    /**
     * @var string
     */
    private $videoId;

    /**
     * base url for getting the video information
     * @var string
     */
    private $videoInfoUrl = 'https://youtube.com/get_video_info?video_id=';

    /**
     * @var array
     */
    private $videoInfo;

    public function __construct($videoId = '')
    {
        $this->setVideoId($videoId);
    }

    public function curlProxy($url, $proxyServer, $proxyAuth) {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_REFERER, "https://youtube.com/");
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Ubuntu; Linux; rv:69.0) Gecko/20100101 Firefox/69.0");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_PROXY, $proxyServer);
        curl_setopt($curl, CURLOPT_PROXYUSERPWD, $proxyAuth);
        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    /**
     * Set the video ID and get the raw video information
     *
     * @param $videoId
     * @return $this
     * @throws \Exception
     */
    public function setVideoId($videoId): self
    {
        $this->videoId = $videoId;
        if (!$this->videoId) {
            throw new \InvalidArgumentException('Video Id is empty');
        }

        $url = $this->videoInfoUrl . $this->videoId;
        $proxyServer = "http://proxy.apify.com:8000";
        $proxyAuth = env('APIFY_PROXY_USER').":".env('APIFY_PROXY_KEY');

        parse_str($this->curlProxy($url, $proxyServer, $proxyAuth), $info);
        if (count($info)==0){
            Log::error("Could not retrieve Video $url");
            return false;

        }
        if (!isset($info['player_response'])) {
            throw new \Exception("Video not found");
        }
        $this->videoInfo = json_decode($info['player_response'], true);
        return $this;
    }

    /**
     * @return array
     */
    public function getVideoInfo(): array
    {
        return $this->videoInfo;
    }

    /**
     * @return Details
     */
    public function getDetails(): Details
    {
        return new Details($this->videoInfo['videoDetails']);
    }

    /**
     * @return Formats
     * @throws \Exception
     */
    public function getFormats(): Formats
    {
        return new Formats($this->videoInfo['streamingData']);
    }

    /**
     * @param string $lang [default='en']
     * @return Captions
     * @throws \Exception
     */
    public function getCaptions($lang = 'en'): Captions
    {
        return new Captions($this->videoInfo['captions'], $lang);
    }
}
