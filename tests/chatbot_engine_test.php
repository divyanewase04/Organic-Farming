<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/chatbot_engine.php';

function assertContains(string $needle, string $haystack, string $label): void
{
    if (stripos($haystack, $needle) === false) {
        throw new RuntimeException("Assertion failed for {$label}. Expected '{$needle}' in '{$haystack}'.");
    }
}

$soil = buildChatbotReply('How to improve soil pH?');
assertContains('soil', strtolower($soil['reply']), 'soil intent');

$fertilizer = buildChatbotReply('Suggest organic fertilizer');
assertContains('bio-fertilizers', strtolower($fertilizer['reply']), 'fertilizer intent');

$pest = buildChatbotReply('Any pest control tips?');
assertContains('ipm', strtolower($pest['reply']), 'pest intent');

$fallback = buildChatbotReply('Tell me something random');
assertContains('soil', strtolower($fallback['reply']), 'fallback guidance');

echo "All chatbot_engine tests passed.\n";
