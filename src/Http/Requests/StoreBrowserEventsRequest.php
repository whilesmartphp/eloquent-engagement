<?php

namespace Whilesmart\Engagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Whilesmart\Engagement\Support\ClientRegistry;

class StoreBrowserEventsRequest extends FormRequest
{
    private ?array $engagementClient = null;

    public function authorize(): bool
    {
        $this->engagementClient = app(ClientRegistry::class)->matching(
            (string) ($this->header('X-Engagement-Site-Key') ?? $this->input('site_key')),
            $this->header('Origin')
        );

        return $this->engagementClient !== null;
    }

    public function rules(): array
    {
        $maximum = (int) config('engagement.max_batch_size', 20);

        return [
            'site_key' => ['nullable', 'string', 'max:200'],
            'events' => ['required', 'array', 'min:1', 'max:'.$maximum],
            'events.*.name' => ['required', 'string', 'max:120', 'regex:/^[a-zA-Z0-9._:-]+$/'],
            'events.*.visitor_id' => ['required', 'string', 'max:100'],
            'events.*.session_id' => ['required', 'string', 'max:100'],
            'events.*.url' => ['nullable', 'url', 'max:4000'],
            'events.*.referrer' => ['nullable', 'url', 'max:4000'],
            'events.*.source' => ['nullable', 'string', 'max:200'],
            'events.*.utm_medium' => ['nullable', 'string', 'max:200'],
            'events.*.utm_campaign' => ['nullable', 'string', 'max:200'],
            'events.*.utm_term' => ['nullable', 'string', 'max:200'],
            'events.*.utm_content' => ['nullable', 'string', 'max:200'],
            'events.*.click_id' => ['nullable', 'string', 'max:500'],
            'events.*.properties' => ['nullable', 'array'],
            'events.*.occurred_at' => ['nullable', 'date', 'before_or_equal:now'],
        ];
    }

    public function clientKey(): string
    {
        return (string) $this->engagementClient['key'];
    }
}
