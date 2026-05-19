<?php

namespace BizwaChat\LaravelNotification\Messages;

use BizwaChat\LaravelNotification\BizwaChatManager;
use BizwaChat\LaravelNotification\Support\ApiResponse;
use Illuminate\Http\UploadedFile;
use SplFileInfo;

class MediaMessage extends AbstractMessage
{
    protected ?string $mediaUrl = null;

    protected mixed $mediaFile = null;

    protected ?string $caption = null;

    protected ?string $filename = null;

    public function __construct(protected string $mediaType)
    {
    }

    public static function make(string $mediaType): self
    {
        return new self($mediaType);
    }

    public static function image(mixed $source): self
    {
        return self::make('image')->source($source);
    }

    public static function document(mixed $source): self
    {
        return self::make('document')->source($source);
    }

    public static function video(mixed $source): self
    {
        return self::make('video')->source($source);
    }

    public static function audio(mixed $source): self
    {
        return self::make('audio')->source($source);
    }

    public function source(mixed $source): self
    {
        if ($this->isFileLike($source)) {
            $this->mediaFile = $source;
            $this->mediaUrl = null;

            return $this;
        }

        $this->mediaUrl = (string) $source;
        $this->mediaFile = null;

        return $this;
    }

    public function url(string $url): self
    {
        $this->mediaUrl = $url;
        $this->mediaFile = null;

        return $this;
    }

    public function file(mixed $file): self
    {
        $this->mediaFile = $file;
        $this->mediaUrl = null;

        return $this;
    }

    public function caption(string $caption): self
    {
        $this->caption = $caption;

        return $this;
    }

    public function filename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function payload(array $route): array
    {
        return $this->mergeBasePayload(array_filter([
            'media_type' => $this->mediaType,
            'media_url' => $this->mediaUrl,
            'media_file' => $this->mediaFile,
            'caption' => $this->caption,
            'filename' => $this->filename,
        ], fn (mixed $value): bool => $value !== null && $value !== ''), $route);
    }

    public function send(BizwaChatManager $manager, array $route): ApiResponse
    {
        return $manager->messages()->sendMedia($this->payload($route), $this->subdomain($route));
    }

    protected function isFileLike(mixed $source): bool
    {
        return $source instanceof UploadedFile
            || $source instanceof SplFileInfo
            || (is_string($source) && is_file($source));
    }
}