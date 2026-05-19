<?php

namespace BizwaChat\LaravelNotification\Messages;

use BizwaChat\LaravelNotification\BizwaChatManager;
use BizwaChat\LaravelNotification\Support\ApiResponse;

class TemplateMessage extends AbstractMessage
{
    protected array $attributes = [];

    public function __construct(
        protected string $templateName,
        protected string $language = 'en',
    ) {
    }

    public static function make(string $templateName, string $language = 'en'): self
    {
        return new self($templateName, $language);
    }

    public function language(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function with(string $key, mixed $value): self
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    public function fields(array $fields): self
    {
        foreach (array_values($fields) as $index => $value) {
            $this->field($index + 1, $value);
        }

        return $this;
    }

    public function field(int $index, mixed $value): self
    {
        $this->attributes['field_'.$index] = $value;

        return $this;
    }

    public function headerTextParameter(mixed $value): self
    {
        $this->attributes['header_field_1'] = $value;

        return $this;
    }

    public function headerImageUrl(string $url): self
    {
        $this->attributes['header_image_url'] = $url;

        return $this;
    }

    public function headerImageFile(mixed $file): self
    {
        $this->attributes['header_image_file'] = $file;

        return $this;
    }

    public function headerVideoUrl(string $url): self
    {
        $this->attributes['header_video_url'] = $url;

        return $this;
    }

    public function headerVideoFile(mixed $file): self
    {
        $this->attributes['header_video_file'] = $file;

        return $this;
    }

    public function headerDocumentUrl(string $url, ?string $documentName = null): self
    {
        $this->attributes['header_document_url'] = $url;

        if ($documentName !== null) {
            $this->attributes['header_document_name'] = $documentName;
        }

        return $this;
    }

    public function headerDocumentName(string $documentName): self
    {
        $this->attributes['header_document_name'] = $documentName;

        return $this;
    }

    public function headerDocumentFile(mixed $file, ?string $documentName = null): self
    {
        $this->attributes['header_document_file'] = $file;

        if ($documentName !== null) {
            $this->attributes['header_document_name'] = $documentName;
        }

        return $this;
    }

    public function button(int $index, mixed $value): self
    {
        $this->attributes['button_'.$index] = $value;

        return $this;
    }

    public function copyCode(string $copyCode): self
    {
        $this->attributes['copy_code'] = $copyCode;

        return $this;
    }

    public function autoGenerateOtp(bool $enabled = true, ?int $length = null, ?int $fieldNumber = null): self
    {
        $this->attributes['auto_generate_otp'] = $enabled;

        if ($length !== null) {
            $this->attributes['otp_length'] = $length;
        }

        if ($fieldNumber !== null) {
            $this->attributes['otp_field_number'] = $fieldNumber;
        }

        return $this;
    }

    public function payload(array $route): array
    {
        return $this->mergeBasePayload(array_merge([
            'template_name' => $this->templateName,
            'template_language' => $this->language,
        ], $this->attributes), $route);
    }

    public function send(BizwaChatManager $manager, array $route): ApiResponse
    {
        return $manager->messages()->sendTemplate($this->payload($route), $this->subdomain($route));
    }
}