<?php
namespace App\Controller;

use App\Service\ChatBot;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class EliteChatBot extends AbstractController
{
    private const DEFAULT_TREAD = "thread_tt3NGc1eoNenmFrQYqgTtbUW";
    private string $assistantId;
    public $client;
    public function __construct(private SerializerInterface $serializer, private ChatBot $bot, string $assistantId)
    {
        $this->assistantId = $assistantId;
    }
    #[Route("/test", name:"test")]
    public function test() {
        return new Response("ok");
    }

    #[Route("/api/chatbot", name:"init_thread", methods: ["GET"])]
    public function initThread(): JsonResponse
    {
        $thread = $this->bot->createThread();
        $data = [
            "thread_id" => $thread["id"],
            "messages" => []
        ];
        return $this->json($data, Response::HTTP_CREATED, []);
    }
    #[Route("/api/chatbot/{threadId}", name:"get_thread", methods: ["GET"])]
    public function getThreadMessage(string $threadId): JsonResponse
    {
        if(!$this->bot->getThread($threadId))
        {
            return $this->json("", Response::HTTP_BAD_REQUEST);
        }
        $messages = $this->bot->getThreadMessages($threadId);
        $data = [
            "thread_id" => $threadId,
            "messages" => $messages
        ];
        return $this->json($data, Response::HTTP_OK);
        //thread_jgkCqFKWTFhdG4tI26edCzQY
        //thread_tt3NGc1eoNenmFrQYqgTtbUW
    }
    #[Route("/api/chatbot/{threadId}", name:"ask_question", methods: ["POST"])]
    public function askQuestion(Request $request, string $threadId): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $answer = $this->bot->askQuestion($this->assistantId, "$threadId", $data["question"]);
        if(!$answer)
        {
            return $this->json("", Response::HTTP_BAD_REQUEST);
        }
        return $this->json($answer, Response::HTTP_OK);
    }
    #[Route("/api/chatbot", name:"ask_question_in_default_thread", methods: ["POST"])]
    public function askQuestionDefaultThread(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $answer = $this->bot->askQuestion($this->assistantId, self::DEFAULT_TREAD, $data["question"]);
        if(!$answer)
        {
            return $this->json("", Response::HTTP_BAD_REQUEST);
        }
        return $this->json($answer, Response::HTTP_OK);
    }
}
