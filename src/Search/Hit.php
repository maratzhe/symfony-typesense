<?php
declare(strict_types=1);

namespace Maratzhe\SymfonyTypesense\Search;

/**
 * @template T of object
 */
readonly class Hit
{
    public function __construct(
        /**
         * @var T
         */
        public object $document,

        /**
         * @var array<string, mixed>
         */
        public array $highlight,


        public ?int $text_match = null,

        /**
         * @var array<string, mixed>
         */
        public ?array $text_match_info = null,
    ) {
    }
}
