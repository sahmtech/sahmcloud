<?php

namespace Bl\FatooraZatca\Actions;

use Bl\FatooraZatca\Helpers\ConfigHelper;

class VerifyAppKeyAction
{
    /**
     * terminate the app when no valid host in production.
     *
     * @return void
     */
    public function handle()
    {
        if(! ConfigHelper::isProduction()) {
            return;
        }

        $clients = json_decode(
            base64_decode(
                file_get_contents(__DIR__ . '/../clients.txt')
            )
        );

        $appKey = ConfigHelper::get('zatca.app.key');

        if(! isset($clients->{$appKey})) {
            return $this->handleFallback('aW52YWxpZC1hcHAta2V5');
        }

        $host = $clients->{$appKey}->host;

        if(! $this->isValidHost($host)) {
            return $this->handleFallback('aW52YWxpZC1ob3N0LW5hbWU=');
        }
    }

    /**
     * validate the host with domain or sub domains.
     *
     * @param  string $host
     * @return bool
     */
    private function isValidHost($host)
    {
        return strpos($_SERVER['HTTP_HOST'], $host) !== false;
    }

    /**
     * handle fallback redirect.
     *
     * @param string $message
     * @return void
     */
    private function handleFallback($message)
    {
        $message = base64_decode($message);
        header('location: ' . base64_decode('aHR0cHM6Ly9mYXRvb3JhemF0Y2EuY29t') . "?message=$message");
        exit();
    }
}
