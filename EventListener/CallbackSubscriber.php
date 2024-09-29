<?php

declare(strict_types=1);

namespace MauticPlugin\MZagmajsterPostalBundle\EventListener;

use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\EmailBundle\EmailEvents;
use Mautic\EmailBundle\Event\TransportWebhookEvent;
use MauticPlugin\MZagmajsterPostalBundle\Exception\WebhookProcessException;
use MauticPlugin\MZagmajsterPostalBundle\Service\WebhookService;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Transport\Dsn;

class CallbackSubscriber implements EventSubscriberInterface
{
    private const ALLOWED_SCHEMES = [
        'mautic-postal+smtp',
        'mautic-postal+api',
    ];

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly CoreParametersHelper $coreParametersHelper,
        private readonly WebhookService $webhookService,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EmailEvents::ON_TRANSPORT_WEBHOOK => 'processCallbackRequest',
        ];
    }

    public function processCallbackRequest(TransportWebhookEvent $webhookEvent): void
    {
        $dsn = Dsn::fromString(
            $this->coreParametersHelper->get('mailer_dsn')
        );
        if (
            !in_array($dsn->getScheme(), self::ALLOWED_SCHEMES, true)
        ) {
            return;
        }

        try {
            $postData = $webhookEvent->getRequest()->request->all();
            $this->webhookService->processCallbackRequest($postData);
            $webhookEvent->setResponse(new Response('Callback processed'));
        } catch (WebhookProcessException $webhookProcessException) {
            $this->logger->error($webhookProcessException->getMessage());
            $webhookEvent->setResponse(new Response('Callback processing failed'));
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            $webhookEvent->setResponse(new Response('Callback processing failed'));
        }
    }
}
