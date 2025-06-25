<?php

declare(strict_types=1);

use DIJ\Langfuse\Laravel\Facades\Langfuse;
use DIJ\Langfuse\PHP\Responses\TextPromptResponse;
use DIJ\Langfuse\PHP\Testing\Responses\GetPromptResponse;

use function Pest\Faker\fake;

it('can use a fake to get a response', function (): void {
    $promptName = fake()->name();
    $prompt = fake()->sentence();

    Langfuse::fake([
        new GetPromptResponse(data: [
            'name' => $promptName,
            'type' => 'text',
            'prompt' => $prompt,
        ]),
    ]);

    $prompt = Langfuse::prompt()->text($promptName);
    expect($prompt)->toBeInstanceOf(TextPromptResponse::class);
});
