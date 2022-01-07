<?php

namespace App\Controller\Api;

use App\Entity\Orders;
use App\Entity\OrderDetails;
use App\Entity\User;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Repository\OrdersRepository;
use App\Repository\OrderDetailsRepository;
use App\Repository\ProductsRepository;


/**
 * @Route("/order")
 */
class ApiOrdersController extends AbstractController
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }



    /**
     * @Route("/create", name="api_order_new",  methods={"POST"})
     * @param Request $request
     * @param TokenStorageInterface $token
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function create(TokenStorageInterface $token, Request $request, OrderDetailsRepository $orderDetailsRepository, ProductsRepository $ProductsRepository)
    {
        $data = json_decode(
            $request->getContent(),
            true
        );

        $user_id = $this->getUser()->getId();

        try {

            $entityManager = $this->getDoctrine()->getManager();

            $this->is_valid_date(new \DateTime('@' . strtotime($data['ShippingDate'])));

            $order = new Orders();
            $order->setAddress($data['address']);
            $order->setOrderCode($data['orderCode']);
            $order->setShippingDate(new \DateTime('@' . strtotime($data['ShippingDate'])));
            $order->setUserId($user_id);
            $entityManager->persist($order);
            $entityManager->flush();
            $insert_id = $order->getId();

            foreach ($data['orders'] as $value => $order_product) {

                $this->is_valid_product($order_product['product_id'], $ProductsRepository);
                $this->check_in_stock($order_product['product_id'], intval($order_product['quantity']), $ProductsRepository);

                $orderdetails = new OrderDetails();
                $orderdetails->setProductId(intval($order_product['product_id']));
                $orderdetails->setQuantity(intval($order_product['quantity']));
                $orderdetails->setUserId($user_id);
                $orderdetails->setOrderId($insert_id);
                $entityManager->persist($orderdetails);
                $entityManager->flush();
            }
        } catch (\Exception $e) {
            return new JsonResponse(["error" => $e->getMessage()], 500);
        }
        return new JsonResponse(["success" => $data['orderCode'] . " order has been archived."], 200);
    }


    /**
     * @Route("/update/{order_id}", name="api_order_update",  methods={"PUT"})
     * @param Request $request
     * @param TokenStorageInterface $token
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function update(TokenStorageInterface $token, Request $request, OrderDetailsRepository $orderDetailsRepository, OrdersRepository $ordersRepository, ProductsRepository $ProductsRepository, $order_id)
    {
        $data = json_decode(
            $request->getContent(),
            true
        );

        $user_id = $this->getUser()->getId();

        try {

            $entityManager = $this->getDoctrine()->getManager();

            $order = $entityManager->getRepository(Orders::class)->find($order_id);
            $this->is_valid_date(new \DateTime('@' . strtotime($data['ShippingDate'])));

            if (!$order) {
                throw $this->createNotFoundException(
                    'No order found for id ' . $order_id
                );
            }

            $order->setAddress($data['address']);
            $order->setOrderCode($data['orderCode']);
            $order->setShippingDate(new \DateTime('@' . strtotime($data['ShippingDate'])));
            $order->setUserId($user_id);

            $entityManager->flush();

            $orderDetailsRepository->deleteByOrderId($order_id);

            foreach ($data['orders'] as $value => $order_product) {

                $this->is_valid_product($order_product['product_id'], $ProductsRepository);
                $this->check_in_stock($order_product['product_id'], intval($order_product['quantity']), $ProductsRepository);

                $orderdetails = new OrderDetails();
                $orderdetails->setProductId(intval($order_product['product_id']));
                $orderdetails->setQuantity(intval($order_product['quantity']));
                $orderdetails->setUserId($user_id);
                $orderdetails->setOrderId($order_id);
                $entityManager->persist($orderdetails);
                $entityManager->flush();
            }
        } catch (\Exception $e) {
            return new JsonResponse(["error" => $e->getMessage()], 500);
        }
        return new JsonResponse(["success" => $data['orderCode'] . " order has been updated and archived."], 200);
    }


    /**
     * @Route("/{order_id}", name="api_order_detail", methods={"GET"})
     * @param TokenStorageInterface $token
     * @param User $user
     * @return Response
     */
    public function detail(TokenStorageInterface $token, OrderDetailsRepository $orderDetailsRepository, $order_id)
    {

        $user_id = $this->getUser()->getId();

        $result = $orderDetailsRepository->findByOrderId($order_id, $user_id);

        if ($result) :
            return new Response($this->serialize($result), 200);
        else :
            return new JsonResponse([
                "code" => 401,
                "status" => "error",
                "message" => "#" . $order_id . " order info does not exist."
            ], 401);
        endif;
    }

    /**
     * @Route("/", name="api_order", methods={"GET"})
     * @param TokenStorageInterface $token
     * @return Response
     */
    public function index(TokenStorageInterface $token, OrdersRepository $ordersRepository)
    {
        $user = $token->getToken() ? $token->getToken()->getUser() : null;

        if ($user) :
            $id = $this->getUser()->getId();
            $result = $ordersRepository->findByUser($id);

            return new Response($this->serialize($result), 200);
        else :
            return new JsonResponse([
                "code" => 401,
                "status" => "error",
                "message" => "Existing orders not found."
            ], 401);
        endif;
    }


    protected function serialize($array)
    {
        return $this->serializer->serialize($array, 'json');
    }



    protected function is_valid_product($product_id, ProductsRepository $ProductsRepository)
    {

        $result = $ProductsRepository->find($product_id);

        if ($result != null) :
            return true;
        else :
            throw new \Exception('Invalid product ids.');
        endif;
    }

    protected function is_valid_date(\DateTime $date)
    {

        if (new \DateTime() > $date) :
            throw new \Exception('Date is in the past.');
        else :
            return true;
        endif;
    }

    protected function check_in_stock($product_id, $quantity, ProductsRepository $ProductsRepository)
    {
        $result = $ProductsRepository->find($product_id);
        if ($result->getQuantity() > $quantity) :
            return true;
        else :
            throw new \Exception('Some of products not available.');
        endif;
    }
}
