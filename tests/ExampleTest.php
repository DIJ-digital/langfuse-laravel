<?php

declare(strict_types=1);

use DIJ\Langfuse\Laravel\Facades\Langfuse;
use DIJ\Langfuse\Laravel\LangfuseDecorator;
use DIJ\Langfuse\PHP\Responses\TextPromptResponse;
use DIJ\Langfuse\PHP\Testing\Responses\GetPromptResponse;

use function Pest\Faker\fake;

it('can use a fake to get a prompt response', function (): void {
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

it('returns a LangfuseDecorator instance from fake', function (): void {
    $manager = Langfuse::fake();

    expect($manager)->toBeInstanceOf(LangfuseDecorator::class);
});
