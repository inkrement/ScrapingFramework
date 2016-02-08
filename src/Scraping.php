<?php

namespace Inkrement\ScrapingFramework;

use Cascade\Cascade;

class Scraping
{
    private const LINUX_CONFIG_PATH ='/etc/WebScraping/config.yaml';

    protected function init()
    {
      if(file_exists(LINUX_CONFIG_PATH)){
        // init logging
        Cascade::fileConfig(LINUX_CONFIG_PATH);
      }
    }

    protected function getLogger($prog_name){
      return Cascade::getLogger($prog_name)
    }
}
