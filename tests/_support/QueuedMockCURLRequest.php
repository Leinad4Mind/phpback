<?php

namespace Tests\Support;

use CodeIgniter\Test\Mock\MockCURLRequest;

/**
 * MockCURLRequest replays a single canned body; the Google OAuth callback
 * makes two HTTP calls (token exchange, then userinfo), so this variant
 * replays a queue of bodies in order.
 */
class QueuedMockCURLRequest extends MockCURLRequest
{
    /** @var list<string> */
    private array $outputs = [];

    /**
     * @param list<string> $outputs
     *
     * @return $this
     */
    public function setOutputs(array $outputs)
    {
        $this->outputs = $outputs;

        return $this;
    }

    protected function sendRequest(array $curlOptions = []): string
    {
        if ($this->outputs !== []) {
            $this->setOutput(array_shift($this->outputs));
        }

        return parent::sendRequest($curlOptions);
    }
}
