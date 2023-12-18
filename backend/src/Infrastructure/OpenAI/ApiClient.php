<?php

namespace App\Infrastructure\OpenAI;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiClient implements ApiClientInterface
{
    private const BASE_URL = 'https://api.openai.com/v1';
    private const ASSISTANTS_ENDPOINT = '/assistants';
    private const THREADS_ENDPOINT = '/threads';
    private const MESSAGES_ENDPOINT = '/threads/%s/messages';
    private const RUNS_ENDPOINT = '/threads/%s/runs';
    private const ASSISTANT_TOOLS = [
        ['type' => 'retrieval'],
        ['type' => 'function',
         'function' => [
            'name' => 'generateQuestions',
            'description' => "Generate question base on Create new problems based on user's skill and performance, function will attach question to user's assessment,
            if everything goes fine it will return bool true, otherwise bool false. When received bool false rerun function with new argument.
            If bool true returned complete run without additional messages.",
             'parameters' => [
                'type' => 'object',
                'properties' => [
                    'content' => ['type' => 'string', 'description' => 'A question itself.'],
                    'options' => ['type' => 'array', 'description' => 'If assessmentTypeName is "quiz" or "multiple_choice" generate answer options', 'items' => ['type' => 'string', 'maxItems' => 4]],
                    'correctAnswer' => ['type' => 'string', 'description' => 'An outline of correct answer for question or problem.'],
                ],
                 'required' => ['content', 'correctAnswer'],
             ],
         ]],
        ['type' => 'function',
         'function' => [
             'name' => 'handleUserInput',
             'description' => "Base on parameters and provided answer handle it by providing as argument information if the answer is correct
            and explain if it's not, then trigger Adjust Difficulty function, 
            if everything goes fine it will return bool true, otherwise bool false. When received bool false rerun function with new argument.
            If bool true returned complete run without additional messages.",
             'parameters' => [
                 'type' => 'object',
                 'properties' => [
                     'userAnswer' => ['type' => 'string', 'description' => 'User\'s answer'],
                     'isCorrect' => ['type' => 'boolean', 'description' => 'Decide if provided answer by user is correct.'],
                     'explanation' => ['type' => 'string', 'description' => 'Explanation why correct answer is correct.'],
                 ],
                 'required' => ['userAnswer', 'isCorrect', 'explanation'],
             ],
         ],
        ],
        ['type' => 'function',
         'function' => [
             'name' => 'adjustDifficulty',
             'description' => 'Modify the complexity level of upcoming questions, function will update current difficulty in user\'s assessment, 
            if everything goes fine it will return bool true, otherwise bool false. When received bool false rerun function with new argument.
            If bool true returned complete run without additional messages.',
             'parameters' => [
                 'type' => 'object',
                 'properties' => [
                     'adjustedDifficulty' => ['type' => 'string', 'enum' => ['beginner', 'intermediate', 'advanced']],
                 ],
                 'required' => ['adjustedDifficulty'],
             ],
         ],
        ],
        ['type' => 'function',
         'function' => [
             'name' => 'feedback',
             'description' => 'Call Retrieve Assessment function to get access to full user\'s assessment,
            base on that provide feedback to user regarding to his performance during the assessment. Function will update
            assessment\'s feedback and sign it as completed,
            if everything goes fine it will return bool true, otherwise bool false. When received bool false rerun function with new argument.
            If bool true returned complete run without additional messages.',
             'parameters' => [
                 'type' => 'object',
                 'properties' => [
                     'feedback' => ['type' => 'string'],
                 ],
                 'required' => ['feedback'],
             ],
         ],
        ],
        ['type' => 'function',
         'function' => [
             'name' => 'retrieveAssessment',
             'description' => 'Fetch user\'s completed assessment, function as output will provide assessment Object',
             'parameters' => [
                 'type' => 'object',
                 'properties' => [
                    'assessmentId' => ['type' => 'string', 'minLength' => 8],
                 ],
             ],
             'required' => ['assessmentId'],
         ],
        ],
    ];

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly ParameterBagInterface $parameterBag,
    ) {
    }

    protected function getHeaders(): array
    {
        return [
            'OpenAI-Beta' => 'assistants=v1',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$this->parameterBag->get('app.open_ai.secret'),
        ];
    }

    public function createAssistant(string $name, string $instructions): array
    {
        return $this->client->request('POST', self::BASE_URL.self::ASSISTANTS_ENDPOINT, [
            'headers' => $this->getHeaders(),
            'json' => [
                'model' => $this->parameterBag->get('app.open_ai.model'),
                'name' => $name,
                'instructions' => $instructions,
                'tools' => self::ASSISTANT_TOOLS,
            ],
        ])->toArray();
    }

    public function createThread(): array
    {
        return $this->client->request(
            'POST',
            self::BASE_URL.self::THREADS_ENDPOINT, [
                'headers' => $this->getHeaders(),
        ])->toArray();
    }

    public function addMessage(string $threadId, string $message): array
    {
        return $this->client->request(
            'POST',
            sprintf(self::BASE_URL.self::MESSAGES_ENDPOINT, $threadId), [
                'headers' => $this->getHeaders(),
                'json' => [
                    'role' => 'user',
                    'content' => $message,
                ],
            ])->toArray();
    }

    public function getMessages(string $threadId): array
    {
        return $this->client->request(
            'GET',
            sprintf(self::BASE_URL.self::MESSAGES_ENDPOINT, $threadId), [
            'headers' => $this->getHeaders(),
        ])->toArray();
    }

    public function runAssistant(string $threadId, string $assistantId): array
    {
        return $this->client->request(
            'POST',
            sprintf(self::BASE_URL.self::RUNS_ENDPOINT, $threadId), [
                'headers' => $this->getHeaders(),
                'json' => [
                    'assistant_id' => $assistantId,
                ],
        ])->toArray();
    }

    public function getRunStatus(string $threadId, string $runId): array
    {
        return $this->client->request(
            'GET',
            sprintf(self::BASE_URL.self::RUNS_ENDPOINT.'/%s', $threadId, $runId), [
            'headers' => $this->getHeaders(),
        ])->toArray();
    }

    public function submitToolOutputs(string $threadId, string $runId, array $toolOutputs): array
    {
        return $this->client->request(
            'POST',
            sprintf(self::BASE_URL.self::RUNS_ENDPOINT.'/%s/submit_tool_outputs', $threadId, $runId), [
            'headers' => $this->getHeaders(),
            'json' => [
                'tool_outputs' => $toolOutputs,
            ],
        ])->toArray();
    }

    public function deleteAssistant(string $assistantId): array
    {
        return $this->client->request(
            'DELETE',
            self::BASE_URL.self::ASSISTANTS_ENDPOINT."/$assistantId", [
                'headers' => $this->getHeaders(),
        ])->toArray();
    }
}
