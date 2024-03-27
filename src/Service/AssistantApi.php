<?php
namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Serializer\SerializerInterface;

class AssistantApi extends AbstractController
{
    private string $apiKey;
    private array $header;
    private $client;
    public function __construct(private SerializerInterface $serializer, string $apiKey)
    {
        $this->apiKey = $apiKey;
        $this->client = HttpClient::create([
            'verify_peer' => false,
            'verify_host' => false,
        ]);
        $this->header = [
            "Content-Type" => "application/json",
            "Authorization" => "Bearer " . $this->apiKey,
            "OpenAI-Beta" => "assistants=v1"
        ];
    }
    private function request(string $url, ?array $data = []): ?array
    {
        $jsonData = $this->serializer->serialize($data, "json");
        try {
            $response = $this->client->request("POST", $url, [
                "headers" => $this->header,
                "body" => $jsonData,
            ]);
            return $response->toArray();
        } catch (\Throwable $th) {
            return null;
        }
    }
    public function query(string $url): ?array
    {
        try {
            $response = $this->client->request("GET", $url, [
                "headers" => $this->header,
            ]);
            return $response->toArray();
        } catch (\Throwable $th) {
            return null;
        }
    }
    public function getAssistant(string $assistantId): ?array
    {
        $url = "https://api.openai.com/v1/assistants/". $assistantId;
        return $this->query($url);
    }
    public function getMessagesList(string $threadId, int $limit): ?array
    {
        $url = "https://api.openai.com/v1/threads/". $threadId ."/messages?limit=". $limit;
        return $this->query($url);
    }
    public function createMessage(string $threadId, string $question): ?array
    {
        $url = "https://api.openai.com/v1/threads/". $threadId ."/messages";
        $data = [
            "role" => "user",
            "content" => $question,
        ];
        return $this->request($url, $data);
    }
    public function run(string $threadId, string $assistantId): ?array
    {
        $url = "https://api.openai.com/v1/threads/". $threadId ."/runs";
        $data = [
            "assistant_id" => $assistantId,
        ];
        return $this->request($url, $data);
    }
    public function getRunList(string $threadId, ?int $limit = 20): ?array
    {
        $url = "https://api.openai.com/v1/threads/". $threadId ."/runs?limit=". $limit;
        return $this->query($url);
    }
    public function createThread(): ?array
    {
        $url = "https://api.openai.com/v1/threads";
        return $this->request($url);
    }
    public function getThread(string $threadId): ?array
    {
        $url = "https://api.openai.com/v1/threads/". $threadId;
        return $this->query($url);
    }
}