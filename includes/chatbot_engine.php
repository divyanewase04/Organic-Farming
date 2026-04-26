<?php
declare(strict_types=1);

function containsAny(string $haystack, array $keywords): bool
{
    foreach ($keywords as $keyword) {
        if ($keyword !== '' && stripos($haystack, $keyword) !== false) {
            return true;
        }
    }

    return false;
}

function buildChatbotReply(string $message): array
{
    $input = strtolower(trim($message));
    if ($input === '') {
        return [
            'category' => 'empty',
            'reply' => 'Please type a farming question so I can help.',
        ];
    }

    if (containsAny($input, ['hello', 'hi', 'namaste'])) {
        return [
            'category' => 'greeting',
            'reply' => 'Hello. I can guide you on soil care, crops, compost, pest control, and irrigation.',
        ];
    }

    if (containsAny($input, ['soil', 'ph', 'fertility'])) {
        return [
            'category' => 'soil',
            'reply' => 'Use soil testing every season, keep pH near 6.5-7.5, rotate crops, and add compost plus green manure.',
        ];
    }

    if (containsAny($input, ['compost', 'fertilizer', 'nutrient'])) {
        return [
            'category' => 'fertilizer',
            'reply' => 'Apply farmyard manure, vermicompost, and bio-fertilizers in split doses based on crop stage.',
        ];
    }

    if (containsAny($input, ['pest', 'disease', 'insect', 'aphid'])) {
        return [
            'category' => 'pest',
            'reply' => 'Start with preventive IPM: sticky traps, neem spray, crop sanitation, and resistant seed varieties.',
        ];
    }

    if (containsAny($input, ['irrigation', 'water', 'drip'])) {
        return [
            'category' => 'irrigation',
            'reply' => 'Prefer drip irrigation in early morning, mulch to reduce evaporation, and avoid overwatering root zones.',
        ];
    }

    if (containsAny($input, ['market', 'price', 'sell'])) {
        return [
            'category' => 'market',
            'reply' => 'Track weekly mandi prices, group produce by grade, and plan harvest timing around expected demand peaks.',
        ];
    }

    return [
        'category' => 'fallback',
        'reply' => 'I can help with soil, fertilizer, pests, irrigation, and crop planning. Ask one specific farm question.',
    ];
}
