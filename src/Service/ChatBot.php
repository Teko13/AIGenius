<?php
namespace App\Service;


class ChatBot
{
    public function __construct(private AssistantApi $assistant) {}
    public function askQuestion(string $assistantId, string $threadId, string $question): ?string
    // This methode send an 'user' message to assistant thread and return assistant answer for this quesion;
    // STEP 1: Add new message to assistant thread
    // STEP 2: Run this assistant to answser added message;
    // STEP 3: Check Run status until iit complet
    // STEP 4: Find the assistant last message (answer)
    {
        try {
            $this->assistant->createMessage($threadId, $question);
            $this->assistant->run($threadId, $assistantId);
            $completed = false;
            while(!$completed)
            {
                sleep(1.5);
                $completed = ($this->assistant->getRunList($threadId, 1)["data"][0]["status"] === "completed");
            }
            $message = $this->assistant->getMessagesList($threadId, 1);
            return $message["data"][0]["content"][0]["text"]["value"];
        } catch (\Throwable $th) {
            return null;
        }
    }
    public function createThread(): ?array
    {
        return $this->assistant->createThread();
    }
    public function getThread($threadId): ?array
    {
       return $this->assistant->getThread($threadId);
    }
    public function getThreadMessages(string $threadId, ?int $limit = 20): array
    {
        $response = $this->assistant->getMessagesList($threadId, $limit);
        $messages = [];
        foreach($response["data"] as $message)
        {
            $messages[] = [
                "id_message" => $message["id"],
                "created_ad" => $message["created_at"],
                "role" => $message["role"],
                "content" => $message["content"][0]["text"]["value"],
            ];
        }
        return $messages;
    }
}