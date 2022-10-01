<?php

declare(strict_types=1);

namespace App\Kafka\Consumer;

enum ConsumeStatus
{
    case Success;
    case Kafka_PartitionEOF;
    case Kafka_Timeout;
    case UnrecognizedError;
    case MoveToDeadLetterTopic;

    public const STATUS_FOR_COMMIT = [
        self::Success,
        self::MoveToDeadLetterTopic,
    ];

    public const STATUS_FOR_SLEEP = [
        self::Kafka_PartitionEOF,
        self::Kafka_Timeout,
    ];

    public function toString(): string
    {
        return match ($this) {
            self::Success => 'Сообщение успешно обработано.',
            self::Kafka_PartitionEOF => 'Достигнут конец партиции.',
            self::Kafka_Timeout => 'Таймаут.',
            self::UnrecognizedError => 'Неизвестная ошибка.',
            self::MoveToDeadLetterTopic => 'Не удалось обработать сообщение. Отправлено в DLQ.',
        };
    }
}
