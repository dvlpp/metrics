<?php

namespace Dvlpp\Metrics\Tools;

use Illuminate\Console\Command;

trait LogToConsoleTrait {

    /**
     * Handle to the Console, to redirect display there
     * 
     * @var Command;
     */
    protected $console;

    /**
     * Set a console command to redirect output to
     * 
     * @param Command $console
     * @return void
     */
    public function setConsole(Command $console)
    {
        $this->console = $console;
    }

    /**
     * Shortcut method to add logging info
     * 
     * @param  string $text
     * @return void
     */
    protected function info($text)
    {
        if($this->console) {
            $this->console->info($text);
        }
    }

    /**
     * Shortcut method to add logging warning
     * 
     * @param  string $text
     * @return void
     */
    protected function warning($text)
    {
        if($this->console) {
            $this->console->comment($text);
        }
    }

    /**
     * Shortcut method to add logging error
     * 
     * @param  string $text
     * @return void
     */
    protected function error($text)
    {
        if($this->console) {
            $this->console->error($text);
        }
    }

}