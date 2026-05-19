<?php

namespace BizwaChat\LaravelNotification;

use BizwaChat\LaravelNotification\Concerns\LogsExceptions;
use BizwaChat\LaravelNotification\Exceptions\BizwaChatException;
use BizwaChat\LaravelNotification\Http\BizwaChatClient;
use BizwaChat\LaravelNotification\Messages\AbstractMessage;
use BizwaChat\LaravelNotification\Messages\MediaMessage;
use BizwaChat\LaravelNotification\Messages\SimpleMessage;
use BizwaChat\LaravelNotification\Messages\TemplateMessage;
use BizwaChat\LaravelNotification\Resources\ContactsResource;
use BizwaChat\LaravelNotification\Resources\GroupsResource;
use BizwaChat\LaravelNotification\Resources\MessageBotsResource;
use BizwaChat\LaravelNotification\Resources\MessagesResource;
use BizwaChat\LaravelNotification\Resources\SourcesResource;
use BizwaChat\LaravelNotification\Resources\StatusesResource;
use BizwaChat\LaravelNotification\Resources\TemplateBotsResource;
use BizwaChat\LaravelNotification\Resources\TemplatesResource;
use BizwaChat\LaravelNotification\Support\ApiResponse;
use BizwaChat\LaravelNotification\Support\BizwaChatRouteResolver;
use Illuminate\Notifications\Notification;
use Throwable;

class BizwaChatManager
{
    use LogsExceptions;

    protected ?ContactsResource $contacts = null;

    protected ?StatusesResource $statuses = null;

    protected ?SourcesResource $sources = null;

    protected ?GroupsResource $groups = null;

    protected ?TemplatesResource $templates = null;

    protected ?TemplateBotsResource $templateBots = null;

    protected ?MessageBotsResource $messageBots = null;

    protected ?MessagesResource $messages = null;

    public function __construct(
        protected ?BizwaChatClient $client = null,
        protected ?BizwaChatRouteResolver $routeResolver = null,
    ) {
        $this->client ??= new BizwaChatClient();
        $this->routeResolver ??= new BizwaChatRouteResolver();
    }

    public function version(): string
    {
        return '1.0.0';
    }

    public function client(): BizwaChatClient
    {
        return $this->client;
    }

    public function routeResolver(): BizwaChatRouteResolver
    {
        return $this->routeResolver;
    }

    public function contacts(): ContactsResource
    {
        return $this->contacts ??= new ContactsResource($this->client());
    }

    public function statuses(): StatusesResource
    {
        return $this->statuses ??= new StatusesResource($this->client());
    }

    public function sources(): SourcesResource
    {
        return $this->sources ??= new SourcesResource($this->client());
    }

    public function groups(): GroupsResource
    {
        return $this->groups ??= new GroupsResource($this->client());
    }

    public function templates(): TemplatesResource
    {
        return $this->templates ??= new TemplatesResource($this->client());
    }

    public function templateBots(): TemplateBotsResource
    {
        return $this->templateBots ??= new TemplateBotsResource($this->client());
    }

    public function messageBots(): MessageBotsResource
    {
        return $this->messageBots ??= new MessageBotsResource($this->client());
    }

    public function messages(): MessagesResource
    {
        return $this->messages ??= new MessagesResource($this->client());
    }

    public function request(
        string $method,
        string $uri,
        array $payload = [],
        array $query = [],
        array $headers = [],
        bool $multipart = false,
    ): ApiResponse {
        return $this->client()->request($method, $uri, $payload, $query, $headers, $multipart);
    }

    public function raw(
        string $method,
        string $uri,
        array $payload = [],
        array $query = [],
        array $headers = [],
        bool $multipart = false,
    ): ApiResponse {
        return $this->request($method, $uri, $payload, $query, $headers, $multipart);
    }

    public function simple(string $messageBody): SimpleMessage
    {
        return SimpleMessage::make($messageBody);
    }

    public function template(string $templateName, string $language = 'en'): TemplateMessage
    {
        return TemplateMessage::make($templateName, $language);
    }

    public function media(string $mediaType): MediaMessage
    {
        return MediaMessage::make($mediaType);
    }

    public function send(AbstractMessage $message, mixed $notifiable = null, ?Notification $notification = null): ApiResponse
    {
        try {
            $route = $this->routeResolver()->resolve($notifiable, $notification, $message);

            return $message->send($this, $route);
        } catch (Throwable $exception) {
            $this->logException('Failed to dispatch BizwaChat message.', $exception, [
                'message_class' => $message::class,
                'notifiable_class' => is_object($notifiable) ? $notifiable::class : gettype($notifiable),
                'notification_class' => $notification ? $notification::class : null,
            ]);

            if ($exception instanceof BizwaChatException) {
                throw $exception;
            }

            throw new BizwaChatException('Failed to dispatch BizwaChat message.', [], 0, $exception);
        }
    }

    public function sendText(string $phoneNumber, string $messageBody, array $options = []): ApiResponse
    {
        $message = $this->simple($messageBody)->to($phoneNumber);

        return $this->send($this->applyMessageOptions($message, $options));
    }

    public function sendTemplate(string $phoneNumber, string $templateName, string $language = 'en', array $options = []): ApiResponse
    {
        $message = $this->template($templateName, $language)->to($phoneNumber);

        return $this->send($this->applyMessageOptions($message, $options));
    }

    public function sendMedia(string $phoneNumber, string $mediaType, mixed $source, array $options = []): ApiResponse
    {
        $message = $this->media($mediaType)->to($phoneNumber)->source($source);

        return $this->send($this->applyMessageOptions($message, $options));
    }

    protected function applyMessageOptions(AbstractMessage $message, array $options): AbstractMessage
    {
        if (isset($options['subdomain'])) {
            $message->onSubdomain((string) $options['subdomain']);
        }

        if (isset($options['from_phone_number_id'])) {
            $message->fromPhoneNumberId((string) $options['from_phone_number_id']);
        }

        if (isset($options['contact'])) {
            $message->contact($options['contact']);
        }

        return $message;
    }
}