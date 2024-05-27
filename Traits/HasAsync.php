<?php

declare(strict_types=1);

namespace MoonShine\Support\Traits;

use Closure;
use Illuminate\Support\Arr;
use MoonShine\Support\AlpineJs;
use MoonShine\Support\DTOs\AsyncCallback;

trait HasAsync
{
    protected Closure|string|null $asyncUrl = null;

    protected string|array|null $asyncEvents = null;

    protected ?AsyncCallback $asyncCallback = null;

    public function isAsync(): bool
    {
        return ! is_null($this->asyncUrl);
    }

    protected function prepareAsyncUrl(Closure|string|null $url = null): Closure|string|null
    {
        return $url;
    }

    protected function prepareAsyncUrlFromPaginator(): string
    {
        $withoutQuery = strtok($this->getAsyncUrl(), '?');

        if (! $withoutQuery) {
            return $this->getAsyncUrl();
        }

        $query = parse_url($this->getAsyncUrl(), PHP_URL_QUERY);

        parse_str((string) $query, $asyncUri);

        $paginatorUri = $this->getPaginator()
            ?->resolveQueryString() ?? [];

        $asyncUri = array_filter(
            $asyncUri,
            static fn ($value, $key): bool => ! isset($paginatorUri[$key]),
            ARRAY_FILTER_USE_BOTH
        );

        if ($asyncUri !== []) {
            return $withoutQuery . "?" . Arr::query($asyncUri);
        }

        return $withoutQuery;
    }

    public function disableAsync(): static
    {
        $this->asyncUrl = null;
        $this->asyncEvents = [];
        $this->asyncCallback = null;

        return $this;
    }

    public function async(
        Closure|string|null $url = null,
        string|array|null $events = null,
        ?AsyncCallback $callback = null,
    ): static {
        $this->asyncUrl = $this->prepareAsyncUrl($url);
        $this->asyncEvents = $events;
        $this->asyncCallback = $callback;

        return $this;
    }

    public function getAsyncUrl(): ?string
    {
        return value($this->asyncUrl, $this);
    }

    public function asyncEvents(): string|null
    {
        if (is_null($this->asyncEvents)) {
            return $this->asyncEvents;
        }

        return AlpineJs::prepareEvents($this->asyncEvents);
    }

    public function asyncCallback(): ?AsyncCallback
    {
        return $this->asyncCallback;
    }
}
