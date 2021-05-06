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
    private $UA_file = __DIR__."/useragents.txt";
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
    public function randomUseragent() {
        $lines = file($this->UA_file);
        return trim($lines[array_rand($lines)]);
    }
    public function curlProxy($url, $proxyServer, $proxyAuth) {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_REFERER, "https://youtube.com/");
        curl_setopt($curl, CURLOPT_USERAGENT, $this->randomUseragent());
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
    public function setVideoId($videoId)
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
        if (array_key_exists('videoDetails', $this->videoInfo)){
            return new Details($this->videoInfo['videoDetails']);
        }else{
            return new Details([]);
        }

    }

    /**
     * @return Formats
     * @throws \Exception
     */
    public function getFormats(): Formats
    {
        if (array_key_exists('streamingData', $this->videoInfo)){
            return new Formats($this->videoInfo['streamingData']);
        }else{
            return new Formats([]);
        }

    }

    /**
     * @param string $lang [default='en']
     * @return Captions
     * @throws \Exception
     */
    public function getCaptions($lang = 'en'): Captions
    {
        try {
            if (array_key_exists('captions', $this->videoInfo)){
                return new Captions($this->videoInfo['captions'], $lang);
            }else{
                return new Captions([],$lang);
            }
        } catch (CException $ex) {
            return new Captions([],$lang);
        }catch (\Exception $ex) {
            return new Captions([],$lang);
        }


    }
}
