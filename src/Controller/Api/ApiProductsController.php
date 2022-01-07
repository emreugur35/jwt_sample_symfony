<?php

namespace App\Controller\Api;

use App\Entity\Products;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Repository\ProductsRepository;


/**
 * @Route("/products")
 */
class ApiProductsController extends AbstractController
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @Route("/", name="api_products", methods={"GET"})
     * @param TokenStorageInterface $token
     * @return Response
     */
    public function index(TokenStorageInterface $token, ProductsRepository $productsRepository)
    {
        $user = $token->getToken() ? $token->getToken()->getUser() : null;

        if ($user) :
            $id = $this->getUser()->getId();
            $result = $productsRepository->findByAvailableProducts();

            return new Response($this->serialize($result), 200);
        else :
            return new JsonResponse([
                "code" => 401,
                "status" => "error",
                "message" => "Products does not exist."
            ], 401);
        endif;
    }


 
    protected function serialize($products)
    {
        return $this->serializer->serialize($products, 'json');
    }
}
