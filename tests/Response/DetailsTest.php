<?php

namespace Response;

use PHPUnit\Framework\TestCase;
use FireCentaur\Response\Captions;
use FireCentaur\Response\Details;
use FireCentaur\Video;

class DetailsTest extends TestCase
{
    /**
     * @var Captions
     */
    private $details;
    private $scrapper;
    private $videoId = 'VVx6ntr5OqI';

    protected function setUp(): void
    {
        $this->scrapper = new Video($this->videoId);
        $this->details = $this->scrapper->getDetails();
        parent::setUp();
    }

    public function testGetVideoId()
    {
        $this->assertEquals($this->videoId, $this->details->getVideoId());
    }


    public function testGetTitle()
    {
        $this->assertStringContainsString('Don\'t find a job, find a mission', $this->details->getTitle());
    }

    public function getThumbnails()
    {
        $this->assertIsArray($this->details->getThumbnails());
    }

    public function getViewCount()
    {
        $this->assertIsInt($this->details->getViewCount());
    }

    public function getRating()
    {
        $this->assertIsInt($this->details->getRating());
    }

}
