<?php

namespace MauticPlugin\MZagmajsterPostalBundle;

final class PostalMessageStatus
{
    public const MESSAGE_SENT = 'MessageSent';

    public const MESSAGE_DELAYED = 'MessageDelayed';

    public const MESSAGE_DELIVERY_FAILED = 'MessageDeliveryFailed';

    public const MESSAGE_HELD = 'MessageHeld';

    public const MESSAGE_BOUNCED = 'MessageBounced';

    public const MESSAGE_LINK_CLICKED = 'MessageLinkClicked';

    public const MESSAGE_LOADED = 'MessageLoaded';

    public const DOMAIN_DNS_ERROR = 'DomainDNSError';
}
