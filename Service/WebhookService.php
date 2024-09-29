<?php

namespace MauticPlugin\MZagmajsterPostalBundle\Service;

use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\EmailBundle\Model\TransportCallback;
use Mautic\LeadBundle\Entity\DoNotContact;
use MauticPlugin\MZagmajsterPostalBundle\Exception\WebhookProcessException;
use MauticPlugin\MZagmajsterPostalBundle\PostalMessageStatus;
use Psr\Log\LoggerInterface;

class WebhookService
{
    public function __construct(
        private LoggerInterface $logger,
        private TransportCallback $transportCallback,
        private CoreParametersHelper $coreParametersHelper,
    ) {
    }

    /**
     * Process Callback Request.
     *
     * @throws WebhookProcessException
     */
    public function processCallbackRequest(array $payload): void
    {
        $method = 'processCallbackRequest';
        $this->logger->info($method);

        $event = $payload['event'] ?? null;
        if (null === $event) {
            throw new WebhookProcessException('Event not specified');
        }

        $subPayload = $payload['payload'] ?? null;
        if (null === $subPayload) {
            throw new WebhookProcessException('Payload not specified');
        }

        $message    = $subPayload['original_message'] ?? $subPayload['message'];
        /**
         * @todo Use these variables to correctly set DNC records.
         * Note: Message ID is gonna be useful only in case of API sending.
         */
        $postalMessageId   = $message['id'];
        $emailAddress      = $message['to'];

        $email      = $message['to'];
        $emailId    = $message['message_id'];

        switch ($event) {
            case PostalMessageStatus::MESSAGE_DELAYED:
                /**
                 * @todo Limit the number of times we can delay the message and add contact to DNC after that.
                 * Email stats.is_failed=1
                 */
                break;

            case PostalMessageStatus::MESSAGE_DELIVERY_FAILED:
                // @todo: Hard bounce DNC::BOUNCED
                // * Email stats.is_failed=1
                $this->transportCallback->addFailureByAddress($email, 'Delivery failed', DoNotContact::BOUNCED, $emailId);
                break;

            case PostalMessageStatus::MESSAGE_BOUNCED:
                // @todo: Detect soft or hard bounce.
                $this->transportCallback->addFailureByAddress($email, 'Hard bounce', DoNotContact::BOUNCED, $emailId);
                break;

            case PostalMessageStatus::MESSAGE_HELD:
            case PostalMessageStatus::MESSAGE_SENT:
            case PostalMessageStatus::MESSAGE_LINK_CLICKED:
            case PostalMessageStatus::MESSAGE_LOADED:
            case PostalMessageStatus::DOMAIN_DNS_ERROR:
            default:
                break;
        }
    }
}
