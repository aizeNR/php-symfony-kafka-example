<?php

declare(strict_types=1);

namespace App\Kafka\Enum;

class Topic
{
    /**
     * Топик для получения изменения товара.
     */
    public const SYSTEM_ITEM_V1 = 'system.item.v1';

    /**
     * Внутрений топик для отправки сообщений, которые не смогли обработать.
     */
    public const PARTNERS_DLQ_V1 = 'partners.dlq.v1';
}
