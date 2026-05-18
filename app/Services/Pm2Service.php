<?php

namespace App\Services;

use Illuminate\Support\Facades\Process;

class Pm2Service
{
    private string $nvmBin;

    public function __construct()
    {
        $this->nvmBin = '/root/.nvm/versions/node/v25.9.0/bin';
    }

    public function stop(string $IdorName): void
    {
        Process::run("sudo env PATH={$this->nvmBin}:\$PATH {$this->nvmBin}/pm2 stop {$IdorName}");
    }

    public function start(string $IdorName): void
    {
        Process::run("sudo env PATH={$this->nvmBin}:\$PATH {$this->nvmBin}/pm2 start {$IdorName}");
    }

    public function restart(string $IdorName): void
    {
        Process::run("sudo env PATH={$this->nvmBin}:\$PATH {$this->nvmBin}/pm2 restart {$IdorName}");
    }
}