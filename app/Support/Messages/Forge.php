<?php

namespace App\Support\Messages;

use Illuminate\Support\Facades\Http;

class Forge extends Message
{
    public function send(): void
    {
        Http::asJson()
            ->timeout(10)
            ->post($this->relay->webhook_url, [
                'cards' => [
                    [
                        'sections' => [
                            [
                                'widgets' => [
                                    [
                                        ['textParagraph' => [
                                            'text' => $this->payload('status', 'failed') == 'success'
                                                ? '<font color="#4BB543"><b>Laravel Forge: A new update has been deployed</b></font>'
                                                : '<font color="#ff0000"><b>Laravel Forge: Deployment failed!</b></font>',
                                        ]],
                                        ['keyValue' => [
                                            'topLabel' => 'Commit Message',
                                            'content' => $this->payload('commit_message', ''),
                                        ]],
                                        ['keyValue' => [
                                            'topLabel' => 'Commit',
                                            'content' => __('<a href=":url">:hash</a>', [
                                                'url' => $this->payload('commit_url', '#'),
                                                'hash' => $this->payload('commit_hash', 'Hash'),
                                            ]),
                                        ]],
                                        ['keyValue' => [
                                            'topLabel' => 'Author',
                                            'content' => $this->payload('commit_author', ''),
                                        ]],
                                        ['keyValue' => [
                                            'topLabel' => 'Server',
                                            'content' => $this->payload('server.name', ''),
                                        ]],
                                        ['keyValue' => [
                                            'topLabel' => 'Site',
                                            'content' => $this->payload('site.name', ''),
                                        ]],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ])
            ->throw();
    }

    /**
     * @return array<string, mixed>
     */
    public static function samplePayload(): array
    {
        return [
            'status' => 'success',
            'commit_message' => 'Test message from Chismosa. No deployment happened.',
            'commit_hash' => 'test',
            'commit_author' => 'Chismosa',
            'server' => ['name' => 'Test server'],
            'site' => ['name' => 'Test site'],
        ];
    }
}
