<?php

namespace App\Controller\Api;

use App\Entity\User;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/user")
 */
class ApiUserController extends AbstractController
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @Route("/{id}", name="api_user_detail", methods={"GET"})
     * @param TokenStorageInterface $token
     * @return Response
     */
    public function detail(TokenStorageInterface $token)
    {
        $user = $token->getToken() ? $token->getToken()->getUser() : null;

        if($user):
            return new Response($this->serialize($user->id), 200);
        else:
            return new JsonResponse([
                "code" => 401,
                "status" => "error",
                "message" => "User does not exist or you do not have access to this users information."
            ], 401);
        endif;
    }

    /**
     * Serializer 
     *
     * @param User $user
     * @return mixed
     */
    protected function serialize(User $user)
    {
        return $this->serializer->serialize($user, 'json');
    }
}